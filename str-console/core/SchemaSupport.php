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
     * $likeParam is a single bound name (e.g. ':q') reused for all LIKE clauses.
     *
     * @return array{0: list<string>, 1: array<string, mixed>}
     */
    public static function customerMatchOrParts(string $likeParam, string $likeValue): array
    {
        $parts = ['c.full_name LIKE ' . $likeParam, 'c.phone LIKE ' . $likeParam];
        if (self::customersHasColumn('email')) {
            $parts[] = 'c.email LIKE ' . $likeParam;
        }
        $parts[] = 'c.nin LIKE ' . $likeParam;
        $parts[] = 'c.bvn LIKE ' . $likeParam;

        return [$parts, [$likeParam => $likeValue]];
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
