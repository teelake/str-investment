<?php

declare(strict_types=1);

/**
 * HTTP routes for str-console.
 *
 * Each entry: [ METHOD, path, ControllerClass::class, actionMethod, routeId ]
 * routeId must exist in str_console_route_permissions().
 *
 * Controllers must be loaded before str_console_routes() is evaluated (see bootstrap order).
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
    ];
}
