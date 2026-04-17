<?php

declare(strict_types=1);

abstract class BaseController
{
    /**
     * @param array<string, mixed> $params
     */
    protected function render(string $view, array $params = []): void
    {
        $viewsDir = STR_CONSOLE_ROOT . '/views';
        $file = $viewsDir . '/' . $view . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            echo 'View not found.';
            return;
        }

        extract($params, EXTR_SKIP);
        $basePath = Request::basePath();

        ob_start();
        require $file;
        $content = ob_get_clean();

        require $viewsDir . '/layout.php';
    }

    protected function redirect(string $path, int $status = 302): void
    {
        $base = Request::basePath();
        if ($path !== '' && $path[0] === '/') {
            $loc = $base . $path;
        } else {
            $loc = $base . '/' . $path;
        }
        header('Location: ' . $loc, true, $status);
        exit;
    }
}
