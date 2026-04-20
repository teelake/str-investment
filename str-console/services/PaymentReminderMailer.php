<?php

declare(strict_types=1);

/**
 * Plain-text borrower emails using the same From address as other console mail.
 */
final class PaymentReminderMailer
{
    public static function mailFromConfigured(): bool
    {
        return PasswordResetNotifier::mailFromConfigured();
    }

    public static function send(string $toEmail, string $subject, string $bodyPlain): bool
    {
        $from = PasswordResetNotifier::mailFrom();
        if ($from === '') {
            return false;
        }
        $toEmail = trim($toEmail);
        if ($toEmail === '') {
            return false;
        }
        $headers = 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";

        return @mail($toEmail, $subject, $bodyPlain, $headers);
    }
}
