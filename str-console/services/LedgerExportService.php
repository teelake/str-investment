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

    /**
     * @param array<string, mixed> $loan
     * @param list<array<string, mixed>> $ledger
     */
    public static function tryStreamPdf(
        int $loanId,
        array $loan,
        string $customerName,
        array $ledger,
        float $outstanding
    ): bool {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            return false;
        }
        $html = self::buildHtmlDocument($loanId, $loan, $customerName, $ledger, $outstanding);
        try {
            $options = class_exists(\Dompdf\Options::class, true)
                ? new \Dompdf\Options()
                : null;
            if ($options !== null) {
                $options->set('isRemoteEnabled', false);
                $options->set('isHtml5ParserEnabled', true);
            }
            $dompdf = $options !== null ? new \Dompdf\Dompdf($options) : new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $name = 'loan-' . $loanId . '-ledger.pdf';
            $dompdf->stream($name, ['Attachment' => true]);
            exit;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $loan
     * @param list<array<string, mixed>> $ledger
     */
    public static function buildHtmlDocument(
        int $loanId,
        array $loan,
        string $customerName,
        array $ledger,
        float $outstanding
    ): string {
        $e = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $rows = '';
        foreach ($ledger as $row) {
            $pay = $row['payment_amount'] ?? null;
            $paid = $pay !== null && $pay !== '' ? self::fmtPlain((float) $pay) : '—';
            $rows .= '<tr>'
                . '<td>' . (int) ($row['line_no'] ?? 0) . '</td>'
                . '<td>' . $e((string) ($row['period_date'] ?? '')) . '</td>'
                . '<td class="n">' . $e(self::fmtPlain((float) ($row['opening_balance'] ?? 0))) . '</td>'
                . '<td class="n">' . $e((string) ($row['rate_percent'] ?? '')) . '</td>'
                . '<td class="n">' . $e(self::fmtPlain((float) ($row['interest_amount'] ?? 0))) . '</td>'
                . '<td class="n">' . $e(self::fmtPlain((float) ($row['amount_due'] ?? 0))) . '</td>'
                . '<td class="n">' . $e($paid) . '</td>'
                . '<td class="n">' . $e(self::fmtPlain((float) ($row['closing_balance'] ?? 0))) . '</td>'
                . "</tr>\n";
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="8" style="padding:12px">No ledger lines yet.</td></tr>';
        }
        $title = 'Loan #' . $loanId . ' — Ledger';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8" />'
            . '<title>' . $e($title) . '</title>'
            . '<style>body{font-family:DejaVu Sans,Helvetica,Arial,sans-serif;font-size:11px;color:#111}'
            . 'h1{font-size:16px;margin:0 0 8px} .meta td{padding:2px 12px 2px 0;font-size:12px} '
            . 'table.ledger{border-collapse:collapse;width:100%;margin-top:10px} '
            . 'table.ledger th,table.ledger td{border:1px solid #999;padding:4px 6px} '
            . 'table.ledger th{background:#f0f0f0;font-weight:600;text-align:left} '
            . 'td.n,th.n{text-align:right} '
            . '</style></head><body>'
            . '<h1>' . $e($title) . '</h1>'
            . '<table class="meta">'
            . '<tr><td><strong>Customer</strong></td><td>' . $e($customerName) . '</td></tr>'
            . '<tr><td><strong>Status</strong></td><td>' . $e((string) ($loan['status'] ?? '')) . '</td></tr>'
            . '<tr><td><strong>Principal</strong></td><td>' . $e(self::fmtPlain((float) ($loan['principal_amount'] ?? 0))) . '</td></tr>'
            . '<tr><td><strong>Outstanding</strong></td><td>' . $e(self::fmtPlain($outstanding)) . '</td></tr>'
            . '</table>'
            . '<table class="ledger"><thead><tr><th>#</th><th>Date</th><th class="n">Opening</th><th class="n">Rate %</th>'
            . '<th class="n">Interest</th><th class="n">Total due</th><th class="n">Paid</th><th class="n">Closing</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table></body></html>';
    }

    private static function fmtPlain(float $n): string
    {
        return number_format(round($n, 2), 2, '.', '');
    }
}
