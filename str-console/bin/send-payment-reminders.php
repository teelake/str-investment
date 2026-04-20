<?php

declare(strict_types=1);

/**
 * Send borrower payment reminder emails (when enabled in Settings and STR_CONSOLE_MAIL_FROM is set).
 *
 * Usage:
 *   STR_CONSOLE_PAYMENT_REMINDER_CRON=1 php bin/send-payment-reminders.php [YYYY-MM-DD]
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

if (getenv('STR_CONSOLE_PAYMENT_REMINDER_CRON') !== '1') {
    fwrite(STDERR, "Refusing to run: set STR_CONSOLE_PAYMENT_REMINDER_CRON=1 for this invocation (e.g. in cron).\n");
    exit(2);
}

$root = dirname(__DIR__);
require $root . '/bootstrap.php';

if (!str_console_database_ready()) {
    fwrite(STDERR, "Database not configured.\n");
    exit(1);
}

$todayRaw = isset($argv[1]) ? trim((string) $argv[1]) : '';
$today = $todayRaw === '' ? InputValidate::todayYmd() : InputValidate::parseDateYmd($todayRaw);
if ($today === null) {
    fwrite(STDERR, "Invalid date. Use YYYY-MM-DD or omit for today.\n");
    exit(1);
}

try {
    $r = PaymentReminderService::runForDate($today);
    fwrite(
        STDOUT,
        sprintf(
            "Payment reminders for %s: sent=%d skipped_no_email=%d skipped_past_due=%d skipped_mail_config=%d errors=%d\n",
            $today,
            $r['sent'],
            $r['skipped_no_email'],
            $r['skipped_past_due'],
            $r['skipped_mail'],
            $r['errors']
        )
    );
    if ($r['sent'] > 0) {
        AuditLogger::log(null, 'loan.payment_reminders.batch', 'bulk_import', null, [
            'date' => $today,
            'sent' => $r['sent'],
            'source' => 'cli_cron',
        ]);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
