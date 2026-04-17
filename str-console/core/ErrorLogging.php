<?php

declare(strict_types=1);

/**
 * Send PHP errors, warnings, notices, fatals, and uncaught exceptions to PHP's error log.
 *
 * Optional environment / local.php:
 *   STR_CONSOLE_ERROR_LOG — absolute path to a writable log file (overrides default error_log)
 *   STR_CONSOLE_ENV=production — turns display_errors off (errors still logged)
 *
 * Standard PHP ini should have log_errors=On; this layer forces logging for the console app.
 */

function str_console_bootstrap_error_logging(): void
{
    ini_set('log_errors', '1');

    $customLog = getenv('STR_CONSOLE_ERROR_LOG');
    if (is_string($customLog) && $customLog !== '') {
        ini_set('error_log', $customLog);
    } elseif (defined('STR_CONSOLE_ERROR_LOG') && is_string(STR_CONSOLE_ERROR_LOG) && STR_CONSOLE_ERROR_LOG !== '') {
        ini_set('error_log', STR_CONSOLE_ERROR_LOG);
    }

    error_reporting(E_ALL);

    $prod = getenv('STR_CONSOLE_ENV') === 'production'
        || getenv('APP_ENV') === 'production'
        || (defined('STR_CONSOLE_ENV') && STR_CONSOLE_ENV === 'production');
    if ($prod) {
        ini_set('display_errors', '0');
    }

    set_exception_handler(static function (Throwable $e): void {
        $msg = sprintf(
            "[str-console] Uncaught %s: %s in %s:%d\nStack trace:\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        error_log($msg);

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body><p>An unexpected error occurred.</p></body></html>';
        exit(1);
    });

    register_shutdown_function(static function (): void {
        $err = error_get_last();
        if ($err === null) {
            return;
        }
        $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($err['type'], $fatal, true)) {
            return;
        }
        error_log(sprintf(
            '[str-console] Fatal error (%d): %s in %s:%d',
            $err['type'],
            $err['message'],
            $err['file'],
            $err['line']
        ));
    });
}
