<?php

declare(strict_types=1);

/**
 * Introspects live DB columns so read paths work before optional migrations are applied.
 */
final class SchemaSupport
{
    /** @var array<string, true>|null */
    private static ?array $customerColumns = null;

    private static ?bool $regexpReplaceOk = null;

    /**
     * @return array<string, true> lowercase column names
     */
    public static function customerColumnSet(): array
    {
        if (self::$customerColumns !== null) {
            return self::$customerColumns;
        }
        self::$customerColumns = [];
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->query(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers'"
            );
            if ($stmt !== false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $n = strtolower((string) ($row['COLUMN_NAME'] ?? ''));
                    if ($n !== '') {
                        self::$customerColumns[$n] = true;
                    }
                }
            }
        } catch (Throwable) {
            // leave empty; callers may fall back or fail clearly
        }

        return self::$customerColumns;
    }

    public static function customersHasColumn(string $name): bool
    {
        return isset(self::customerColumnSet()[strtolower($name)]);
    }

    public static function regexpReplaceSupported(): bool
    {
        if (self::$regexpReplaceOk !== null) {
            return self::$regexpReplaceOk;
        }
        self::$regexpReplaceOk = false;
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->query("SELECT REGEXP_REPLACE('a1b','[^0-9]','') AS x");
            if ($stmt !== false) {
                self::$regexpReplaceOk = true;
            }
        } catch (Throwable) {
            self::$regexpReplaceOk = false;
        }

        return self::$regexpReplaceOk;
    }

    /**
     * OR-group for text search on customers (name, phone, optional email, nin, bvn).
     * Each LIKE uses a distinct placeholder — required when PDO::ATTR_EMULATE_PREPARES is false
     * (MySQL native prepares do not allow the same name bound multiple times).
     *
     * @param string $namePrefix Alphanumeric prefix for placeholder names (e.g. 'sc', 'rptq').
     * @return array{0: list<string>, 1: array<string, mixed>}
     */
    public static function customerMatchOrParts(string $likeValue, string $namePrefix = 'cm'): array
    {
        $p = preg_replace('/[^a-zA-Z0-9_]/', '', $namePrefix);
        if ($p === '') {
            $p = 'cm';
        }
        $parts = [
            'c.full_name LIKE :' . $p . '_fn',
            'c.phone LIKE :' . $p . '_ph',
        ];
        $params = [
            ':' . $p . '_fn' => $likeValue,
            ':' . $p . '_ph' => $likeValue,
        ];
        if (self::customersHasColumn('email')) {
            $parts[] = 'c.email LIKE :' . $p . '_em';
            $params[':' . $p . '_em'] = $likeValue;
        }
        $parts[] = 'c.nin LIKE :' . $p . '_nin';
        $params[':' . $p . '_nin'] = $likeValue;
        $parts[] = 'c.bvn LIKE :' . $p . '_bvn';
        $params[':' . $p . '_bvn'] = $likeValue;

        return [$parts, $params];
    }

    /**
     * Append phone digit match (strip non-digits vs loose LIKE fallback).
     *
     * @param array<string, mixed> $params
     */
    public static function appendPhoneDigitMatch(array &$parts, array &$params, string $digParam, string $digitsOnly): void
    {
        if (strlen($digitsOnly) < 2) {
            return;
        }
        $escaped = '%' . addcslashes($digitsOnly, '%_\\') . '%';
        if (self::regexpReplaceSupported()) {
            $parts[] = "REGEXP_REPLACE(c.phone, '[^0-9]', '') LIKE " . $digParam;
            $params[$digParam] = $escaped;
        } else {
            $parts[] = 'c.phone LIKE ' . $digParam;
            $params[$digParam] = '%' . addcslashes($digitsOnly, '%_\\') . '%';
        }
    }

    /**
     * SELECT list for customers list (paginate) with optional email column.
     */
    public static function customerSelectColumnsPrefixed(): string
    {
        $cols = 'c.id, c.full_name, c.phone';
        if (self::customersHasColumn('email')) {
            $cols .= ', c.email';
        }
        $cols .= ', c.address, c.nin, c.bvn, c.assigned_user_id, c.created_at, c.updated_at';

        return $cols;
    }

    public static function customerActiveSql(): string
    {
        return self::customersHasColumn('is_active') ? ' AND c.is_active = 1' : '';
    }
}
