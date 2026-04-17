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
}
