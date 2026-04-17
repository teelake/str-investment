<?php

declare(strict_types=1);

/**
 * Sends password reset email when STR_CONSOLE_MAIL_FROM is set; otherwise logs (dev) or no-op.
 */
final class PasswordResetNotifier
{
    public static function mailFromConfigured(): bool
    {
        $from = getenv('STR_CONSOLE_MAIL_FROM');
        if (is_string($from) && trim($from) !== '') {
            return true;
        }
        return defined('STR_CONSOLE_MAIL_FROM') && is_string(STR_CONSOLE_MAIL_FROM) && STR_CONSOLE_MAIL_FROM !== '';
    }

    public static function mailFrom(): string
    {
        $from = getenv('STR_CONSOLE_MAIL_FROM');
        if (is_string($from) && trim($from) !== '') {
            return trim($from);
        }
        if (defined('STR_CONSOLE_MAIL_FROM') && is_string(STR_CONSOLE_MAIL_FROM)) {
            return trim(STR_CONSOLE_MAIL_FROM);
        }
        return '';
    }

    public static function devLinkInUiEnabled(): bool
    {
        if (getenv('STR_CONSOLE_DEV_RESET_LINK') === '1') {
            return true;
        }
        return defined('STR_CONSOLE_DEV_RESET_LINK') && STR_CONSOLE_DEV_RESET_LINK === true;
    }

    /**
     * @return bool True if PHP mail() reported success
     */
    public static function sendResetLink(string $toEmail, string $resetUrl): bool
    {
        $from = self::mailFrom();
        if ($from === '') {
            if (self::devLinkInUiEnabled()) {
                error_log('[STR Console] Password reset link for ' . $toEmail . ' (dev): ' . $resetUrl);
            }
            return false;
        }

        $subject = 'Reset your STR Console password';
        $body = "We received a request to reset your STR Console password.\r\n\r\n"
            . "Open this link within one hour:\r\n\r\n"
            . $resetUrl . "\r\n\r\n"
            . "If you did not request this, you can ignore this message.\r\n";
        $headers = 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";

        return @mail($toEmail, $subject, $body, $headers);
    }
}
