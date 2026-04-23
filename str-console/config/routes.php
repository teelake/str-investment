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
        ['GET', '/forgot-password', AuthController::class, 'showForgotPassword', 'auth.forgot'],
        ['POST', '/forgot-password', AuthController::class, 'submitForgotPassword', 'auth.forgot.submit'],
        ['GET', '/reset-password', AuthController::class, 'showResetPassword', 'auth.reset'],
        ['POST', '/reset-password', AuthController::class, 'submitResetPassword', 'auth.reset.submit'],
        ['POST', '/logout', AuthController::class, 'logout', 'auth.logout'],

        ['GET', '/customers', CustomersController::class, 'index', 'customers.index'],
        ['GET', '/customers/create', CustomersController::class, 'create', 'customers.create'],
        ['POST', '/customers', CustomersController::class, 'store', 'customers.store'],

        ['GET', '#^/customers/(\d+)/edit$#', CustomersController::class, 'edit', 'customers.edit'],
        ['POST', '#^/customers/(\d+)/update$#', CustomersController::class, 'update', 'customers.update'],
        ['POST', '#^/customers/(\d+)/deactivate$#', CustomersController::class, 'deactivate', 'customers.deactivate'],

        ['GET', '/loans', LoansController::class, 'index', 'loans.index'],
        ['GET', '/loans/create', LoansController::class, 'create', 'loans.create'],
        ['POST', '/loans', LoansController::class, 'store', 'loans.store'],

        ['GET', '/loan-products', LoanProductsController::class, 'index', 'loan_products.index'],
        ['GET', '/loan-products/create', LoanProductsController::class, 'create', 'loan_products.create'],
        ['POST', '/loan-products', LoanProductsController::class, 'store', 'loan_products.store'],
        ['GET', '#^/loan-products/(\d+)$#', LoanProductsController::class, 'show', 'loan_products.show'],
        ['GET', '#^/loan-products/(\d+)/edit$#', LoanProductsController::class, 'edit', 'loan_products.edit'],
        ['POST', '#^/loan-products/(\d+)/update$#', LoanProductsController::class, 'update', 'loan_products.update'],
        ['POST', '#^/loan-products/(\d+)/retire$#', LoanProductsController::class, 'retire', 'loan_products.retire'],

        ['GET', '#^/loans/(\d+)/edit$#', LoansController::class, 'edit', 'loans.edit'],
        ['POST', '#^/loans/(\d+)/update$#', LoansController::class, 'update', 'loans.update'],
        ['GET', '#^/loans/(\d+)/ledger-export$#', LoansController::class, 'ledgerExport', 'loans.ledger_export'],
        ['GET', '#^/loans/(\d+)/ledger-print$#', LoansController::class, 'ledgerPrint', 'loans.ledger_print'],
        ['GET', '#^/loans/(\d+)$#', LoansController::class, 'show', 'loans.show'],
        ['POST', '#^/loans/(\d+)/submit$#', LoansController::class, 'submit', 'loans.submit'],
        ['POST', '#^/loans/(\d+)/approve$#', LoansController::class, 'approve', 'loans.approve'],
        ['POST', '#^/loans/(\d+)/reject$#', LoansController::class, 'reject', 'loans.reject'],
        ['POST', '#^/loans/(\d+)/disburse$#', LoansController::class, 'disburse', 'loans.disburse'],
        ['POST', '#^/loans/(\d+)/accrue$#', LoansController::class, 'accrue', 'loans.accrue'],
        ['POST', '#^/loans/(\d+)/payment$#', LoansController::class, 'payment', 'loans.payment'],
        ['POST', '#^/loans/(\d+)/close$#', LoansController::class, 'close', 'loans.close'],
        ['POST', '#^/loans/(\d+)/payment-void$#', LoansController::class, 'paymentVoid', 'loans.payment_void'],
        ['POST', '#^/loans/(\d+)/payment-adjust$#', LoansController::class, 'paymentAdjust', 'loans.payment_adjust'],
        ['POST', '#^/loans/(\d+)/reminder-installment$#', LoansController::class, 'saveReminderInstallment', 'loans.reminder_installment'],

        ['GET', '/settings/policies', SettingsController::class, 'policies', 'settings.policies'],
        ['POST', '/settings/policies', SettingsController::class, 'savePolicies', 'settings.policies'],

        ['GET', '/settings/payment-reminders', SettingsPaymentRemindersController::class, 'index', 'settings.payment_reminders'],
        ['POST', '/settings/payment-reminders', SettingsPaymentRemindersController::class, 'save', 'settings.payment_reminders.save'],

        ['GET', '/settings/users', SettingsUsersController::class, 'index', 'settings.users'],
        ['GET', '/settings/users/create', SettingsUsersController::class, 'create', 'settings.users'],
        ['POST', '/settings/users', SettingsUsersController::class, 'store', 'settings.users'],
        ['GET', '#^/settings/users/(\d+)/edit$#', SettingsUsersController::class, 'edit', 'settings.users'],
        ['POST', '#^/settings/users/(\d+)/update$#', SettingsUsersController::class, 'update', 'settings.users'],
        ['POST', '#^/settings/users/(\d+)/deactivate$#', SettingsUsersController::class, 'deactivate', 'settings.users.deactivate'],

        ['GET', '/settings/roles', SettingsRolesController::class, 'index', 'settings.roles'],
        ['POST', '/settings/roles', SettingsRolesController::class, 'save', 'settings.roles'],

        ['GET', '/settings/system', SettingsSystemController::class, 'index', 'settings.system'],
        ['POST', '/settings/system', SettingsSystemController::class, 'save', 'settings.system'],

        ['GET', '/account/profile', AccountController::class, 'profile', 'account.profile'],
        ['POST', '/account/profile', AccountController::class, 'saveProfile', 'account.profile.update'],
        ['GET', '/account/password', AccountController::class, 'password', 'account.password'],
        ['POST', '/account/password', AccountController::class, 'savePassword', 'account.password.update'],

        ['GET', '/search', SearchController::class, 'index', 'search.index'],
        ['GET', '/audit', AuditController::class, 'index', 'audit.index'],

        ['GET', '/reports', ReportsController::class, 'index', 'reports.index'],
        ['GET', '/reports/export', ReportsController::class, 'export', 'reports.export'],

        ['GET', '/bulk-upload/customers', BulkUploadController::class, 'customersForm', 'bulk_upload.customers'],
        ['POST', '/bulk-upload/customers', BulkUploadController::class, 'customersImport', 'bulk_upload.customers'],
        ['GET', '/downloads/customers-import-template.csv', BulkUploadController::class, 'downloadCustomersTemplateCsv', 'bulk_upload.customers'],
        ['GET', '/bulk-upload/loans', BulkUploadController::class, 'loansForm', 'bulk_upload.loans'],
        ['POST', '/bulk-upload/loans', BulkUploadController::class, 'loansImport', 'bulk_upload.loans'],
        ['GET', '/downloads/loans-import-template.csv', BulkUploadController::class, 'downloadLoansTemplateCsv', 'bulk_upload.loans'],

        ['GET', '#^/customers/(\d+)$#', CustomersController::class, 'show', 'customers.show'],
        ['POST', '#^/customers/(\d+)/documents$#', CustomersController::class, 'documentStore', 'customers.documents.store'],
        ['GET', '#^/customers/(\d+)/documents/(\d+)/file$#', CustomersController::class, 'documentDownload', 'customers.documents.download'],
        ['POST', '#^/customers/(\d+)/documents/(\d+)/delete$#', CustomersController::class, 'documentDestroy', 'customers.documents.destroy'],
    ];
}
