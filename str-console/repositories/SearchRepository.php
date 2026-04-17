<?php

declare(strict_types=1);

final class SearchRepository
{
    private const LIMIT = 25;

    /**
     * @param list<string> $grants
     * @return array{customers: list<array<string, mixed>>, loans: list<array<string, mixed>>}
     */
    public function run(string $query, ?int $consoleUserId, array $grants): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return ['customers' => [], 'loans' => []];
        }

        $like = '%' . addcslashes($query, '%_\\') . '%';
        $pdo = Database::pdo();

        $custWide = PolicyService::customersWideAccess($grants);
        $loanWide = PolicyService::loansWideAccess($grants);

        $customers = [];
        if (str_console_authorize($grants, ['customers.list'])) {
            if (!$custWide && $consoleUserId === null) {
                $customers = [];
            } elseif ($custWide) {
                $stmt = $pdo->prepare(
                    'SELECT id, full_name, phone, assigned_user_id
                     FROM customers
                     WHERE full_name LIKE :q OR phone LIKE :q OR nin LIKE :q OR bvn LIKE :q
                     ORDER BY full_name ASC
                     LIMIT ' . self::LIMIT
                );
                $stmt->execute([':q' => $like]);
                $customers = $stmt->fetchAll();
            } else {
                $stmt = $pdo->prepare(
                    'SELECT id, full_name, phone, assigned_user_id
                     FROM customers
                     WHERE (full_name LIKE :q OR phone LIKE :q OR nin LIKE :q OR bvn LIKE :q)
                     AND assigned_user_id <=> :uid
                     ORDER BY full_name ASC
                     LIMIT ' . self::LIMIT
                );
                $stmt->execute([':q' => $like, ':uid' => $consoleUserId]);
                $customers = $stmt->fetchAll();
            }
        }

        $loans = [];
        if (str_console_authorize($grants, ['loans.list'])) {
            if (!$loanWide && $consoleUserId === null) {
                $loans = [];
            } else {
                $idExact = ctype_digit($query) ? (int) $query : 0;
                if ($loanWide) {
                    $sql = 'SELECT l.id, l.status, l.principal_amount, l.customer_id, c.full_name AS customer_name
                            FROM loans l
                            INNER JOIN customers c ON c.id = l.customer_id
                            WHERE (c.full_name LIKE :q OR c.phone LIKE :q)';
                    $params = [':q' => $like];
                    if ($idExact > 0) {
                        $sql .= ' OR l.id = :lid';
                        $params[':lid'] = $idExact;
                    }
                    $sql .= ' ORDER BY l.id DESC LIMIT ' . self::LIMIT;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $loans = $stmt->fetchAll();
                } else {
                    $sql = 'SELECT l.id, l.status, l.principal_amount, l.customer_id, c.full_name AS customer_name
                            FROM loans l
                            INNER JOIN customers c ON c.id = l.customer_id
                            WHERE (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)
                            AND (c.full_name LIKE :q OR c.phone LIKE :q';
                    $params = [':q' => $like, ':uid' => $consoleUserId, ':uid2' => $consoleUserId];
                    if ($idExact > 0) {
                        $sql .= ' OR l.id = :lid';
                        $params[':lid'] = $idExact;
                    }
                    $sql .= ') ORDER BY l.id DESC LIMIT ' . self::LIMIT;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $loans = $stmt->fetchAll();
                }
            }
        }

        /** @var list<array<string, mixed>> $customers */
        /** @var list<array<string, mixed>> $loans */
        return ['customers' => $customers, 'loans' => $loans];
    }
}
