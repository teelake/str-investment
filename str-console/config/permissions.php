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
        'auth.logout' => [...$auth],

        // Dashboard
        'dashboard.index' => [...$auth, 'dashboard.view'],

        // Global search (customers/loans unified)
        'search.index' => [...$auth, 'customers.list', 'loans.list'],
        'search.query' => [...$auth, 'customers.list', 'loans.list'],

        // Customers
        'customers.index' => [...$auth, 'customers.list'],
        'customers.show' => [...$auth, 'customers.view'],
        'customers.create' => [...$auth, 'customers.create'],
        'customers.store' => [...$auth, 'customers.create'],
        'customers.edit' => [...$auth, 'customers.edit'],
        'customers.update' => [...$auth, 'customers.edit'],
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
        'loans.payment' => [...$auth, 'payments.record'],

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
        'settings.roles' => [...$auth, 'settings.roles'],
        'settings.system' => [...$auth, 'settings.system'],
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
