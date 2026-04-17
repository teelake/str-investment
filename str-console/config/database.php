<?php

declare(strict_types=1);

/**
 * Database configuration via environment variables or constants from config/local.php:
 *
 *   STR_CONSOLE_DB_DSN  (e.g. mysql:host=127.0.0.1;dbname=strinvestment;charset=utf8mb4)
 *   STR_CONSOLE_DB_USER
 *   STR_CONSOLE_DB_PASS
 */

function str_console_database_ready(): bool
{
    $dsn = getenv('STR_CONSOLE_DB_DSN');
    if (is_string($dsn) && $dsn !== '') {
        return true;
    }
    return defined('STR_CONSOLE_DB_DSN') && is_string(STR_CONSOLE_DB_DSN) && STR_CONSOLE_DB_DSN !== '';
}

/**
 * @return array{dsn: string, user: string, pass: string}
 */
function str_console_db_config(): array
{
    $dsn = getenv('STR_CONSOLE_DB_DSN');
    if (!is_string($dsn) || $dsn === '') {
        $dsn = defined('STR_CONSOLE_DB_DSN') ? (string) STR_CONSOLE_DB_DSN : '';
    }
    $user = getenv('STR_CONSOLE_DB_USER');
    if (!is_string($user) || $user === '') {
        $user = defined('STR_CONSOLE_DB_USER') ? (string) STR_CONSOLE_DB_USER : '';
    }
    $pass = getenv('STR_CONSOLE_DB_PASS');
    if (!is_string($pass)) {
        $pass = defined('STR_CONSOLE_DB_PASS') ? (string) STR_CONSOLE_DB_PASS : '';
    }

    if ($dsn === '') {
        throw new RuntimeException(
            'Database is not configured. Set STR_CONSOLE_DB_DSN (and user/pass) or define them in str-console/config/local.php.'
        );
    }

    return ['dsn' => $dsn, 'user' => $user, 'pass' => $pass];
}
