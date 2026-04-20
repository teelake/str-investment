<?php

declare(strict_types=1);

final class ReportsController extends BaseController
{
    public function index(): void
    {
        $g = ConsoleAuth::grants();
        $canLoans = str_console_authorize($g, ['loans.list']);
        $canCustomers = str_console_authorize($g, ['customers.list']);

        $kind = trim((string) Request::query('kind', 'loans'));
        if ($kind !== 'customers' && $kind !== 'loans') {
            $kind = 'loans';
        }
        if ($kind === 'loans' && !$canLoans) {
            $kind = $canCustomers ? 'customers' : 'loans';
        }
        if ($kind === 'customers' && !$canCustomers) {
            $kind = $canLoans ? 'loans' : 'customers';
        }

        if (!str_console_database_ready()) {
            $this->render('reports/index', [
                'kind' => $kind,
                'canLoans' => $canLoans,
                'canCustomers' => $canCustomers,
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => ReportRepository::PER_PAGE],
                'status' => '',
                'from' => '',
                'to' => '',
                'q' => '',
                'statusInvalid' => false,
                'dateFromInvalid' => false,
                'dateToInvalid' => false,
                'filterQuery' => self::filterQueryString($kind, null, null, null, null),
                'dbError' => 'Database not configured.',
                'canExport' => str_console_authorize($g, ['reports.export']),
            ]);
            return;
        }

        $page = Pagination::sanitizeRequestedPage(Request::query('page', 1));
        $statusRaw = trim((string) Request::query('status', ''));
        $fromRaw = trim((string) Request::query('from', ''));
        $toRaw = trim((string) Request::query('to', ''));
        $qRaw = trim((string) Request::query('q', ''));
        if (mb_strlen($qRaw) > 120) {
            $qRaw = mb_substr($qRaw, 0, 120);
        }
        $qForRepo = $qRaw === '' ? null : $qRaw;

        $statusNorm = ReportRepository::normalizeLoanStatus($statusRaw);
        $fromNorm = ReportRepository::normalizeDate($fromRaw);
        $toNorm = ReportRepository::normalizeDate($toRaw);

        try {
            $repo = new ReportRepository();
            if ($kind === 'customers' && $canCustomers) {
                $data = $repo->paginateCustomers(ConsoleAuth::userId(), $g, $page, $fromNorm, $toNorm, $qForRepo);
            } elseif ($kind === 'loans' && $canLoans) {
                $data = $repo->paginateLoans(ConsoleAuth::userId(), $g, $page, $statusNorm, $fromNorm, $toNorm, $qForRepo);
            } else {
                $data = ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => ReportRepository::PER_PAGE];
            }

            $filterQuery = self::filterQueryString($kind, $statusNorm, $fromNorm, $toNorm, $qForRepo);
            $this->render('reports/index', [
                'kind' => $kind,
                'canLoans' => $canLoans,
                'canCustomers' => $canCustomers,
                'pagination' => $data,
                'status' => $statusRaw,
                'from' => $fromRaw,
                'to' => $toRaw,
                'q' => $qRaw,
                'statusInvalid' => $statusRaw !== '' && $statusNorm === null,
                'dateFromInvalid' => $fromRaw !== '' && $fromNorm === null,
                'dateToInvalid' => $toRaw !== '' && $toNorm === null,
                'filterQuery' => $filterQuery,
                'dbError' => null,
                'canExport' => str_console_authorize($g, ['reports.export']),
            ]);
        } catch (Throwable) {
            $this->render('reports/index', [
                'kind' => $kind,
                'canLoans' => $canLoans,
                'canCustomers' => $canCustomers,
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => ReportRepository::PER_PAGE],
                'status' => '',
                'from' => '',
                'to' => '',
                'q' => '',
                'statusInvalid' => false,
                'dateFromInvalid' => false,
                'dateToInvalid' => false,
                'filterQuery' => self::filterQueryString($kind, null, null, null, null),
                'dbError' => 'Could not load report.',
                'canExport' => str_console_authorize($g, ['reports.export']),
            ]);
        }
    }

    public function export(): void
    {
        $g = ConsoleAuth::grants();
        if (!str_console_authorize($g, ['reports.view', 'reports.export'])) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $canLoans = str_console_authorize($g, ['loans.list']);
        $canCustomers = str_console_authorize($g, ['customers.list']);

        $kind = trim((string) Request::query('kind', 'loans'));
        if ($kind !== 'customers' && $kind !== 'loans') {
            $kind = 'loans';
        }

        if (($kind === 'loans' && !$canLoans) || ($kind === 'customers' && !$canCustomers)) {
            http_response_code(400);
            echo 'Invalid report type for your access.';
            return;
        }

        if (!str_console_database_ready()) {
            http_response_code(503);
            echo 'Database not configured.';
            return;
        }

        $statusRaw = trim((string) Request::query('status', ''));
        $fromRaw = trim((string) Request::query('from', ''));
        $toRaw = trim((string) Request::query('to', ''));
        $qRaw = trim((string) Request::query('q', ''));
        if (mb_strlen($qRaw) > 120) {
            $qRaw = mb_substr($qRaw, 0, 120);
        }
        $qForRepo = $qRaw === '' ? null : $qRaw;

        $statusNorm = ReportRepository::normalizeLoanStatus($statusRaw);
        $fromNorm = ReportRepository::normalizeDate($fromRaw);
        $toNorm = ReportRepository::normalizeDate($toRaw);

        try {
            $repo = new ReportRepository();
            if ($kind === 'customers') {
                $rows = $repo->exportCustomers(ConsoleAuth::userId(), $g, $fromNorm, $toNorm, $qForRepo);
                $name = 'customers-report-' . date('Y-m-d') . '.csv';
                self::sendCsv(
                    $name,
                    ['id', 'full_name', 'phone', 'passport_phone', 'email', 'address', 'nin', 'bvn', 'assigned_user_id', 'assigned_to', 'created_at'],
                    $rows
                );
                return;
            }

            $rows = $repo->exportLoans(ConsoleAuth::userId(), $g, $statusNorm, $fromNorm, $toNorm, $qForRepo);
            $name = 'loans-report-' . date('Y-m-d') . '.csv';
            self::sendCsv(
                $name,
                ['id', 'customer_id', 'customer_name', 'status', 'principal_amount', 'rate_percent', 'interest_basis', 'period_months', 'created_at', 'disbursed_at', 'closed_at'],
                $rows
            );
        } catch (Throwable) {
            http_response_code(500);
            echo 'Export failed.';
        }
    }

    private static function filterQueryString(string $kind, ?string $statusNorm, ?string $fromNorm, ?string $toNorm, ?string $searchQ): string
    {
        $q = ['kind' => $kind];
        if ($kind === 'loans' && $statusNorm !== null && $statusNorm !== '') {
            $q['status'] = $statusNorm;
        }
        if ($fromNorm !== null) {
            $q['from'] = $fromNorm;
        }
        if ($toNorm !== null) {
            $q['to'] = $toNorm;
        }
        $sq = trim((string) $searchQ);
        if ($sq !== '') {
            $q['q'] = $sq;
        }
        return http_build_query($q);
    }

    /**
     * @param list<string> $headers
     * @param list<array<string, mixed>> $rows
     */
    private static function sendCsv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . str_replace(['"', "\n"], '', $filename) . '"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $r) {
            $line = [];
            foreach ($headers as $h) {
                $v = $r[$h] ?? '';
                if (is_float($v) || is_int($v)) {
                    $line[] = $v;
                } else {
                    $line[] = (string) $v;
                }
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }
}
