<?php

declare(strict_types=1);

final class SettingsPaymentRemindersController extends BaseController
{
    public function index(): void
    {
        $defs = PaymentReminderService::defaults();
        $g = static function (string $k) use ($defs): string {
            $v = ConsoleSettingRepository::get($k);

            return $v !== null && trim($v) !== '' ? $v : (string) ($defs[$k] ?? '');
        };

        $this->render('settings/payment-reminders', [
            'enabled' => PolicyService::paymentRemindersEnabled(),
            'daysBefore' => PolicyService::paymentReminderDaysBefore(),
            'sendOnDue' => PolicyService::paymentRemindersSendOnDueDay(),
            'amountMode' => PaymentReminderService::amountMode(),
            'organizationName' => $g('payment_reminders.organization_name'),
            'currencySymbol' => $g('payment_reminders.currency_symbol'),
            'subjectAdvance' => $g('payment_reminders.subject_advance'),
            'subjectDue' => $g('payment_reminders.subject_due'),
            'bodyAdvance' => $g('payment_reminders.body_advance'),
            'bodyDue' => $g('payment_reminders.body_due'),
            'mailOk' => PaymentReminderMailer::mailFromConfigured(),
            'flash' => Request::query('flash'),
            'error' => Request::query('error'),
        ]);
    }

    public function save(): void
    {
        $this->requirePostedCsrf('/settings/payment-reminders');
        if (!str_console_database_ready()) {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $enabled = isset($_POST['enabled']) && (string) $_POST['enabled'] === '1';
        $daysRaw = trim((string) ($_POST['days_before'] ?? '2'));
        $days = is_numeric($daysRaw) ? max(0, min(60, (int) $daysRaw)) : 2;
        $sendOnDue = isset($_POST['send_on_due']) && (string) $_POST['send_on_due'] === '1';
        $mode = trim((string) ($_POST['amount_mode'] ?? ''));
        $amountMode = $mode === 'ledger_only' ? 'ledger_only' : 'installment_when_set';

        $org = trim(str_replace(["\0", "\r"], '', (string) ($_POST['organization_name'] ?? '')));
        if (mb_strlen($org) > 190) {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Organization name is too long.'));
            return;
        }
        $cur = trim((string) ($_POST['currency_symbol'] ?? '₦'));
        if ($cur === '' || mb_strlen($cur) > 8) {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Enter a short currency symbol (e.g. ₦).'));
            return;
        }

        $subjA = trim(str_replace("\0", '', (string) ($_POST['subject_advance'] ?? '')));
        $subjD = trim(str_replace("\0", '', (string) ($_POST['subject_due'] ?? '')));
        $bodyA = str_replace("\0", '', (string) ($_POST['body_advance'] ?? ''));
        $bodyD = str_replace("\0", '', (string) ($_POST['body_due'] ?? ''));
        if ($subjA === '' || $subjD === '' || trim($bodyA) === '' || trim($bodyD) === '') {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Fill in all subjects and email bodies (or reset defaults).'));
            return;
        }
        if (mb_strlen($subjA) > 250 || mb_strlen($subjD) > 250) {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Subject lines are too long.'));
            return;
        }
        if (mb_strlen($bodyA) > 12000 || mb_strlen($bodyD) > 12000) {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Email text is too long.'));
            return;
        }

        try {
            $uid = ConsoleAuth::userId();
            ConsoleSettingRepository::set('payment_reminders.enabled', $enabled ? '1' : '0', $uid);
            ConsoleSettingRepository::set('payment_reminders.days_before', (string) $days, $uid);
            ConsoleSettingRepository::set('payment_reminders.send_on_due_day', $sendOnDue ? '1' : '0', $uid);
            ConsoleSettingRepository::set('payment_reminders.amount_mode', $amountMode, $uid);
            ConsoleSettingRepository::set('payment_reminders.organization_name', $org !== '' ? $org : 'Our team', $uid);
            ConsoleSettingRepository::set('payment_reminders.currency_symbol', $cur, $uid);
            ConsoleSettingRepository::set('payment_reminders.subject_advance', $subjA, $uid);
            ConsoleSettingRepository::set('payment_reminders.subject_due', $subjD, $uid);
            ConsoleSettingRepository::set('payment_reminders.body_advance', $bodyA, $uid);
            ConsoleSettingRepository::set('payment_reminders.body_due', $bodyD, $uid);

            AuditLogger::log($uid, 'settings.payment_reminders.update', 'console_settings', null, [
                'enabled' => $enabled,
                'days_before' => $days,
                'send_on_due_day' => $sendOnDue,
                'amount_mode' => $amountMode,
            ]);
            $this->redirect('/settings/payment-reminders?flash=' . rawurlencode('Payment reminder settings saved.'));
        } catch (Throwable) {
            $this->redirect('/settings/payment-reminders?error=' . rawurlencode('Could not save settings.'));
        }
    }
}
