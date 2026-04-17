<?php

declare(strict_types=1);

final class ReportRepository
{
    public const PER_PAGE = 25;

    public const EXPORT_MAX_ROWS = 5000;

    /** @var list<string> */
    public const LOAN_STATUSES = [
        'draft',
        'pending_approval',
        'approved',
        'active',
        'closed',
        'rejected',
    ];

    /**
     * @param list<string> $grants
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginateLoans(
        ?int $consoleUserId,
        array $grants,
        int $page,
        ?string $status,
        ?string $dateFromYmd,
        ?string $dateToYmd
    ): array {
        $page = max(1, $page);
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $wide = PolicyService::loansWideAccess($grants);
        $pdo = Database::pdo();

        if (!$wide && $consoleUserId === null) {
            return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }

        [$filterSql, $filterParams] = self::loanFilterClause($status, $dateFromYmd, $dateToYmd);
        $baseFrom = 'FROM loans l INNER JOIN customers c ON c.id = l.customer_id';

        if ($wide) {
            $countSql = 'SELECT COUNT(*) AS c ' . $baseFrom . ' WHERE 1=1' . $filterSql;
            $stmtCount = $pdo->prepare($countSql);
            $stmtCount->execute($filterParams);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT l.*, c.full_name AS customer_name, c.assigned_user_id AS customer_assigned_user_id
                 ' . $baseFrom . '
                 WHERE 1=1' . $filterSql . '
                 ORDER BY l.id DESC LIMIT :lim OFFSET :off'
            );
            foreach ($filterParams as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $scope = ' AND (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)';
            $params = array_merge($filterParams, [':uid' => $consoleUserId, ':uid2' => $consoleUserId]);
            $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c ' . $baseFrom . ' WHERE 1=1' . $scope . $filterSql);
            $stmtCount->execute($params);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT l.*, c.full_name AS customer_name, c.assigned_user_id AS customer_assigned_user_id
                 ' . $baseFrom . '
                 WHERE 1=1' . $scope . $filterSql . '
                 ORDER BY l.id DESC LIMIT :lim OFFSET :off'
            );
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * @param list<string> $grants
     * @return list<array<string, mixed>>
     */
    public function exportLoans(
        ?int $consoleUserId,
        array $grants,
        ?string $status,
        ?string $dateFromYmd,
        ?string $dateToYmd
    ): array {
        $wide = PolicyService::loansWideAccess($grants);
        $pdo = Database::pdo();
        if (!$wide && $consoleUserId === null) {
            return [];
        }
        [$filterSql, $filterParams] = self::loanFilterClause($status, $dateFromYmd, $dateToYmd);
        $baseFrom = 'FROM loans l INNER JOIN customers c ON c.id = l.customer_id';
        $lim = self::EXPORT_MAX_ROWS;

        if ($wide) {
            $stmt = $pdo->prepare(
                'SELECT l.id, l.customer_id, c.full_name AS customer_name, l.status, l.principal_amount,
                        l.rate_percent, l.period_months, l.created_at, l.disbursed_at, l.closed_at
                 ' . $baseFrom . '
                 WHERE 1=1' . $filterSql . '
                 ORDER BY l.id DESC LIMIT ' . (string) (int) $lim
            );
            $stmt->execute($filterParams);
        } else {
            $scope = ' AND (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)';
            $params = array_merge($filterParams, [':uid' => $consoleUserId, ':uid2' => $consoleUserId]);
            $stmt = $pdo->prepare(
                'SELECT l.id, l.customer_id, c.full_name AS customer_name, l.status, l.principal_amount,
                        l.rate_percent, l.period_months, l.created_at, l.disbursed_at, l.closed_at
                 ' . $baseFrom . '
                 WHERE 1=1' . $scope . $filterSql . '
                 ORDER BY l.id DESC LIMIT ' . (string) (int) $lim
            );
            $stmt->execute($params);
        }

        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    /**
     * @param list<string> $grants
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginateCustomers(
        ?int $consoleUserId,
        array $grants,
        int $page,
        ?string $dateFromYmd,
        ?string $dateToYmd
    ): array {
        $page = max(1, $page);
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $wide = PolicyService::customersWideAccess($grants);
        $pdo = Database::pdo();

        if (!$wide && $consoleUserId === null) {
            return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }

        [$filterSql, $filterParams] = self::customerDateClause($dateFromYmd, $dateToYmd);

        if ($wide) {
            $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c FROM customers c WHERE 1=1' . $filterSql);
            $stmtCount->execute($filterParams);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT c.id, c.full_name, c.phone, c.assigned_user_id, c.created_at, c.updated_at,
                        COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_user_label
                 FROM customers c
                 LEFT JOIN console_users cu ON cu.id = c.assigned_user_id
                 WHERE 1=1' . $filterSql . '
                 ORDER BY c.id DESC LIMIT :lim OFFSET :off'
            );
            foreach ($filterParams as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $scope = ' AND c.assigned_user_id <=> :uid';
            $params = array_merge($filterParams, [':uid' => $consoleUserId]);
            $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c FROM customers c WHERE 1=1' . $scope . $filterSql);
            $stmtCount->execute($params);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT c.id, c.full_name, c.phone, c.assigned_user_id, c.created_at, c.updated_at,
                        COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_user_label
                 FROM customers c
                 LEFT JOIN console_users cu ON cu.id = c.assigned_user_id
                 WHERE 1=1' . $scope . $filterSql . '
                 ORDER BY c.id DESC LIMIT :lim OFFSET :off'
            );
            foreach ($params as $k => $v) {
                $type = PDO::PARAM_STR;
                if ($k === ':uid') {
                    $type = $consoleUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT;
                }
                $stmt->bindValue($k, $v, $type);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * @param list<string> $grants
     * @return list<array<string, mixed>>
     */
    public function exportCustomers(
        ?int $consoleUserId,
        array $grants,
        ?string $dateFromYmd,
        ?string $dateToYmd
    ): array {
        $wide = PolicyService::customersWideAccess($grants);
        $pdo = Database::pdo();
        if (!$wide && $consoleUserId === null) {
            return [];
        }
        [$filterSql, $filterParams] = self::customerDateClause($dateFromYmd, $dateToYmd);
        $lim = self::EXPORT_MAX_ROWS;

        if ($wide) {
            $stmt = $pdo->prepare(
                'SELECT c.id, c.full_name, c.phone, c.address, c.nin, c.bvn, c.assigned_user_id, c.created_at,
                        COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_to
                 FROM customers c
                 LEFT JOIN console_users cu ON cu.id = c.assigned_user_id
                 WHERE 1=1' . $filterSql . '
                 ORDER BY c.id DESC LIMIT ' . (string) (int) $lim
            );
            $stmt->execute($filterParams);
        } else {
            $scope = ' AND c.assigned_user_id <=> :uid';
            $params = array_merge($filterParams, [':uid' => $consoleUserId]);
            $stmt = $pdo->prepare(
                'SELECT c.id, c.full_name, c.phone, c.address, c.nin, c.bvn, c.assigned_user_id, c.created_at,
                        COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_to
                 FROM customers c
                 LEFT JOIN console_users cu ON cu.id = c.assigned_user_id
                 WHERE 1=1' . $scope . $filterSql . '
                 ORDER BY c.id DESC LIMIT ' . (string) (int) $lim
            );
            $stmt->execute($params);
        }

        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    public static function normalizeDate(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
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

    public static function normalizeLoanStatus(?string $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $s = trim($raw);
        return in_array($s, self::LOAN_STATUSES, true) ? $s : null;
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private static function loanFilterClause(?string $status, ?string $dateFromYmd, ?string $dateToYmd): array
    {
        $conditions = [];
        $params = [];
        if ($status !== null && $status !== '') {
            $conditions[] = 'l.status = :lst';
            $params[':lst'] = $status;
        }
        if ($dateFromYmd !== null) {
            $conditions[] = 'DATE(l.created_at) >= :dfrom';
            $params[':dfrom'] = $dateFromYmd;
        }
        if ($dateToYmd !== null) {
            $conditions[] = 'DATE(l.created_at) <= :dto';
            $params[':dto'] = $dateToYmd;
        }
        $sql = $conditions === [] ? '' : ' AND ' . implode(' AND ', $conditions);
        return [$sql, $params];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private static function customerDateClause(?string $dateFromYmd, ?string $dateToYmd): array
    {
        $conditions = [];
        $params = [];
        if ($dateFromYmd !== null) {
            $conditions[] = 'DATE(c.created_at) >= :cdfrom';
            $params[':cdfrom'] = $dateFromYmd;
        }
        if ($dateToYmd !== null) {
            $conditions[] = 'DATE(c.created_at) <= :cdto';
            $params[':cdto'] = $dateToYmd;
        }
        $sql = $conditions === [] ? '' : ' AND ' . implode(' AND ', $conditions);
        return [$sql, $params];
    }
}
