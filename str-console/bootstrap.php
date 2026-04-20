<?php

declare(strict_types=1);

/**
 * str-console bootstrap: paths, session, config, permission helpers.
 */

define('STR_CONSOLE_ROOT', __DIR__);

/**
 * Load optional local overrides first so STR_CONSOLE_ERROR_LOG / DB / env constants exist.
 */
$local = STR_CONSOLE_ROOT . '/config/local.php';
if (is_file($local)) {
    require_once $local;
}

require_once STR_CONSOLE_ROOT . '/core/ErrorLogging.php';
str_console_bootstrap_error_logging();

require_once STR_CONSOLE_ROOT . '/config/permissions.php';
require_once STR_CONSOLE_ROOT . '/config/customer_documents.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
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

/** Verbose errors in UI (e.g. SQL message) — set STR_CONSOLE_DEBUG=1 or APP_DEBUG=1. */
function str_console_debug(): bool
{
    if (getenv('STR_CONSOLE_DEBUG') === '1' || getenv('APP_DEBUG') === '1') {
        return true;
    }

    return defined('STR_CONSOLE_DEBUG') && STR_CONSOLE_DEBUG === true;
}

require_once STR_CONSOLE_ROOT . '/core/Request.php';
require_once STR_CONSOLE_ROOT . '/core/Pagination.php';
require_once STR_CONSOLE_ROOT . '/core/FormGuard.php';
require_once STR_CONSOLE_ROOT . '/core/InputValidate.php';
require_once STR_CONSOLE_ROOT . '/core/LoanInterestBasis.php';
require_once STR_CONSOLE_ROOT . '/core/ErrorPage.php';
require_once STR_CONSOLE_ROOT . '/core/ConsoleAuth.php';
require_once STR_CONSOLE_ROOT . '/core/BaseController.php';
require_once STR_CONSOLE_ROOT . '/core/Database.php';

require_once STR_CONSOLE_ROOT . '/services/AuditLogger.php';
require_once STR_CONSOLE_ROOT . '/services/CustomerDocumentStorage.php';
require_once STR_CONSOLE_ROOT . '/repositories/UserRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/PasswordResetRepository.php';
require_once STR_CONSOLE_ROOT . '/services/PasswordResetNotifier.php';
require_once STR_CONSOLE_ROOT . '/repositories/ConsoleSettingRepository.php';
require_once STR_CONSOLE_ROOT . '/services/PolicyService.php';
require_once STR_CONSOLE_ROOT . '/repositories/CustomerRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/CustomerDocumentRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/LoanProductRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/LoanLedgerRepository.php';
require_once STR_CONSOLE_ROOT . '/services/LoanLedgerService.php';
require_once STR_CONSOLE_ROOT . '/repositories/ReportRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/LoanRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/SearchRepository.php';
require_once STR_CONSOLE_ROOT . '/repositories/AuditLogRepository.php';

require_once STR_CONSOLE_ROOT . '/controllers/AuthController.php';
require_once STR_CONSOLE_ROOT . '/controllers/DashboardController.php';
require_once STR_CONSOLE_ROOT . '/controllers/CustomersController.php';
require_once STR_CONSOLE_ROOT . '/controllers/LoanProductsController.php';
require_once STR_CONSOLE_ROOT . '/controllers/LoansController.php';
require_once STR_CONSOLE_ROOT . '/controllers/SettingsController.php';
require_once STR_CONSOLE_ROOT . '/controllers/SettingsUsersController.php';
require_once STR_CONSOLE_ROOT . '/controllers/SettingsRolesController.php';
require_once STR_CONSOLE_ROOT . '/controllers/SettingsSystemController.php';
require_once STR_CONSOLE_ROOT . '/controllers/AccountController.php';
require_once STR_CONSOLE_ROOT . '/controllers/SearchController.php';
require_once STR_CONSOLE_ROOT . '/controllers/AuditController.php';
require_once STR_CONSOLE_ROOT . '/controllers/ReportsController.php';
require_once STR_CONSOLE_ROOT . '/controllers/BulkUploadController.php';

require_once STR_CONSOLE_ROOT . '/config/routes.php';

require_once STR_CONSOLE_ROOT . '/core/Router.php';
