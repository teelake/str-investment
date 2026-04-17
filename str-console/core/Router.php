<?php

declare(strict_types=1);

final class Router
{
    public static function dispatch(): void
    {
        $method = Request::method();
        $path = Request::path();

        foreach (str_console_routes() as [$m, $p, $class, $action, $routeId]) {
            if ($m !== $method || $p !== $path) {
                continue;
            }

            if (!self::authorizeRequest($routeId)) {
                return;
            }

            /** @var BaseController $controller */
            $controller = new $class();
            $controller->{$action}();
            return;
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Not found</title></head><body><p>Not found.</p></body></html>';
    }

    private static function authorizeRequest(string $routeId): bool
    {
        $map = str_console_route_permissions();
        if (!isset($map[$routeId])) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
            echo 'Route is not registered in permissions map.';
            return false;
        }

        $required = $map[$routeId];
        $needsSession = in_array('auth.session', $required, true);

        if ($needsSession && !ConsoleAuth::check()) {
            $q = http_build_query(['next' => Request::path()]);
            header('Location: ' . Request::basePath() . '/login?' . $q, true, 302);
            return false;
        }

        $grants = ConsoleAuth::grants();
        if (!str_console_authorize_route($grants, $routeId)) {
            http_response_code(403);
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Forbidden</title></head><body><p>You do not have access to this action.</p></body></html>';
            return false;
        }

        return true;
    }
}
