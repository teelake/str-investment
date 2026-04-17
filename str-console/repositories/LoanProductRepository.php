<?php

declare(strict_types=1);

final class LoanProductRepository
{
    private const PER_PAGE = 25;

    /**
     * @param ''|'active'|'retired' $activity
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(int $page, string $activity = ''): array
    {
        $page = Pagination::sanitizeRequestedPage($page);
        $perPage = self::PER_PAGE;
        $pdo = Database::pdo();

        $where = '1=1';
        if ($activity === 'active') {
            $where .= ' AND is_active = 1';
        } elseif ($activity === 'retired') {
            $where .= ' AND is_active = 0';
        }

        $total = (int) $pdo->query('SELECT COUNT(*) AS c FROM loan_products WHERE ' . $where)->fetch()['c'];
        $page = Pagination::normalizePage($page, $total, $perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT id, name, rate_percent, period_months, is_active, created_at, updated_at
             FROM loan_products WHERE ' . $where . '
             ORDER BY is_active DESC, name ASC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        /** @var list<array<string, mixed>> */
        $rows = $stmt->fetchAll();

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

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
