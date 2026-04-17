<?php

declare(strict_types=1);

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Base URL path for the console (e.g. /str-console), no trailing slash.
     */
    public static function basePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir = str_replace('\\', '/', dirname($script));
        if ($dir === '/' || $dir === '.') {
            return '';
        }
        return rtrim($dir, '/');
    }

    /**
     * Path relative to console root, always starting with /, no trailing slash except root.
     */
    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $path = '/' . trim(str_replace('\\', '/', $path), '/');
        $base = self::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path === '' ? '/' : $path;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Resolve a path relative to the site root (parent of the console folder).
     * Example: asset('assets/styles.css') → /strinvestment/assets/styles.css when console is /strinvestment/str-console.
     */
    public static function asset(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $bp = self::basePath();
        if ($bp === '' || $bp === '/') {
            return '/' . $relativePath;
        }
        $parent = str_replace('\\', '/', dirname($bp));
        if ($parent === '/' || $parent === '.' || $parent === '') {
            return '/' . $relativePath;
        }
        return rtrim($parent, '/') . '/' . $relativePath;
    }

    /**
     * Absolute origin for this console app (no trailing slash). Set STR_CONSOLE_PUBLIC_URL when behind proxies or CLI.
     */
    public static function publicBaseUrl(): string
    {
        $env = getenv('STR_CONSOLE_PUBLIC_URL');
        if (is_string($env) && trim($env) !== '') {
            return rtrim(trim($env), '/');
        }
        if (defined('STR_CONSOLE_PUBLIC_URL') && is_string(STR_CONSOLE_PUBLIC_URL) && STR_CONSOLE_PUBLIC_URL !== '') {
            return rtrim(STR_CONSOLE_PUBLIC_URL, '/');
        }

        $https = false;
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            $https = true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            $https = true;
        }
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host . self::basePath();
    }
}
