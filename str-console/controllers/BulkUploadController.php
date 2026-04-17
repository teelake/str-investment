<?php

declare(strict_types=1);

final class BulkUploadController extends BaseController
{
    private const SESSION_FLASH = 'str_console_bulk_import_flash';

    private const MAX_CSV_BYTES = 2_097_152;

    public function downloadCustomersTemplateCsv(): void
    {
        $name = 'customers-import-template.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            echo 'Could not build file.';
            return;
        }
        fputcsv($out, ['full_name', 'phone', 'address', 'nin', 'bvn']);
        fputcsv($out, ['Ada Okafor', '+2348012345678', '12 Sample Street, Lagos', '', '']);
        fclose($out);
        exit;
    }

    public function downloadLoansTemplateCsv(): void
    {
        $name = 'loans-import-template.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            echo 'Could not build file.';
            return;
        }
        fputcsv($out, ['customer_id', 'loan_product_id', 'principal_amount']);
        fputcsv($out, ['1', '1', '50000.00']);
        fclose($out);
        exit;
    }

    public function customersForm(): void
    {
        $this->render('bulk_upload/customers', [
            'flash' => self::takeFlash(),
            'error' => Request::query('error'),
        ]);
    }

    public function customersImport(): void
    {
        $this->requirePostedCsrf('/bulk-upload/customers');
        if (!str_console_database_ready()) {
            $this->redirect('/bulk-upload/customers?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $file = $_FILES['csv'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->redirect('/bulk-upload/customers?error=' . rawurlencode('Upload a CSV file.'));
            return;
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $size <= 0 || $size > self::MAX_CSV_BYTES) {
            $this->redirect('/bulk-upload/customers?error=' . rawurlencode('Invalid file (max 2 MB).'));
            return;
        }

        $parsed = self::parseCsv($tmp);
        if ($parsed === null) {
            $this->redirect('/bulk-upload/customers?error=' . rawurlencode('Could not read CSV.'));
            return;
        }

        [$header, $rows] = $parsed;
        $col = self::mapHeader($header);
        if (!isset($col['full_name'], $col['phone'])) {
            $this->redirect('/bulk-upload/customers?error=' . rawurlencode('CSV must include columns: full_name (or name), phone.'));
            return;
        }

        $repo = new CustomerRepository();
        $uid = ConsoleAuth::userId();
        $imported = 0;
        /** @var list<array{line: int, message: string}> $errors */
        $errors = [];
        $lineNo = 1;

        foreach ($rows as $row) {
            ++$lineNo;
            if (self::rowIsEmpty($row)) {
                continue;
            }
            $name = trim((string) self::cell($row, $col['full_name'] ?? null));
            $phone = trim((string) self::cell($row, $col['phone'] ?? null));
            $address = isset($col['address']) ? trim((string) self::cell($row, $col['address'])) : '';
            $nin = isset($col['nin']) ? trim((string) self::cell($row, $col['nin'])) : '';
            $bvn = isset($col['bvn']) ? trim((string) self::cell($row, $col['bvn'])) : '';

            if ($name === '' || $phone === '') {
                $errors[] = ['line' => $lineNo, 'message' => 'Name and phone required.'];
                continue;
            }

            $ninNorm = InputValidate::optionalNinBvn($nin);
            if ($ninNorm === false) {
                $errors[] = ['line' => $lineNo, 'message' => 'NIN must be blank or exactly 11 digits (Nigeria NIMC).'];
                continue;
            }
            $bvnNorm = InputValidate::optionalNinBvn($bvn);
            if ($bvnNorm === false) {
                $errors[] = ['line' => $lineNo, 'message' => 'BVN must be blank or exactly 11 digits (Nigeria CBN).'];
                continue;
            }

            try {
                $newId = $repo->create(
                    $name,
                    $phone,
                    $address === '' ? null : $address,
                    $ninNorm,
                    $bvnNorm,
                    $uid
                );
                ++$imported;
                AuditLogger::log(ConsoleAuth::userId(), 'customer.create', 'customer', $newId, [
                    'source' => 'bulk_csv',
                    'full_name' => $name,
                ]);
            } catch (Throwable) {
                $errors[] = ['line' => $lineNo, 'message' => 'Save failed.'];
            }
        }

        AuditLogger::log(ConsoleAuth::userId(), 'bulk_import.customers', 'bulk_import', null, [
            'imported' => $imported,
            'errors' => count($errors),
        ]);

        $_SESSION[self::SESSION_FLASH] = [
            'type' => 'customers',
            'imported' => $imported,
            'errors' => array_slice($errors, 0, 50),
        ];
        $this->redirect('/bulk-upload/customers');
    }

    public function loansForm(): void
    {
        $this->render('bulk_upload/loans', [
            'flash' => self::takeFlash(),
            'error' => Request::query('error'),
        ]);
    }

    public function loansImport(): void
    {
        $this->requirePostedCsrf('/bulk-upload/loans');
        if (!str_console_database_ready()) {
            $this->redirect('/bulk-upload/loans?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $file = $_FILES['csv'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->redirect('/bulk-upload/loans?error=' . rawurlencode('Upload a CSV file.'));
            return;
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $size <= 0 || $size > self::MAX_CSV_BYTES) {
            $this->redirect('/bulk-upload/loans?error=' . rawurlencode('Invalid file (max 2 MB).'));
            return;
        }

        $parsed = self::parseCsv($tmp);
        if ($parsed === null) {
            $this->redirect('/bulk-upload/loans?error=' . rawurlencode('Could not read CSV.'));
            return;
        }

        [$header, $rows] = $parsed;
        $col = self::mapHeader($header);
        if (!isset($col['customer_id'])) {
            $this->redirect('/bulk-upload/loans?error=' . rawurlencode('CSV must include column: customer_id.'));
            return;
        }
        if (!isset($col['loan_product_id']) || !isset($col['principal_amount'])) {
            $this->redirect('/bulk-upload/loans?error=' . rawurlencode('CSV must include: loan_product_id, principal_amount (or principal).'));
            return;
        }
        $custRepo = new CustomerRepository();
        $loanRepo = new LoanRepository();
        $prodRepo = new LoanProductRepository();
        $consoleUid = ConsoleAuth::userId();
        $grants = ConsoleAuth::grants();

        $imported = 0;
        /** @var list<array{line: int, message: string}> $errors */
        $errors = [];
        $lineNo = 1;

        foreach ($rows as $row) {
            ++$lineNo;
            if (self::rowIsEmpty($row)) {
                continue;
            }
            $customerId = (int) self::cell($row, $col['customer_id'] ?? null);
            $productId = (int) self::cell($row, $col['loan_product_id'] ?? null);
            $principalRaw = self::cell($row, $col['principal_amount']);
            $principal = self::parseMoney($principalRaw);

            if ($customerId <= 0 || $productId <= 0 || $principal === null || $principal <= 0) {
                $errors[] = ['line' => $lineNo, 'message' => 'Valid customer_id, loan_product_id, and principal required.'];
                continue;
            }

            $c = $custRepo->find($customerId, $consoleUid, $grants);
            if ($c === null) {
                $errors[] = ['line' => $lineNo, 'message' => 'Customer not in your scope or missing.'];
                continue;
            }

            $product = $prodRepo->find($productId);
            if ($product === null || !(int) ($product['is_active'] ?? 0)) {
                $errors[] = ['line' => $lineNo, 'message' => 'Invalid or inactive loan product.'];
                continue;
            }

            $rate = (float) $product['rate_percent'];
            $pm = (int) ($product['period_months'] ?? 1);
            $assignLoan = $consoleUid;
            if ($assignLoan === null) {
                $assignLoan = isset($c['assigned_user_id']) ? (int) $c['assigned_user_id'] : null;
            }

            try {
                $lid = $loanRepo->create(
                    $customerId,
                    $productId,
                    $principal,
                    $rate,
                    $pm,
                    $assignLoan,
                    ConsoleAuth::userId()
                );
                ++$imported;
                AuditLogger::log(ConsoleAuth::userId(), 'loan.create', 'loan', $lid, [
                    'source' => 'bulk_csv',
                    'customer_id' => $customerId,
                    'principal' => $principal,
                ]);
            } catch (Throwable) {
                $errors[] = ['line' => $lineNo, 'message' => 'Save failed.'];
            }
        }

        AuditLogger::log(ConsoleAuth::userId(), 'bulk_import.loans', 'bulk_import', null, [
            'imported' => $imported,
            'errors' => count($errors),
        ]);

        $_SESSION[self::SESSION_FLASH] = [
            'type' => 'loans',
            'imported' => $imported,
            'errors' => array_slice($errors, 0, 50),
        ];
        $this->redirect('/bulk-upload/loans');
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>}|null
     */
    private static function parseCsv(string $path): ?array
    {
        $h = fopen($path, 'rb');
        if ($h === false) {
            return null;
        }
        $header = fgetcsv($h);
        if ($header === false || $header === [null] || $header === []) {
            fclose($h);
            return null;
        }
        /** @var list<list<string>> $rows */
        $rows = [];
        while (($r = fgetcsv($h)) !== false) {
            $rows[] = $r;
        }
        fclose($h);
        /** @var list<string> $header */
        return [$header, $rows];
    }

    /**
     * @param list<string> $headerRow
     * @return array<string, int>
     */
    private static function mapHeader(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $i => $cell) {
            $k = strtolower(trim((string) $cell));
            $k = str_replace([' ', '-'], '_', $k);
            if ($k === 'name') {
                $k = 'full_name';
            }
            if ($k === 'principal') {
                $k = 'principal_amount';
            }
            if ($k !== '') {
                $map[$k] = (int) $i;
            }
        }
        return $map;
    }

    /**
     * @param list<string>|false $row
     */
    private static function rowIsEmpty(array $row): bool
    {
        foreach ($row as $c) {
            if (trim((string) $c) !== '') {
                return false;
            }
        }
        return true;
    }

    private static function cell(array $row, ?int $idx): string
    {
        if ($idx === null || $idx < 0) {
            return '';
        }
        return (string) ($row[$idx] ?? '');
    }

    private static function parseMoney(string $raw): ?float
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $clean = str_replace([',', ' '], '', $raw);
        if (!is_numeric($clean)) {
            return null;
        }
        $f = (float) $clean;
        return $f > 0 ? round($f, 2) : null;
    }

    /**
     * @return array{type: string, imported: int, errors: list<array{line: int, message: string}>}|null
     */
    private static function takeFlash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        if (!isset($_SESSION[self::SESSION_FLASH]) || !is_array($_SESSION[self::SESSION_FLASH])) {
            return null;
        }
        $f = $_SESSION[self::SESSION_FLASH];
        unset($_SESSION[self::SESSION_FLASH]);
        return $f;
    }
}
