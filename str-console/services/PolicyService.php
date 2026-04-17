<?php

declare(strict_types=1);

/**
 * Org policies stored in console_settings. Missing keys fall back to defaults (secure-by-default scoping).
 */
final class PolicyService
{
    public static function bool(string $key, bool $default): bool
    {
        $raw = ConsoleSettingRepository::get($key);
        if ($raw === null) {
            return $default;
        }
        $raw = strtolower(trim($raw));
        return $raw === '1' || $raw === 'true' || $raw === 'yes' || $raw === 'on';
    }

    /**
     * When true, users without data.view_all_customers only see customers assigned to them (unless policy turns scoping off).
     */
    public static function scopeCustomersByAssignment(): bool
    {
        return self::bool('scope.customers_by_assignment', true);
    }

    /**
     * When true, users without data.view_all_loans only see loans tied to their assignment (loan or customer).
     */
    public static function scopeLoansByAssignment(): bool
    {
        return self::bool('scope.loans_by_assignment', true);
    }

    /**
     * @param list<string> $grants
     */
    public static function customersWideAccess(array $grants): bool
    {
        if (str_console_authorize($grants, ['data.view_all_customers'])) {
            return true;
        }
        return !self::scopeCustomersByAssignment();
    }

    /**
     * @param list<string> $grants
     */
    public static function loansWideAccess(array $grants): bool
    {
        if (str_console_authorize($grants, ['data.view_all_loans'])) {
            return true;
        }
        return !self::scopeLoansByAssignment();
    }

    /**
     * When true, active loans accrue one charge (booked monthly rate on the balance) into new ledger lines every
     * 30 days from the previous line (no payment) until the booked term (period_months from disbursement) or the
     * as-of date, whichever comes first.
     */
    public static function ledgerAutoAccrue(): bool
    {
        return self::bool('ledger.auto_accrue', true);
    }

    /**
     * Plain-text banner for all signed-in users (set under Settings → System).
     */
    public static function maintenanceNotice(): string
    {
        $raw = ConsoleSettingRepository::get('system.maintenance_notice');
        if ($raw === null) {
            return '';
        }
        return trim($raw);
    }
}
