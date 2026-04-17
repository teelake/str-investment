<?php

declare(strict_types=1);

/**
 * Keeps page in range when totals change (filters, deletes, or bogus ?page= in URL).
 */
final class Pagination
{
    public static function normalizePage(int $page, int $total, int $perPage): int
    {
        $perPage = max(1, $perPage);
        $page = max(1, $page);
        if ($total <= 0) {
            return 1;
        }
        $maxPage = (int) max(1, ceil($total / $perPage));

        return min($page, $maxPage);
    }

    /** Cap absurd page numbers from crawlers / typos before hitting the DB. */
    public static function sanitizeRequestedPage(mixed $raw): int
    {
        $p = is_numeric($raw) ? (int) $raw : 1;

        return max(1, min($p, 50_000));
    }
}
