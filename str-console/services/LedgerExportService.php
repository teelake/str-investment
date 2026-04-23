<?php

declare(strict_types=1);

final class LedgerExportService
{
    /**
     * @param array<string, mixed> $loan
     * @param list<array<string, mixed>> $ledger
     */
    public static function streamCsv(
        int $loanId,
        array $loan,
        string $customerName,
        array $ledger,
        float $outstanding
    ): void {
        $fn = 'loan-' . $loanId . '-ledger-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . str_replace(['"', "\n"], '', $fn) . '"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['STR Console — loan ledger export']);
        fputcsv($out, ['Loan ID', (string) $loanId]);
        fputcsv($out, ['Customer', $customerName]);
        fputcsv($out, ['Status', (string) ($loan['status'] ?? '')]);
        fputcsv($out, ['Principal (booked)', self::fmtPlain((float) ($loan['principal_amount'] ?? 0))]);
        fputcsv($out, ['Outstanding (current)', self::fmtPlain($outstanding)]);
        fputcsv($out, []);
        fputcsv($out, ['Line', 'Date', 'Opening', 'Rate %', 'Interest', 'Total due', 'Paid', 'Closing']);
        foreach ($ledger as $row) {
            $pay = $row['payment_amount'] ?? null;
            fputcsv($out, [
                (int) ($row['line_no'] ?? 0),
                (string) ($row['period_date'] ?? ''),
                self::fmtPlain((float) ($row['opening_balance'] ?? 0)),
                (string) ($row['rate_percent'] ?? ''),
                self::fmtPlain((float) ($row['interest_amount'] ?? 0)),
                self::fmtPlain((float) ($row['amount_due'] ?? 0)),
                $pay !== null && $pay !== '' ? self::fmtPlain((float) $pay) : '',
                self::fmtPlain((float) ($row['closing_balance'] ?? 0)),
            ]);
        }
        fclose($out);
        exit;
    }

    private static function fmtPlain(float $n): string
    {
        return number_format(round($n, 2), 2, '.', '');
    }
}
