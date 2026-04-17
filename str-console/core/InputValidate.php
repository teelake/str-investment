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

    /**
     * Nigeria NIN (NIMC) and BVN (CBN) are 11-digit numeric identifiers.
     * Non-digits are stripped; empty after strip means “not provided”.
     *
     * @return null Omit field. non-empty-string Exactly 11 digits. false Invalid (wrong length or non-numeric after strip).
     */
    public static function optionalNinBvn(string $raw): null|string|false
    {
        $digits = preg_replace('/\D/', '', trim(str_replace(["\0", "\r"], '', $raw))) ?? '';
        if ($digits === '') {
            return null;
        }
        if (strlen($digits) !== 11 || !ctype_digit($digits)) {
            return false;
        }
        return $digits;
    }

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
