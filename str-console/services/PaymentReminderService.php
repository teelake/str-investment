<?php

declare(strict_types=1);

/**
 * Runs daily (cron): 2 days before anchor and on anchor day, when enabled and mail is configured.
 */
final class PaymentReminderService
{
    private const KEYS = [
        'enabled' => 'payment_reminders.enabled',
        'days_before' => 'payment_reminders.days_before',
        'send_on_due' => 'payment_reminders.send_on_due_day',
        'amount_mode' => 'payment_reminders.amount_mode',
        'org_name' => 'payment_reminders.organization_name',
        'currency' => 'payment_reminders.currency_symbol',
        'subject_advance' => 'payment_reminders.subject_advance',
        'subject_due' => 'payment_reminders.subject_due',
        'body_advance' => 'payment_reminders.body_advance',
        'body_due' => 'payment_reminders.body_due',
    ];

    public static function defaults(): array
    {
        return [
            'payment_reminders.enabled' => '0',
            'payment_reminders.days_before' => '2',
            'payment_reminders.send_on_due_day' => '1',
            'payment_reminders.amount_mode' => 'installment_when_set',
            'payment_reminders.organization_name' => 'Our team',
            'payment_reminders.currency_symbol' => '₦',
            'payment_reminders.subject_advance' => 'Reminder: upcoming payment — {loan_title}',
            'payment_reminders.subject_due' => 'Payment due today — {loan_title}',
            'payment_reminders.body_advance' => "Dear {customer_name},\n\n"
                . "This is a friendly reminder about your loan \"{loan_title}\" (reference #{loan_id}).\n\n"
                . "Next payment date: {due_date}\n"
                . "Amount for this payment (as agreed or ledger): {currency_symbol}{amount_due_this_period}\n"
                . "Total balance still owing: {currency_symbol}{outstanding_balance}\n\n"
                . "{reminder_note}\n\n"
                . "— {organization_name}\n",
            'payment_reminders.body_due' => "Dear {customer_name},\n\n"
                . "Your payment for loan \"{loan_title}\" (reference #{loan_id}) is due today ({due_date}).\n\n"
                . "Amount for this payment: {currency_symbol}{amount_due_this_period}\n"
                . "Total balance still owing: {currency_symbol}{outstanding_balance}\n\n"
                . "— {organization_name}\n",
        ];
    }

    public static function amountMode(): string
    {
        $raw = strtolower(trim((string) (ConsoleSettingRepository::get(self::KEYS['amount_mode']) ?? '')));
        if ($raw === 'ledger_only') {
            return 'ledger_only';
        }
        return 'installment_when_set';
    }

    public static function getTemplate(string $keySuffix): string
    {
        $defaults = self::defaults();
        $fullKey = 'payment_reminders.' . $keySuffix;
        $raw = ConsoleSettingRepository::get($fullKey);
        if ($raw !== null && trim($raw) !== '') {
            return $raw;
        }
        return (string) ($defaults[$fullKey] ?? '');
    }

    /**
     * @return array{sent: int, skipped_no_email: int, skipped_past_due: int, skipped_mail: int, errors: int}
     */
    public static function runForDate(string $todayYmd): array
    {
        $sent = 0;
        $skippedNoEmail = 0;
        $skippedPast = 0;
        $skippedMail = 0;
        $errors = 0;

        if (!PolicyService::paymentRemindersEnabled()) {
            return [
                'sent' => 0,
                'skipped_no_email' => 0,
                'skipped_past_due' => 0,
                'skipped_mail' => 0,
                'errors' => 0,
            ];
        }
        if (!PaymentReminderMailer::mailFromConfigured()) {
            return [
                'sent' => 0,
                'skipped_no_email' => 0,
                'skipped_past_due' => 0,
                'skipped_mail' => 1,
                'errors' => 0,
            ];
        }

        $today = DateTimeImmutable::createFromFormat('Y-m-d', $todayYmd);
        if ($today === false || $today->format('Y-m-d') !== $todayYmd) {
            throw new InvalidArgumentException('Invalid run date.');
        }

        $daysBefore = PolicyService::paymentReminderDaysBefore();
        $sendOnDue = PolicyService::paymentRemindersSendOnDueDay();
        $mode = self::amountMode();
        $org = trim((string) (ConsoleSettingRepository::get(self::KEYS['org_name']) ?? ''));
        if ($org === '') {
            $org = trim((string) (self::defaults()['payment_reminders.organization_name'] ?? 'Our team'));
        }
        $currency = trim((string) (ConsoleSettingRepository::get(self::KEYS['currency']) ?? ''));
        if ($currency === '') {
            $currency = '₦';
        }

        $repo = new LoanRepository();
        $log = new PaymentReminderLogRepository();
        $ids = $repo->listActiveDisbursedLoanIds();

        foreach ($ids as $loanId) {
            try {
                $loan = $repo->find($loanId);
                if (!is_array($loan)) {
                    continue;
                }
                $proj = LoanLedgerService::projectNextPaymentReminder($loanId);
                if ($proj === null) {
                    continue;
                }
                $anchor = $proj['next_due_ymd'];
                $anchorDt = DateTimeImmutable::createFromFormat('Y-m-d', $anchor);
                if ($anchorDt === false || $anchorDt->format('Y-m-d') !== $anchor) {
                    continue;
                }
                if ($anchorDt < $today) {
                    ++$skippedPast;
                    continue;
                }

                $email = trim((string) ($loan['customer_email'] ?? ''));
                if ($email === '') {
                    ++$skippedNoEmail;
                    continue;
                }

                $ledgerDue = (float) $proj['ledger_amount_due'];
                $outstanding = (float) $proj['outstanding'];
                $installment = $proj['reminder_installment_amount'];

                $amountLine = $ledgerDue;
                if ($mode === 'installment_when_set' && $installment !== null) {
                    $amountLine = min($installment, $ledgerDue);
                }

                $loanTitle = trim((string) ($loan['loan_product_name'] ?? 'Loan'));
                if ($loanTitle === '') {
                    $loanTitle = 'Loan';
                }
                $custName = trim((string) ($loan['customer_name'] ?? 'Customer'));

                $basePlaceholders = [
                    '{customer_name}' => $custName,
                    '{loan_title}' => $loanTitle,
                    '{loan_id}' => (string) (int) ($loan['id'] ?? 0),
                    '{due_date}' => $anchor,
                    '{amount_due_this_period}' => self::fmtMoney($amountLine),
                    '{ledger_amount_due}' => self::fmtMoney($ledgerDue),
                    '{outstanding_balance}' => self::fmtMoney($outstanding),
                    '{currency_symbol}' => $currency,
                    '{organization_name}' => $org,
                ];

                if ($sendOnDue && $today->format('Y-m-d') === $anchor) {
                    $placeholders = $basePlaceholders + [
                        '{reminder_note}' => 'This payment is due today.',
                        '{days_until_due}' => '0',
                    ];
                    $subj = self::applyTpl(self::getTemplate('subject_due'), $placeholders);
                    $body = self::applyTpl(self::getTemplate('body_due'), $placeholders);
                    if ($log->tryInsert($loanId, 'due', $anchor, $email)) {
                        if (PaymentReminderMailer::send($email, $subj, $body)) {
                            ++$sent;
                        } else {
                            ++$errors;
                        }
                    }
                    continue;
                }

                if ($daysBefore <= 0) {
                    continue;
                }
                $advanceSend = $anchorDt->modify('-' . $daysBefore . ' days');
                if ($today->format('Y-m-d') !== $advanceSend->format('Y-m-d')) {
                    continue;
                }

                $daysUntil = max(
                    0,
                    (int) floor(($anchorDt->getTimestamp() - $today->getTimestamp()) / 86400)
                );
                $note = match (true) {
                    $daysUntil <= 0 => '',
                    $daysUntil === 1 => 'Your payment is due tomorrow.',
                    default => 'Your payment is due in ' . $daysUntil . ' days.',
                };
                $placeholders = $basePlaceholders + [
                    '{reminder_note}' => $note,
                    '{days_until_due}' => (string) $daysUntil,
                ];
                $subj = self::applyTpl(self::getTemplate('subject_advance'), $placeholders);
                $body = self::applyTpl(self::getTemplate('body_advance'), $placeholders);
                if ($log->tryInsert($loanId, 'advance', $anchor, $email)) {
                    if (PaymentReminderMailer::send($email, $subj, $body)) {
                        ++$sent;
                    } else {
                        ++$errors;
                    }
                }
            } catch (Throwable) {
                ++$errors;
            }
        }

        return [
            'sent' => $sent,
            'skipped_no_email' => $skippedNoEmail,
            'skipped_past_due' => $skippedPast,
            'skipped_mail' => $skippedMail,
            'errors' => $errors,
        ];
    }

    /**
     * @param array<string, string> $ph
     */
    private static function applyTpl(string $tpl, array $ph): string
    {
        return strtr($tpl, $ph);
    }

    private static function fmtMoney(float $n): string
    {
        return number_format(LoanLedgerService::money($n), 2, '.', ',');
    }
}
