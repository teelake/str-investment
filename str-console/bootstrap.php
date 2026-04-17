<?php

declare(strict_types=1);

/**
 * str-console bootstrap: paths, session, config, permission helpers.
 */

define('STR_CONSOLE_ROOT', __DIR__);

require_once STR_CONSOLE_ROOT . '/config/permissions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

/**
 * Load optional local overrides (not committed): dev flags, DB DSN, etc.
 */
$local = STR_CONSOLE_ROOT . '/config/local.php';
if (is_file($local)) {
    require_once $local;
}

/**
 * Demo login: allow role picker when STR_CONSOLE_DEV_LOGIN=1 or config enables it.
 */
function str_console_dev_login_enabled(): bool
{
    if (getenv('STR_CONSOLE_DEV_LOGIN') === '1') {
        return true;
    }
    return defined('STR_CONSOLE_DEV_LOGIN') && STR_CONSOLE_DEV_LOGIN === true;
}

require_once STR_CONSOLE_ROOT . '/core/Request.php';
require_once STR_CONSOLE_ROOT . '/core/ConsoleAuth.php';
require_once STR_CONSOLE_ROOT . '/core/BaseController.php';

require_once STR_CONSOLE_ROOT . '/controllers/AuthController.php';
require_once STR_CONSOLE_ROOT . '/controllers/DashboardController.php';

require_once STR_CONSOLE_ROOT . '/config/routes.php';

require_once STR_CONSOLE_ROOT . '/core/Router.php';
