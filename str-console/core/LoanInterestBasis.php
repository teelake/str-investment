<?php

declare(strict_types=1);

/**
 * How recurring interest is computed for a loan (snapshot on the loan row; product defines defaults and allowed options).
 */
final class LoanInterestBasis
{
    public const REDUCING_BALANCE = 'reducing_balance';

    /** Monthly-rate charge on original booked principal each interest period (not on current outstanding). */
    public const FLAT_MONTHLY = 'flat_monthly';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::REDUCING_BALANCE, self::FLAT_MONTHLY];
    }

    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $t = trim($raw);

        return in_array($t, self::all(), true) ? $t : null;
    }

    /**
     * @param array<string, mixed> $productRow loan_products row
     */
    public static function isBasisAllowed(string $basis, array $productRow): bool
    {
        if ($basis === self::REDUCING_BALANCE) {
            return (int) ($productRow['allow_reducing_balance'] ?? 1) === 1;
        }
        if ($basis === self::FLAT_MONTHLY) {
            return (int) ($productRow['allow_flat_monthly'] ?? 1) === 1;
        }

        return false;
    }

    public static function label(string $basis): string
    {
        return match ($basis) {
            self::FLAT_MONTHLY => 'Flat monthly (on original principal)',
            self::REDUCING_BALANCE => 'Reducing balance',
            default => 'Reducing balance',
        };
    }

    /**
     * Default basis from product row (for new loans / CSV).
     *
     * @param array<string, mixed> $productRow
     */
    public static function defaultFromProduct(array $productRow): string
    {
        $d = self::normalize((string) ($productRow['default_interest_basis'] ?? ''));
        if ($d !== null && self::isBasisAllowed($d, $productRow)) {
            return $d;
        }
        if (self::isBasisAllowed(self::REDUCING_BALANCE, $productRow)) {
            return self::REDUCING_BALANCE;
        }
        if (self::isBasisAllowed(self::FLAT_MONTHLY, $productRow)) {
            return self::FLAT_MONTHLY;
        }

        return self::REDUCING_BALANCE;
    }
}
