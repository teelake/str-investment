<?php

declare(strict_types=1);

/**
 * Rolling balance ledger: each line is opening → interest (rate% of opening) → amount due → payment → closing.
 * First line after disburse uses opening = principal. Further lines use opening = previous closing.
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
        $asOf = DateTimeImmutable::createFromFormat('Y-m-d', $asOfDateYmd);
        if ($asOf === false || $asOf->format('Y-m-d') !== $asOfDateYmd) {
            throw new InvalidArgumentException('Invalid as-of date.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT id, status, rate_percent, period_months, disbursed_at
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

    public static function applyPayment(int $loanId, float $amount, string $paymentDateYmd): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be positive.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id, status, rate_percent FROM loans WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $loanId]);
            $loan = $stmt->fetch();
            if (!is_array($loan) || ($loan['status'] ?? '') !== 'active') {
                $pdo->rollBack();
                throw new RuntimeException('Loan is not active.');
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
            $pay = self::money(min($amount, $amountDue));
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
}
