<?php

declare(strict_types=1);

final class CustomerRepository
{
    private const PER_PAGE = 20;

    /**
     * @param list<string> $grants
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginateForConsoleUser(?int $consoleUserId, array $grants, int $page, ?string $searchQ = null): array
    {
        $page = Pagination::sanitizeRequestedPage($page);
        $perPage = self::PER_PAGE;

        $wide = PolicyService::customersWideAccess($grants);
        $pdo = Database::pdo();

        if (!$wide && $consoleUserId === null) {
            return [
                'rows' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $perPage,
            ];
        }

        [$searchSql, $searchParams] = self::listSearchClause($searchQ);
        $activeOnly = ' AND c.is_active = 1';

        if ($wide) {
            $countSql = 'SELECT COUNT(*) AS c FROM customers c WHERE 1=1' . $activeOnly . $searchSql;
            $stmtCount = $pdo->prepare($countSql);
            $stmtCount->execute($searchParams);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $page = Pagination::normalizePage($page, $total, $perPage);
            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare(
                'SELECT c.id, c.full_name, c.phone, c.address, c.nin, c.bvn, c.assigned_user_id, c.created_at, c.updated_at,
                        COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_user_label
                 FROM customers c
                 LEFT JOIN console_users cu ON cu.id = c.assigned_user_id
                 WHERE 1=1' . $activeOnly . $searchSql . '
                 ORDER BY c.id ASC
                 LIMIT :lim OFFSET :off'
            );
            foreach ($searchParams as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $params = array_merge($searchParams, [':uid' => $consoleUserId]);
            $stmtCount = $pdo->prepare(
                'SELECT COUNT(*) AS c FROM customers c WHERE c.assigned_user_id <=> :uid' . $activeOnly . $searchSql
            );
            $stmtCount->execute($params);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $page = Pagination::normalizePage($page, $total, $perPage);
            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare(
                'SELECT c.id, c.full_name, c.phone, c.address, c.nin, c.bvn, c.assigned_user_id, c.created_at, c.updated_at,
                        COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_user_label
                 FROM customers c
                 LEFT JOIN console_users cu ON cu.id = c.assigned_user_id
                 WHERE c.assigned_user_id <=> :uid' . $activeOnly . $searchSql . '
                 ORDER BY c.id ASC
                 LIMIT :lim OFFSET :off'
            );
            foreach ($params as $k => $v) {
                if ($k === ':uid') {
                    $stmt->bindValue($k, $v, $consoleUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                    continue;
                }
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private static function listSearchClause(?string $raw): array
    {
        $t = trim((string) $raw);
        if ($t === '') {
            return ['', []];
        }
        if (mb_strlen($t) > 120) {
            $t = mb_substr($t, 0, 120);
        }
        $like = '%' . addcslashes($t, '%_\\') . '%';
        $parts = ['c.full_name LIKE :clistq', 'c.phone LIKE :clistq', 'c.nin LIKE :clistq', 'c.bvn LIKE :clistq'];
        $params = [':clistq' => $like];
        if (ctype_digit($t) && (int) $t > 0) {
            $parts[] = 'c.id = :clistid';
            $params[':clistid'] = (int) $t;
        }
        $dig = preg_replace('/\D/', '', $t) ?? '';
        if (strlen($dig) >= 2) {
            $parts[] = "REGEXP_REPLACE(c.phone, '[^0-9]', '') LIKE :clistqd";
            $params[':clistqd'] = '%' . addcslashes($dig, '%_\\') . '%';
        }

        return [' AND (' . implode(' OR ', $parts) . ')', $params];
    }

    /**
     * @param list<string> $grants
     */
    public function countScoped(?int $consoleUserId, array $grants): int
    {
        $wide = PolicyService::customersWideAccess($grants);
        if (!$wide && $consoleUserId === null) {
            return 0;
        }
        $pdo = Database::pdo();
        if ($wide) {
            return (int) $pdo->query('SELECT COUNT(*) AS c FROM customers WHERE is_active = 1')->fetch()['c'];
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM customers WHERE is_active = 1 AND assigned_user_id <=> :uid');
        $stmt->execute([':uid' => $consoleUserId]);
        return (int) ($stmt->fetch()['c'] ?? 0);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id, ?int $consoleUserId, array $grants): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, full_name, phone, address, nin, bvn, assigned_user_id, is_active, created_at, updated_at
             FROM customers WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }
        $wide = PolicyService::customersWideAccess($grants);
        if (!$wide) {
            if ($consoleUserId === null) {
                return null;
            }
            $aid = $row['assigned_user_id'] ?? null;
            if ((int) ($aid ?? 0) !== (int) $consoleUserId) {
                return null;
            }
        }
        return $row;
    }

    /**
     * @return list<array{id: int, full_name: string}>
     */
    public function listNamesForConsoleUser(?int $consoleUserId, array $grants, int $limit = 500): array
    {
        $wide = PolicyService::customersWideAccess($grants);
        if (!$wide && $consoleUserId === null) {
            return [];
        }
        $pdo = Database::pdo();
        if ($wide) {
            $stmt = $pdo->prepare('SELECT id, full_name FROM customers WHERE is_active = 1 ORDER BY full_name ASC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, full_name FROM customers WHERE is_active = 1 AND assigned_user_id <=> :uid ORDER BY full_name ASC LIMIT :lim'
            );
            $stmt->bindValue(':uid', $consoleUserId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        /** @var list<array{id: int, full_name: string}> */
        return $stmt->fetchAll();
    }

    /**
     * Block duplicate phone (digits-only match), NIN, or BVN vs other customers.
     *
     * @return non-empty-string|null Error message, or null if OK
     */
    public function validateOnboardingUniqueness(
        string $phone,
        ?string $nin,
        ?string $bvn,
        ?int $exceptCustomerId
    ): ?string {
        $pdo = Database::pdo();
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits !== '') {
            $sql = 'SELECT id FROM customers WHERE phone_digits = :d';
            $params = [':d' => $digits];
            if ($exceptCustomerId !== null) {
                $sql .= ' AND id != :e';
                $params[':e'] = $exceptCustomerId;
            }
            $sql .= ' LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            if (is_array($row)) {
                return 'This phone number is already registered for customer #' . (int) ($row['id'] ?? 0) . '.';
            }
        }
        if ($nin !== null && $nin !== '') {
            $sql = 'SELECT id FROM customers WHERE nin = :n';
            $params = [':n' => $nin];
            if ($exceptCustomerId !== null) {
                $sql .= ' AND id != :e';
                $params[':e'] = $exceptCustomerId;
            }
            $sql .= ' LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            if (is_array($row)) {
                return 'This NIN is already registered for customer #' . (int) ($row['id'] ?? 0) . '.';
            }
        }
        if ($bvn !== null && $bvn !== '') {
            $sql = 'SELECT id FROM customers WHERE bvn = :b';
            $params = [':b' => $bvn];
            if ($exceptCustomerId !== null) {
                $sql .= ' AND id != :e';
                $params[':e'] = $exceptCustomerId;
            }
            $sql .= ' LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            if (is_array($row)) {
                return 'This BVN is already registered for customer #' . (int) ($row['id'] ?? 0) . '.';
            }
        }
        return null;
    }

    public function create(
        string $fullName,
        string $phone,
        ?string $address,
        ?string $nin,
        ?string $bvn,
        ?int $assignedUserId
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO customers (full_name, phone, address, nin, bvn, assigned_user_id, is_active, created_at, updated_at)
             VALUES (:name, :phone, :addr, :nin, :bvn, :aid, 1, NOW(), NOW())'
        );
        $stmt->execute([
            ':name' => $fullName,
            ':phone' => $phone,
            ':addr' => $address,
            ':nin' => $nin,
            ':bvn' => $bvn,
            ':aid' => $assignedUserId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * When $setAssignee is false, assigned_user_id is left unchanged.
     */
    public function update(
        int $id,
        string $fullName,
        string $phone,
        ?string $address,
        ?string $nin,
        ?string $bvn,
        bool $setAssignee,
        ?int $assignedUserId
    ): void {
        $pdo = Database::pdo();
        if ($setAssignee) {
            $stmt = $pdo->prepare(
                'UPDATE customers SET full_name = :name, phone = :phone, address = :addr, nin = :nin, bvn = :bvn,
                 assigned_user_id = :aid, updated_at = NOW() WHERE id = :id'
            );
            $stmt->execute([
                ':name' => $fullName,
                ':phone' => $phone,
                ':addr' => $address,
                ':nin' => $nin,
                ':bvn' => $bvn,
                ':aid' => $assignedUserId,
                ':id' => $id,
            ]);
            return;
        }
        $stmt = $pdo->prepare(
            'UPDATE customers SET full_name = :name, phone = :phone, address = :addr, nin = :nin, bvn = :bvn,
             updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $fullName,
            ':phone' => $phone,
            ':addr' => $address,
            ':nin' => $nin,
            ':bvn' => $bvn,
            ':id' => $id,
        ]);
    }

    public function deactivate(int $id): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE customers SET is_active = 0, updated_at = NOW() WHERE id = :id AND is_active = 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
