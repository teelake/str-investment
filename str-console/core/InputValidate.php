<?php

declare(strict_types=1);

/**
 * Shared server-side checks for text fields (length, control chars). Output escaping remains the XSS primary defense.
 */
final class InputValidate
{
    public const EMAIL_MAX = 190;

    public const PERSON_NAME_MAX = 190;

    public const PASSWORD_MAX_BYTES = 128;

    public const REJECTION_REASON_MAX = 500;

    /** Strip NUL / CR; trim. Returns null if empty after trim. */
    public static function optionalPersonName(string $raw): ?string
    {
        $t = trim(str_replace(["\0", "\r"], '', $raw));
        if ($t === '') {
            return null;
        }
        if (mb_strlen($t) > self::PERSON_NAME_MAX) {
            return null;
        }
        return $t;
    }

    public static function emailOk(string $email): bool
    {
        if ($email === '' || mb_strlen($email) > self::EMAIL_MAX) {
            return false;
        }
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
