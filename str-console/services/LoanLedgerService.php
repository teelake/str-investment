<?php

declare(strict_types=1);

/**
 * Rolling-balance loan ledger (booked rate on the loan row).
 *
 * Model (contract wording may differ — align product/legal before go-live):
 * - Interest each step = booked rate_percent × opening balance for that line, rounded to 2 decimals.
 * - “Opening” on the first line after disburse = principal. Every later line uses opening = previous line’s closing.
 * - Payment lines: interest is charged on opening, total due = opening + interest, payment caps at that due, closing = due − payment.
 * - Periodic accrual (optional, when PolicyService::ledgerAutoAccrue()): inserts lines with no payment so closing = amount due
 *   (interest rolls into balance). Next line’s period_date is previous period_date + 1 calendar month (PHP DateTimeImmutable).
 * - Accrual stops at disbursed_at + period_months (term) or the as-of date, whichever is earlier.
 *
 * Triggers: disburse creates line 1; payments and periodic accrual must not run on GET — use POST or CLI/cron only.
 */
final class LoanLedgerService
{
    public static function money(float $n): float
    {
        return round($n, 2);
    }

    /**
     * Atomically mark loan active, set disbursed_at, and create the first ledger line (principal + interest).
     */
    public static function completeDisbursement(int $loanId, string $periodDateYmd): void
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
                throw new RuntimeException('Disbursement date must be from loan creation through today.');
            }

            $u = $pdo->prepare(
                'UPDATE loans SET status = \'active\', disbursed_at = :d, updated_at = NOW()
                 WHERE id = :id AND status = \'approved\''
            );
            $u->execute([':d' => $periodDateYmd, ':id' => $loanId]);
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
            $interest = self::money($principal * ($rate / 100.0));
            $due = self::money($principal + $interest);
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
     * Append month-end-style accrual lines: interest on the last closing, no payment, closing = amount due.
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
                'SELECT id, status, rate_percent, period_months, disbursed_at, created_at
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

                $nextPd = $lastPd->modify('+1 month');
                if ($nextPd->format('Y-m-d') > $asOf->format('Y-m-d')) {
                    break;
                }
                if ($nextPd > $termEnd) {
                    break;
                }

                $lineNo = $ledger->nextLineNo($loanId);
                $rate = $rateLoan;
                $interest = self::money($opening * ($rate / 100.0));
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
            $stmt = $pdo->prepare('SELECT id, status, rate_percent, disbursed_at FROM loans WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $loanId]);
            $loan = $stmt->fetch();
            if (!is_array($loan) || ($loan['status'] ?? '') !== 'active') {
                $pdo->rollBack();
                throw new RuntimeException('Loan is not active.');
            }
            if (!InputValidate::loanPostDisburseDateOk($paymentDateYmd, (string) ($loan['disbursed_at'] ?? ''))) {
                $pdo->rollBack();
                throw new RuntimeException('Payment date must be from disbursement through today.');
            }

            $rate = (float) $loan['rate_percent'];
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

            $lineNo = $ledger->nextLineNo($loanId);
            $interest = self::money($opening * ($rate / 100.0));
            $amountDue = self::money($opening + $interest);
            $payIn = self::money($amount);
            if ($payIn > $amountDue) {
                $pdo->rollBack();
                throw new RuntimeException(
                    'Payment cannot exceed the amount due for this period (opening balance plus interest): '
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
