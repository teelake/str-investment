<?php
/**
 * str-console permission catalog + route checklist defaults.
 *
 * Usage (future): merge DB-stored grants per user/session with hasPermission($key).
 * Routes list required keys; all listed keys must be satisfied unless noted.
 *
 * Convention:
 * - data.* = scope overrides (still enforce assignment rules when OFF).
 * - settings.* = org configuration surfaces.
 */

declare(strict_types=1);

/**
 * Every permission key the app understands (descriptions for admin UI + seeds).
 *
 * @return array<string, string>
 */
function str_console_permission_catalog(): array
{
    return [
        // Auth
        'auth.session' => 'Use authenticated console session (baseline after login).',

        // Data scope (in addition to per-record assignment when policy requires)
        'data.view_all_customers' => 'View/search all customers (bypass assignment filter).',
        'data.view_all_loans' => 'View/search all loans (bypass assignment filter).',

        // Dashboard
        'dashboard.view' => 'View console dashboard metrics.',

        // Customers
        'customers.list' => 'List customers (respects scope policy).',
        'customers.view' => 'Open customer profile.',
        'customers.create' => 'Register new customer.',
        'customers.edit' => 'Edit customer profile.',
        'customers.assign' => 'Assign or reassign handling officer / team.',
        'customers.view_sensitive_ids' => 'View full NIN/BVN and similar identifiers.',

        // Loans
        'loans.list' => 'List loans (respects scope policy).',
        'loans.view' => 'Open loan details & ledger.',
        'loans.create' => 'Create loan under a customer.',
        'loans.edit' => 'Edit draft / rejected loan before approval.',
        'loans.submit' => 'Submit loan for approval.',
        'loans.approve' => 'Approve pending loan.',
        'loans.reject' => 'Reject pending loan (approval queue).',
        'loans.disburse' => 'Mark loan disbursed / activate after approval.',
        'loans.close' => 'Close completed or written-off loan (if enabled).',

        // Loan products (CRUD limited to system + admin by policy default)
        'loan_products.list' => 'View loan products.',
        'loan_products.view' => 'View single loan product details.',
        'loan_products.create' => 'Create loan product.',
        'loan_products.edit' => 'Edit loan product.',
        'loan_products.retire' => 'Retire / deactivate loan product.',

        // Payments
        'payments.list' => 'List payments for a loan/customer.',
        'payments.record' => 'Record manual payment.',
        'payments.adjust' => 'Correct payment entries (supervised ops).',
        'payments.void' => 'Void / reverse a payment (high risk).',

        // Documents
        'documents.view' => 'View uploaded IDs and documents.',
        'documents.upload' => 'Upload documents.',
        'documents.delete' => 'Delete documents.',

        // Bulk upload
        'bulk_upload.customers' => 'Run customer bulk import.',
        'bulk_upload.loans' => 'Run loan bulk import.',

        // Reports & audit
        'reports.view' => 'Open reports (filters + pagination).',
        'reports.export' => 'Export report results (CSV/PDF/etc.).',
        'audit.view' => 'View audit trail.',

        // Settings / governance
        'settings.policies' => 'Manage org-wide policy toggles (scopes, approvals, thresholds).',
        'settings.users' => 'Create/edit/deactivate console users (within allowed roles).',
        'settings.roles' => 'Edit role ↔ permission matrix (who can grant what).',
        'settings.system' => 'Platform-level settings reserved for system admin.',
    ];
}

/**
 * MVP route/action IDs → permissions required (AND).
 * Pair HTTP method + action in your router; this map keys the action id only.
 *
 * @return array<string, list<string>>
 */
function str_console_route_permissions(): array
{
    $auth = ['auth.session'];

    return [
        // Auth (public login; logout requires session)
        'auth.login' => [],
        'auth.login.submit' => [],
        'auth.forgot' => [],
        'auth.forgot.submit' => [],
        'auth.reset' => [],
        'auth.reset.submit' => [],
        'auth.logout' => [...$auth],

        // Dashboard
        'dashboard.index' => [...$auth, 'dashboard.view'],

        // Global search: any logged-in user may open the route; results are filtered by customers.list / loans.list inside SearchController + SearchRepository.
        'search.index' => [...$auth],

        // Customers
        'customers.index' => [...$auth, 'customers.list'],
        'customers.show' => [...$auth, 'customers.view'],
        'customers.create' => [...$auth, 'customers.create'],
        'customers.store' => [...$auth, 'customers.create'],
        'customers.edit' => [...$auth, 'customers.edit'],
        'customers.update' => [...$auth, 'customers.edit'],
        'customers.deactivate' => [...$auth, 'customers.edit'],
        'customers.assign' => [...$auth, 'customers.assign'],

        // Loans
        'loans.index' => [...$auth, 'loans.list'],
        'loans.show' => [...$auth, 'loans.view'],
        'loans.create' => [...$auth, 'loans.create'],
        'loans.store' => [...$auth, 'loans.create'],
        'loans.edit' => [...$auth, 'loans.edit'],
        'loans.update' => [...$auth, 'loans.edit'],
        'loans.submit' => [...$auth, 'loans.submit'],
        'loans.approve' => [...$auth, 'loans.approve'],
        'loans.reject' => [...$auth, 'loans.reject'],
        'loans.disburse' => [...$auth, 'loans.disburse'],
        'loans.close' => [...$auth, 'loans.close'],
        'loans.accrue' => [...$auth, 'payments.record'],
        'loans.payment' => [...$auth, 'payments.record'],
        'loans.payment_void' => [...$auth, 'payments.void'],
        'loans.payment_adjust' => [...$auth, 'payments.adjust'],

        // Loan products
        'loan_products.index' => [...$auth, 'loan_products.list'],
        'loan_products.show' => [...$auth, 'loan_products.view'],
        'loan_products.create' => [...$auth, 'loan_products.create'],
        'loan_products.store' => [...$auth, 'loan_products.create'],
        'loan_products.edit' => [...$auth, 'loan_products.edit'],
        'loan_products.update' => [...$auth, 'loan_products.edit'],
        'loan_products.retire' => [...$auth, 'loan_products.retire'],

        // Payments (often nested under loan in UI)
        'payments.index' => [...$auth, 'payments.list'],
        'payments.store' => [...$auth, 'payments.record'],

        // Documents (customer or loan context)
        'documents.index' => [...$auth, 'documents.view'],
        'documents.store' => [...$auth, 'documents.upload'],
        'documents.destroy' => [...$auth, 'documents.delete'],

        'customers.documents.store' => [...$auth, 'documents.upload'],
        'customers.documents.download' => [...$auth, 'documents.view'],
        'customers.documents.destroy' => [...$auth, 'documents.delete'],

        // Bulk upload
        'bulk_upload.customers' => [...$auth, 'bulk_upload.customers'],
        'bulk_upload.loans' => [...$auth, 'bulk_upload.loans'],

        // Reports & audit
        'reports.index' => [...$auth, 'reports.view'],
        'reports.export' => [...$auth, 'reports.view', 'reports.export'],
        'audit.index' => [...$auth, 'audit.view'],

        // Settings
        'settings.policies' => [...$auth, 'settings.policies'],
        'settings.users' => [...$auth, 'settings.users'],
        'settings.users.deactivate' => [...$auth, 'settings.users'],
        'settings.roles' => [...$auth, 'settings.roles'],
        'settings.system' => [...$auth, 'settings.system'],

        'account.profile' => [...$auth],
        'account.profile.update' => [...$auth],
        'account.password' => [...$auth],
        'account.password.update' => [...$auth],
    ];
}

/**
 * Seed defaults when DB grants are empty (override via settings.roles in production).
 * '*' = all keys from str_console_permission_catalog().
 *
 * @return array<string, list<string>>
 */
function str_console_default_role_grants(): array
{
    $all = array_keys(str_console_permission_catalog());

    $officerCore = [
        'auth.session',
        'dashboard.view',
        'customers.list',
        'customers.view',
        'customers.create',
        'customers.edit',
        'documents.view',
        'documents.upload',
        'loans.list',
        'loans.view',
        'loans.create',
        'loans.edit',
        'loans.submit',
        'payments.list',
        'payments.record',
        'loan_products.list',
        'loan_products.view',
        'reports.view',
    ];

    $admin = array_values(array_diff($all, ['settings.system', 'settings.roles']));

    return [
        'system_admin' => ['*'],
        'admin' => $admin,
        'manager' => array_values(array_unique([
            ...$officerCore,
            'data.view_all_customers',
            'data.view_all_loans',
            'customers.assign',
            'customers.view_sensitive_ids',
            'loans.approve',
            'loans.reject',
            'loans.disburse',
            'bulk_upload.customers',
            'bulk_upload.loans',
            'reports.export',
            'audit.view',
        ])),
        'credit_officer' => $officerCore,
    ];
}

/**
 * Resolve '*' grants to concrete keys.
 *
 * @param list<string> $grants
 * @return list<string>
 */
function str_console_expand_grants(array $grants): array
{
    if (in_array('*', $grants, true)) {
        return array_keys(str_console_permission_catalog());
    }
    return array_values(array_unique($grants));
}

/**
 * Effective permission keys for a role at login: code defaults, optionally overridden in
 * console_settings as JSON array under key roles.grants.{role_key}.
 *
 * @return list<string>
 */
/**
 * Normalize DB extra_grants_json into a list of permission keys (may be empty).
 *
 * @return list<string>
 */
function str_console_parse_extra_grants_json(mixed $raw): array
{
    if ($raw === null || $raw === '') {
        return [];
    }
    if (is_string($raw)) {
        $trim = trim($raw);
        if ($trim === '') {
            return [];
        }
        $decoded = json_decode($trim, true);
    } elseif (is_array($raw)) {
        $decoded = $raw;
    } else {
        return [];
    }
    if (!is_array($decoded)) {
        return [];
    }
    $keys = [];
    foreach ($decoded as $k) {
        if (is_string($k) && $k !== '') {
            $keys[] = $k;
        }
    }
    return array_values(array_unique($keys));
}

/**
 * Role grants from code + DB role matrix, merged with per-user extra keys from console_users.extra_grants_json.
 *
 * @return list<string>
 */
function str_console_user_login_grants(string $roleKey, mixed $extraGrantsJson): array
{
    $base = str_console_role_grants_for($roleKey);
    $extra = str_console_parse_extra_grants_json($extraGrantsJson);
    if ($extra === []) {
        return $base;
    }
    if (!str_console_validate_permission_keys($extra)) {
        return $base;
    }
    return array_values(array_unique([...$base, ...$extra]));
}

function str_console_role_grants_for(string $roleKey): array
{
    $defaults = str_console_default_role_grants();
    if ($roleKey === 'system_admin') {
        return ['*'];
    }
    if (!isset($defaults[$roleKey])) {
        return [];
    }
    $base = $defaults[$roleKey];
    if (!class_exists('ConsoleSettingRepository', false)) {
        return $base;
    }
    if (!function_exists('str_console_database_ready') || !str_console_database_ready()) {
        return $base;
    }

    try {
        $raw = ConsoleSettingRepository::get('roles.grants.' . $roleKey);
        if ($raw === null || trim($raw) === '') {
            return $base;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $base;
        }
        $keys = [];
        foreach ($decoded as $k) {
            if (is_string($k) && $k !== '') {
                $keys[] = $k;
            }
        }
        $keys[] = 'auth.session';
        return array_values(array_unique($keys));
    } catch (Throwable) {
        return $base;
    }
}

/**
 * @param list<string> $keys
 */
function str_console_validate_permission_keys(array $keys): bool
{
    $catalog = str_console_permission_catalog();
    foreach ($keys as $k) {
        if (!is_string($k) || $k === '' || !isset($catalog[$k])) {
            return false;
        }
    }
    return true;
}

/**
 * Roles that may receive custom grant lists in the database (not system_admin).
 *
 * @return list<string>
 */
function str_console_roles_with_editable_grants(): array
{
    return ['admin', 'manager', 'credit_officer'];
}

/**
 * Role keys an actor may assign when creating or editing console users.
 *
 * @return list<string>
 */
function str_console_assignable_role_keys(string $actorRoleKey): array
{
    $ordered = ['system_admin', 'admin', 'manager', 'credit_officer'];
    if ($actorRoleKey === 'system_admin') {
        return $ordered;
    }
    if ($actorRoleKey === 'admin') {
        return array_values(array_diff($ordered, ['system_admin']));
    }
    return [];
}

/**
 * Only system_admin may see other system_admin accounts (users list) and their audit trail.
 */
function str_console_may_view_system_admin_user_records(string $actorRoleKey): bool
{
    return $actorRoleKey === 'system_admin';
}

/**
 * @param list<string> $grants
 * @param list<string> $required
 */
function str_console_authorize(array $grants, array $required): bool
{
    $set = array_fill_keys(str_console_expand_grants($grants), true);
    foreach ($required as $key) {
        if (!isset($set[$key])) {
            return false;
        }
    }
    return true;
}

/**
 * @param list<string> $grants
 * @param string $routeId
 */
function str_console_authorize_route(array $grants, string $routeId): bool
{
    $map = str_console_route_permissions();
    if (!isset($map[$routeId])) {
        return false;
    }
    return str_console_authorize($grants, $map[$routeId]);
}

// Always load DB helpers with permissions so controllers never see undefined functions
// if an older bootstrap omitted config/database.php.
require_once __DIR__ . '/database.php';
