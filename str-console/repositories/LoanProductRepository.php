<?php

declare(strict_types=1);

final class LoanProductRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id, name, rate_percent, period_months, is_active, created_at, updated_at
             FROM loan_products ORDER BY is_active DESC, name ASC'
        );
        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id, name, rate_percent, period_months, is_active
             FROM loan_products WHERE is_active = 1 ORDER BY name ASC'
        );
        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM loan_products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(string $name, float $ratePercent, int $periodMonths): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO loan_products (name, rate_percent, period_months, is_active, created_at, updated_at)
             VALUES (:n, :r, :pm, 1, NOW(), NOW())'
        );
        $stmt->execute([
            ':n' => $name,
            ':r' => $ratePercent,
            ':pm' => $periodMonths,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, string $name, float $ratePercent, int $periodMonths, bool $isActive): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE loan_products SET name = :n, rate_percent = :r, period_months = :pm, is_active = :a, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':n' => $name,
            ':r' => $ratePercent,
            ':pm' => $periodMonths,
            ':a' => $isActive ? 1 : 0,
            ':id' => $id,
        ]);
    }

    public function retire(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE loan_products SET is_active = 0, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
