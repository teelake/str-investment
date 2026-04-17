<?php

declare(strict_types=1);

final class Router
{
    public static function dispatch(): void
    {
        $method = Request::method();
        $path = Request::path();

        foreach (str_console_routes() as $route) {
            [$m, $p, $class, $action, $routeId] = $route;
            if ($m !== $method) {
                continue;
            }

            $params = [];
            if (is_string($p) && strlen($p) >= 2 && $p[0] === '#' && str_ends_with($p, '#')) {
                if (!preg_match($p, $path, $matches)) {
                    continue;
                }
                /** @var list<string> $captured */
                $captured = array_slice($matches, 1);
                $params = array_map(static fn (string $v): int => (int) $v, $captured);
            } elseif ($p !== $path) {
                continue;
            }

            if (!self::authorizeRequest($routeId)) {
                return;
            }

            /** @var BaseController $controller */
            $controller = new $class();
            if ($params !== []) {
                $controller->{$action}(...$params);
            } else {
                $controller->{$action}();
            }
            return;
        }

        ErrorPage::respond(404, 'Page not found', 'That URL does not exist in STR Console.');
    }

    private static function authorizeRequest(string $routeId): bool
    {
        $map = str_console_route_permissions();
        if (!isset($map[$routeId])) {
            ErrorPage::respond(500, 'Configuration error', 'This route is not registered in the permissions map.');
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
            ErrorPage::respond(403, 'Access denied', 'You do not have permission for this action.');
            return false;
        }

        return true;
    }
}
