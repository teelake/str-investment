<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!function_exists('str_console_database_ready')) {
    require_once __DIR__ . '/config/database.php';
}

Router::dispatch();
