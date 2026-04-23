<?php

declare(strict_types=1);

/**
 * Shared server-side checks for text fields (length, control chars). Output escaping remains the XSS primary defense.
 */
final class InputValidate
{
    /** Inclusive floor for loan disbursement, payments, and accrual dates (calendar). */
    public const LOAN_EVENT_DATE_MIN = '2000-01-01';

    public const EMAIL_MAX = 190;

    public const PERSON_NAME_MAX = 190;

    public const PASSWORD_MIN_LENGTH = 8;

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

    /**
     * Nigeria mobile stored as local MSISDN only: 11 digits, no country code (+234).
     * Non-digits are stripped; same digit-length rules as NIN/BVN.
     *
     * @return null Empty / whitespace-only after strip. non-empty-string 11 digits. false Invalid length.
     */
    public static function optionalPhone11(string $raw): null|string|false
    {
        return self::optionalNinBvn($raw);
    }

    /**
     * @return string Exactly 11 local digits (no country code), or false if empty or invalid.
     */
    public static function requiredPhone11(string $raw): string|false
    {
        $v = self::optionalNinBvn($raw);
        if ($v === null || $v === false) {
            return false;
        }
        return $v;
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

    /**
     * Optional customer email: empty → null; otherwise must be valid (normalized lowercase).
     *
     * @return null|non-empty-string|false null if omitted, false if invalid
     */
    public static function optionalCustomerEmail(string $raw): null|string|false
    {
        $t = trim(str_replace(["\0", "\r"], '', $raw));
        if ($t === '') {
            return null;
        }
        if (mb_strlen($t) > self::EMAIL_MAX) {
            return false;
        }
        if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return mb_strtolower($t);
    }

    /** Real calendar day Y-m-d, or null if malformed. */
    public static function parseDateYmd(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return null;
        }
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
        if ($dt === false || $dt->format('Y-m-d') !== $raw) {
            return null;
        }
        return $raw;
    }

    public static function todayYmd(): string
    {
        return (new DateTimeImmutable('today'))->format('Y-m-d');
    }

    /**
     * Disbursement (book) and optional funds-released date: from LOAN_EVENT_DATE_MIN through
     * **today** (backdating allowed; no future / “front” dates). $loanCreatedAt is unused; kept
     * for call-site compatibility. Post-disburse rules use loanPostDisburseDateOk.
     *
     * @param string $ymd From parseDateYmd
     * @param string $loanCreatedAt Unused
     */
    public static function loanDisburseDateOk(string $ymd, string $loanCreatedAt): bool
    {
        $today = self::todayYmd();
        return $ymd >= self::LOAN_EVENT_DATE_MIN && $ymd <= $today;
    }

    /**
     * Payment date or accrual as-of: not before loan booking (created_at) or disbursement,
     * whichever is later; not after today.
     *
     * @param string $ymd From parseDateYmd
     * @param string $disbursedAt DB datetime or date string
     * @param string $bookedAt Loan created_at (booking). Empty = ignore booking floor (disburse-only checks).
     */
    public static function loanPostDisburseDateOk(string $ymd, string $disbursedAt, string $bookedAt = ''): bool
    {
        $today = self::todayYmd();
        $disb = substr(trim($disbursedAt), 0, 10);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $disb)) {
            return false;
        }
        $floor = max(self::LOAN_EVENT_DATE_MIN, $disb);
        $book = substr(trim($bookedAt), 0, 10);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $book)) {
            $floor = max($floor, $book);
        }

        return $ymd >= $floor && $ymd <= $today;
    }
}
