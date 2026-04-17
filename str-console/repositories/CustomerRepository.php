<?php

declare(strict_types=1);

final class CustomerRepository
{
    private const PER_PAGE = 20;

    /**
     * @param list<string> $grants
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginateForConsoleUser(?int $consoleUserId, array $grants, int $page): array
    {
        $page = max(1, $page);
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $wide = PolicyService::customersWideAccess($grants);
        $pdo = Database::pdo();

        if (!$wide && $consoleUserId === null) {
            return [
                'rows' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
            ];
        }

        if ($wide) {
            $countStmt = $pdo->query('SELECT COUNT(*) AS c FROM customers');
            $total = (int) ($countStmt->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT id, full_name, phone, address, nin, bvn, assigned_user_id, created_at, updated_at
                 FROM customers
                 ORDER BY id DESC
                 LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c FROM customers WHERE assigned_user_id <=> :uid');
            $stmtCount->execute([':uid' => $consoleUserId]);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT id, full_name, phone, address, nin, bvn, assigned_user_id, created_at, updated_at
                 FROM customers
                 WHERE assigned_user_id <=> :uid
                 ORDER BY id DESC
                 LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':uid', $consoleUserId, $consoleUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
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
            return (int) $pdo->query('SELECT COUNT(*) AS c FROM customers')->fetch()['c'];
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM customers WHERE assigned_user_id <=> :uid');
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
            'SELECT id, full_name, phone, address, nin, bvn, assigned_user_id, created_at, updated_at
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
            $stmt = $pdo->prepare('SELECT id, full_name FROM customers ORDER BY full_name ASC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, full_name FROM customers WHERE assigned_user_id <=> :uid ORDER BY full_name ASC LIMIT :lim'
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
            'INSERT INTO customers (full_name, phone, address, nin, bvn, assigned_user_id, created_at, updated_at)
             VALUES (:name, :phone, :addr, :nin, :bvn, :aid, NOW(), NOW())'
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
}
