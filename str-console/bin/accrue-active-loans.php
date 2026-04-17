<?php

declare(strict_types=1);

/**
 * Apply periodic ledger accrual to all active, disbursed loans through a given date (default: today).
 * Respects console setting ledger.auto_accrue (same as the UI policy toggle).
 *
 * Usage:
 *   STR_CONSOLE_ACCRUE_CRON=1 php bin/accrue-active-loans.php [YYYY-MM-DD]
 *
 * The env flag avoids accidental runs. Intended for cron / task scheduler on the server.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>CLI only</title></head><body>';
    echo '<p>This script must run from the command line (cron/SSH), not in a browser.</p></body></html>';
    exit(1);
}

if (getenv('STR_CONSOLE_ACCRUE_CRON') !== '1') {
    fwrite(STDERR, "Refusing to run: set STR_CONSOLE_ACCRUE_CRON=1 for this invocation (e.g. in cron).\n");
    exit(2);
}

$root = dirname(__DIR__);
require $root . '/bootstrap.php';

if (!str_console_database_ready()) {
    fwrite(STDERR, "Database not configured.\n");
    exit(1);
}

$asOf = isset($argv[1]) ? trim((string) $argv[1]) : '';
if ($asOf === '') {
    $asOf = (new DateTimeImmutable('now'))->format('Y-m-d');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOf)) {
    fwrite(STDERR, "Invalid date. Use YYYY-MM-DD or omit for today.\n");
    exit(1);
}

try {
    $result = LoanLedgerService::runPeriodicAccrualAllActiveLoans($asOf);
    fwrite(STDOUT, sprintf(
        "Accrual through %s: loans_seen=%d lines_added=%d\n",
        $asOf,
        $result['loans_seen'],
        $result['lines_added']
    ));
    if ($result['lines_added'] > 0) {
        AuditLogger::log(null, 'loan.ledger.accrual_batch', 'bulk_import', null, [
            'through' => $asOf,
            'loans_seen' => $result['loans_seen'],
            'lines_added' => $result['lines_added'],
            'source' => 'cli_cron',
        ]);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
