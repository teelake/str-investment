<?php

declare(strict_types=1);

/**
 * HTTP routes for str-console.
 *
 * Each entry: [ METHOD, path, ControllerClass::class, actionMethod, routeId ]
 * If path is a PCRE pattern (starts and ends with #), captures are passed as int args to the action.
 *
 * @return list<array{0: string, 1: string, 2: class-string<BaseController>, 3: string, 4: string}>
 */
function str_console_routes(): array
{
    return [
        ['GET', '/', DashboardController::class, 'index', 'dashboard.index'],
        ['GET', '/login', AuthController::class, 'showLogin', 'auth.login'],
        ['POST', '/login', AuthController::class, 'login', 'auth.login.submit'],
        ['POST', '/logout', AuthController::class, 'logout', 'auth.logout'],

        ['GET', '/customers', CustomersController::class, 'index', 'customers.index'],
        ['GET', '/customers/create', CustomersController::class, 'create', 'customers.create'],
        ['POST', '/customers', CustomersController::class, 'store', 'customers.store'],

        ['GET', '#^/customers/(\d+)/edit$#', CustomersController::class, 'edit', 'customers.edit'],
        ['POST', '#^/customers/(\d+)/update$#', CustomersController::class, 'update', 'customers.update'],

        ['GET', '/loans', LoansController::class, 'index', 'loans.index'],
        ['GET', '/loans/create', LoansController::class, 'create', 'loans.create'],
        ['POST', '/loans', LoansController::class, 'store', 'loans.store'],

        ['GET', '/loan-products', LoanProductsController::class, 'index', 'loan_products.index'],
        ['GET', '/loan-products/create', LoanProductsController::class, 'create', 'loan_products.create'],
        ['POST', '/loan-products', LoanProductsController::class, 'store', 'loan_products.store'],
        ['GET', '#^/loan-products/(\d+)/edit$#', LoanProductsController::class, 'edit', 'loan_products.edit'],
        ['POST', '#^/loan-products/(\d+)/update$#', LoanProductsController::class, 'update', 'loan_products.update'],
        ['POST', '#^/loan-products/(\d+)/retire$#', LoanProductsController::class, 'retire', 'loan_products.retire'],

        ['GET', '#^/loans/(\d+)$#', LoansController::class, 'show', 'loans.show'],
        ['POST', '#^/loans/(\d+)/submit$#', LoansController::class, 'submit', 'loans.submit'],
        ['POST', '#^/loans/(\d+)/approve$#', LoansController::class, 'approve', 'loans.approve'],
        ['POST', '#^/loans/(\d+)/reject$#', LoansController::class, 'reject', 'loans.reject'],
        ['POST', '#^/loans/(\d+)/disburse$#', LoansController::class, 'disburse', 'loans.disburse'],
        ['POST', '#^/loans/(\d+)/payment$#', LoansController::class, 'payment', 'loans.payment'],

        ['GET', '/settings/policies', SettingsController::class, 'policies', 'settings.policies'],
        ['POST', '/settings/policies', SettingsController::class, 'savePolicies', 'settings.policies'],

        ['GET', '/search', SearchController::class, 'index', 'search.index'],
        ['GET', '/audit', AuditController::class, 'index', 'audit.index'],

        ['GET', '/reports', ReportsController::class, 'index', 'reports.index'],
        ['GET', '/reports/export', ReportsController::class, 'export', 'reports.export'],

        ['GET', '/bulk-upload/customers', BulkUploadController::class, 'customersForm', 'bulk_upload.customers'],
        ['POST', '/bulk-upload/customers', BulkUploadController::class, 'customersImport', 'bulk_upload.customers'],
        ['GET', '/bulk-upload/loans', BulkUploadController::class, 'loansForm', 'bulk_upload.loans'],
        ['POST', '/bulk-upload/loans', BulkUploadController::class, 'loansImport', 'bulk_upload.loans'],

        ['GET', '#^/customers/(\d+)$#', CustomersController::class, 'show', 'customers.show'],
        ['POST', '#^/customers/(\d+)/documents$#', CustomersController::class, 'documentStore', 'customers.documents.store'],
        ['GET', '#^/customers/(\d+)/documents/(\d+)/file$#', CustomersController::class, 'documentDownload', 'customers.documents.download'],
        ['POST', '#^/customers/(\d+)/documents/(\d+)/delete$#', CustomersController::class, 'documentDestroy', 'customers.documents.destroy'],
    ];
}
