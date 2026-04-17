<?php

declare(strict_types=1);

final class LoanLedgerRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByLoan(int $loanId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM loan_ledger_lines WHERE loan_id = :lid ORDER BY line_no ASC'
        );
        $stmt->execute([':lid' => $loanId]);
        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    public function nextLineNo(int $loanId): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(line_no), 0) + 1 AS n FROM loan_ledger_lines WHERE loan_id = :lid');
        $stmt->execute([':lid' => $loanId]);
        return (int) ($stmt->fetch()['n'] ?? 1);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lastLine(int $loanId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM loan_ledger_lines WHERE loan_id = :lid ORDER BY line_no DESC LIMIT 1'
        );
        $stmt->execute([':lid' => $loanId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function maxLineNo(int $loanId): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(line_no), 0) AS m FROM loan_ledger_lines WHERE loan_id = :lid');
        $stmt->execute([':lid' => $loanId]);
        return (int) ($stmt->fetch()['m'] ?? 0);
    }

    public function deleteLine(int $loanId, int $lineNo): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM loan_ledger_lines WHERE loan_id = :lid AND line_no = :ln');
        $stmt->execute([':lid' => $loanId, ':ln' => $lineNo]);
    }

    public function updateLinePaymentAndClosing(
        int $loanId,
        int $lineNo,
        float $paymentAmount,
        float $closingBalance
    ): void {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE loan_ledger_lines SET payment_amount = :pa, closing_balance = :cb WHERE loan_id = :lid AND line_no = :ln'
        );
        $stmt->execute([
            ':pa' => $paymentAmount,
            ':cb' => $closingBalance,
            ':lid' => $loanId,
            ':ln' => $lineNo,
        ]);
    }

    public function insertLine(
        int $loanId,
        int $lineNo,
        string $periodDate,
        float $opening,
        float $ratePercent,
        float $interest,
        float $amountDue,
        ?string $paymentDate,
        ?float $paymentAmount,
        float $closing
    ): void {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO loan_ledger_lines (loan_id, line_no, period_date, opening_balance, rate_percent, interest_amount, amount_due, payment_date, payment_amount, closing_balance, created_at)
             VALUES (:lid, :ln, :pd, :ob, :rp, :int, :due, :pdate, :pamt, :cb, NOW())'
        );
        $stmt->execute([
            ':lid' => $loanId,
            ':ln' => $lineNo,
            ':pd' => $periodDate,
            ':ob' => $opening,
            ':rp' => $ratePercent,
            ':int' => $interest,
            ':due' => $amountDue,
            ':pdate' => $paymentDate,
            ':pamt' => $paymentAmount,
            ':cb' => $closing,
        ]);
    }
}
