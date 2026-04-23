<?php

declare(strict_types=1);

/**
 * Rolling-balance loan ledger (booked rate on the loan row).
 *
 * Booked rate_percent is the **monthly** rate (client MVP): each interest **charge** applies that full rate once per 30-day step (when due).
 * **Reducing balance** (`interest_basis`): charge = rate × **current opening** (rolled balance).
 * **Flat monthly** (`flat_monthly`): charge = rate × **original booked principal** (`loans.principal_amount`), regardless of how much has been paid down.
 * Interest **timing**: **30-day periods** from **disbursement** (day 0–29 = period 0, day 30–59 = period 1, …).
 * No interest on the initial disbursement line—only principal moves into the balance until the first event in a later period.
 *
 * Model (contract wording may differ — align product/legal before go-live):
 * - First line after disburse: opening = principal, **zero** interest, closing = principal (interest starts from period 1 onward).
 * - Payment lines: opening = previous closing. Compare period index of payment date vs last line’s period_date (same disburse anchor).
 *   Same period → no new interest. Later period → add one monthly-rate charge on opening, then apply payment.
 *   Payment date must be on or after the last line’s period_date.
 * - Periodic accrual (optional): each line adds one charge; next line’s period_date = previous + 30 days.
 * - Accrual stops at disbursed_at + period_months (term) or the as-of date, whichever is earlier.
 *
 * Triggers: disburse creates line 1; payments and periodic accrual must not run on GET — use POST or CLI/cron only.
 */
final class LoanLedgerService
{
    /** Interest periods are counted in whole days from disbursement; each period is this many days (MVP / client rule). */
    public const INTEREST_PERIOD_DAYS = 30;

    public static function money(float $n): float
    {
        return round($n, 2);
    }

    /**
     * One interest charge for a ledger step (after the first 30-day period boundary), before payment is applied.
     */
    public static function periodInterestCharge(
        float $opening,
        float $originalPrincipal,
        float $ratePercent,
        string $interestBasis
    ): float {
        if ($interestBasis === LoanInterestBasis::FLAT_MONTHLY) {
            return self::money($originalPrincipal * ($ratePercent / 100.0));
        }

        return self::money($opening * ($ratePercent / 100.0));
    }

    /**
     * 0-based period index from disbursement date (inclusive day 0). Returns -1 if event is before disburse.
     */
    public static function interestPeriodIndex(string $disburseYmd, string $eventYmd): int
    {
        $d0 = DateTimeImmutable::createFromFormat('Y-m-d', substr($disburseYmd, 0, 10));
        $d1 = DateTimeImmutable::createFromFormat('Y-m-d', substr($eventYmd, 0, 10));
        if ($d0 === false || $d1 === false || $d0->format('Y-m-d') !== substr($disburseYmd, 0, 10) || $d1->format('Y-m-d') !== substr($eventYmd, 0, 10)) {
            return 0;
        }
        if ($d1 < $d0) {
            return -1;
        }

        return intdiv((int) $d0->diff($d1)->days, self::INTEREST_PERIOD_DAYS);
    }

    /**
     * Upper bound for a new payment line (payment date and disbursement anchor).
     */
    public static function maxPaymentForNextLine(
        float $opening,
        float $ratePercent,
        string $paymentDateYmd,
        string $lastLinePeriodYmd,
        string $disburseYmd,
        string $interestBasis,
        float $originalPrincipal
    ): float {
        $lastPd = substr(trim($lastLinePeriodYmd), 0, 10);
        $disb = substr(trim($disburseYmd), 0, 10);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $lastPd) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $disb)) {
            $interest = self::periodInterestCharge($opening, $originalPrincipal, $ratePercent, $interestBasis);

            return self::money($opening + $interest);
        }
        $lastIdx = self::interestPeriodIndex($disb, $lastPd);
        $payIdx = self::interestPeriodIndex($disb, $paymentDateYmd);
        if ($payIdx < $lastIdx) {
            return self::money($opening);
        }
        if ($payIdx === $lastIdx) {
            return self::money($opening);
        }
        $interest = self::periodInterestCharge($opening, $originalPrincipal, $ratePercent, $interestBasis);

        return self::money($opening + $interest);
    }

    /**
     * Atomically mark loan active, set disbursed_at, optional disbursement_funds_on, and create the first ledger line (principal; no interest until first 30-day period).
     *
     * @param string|null $fundsDisbursedOnYmd When funds were actually released, if the client uses a different date from the book/interest (ledger) value date.
     */
    public static function completeDisbursement(int $loanId, string $periodDateYmd, ?string $fundsDisbursedOnYmd = null): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $sel = $pdo->prepare(
                'SELECT id, created_at FROM loans WHERE id = :id AND status = \'approved\' FOR UPDATE'
            );
            $sel->execute([':id' => $loanId]);
            $row = $sel->fetch();
            if (!is_array($row)) {
                $pdo->rollBack();
                throw new RuntimeException('Loan is not approved for disbursement.');
            }
            if (!InputValidate::loanDisburseDateOk($periodDateYmd, (string) ($row['created_at'] ?? ''))) {
                $pdo->rollBack();
                throw new RuntimeException('Disbursement date must be a valid value between ' . InputValidate::LOAN_EVENT_DATE_MIN . ' and ' . InputValidate::loanDisburseDateMaxYmd() . '.');
            }
            if ($fundsDisbursedOnYmd !== null && $fundsDisbursedOnYmd !== '' && !InputValidate::loanDisburseDateOk($fundsDisbursedOnYmd, (string) ($row['created_at'] ?? ''))) {
                $pdo->rollBack();
                throw new RuntimeException('The optional funds-released date must be a valid value between ' . InputValidate::LOAN_EVENT_DATE_MIN . ' and ' . InputValidate::loanDisburseDateMaxYmd() . '.');
            }
            $fundsYmd = ($fundsDisbursedOnYmd !== null && $fundsDisbursedOnYmd !== '') ? $fundsDisbursedOnYmd : null;

            $u = $pdo->prepare(
                'UPDATE loans SET status = \'active\', disbursed_at = :d, disbursement_funds_on = :f, updated_at = NOW()
                 WHERE id = :id AND status = \'approved\''
            );
            $u->execute([':d' => $periodDateYmd, ':f' => $fundsYmd, ':id' => $loanId]);
            if ($u->rowCount() === 0) {
                $pdo->rollBack();
                throw new RuntimeException('Loan is not approved for disbursement.');
            }

            $stmt = $pdo->prepare('SELECT principal_amount, rate_percent FROM loans WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $loanId]);
            $loan = $stmt->fetch();
            if (!is_array($loan)) {
                $pdo->rollBack();
                throw new RuntimeException('Loan not found.');
            }

            $principal = (float) $loan['principal_amount'];
            $rate = (float) $loan['rate_percent'];
            $interest = 0.0;
            $due = self::money($principal);
            $closing = $due;

            $ins = $pdo->prepare(
                'INSERT INTO loan_ledger_lines (loan_id, line_no, period_date, opening_balance, rate_percent, interest_amount, amount_due, payment_date, payment_amount, closing_balance, created_at)
                 VALUES (:lid, 1, :pd, :ob, :rp, :int, :due, NULL, NULL, :cb, NOW())'
            );
            $ins->execute([
                ':lid' => $loanId,
                ':pd' => $periodDateYmd,
                ':ob' => $principal,
                ':rp' => $rate,
                ':int' => $interest,
                ':due' => $due,
                ':cb' => $closing,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Append accrual lines every 30 days from the last line: interest on the last closing, no payment, closing = amount due.
     * Stops when the next period date is after $asOfDateYmd, after the loan term (disbursed_at + period_months), or when outstanding <= 0.
     *
     * @return int Number of new ledger lines inserted
     */
    public static function runPeriodicAccrualThrough(int $loanId, string $asOfDateYmd): int
    {
        if (!PolicyService::ledgerAutoAccrue()) {
            return 0;
        }

        $asOf = DateTimeImmutable::createFromFormat('Y-m-d', $asOfDateYmd);
        if ($asOf === false || $asOf->format('Y-m-d') !== $asOfDateYmd) {
            throw new InvalidArgumentException('Invalid as-of date.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT id, status, rate_percent, principal_amount, interest_basis, period_months, disbursed_at, created_at
                 FROM loans WHERE id = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $loanId]);
            $loan = $stmt->fetch();
            if (!is_array($loan) || ($loan['status'] ?? '') !== 'active') {
                $pdo->rollBack();
                return 0;
            }
            $disbursedRaw = $loan['disbursed_at'] ?? null;
            if ($disbursedRaw === null || $disbursedRaw === '') {
                $pdo->rollBack();
                return 0;
            }
            $disbStr = substr((string) $disbursedRaw, 0, 10);
            $disbursed = DateTimeImmutable::createFromFormat('Y-m-d', $disbStr);
            if ($disbursed === false || $disbursed->format('Y-m-d') !== $disbStr) {
                $pdo->rollBack();
                return 0;
            }
            if (!InputValidate::loanPostDisburseDateOk($asOfDateYmd, $disbStr, (string) ($loan['created_at'] ?? ''))) {
                $pdo->rollBack();
                return 0;
            }

            $periodMonths = max(1, (int) ($loan['period_months'] ?? 1));
            $termEnd = $disbursed->modify('+' . $periodMonths . ' months');
            $rateLoan = (float) $loan['rate_percent'];
            $originalPrincipal = (float) ($loan['principal_amount'] ?? 0);
            $basis = (string) ($loan['interest_basis'] ?? LoanInterestBasis::REDUCING_BALANCE);
            if (!in_array($basis, LoanInterestBasis::all(), true)) {
                $basis = LoanInterestBasis::REDUCING_BALANCE;
            }

            $ledger = new LoanLedgerRepository();
            $added = 0;

            while (true) {
                $last = $ledger->lastLine($loanId);
                if ($last === null) {
                    break;
                }
                $opening = (float) $last['closing_balance'];
                if ($opening <= 0) {
                    break;
                }

                $lastPdStr = substr((string) ($last['period_date'] ?? ''), 0, 10);
                $lastPd = DateTimeImmutable::createFromFormat('Y-m-d', $lastPdStr);
                if ($lastPd === false || $lastPd->format('Y-m-d') !== $lastPdStr) {
                    break;
                }

                $nextPd = $lastPd->modify('+' . self::INTEREST_PERIOD_DAYS . ' days');
                if ($nextPd->format('Y-m-d') > $asOf->format('Y-m-d')) {
                    break;
                }
                if ($nextPd > $termEnd) {
                    break;
                }

                $lineNo = $ledger->nextLineNo($loanId);
                $rate = $rateLoan;
                $interest = self::periodInterestCharge($opening, $originalPrincipal, $rate, $basis);
                $due = self::money($opening + $interest);
                $closing = $due;

                $ledger->insertLine(
                    $loanId,
                    $lineNo,
                    $nextPd->format('Y-m-d'),
                    $opening,
                    $rate,
                    $interest,
                    $due,
                    null,
                    null,
                    $closing
                );
                ++$added;
            }

            $pdo->commit();
            return $added;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Batch accrual for automation (e.g. daily cron). Respects ledger.auto_accrue policy.
     *
     * @return array{loans_seen: int, lines_added: int}
     */
    public static function runPeriodicAccrualAllActiveLoans(string $asOfDateYmd): array
    {
        if (!PolicyService::ledgerAutoAccrue()) {
            return ['loans_seen' => 0, 'lines_added' => 0];
        }

        $repo = new LoanRepository();
        $ids = $repo->listActiveDisbursedLoanIds();
        $linesAdded = 0;
        foreach ($ids as $id) {
            try {
                $linesAdded += self::runPeriodicAccrualThrough($id, $asOfDateYmd);
            } catch (Throwable) {
                // continue other loans
            }
        }

        return ['loans_seen' => count($ids), 'lines_added' => $linesAdded];
    }

    public static function applyPayment(int $loanId, float $amount, string $paymentDateYmd): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be positive.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT id, status, rate_percent, principal_amount, interest_basis, disbursed_at, created_at FROM loans WHERE id = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $loanId]);
            $loan = $stmt->fetch();
            if (!is_array($loan) || ($loan['status'] ?? '') !== 'active') {
                $pdo->rollBack();
                throw new RuntimeException('Loan is not active.');
            }
            if (!InputValidate::loanPostDisburseDateOk($paymentDateYmd, (string) ($loan['disbursed_at'] ?? ''), (string) ($loan['created_at'] ?? ''))) {
                $pdo->rollBack();
                throw new RuntimeException('Payment date must be on or after the loan booking and disbursement dates, and not after today.');
            }

            $rate = (float) $loan['rate_percent'];
            $originalPrincipal = (float) ($loan['principal_amount'] ?? 0);
            $basis = (string) ($loan['interest_basis'] ?? LoanInterestBasis::REDUCING_BALANCE);
            if (!in_array($basis, LoanInterestBasis::all(), true)) {
                $basis = LoanInterestBasis::REDUCING_BALANCE;
            }
            $ledger = new LoanLedgerRepository();
            $last = $ledger->lastLine($loanId);
            if ($last === null) {
                $pdo->rollBack();
                throw new RuntimeException('Loan has no ledger; disburse first.');
            }

            $opening = (float) $last['closing_balance'];
            if ($opening <= 0) {
                $pdo->rollBack();
                throw new RuntimeException('Loan is already fully paid.');
            }

            $lastPd = substr((string) ($last['period_date'] ?? ''), 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $lastPd)) {
                $pdo->rollBack();
                throw new RuntimeException('Last ledger line has an invalid period date.');
            }
            if ($paymentDateYmd < $lastPd) {
                $pdo->rollBack();
                throw new RuntimeException(
                    'Payment date cannot be before the last ledger line date (' . $lastPd . ').'
                );
            }

            $disbStr = substr((string) ($loan['disbursed_at'] ?? ''), 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $disbStr)) {
                $pdo->rollBack();
                throw new RuntimeException('Loan has no valid disbursement date.');
            }
            $lastIdx = self::interestPeriodIndex($disbStr, $lastPd);
            $payIdx = self::interestPeriodIndex($disbStr, $paymentDateYmd);
            if ($payIdx < $lastIdx) {
                $pdo->rollBack();
                throw new RuntimeException(
                    'Payment falls in a 30-day interest period before the last ledger line (' . $lastPd . ').'
                );
            }

            $lineNo = $ledger->nextLineNo($loanId);
            if ($payIdx === $lastIdx) {
                $interest = 0.0;
            } else {
                $interest = self::periodInterestCharge($opening, $originalPrincipal, $rate, $basis);
            }
            $amountDue = self::money($opening + $interest);
            $payIn = self::money($amount);
            if ($payIn > $amountDue) {
                $pdo->rollBack();
                throw new RuntimeException(
                    'Payment cannot exceed the amount due for this step (balance'
                    . ($interest > 0 ? ' plus one month of interest' : '')
                    . '): '
                    . number_format($amountDue, 2, '.', '')
                    . '.'
                );
            }
            $pay = $payIn;
            $closing = self::money($amountDue - $pay);

            $ledger->insertLine(
                $loanId,
                $lineNo,
                $paymentDateYmd,
                $opening,
                $rate,
                $interest,
                $amountDue,
                $paymentDateYmd,
                $pay,
                $closing
            );

            if ($closing <= 0) {
                $u = $pdo->prepare('UPDATE loans SET status = :st, closed_at = NOW(), updated_at = NOW() WHERE id = :id');
                $u->execute([':st' => 'closed', ':id' => $loanId]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Outstanding balance = closing balance of last ledger line, or principal if no lines yet (should not happen for active).
     */
    public static function outstandingForLoan(int $loanId): float
    {
        $ledger = new LoanLedgerRepository();
        $last = $ledger->lastLine($loanId);
        if ($last === null) {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare('SELECT principal_amount FROM loans WHERE id = :id');
            $stmt->execute([':id' => $loanId]);
            $row = $stmt->fetch();
            return $row ? (float) $row['principal_amount'] : 0.0;
        }
        return (float) $last['closing_balance'];
    }

    /**
     * Next payment anchor: last line date + 30 days while within the booked term, otherwise loan end date (maturity).
     * "Amount due on that date" matches {@see maxPaymentForNextLine} as if the borrower paid on that calendar day.
     *
     * @return array{
     *   next_due_ymd: string,
     *   ledger_amount_due: float,
     *   outstanding: float,
     *   reminder_installment_amount: float|null
     * }|null
     */
    public static function projectNextPaymentReminder(int $loanId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, status, rate_percent, principal_amount, interest_basis, period_months, disbursed_at, reminder_installment_amount
             FROM loans WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $loanId]);
        $loan = $stmt->fetch();
        if (!is_array($loan) || ($loan['status'] ?? '') !== 'active') {
            return null;
        }
        $disbursedRaw = $loan['disbursed_at'] ?? null;
        if ($disbursedRaw === null || $disbursedRaw === '') {
            return null;
        }
        $disbStr = substr((string) $disbursedRaw, 0, 10);
        $disbursed = DateTimeImmutable::createFromFormat('Y-m-d', $disbStr);
        if ($disbursed === false || $disbursed->format('Y-m-d') !== $disbStr) {
            return null;
        }

        $ledger = new LoanLedgerRepository();
        $last = $ledger->lastLine($loanId);
        if ($last === null) {
            return null;
        }
        $opening = (float) $last['closing_balance'];
        if ($opening <= 0.0000001) {
            return null;
        }

        $lastPdStr = substr((string) ($last['period_date'] ?? ''), 0, 10);
        $lastPdDt = DateTimeImmutable::createFromFormat('Y-m-d', $lastPdStr);
        if ($lastPdDt === false || $lastPdDt->format('Y-m-d') !== $lastPdStr) {
            return null;
        }

        $periodMonths = max(1, (int) ($loan['period_months'] ?? 1));
        $termEnd = $disbursed->modify('+' . $periodMonths . ' months');
        $nextBoundary = $lastPdDt->modify('+' . self::INTEREST_PERIOD_DAYS . ' days');
        if ($nextBoundary <= $termEnd) {
            $nextDue = $nextBoundary;
        } else {
            $nextDue = $termEnd;
        }
        $nextDueStr = $nextDue->format('Y-m-d');

        $basis = (string) ($loan['interest_basis'] ?? LoanInterestBasis::REDUCING_BALANCE);
        if (!in_array($basis, LoanInterestBasis::all(), true)) {
            $basis = LoanInterestBasis::REDUCING_BALANCE;
        }
        $orig = (float) ($loan['principal_amount'] ?? 0);
        $rate = (float) ($loan['rate_percent'] ?? 0);

        $ledgerDue = self::maxPaymentForNextLine(
            $opening,
            $rate,
            $nextDueStr,
            $lastPdStr,
            $disbStr,
            $basis,
            $orig
        );

        $instRaw = $loan['reminder_installment_amount'] ?? null;
        $installment = ($instRaw !== null && $instRaw !== '') ? (float) $instRaw : null;

        return [
            'next_due_ymd' => $nextDueStr,
            'ledger_amount_due' => $ledgerDue,
            'outstanding' => self::money($opening),
            'reminder_installment_amount' => $installment !== null ? self::money($installment) : null,
        ];
    }

    private static function syncLoanClosedState(int $loanId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT status FROM loans WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $loanId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return;
        }
        $st = (string) ($row['status'] ?? '');
        $out = round(self::outstandingForLoan($loanId), 2);
        $loans = new LoanRepository();
        if ($out <= 0 && $st === 'active') {
            $loans->markClosed($loanId);
            return;
        }
        if ($out > 0 && $st === 'closed') {
            $loans->reopenFromClosed($loanId);
        }
    }

    /**
     * Remove the last ledger line if it records a payment (ops correction). Re-opens the loan if needed.
     */
    public static function voidLastPaymentLine(int $loanId): void
    {
        $ledger = new LoanLedgerRepository();
        $last = $ledger->lastLine($loanId);
        if ($last === null) {
            throw new RuntimeException('No ledger lines to void.');
        }
        $lineNo = (int) ($last['line_no'] ?? 0);
        if ($ledger->maxLineNo($loanId) !== $lineNo) {
            throw new RuntimeException('Only the most recent ledger line can be voided.');
        }
        $pay = $last['payment_amount'] ?? null;
        if ($pay === null || (float) $pay <= 0) {
            throw new RuntimeException('The last line is not a payment entry.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $ledger->deleteLine($loanId, $lineNo);
            self::syncLoanClosedState($loanId);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Correct the payment amount on the last line (same period); recalculates closing and loan status.
     */
    public static function adjustLastPaymentLine(int $loanId, float $newPaymentAmount): void
    {
        $ledger = new LoanLedgerRepository();
        $last = $ledger->lastLine($loanId);
        if ($last === null) {
            throw new RuntimeException('No ledger lines.');
        }
        $lineNo = (int) ($last['line_no'] ?? 0);
        if ($ledger->maxLineNo($loanId) !== $lineNo) {
            throw new RuntimeException('Only the last ledger line can be adjusted.');
        }
        if (($last['payment_amount'] ?? null) === null) {
            throw new RuntimeException('The last line has no payment to adjust.');
        }

        $due = self::money((float) ($last['amount_due'] ?? 0));
        $np = self::money($newPaymentAmount);
        if ($np < 0 || $np > $due + 0.0000001) {
            throw new InvalidArgumentException('Payment must be between 0 and the line amount due.');
        }
        $closing = self::money($due - $np);

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $ledger->updateLinePaymentAndClosing($loanId, $lineNo, $np, $closing);
            self::syncLoanClosedState($loanId);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
