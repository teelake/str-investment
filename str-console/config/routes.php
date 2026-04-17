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

        ['GET', '#^/customers/(\d+)$#', CustomersController::class, 'show', 'customers.show'],
        ['POST', '#^/customers/(\d+)/documents$#', CustomersController::class, 'documentStore', 'customers.documents.store'],
        ['GET', '#^/customers/(\d+)/documents/(\d+)/file$#', CustomersController::class, 'documentDownload', 'customers.documents.download'],
        ['POST', '#^/customers/(\d+)/documents/(\d+)/delete$#', CustomersController::class, 'documentDestroy', 'customers.documents.destroy'],
    ];
}
