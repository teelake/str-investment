<?php

declare(strict_types=1);

/**
 * CSRF tokens for session-backed forms and a honeypot field for simple bots on public POSTs.
 */
final class FormGuard
{
    private const SESSION_KEY = 'str_console_csrf_token';

    public const POST_KEY = '__csrf';

    /** Must remain empty; filled by bots. */
    public const HONEYPOT_NAME = 'support_line_extension';

    public static function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        } elseif (strlen($_SESSION[self::SESSION_KEY]) < 32) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function rotate(): void
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    public static function validatePost(): bool
    {
        $posted = trim((string) Request::post(self::POST_KEY, ''));
        $expected = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        return $posted !== '' && $expected !== '' && hash_equals($expected, $posted);
    }

    public static function honeypotTriggered(): bool
    {
        if (!isset($_POST[self::HONEYPOT_NAME])) {
            return false;
        }
        return trim((string) $_POST[self::HONEYPOT_NAME]) !== '';
    }
}
