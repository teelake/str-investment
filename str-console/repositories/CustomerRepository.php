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

        $viewAll = str_console_authorize($grants, ['data.view_all_customers']);
        $pdo = Database::pdo();

        if (!$viewAll && $consoleUserId === null) {
            return [
                'rows' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
            ];
        }

        if ($viewAll) {
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
        $viewAll = str_console_authorize($grants, ['data.view_all_customers']);
        if (!$viewAll && $consoleUserId === null) {
            return 0;
        }
        $pdo = Database::pdo();
        if ($viewAll) {
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
        $viewAll = str_console_authorize($grants, ['data.view_all_customers']);
        if (!$viewAll) {
            $aid = $row['assigned_user_id'] ?? null;
            if ($aid === null && $consoleUserId === null) {
                return null;
            }
            if ((int) ($aid ?? 0) !== (int) ($consoleUserId ?? 0)) {
                return null;
            }
        }
        return $row;
    }

    /**
     * @return int new customer id
     */
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
