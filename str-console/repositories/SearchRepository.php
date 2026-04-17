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
            } else {
                $conds = ['full_name LIKE :q', 'phone LIKE :q', 'nin LIKE :q', 'bvn LIKE :q'];
                $params = [':q' => $like];
                $n = self::positiveIntId($query);
                if ($n !== null) {
                    $conds[] = 'id = :cid';
                    $params[':cid'] = $n;
                }
                $dig = preg_replace('/\D/', '', $query) ?? '';
                if (strlen($dig) >= 2) {
                    $conds[] = "REGEXP_REPLACE(phone, '[^0-9]', '') LIKE :qdig";
                    $params[':qdig'] = '%' . addcslashes($dig, '%_\\') . '%';
                }
                $where = '(' . implode(' OR ', $conds) . ')';
                if ($custWide) {
                    $sql = 'SELECT id, full_name, phone, assigned_user_id
                            FROM customers
                            WHERE ' . $where . '
                            ORDER BY full_name ASC
                            LIMIT ' . self::LIMIT;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                } else {
                    $sql = 'SELECT id, full_name, phone, assigned_user_id
                            FROM customers
                            WHERE ' . $where . '
                            AND assigned_user_id <=> :uid
                            ORDER BY full_name ASC
                            LIMIT ' . self::LIMIT;
                    $stmt = $pdo->prepare($sql);
                    $params[':uid'] = $consoleUserId;
                    $stmt->execute($params);
                }
                $customers = $stmt->fetchAll();
            }
        }

        $loans = [];
        if (str_console_authorize($grants, ['loans.list'])) {
            if (!$loanWide && $consoleUserId === null) {
                $loans = [];
            } else {
                $conds = ['c.full_name LIKE :q', 'c.phone LIKE :q', 'c.nin LIKE :q', 'c.bvn LIKE :q'];
                $params = [':q' => $like];
                $n = self::positiveIntId($query);
                if ($n !== null) {
                    $conds[] = '(l.id = :num OR l.customer_id = :num OR c.id = :num)';
                    $params[':num'] = $n;
                }
                $dig = preg_replace('/\D/', '', $query) ?? '';
                if (strlen($dig) >= 2) {
                    $conds[] = "REGEXP_REPLACE(c.phone, '[^0-9]', '') LIKE :qdig";
                    $params[':qdig'] = '%' . addcslashes($dig, '%_\\') . '%';
                }
                $inner = '(' . implode(' OR ', $conds) . ')';

                if ($loanWide) {
                    $sql = 'SELECT l.id, l.status, l.principal_amount, l.customer_id, c.full_name AS customer_name
                            FROM loans l
                            INNER JOIN customers c ON c.id = l.customer_id
                            WHERE ' . $inner . '
                            ORDER BY l.id DESC
                            LIMIT ' . self::LIMIT;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                } else {
                    $sql = 'SELECT l.id, l.status, l.principal_amount, l.customer_id, c.full_name AS customer_name
                            FROM loans l
                            INNER JOIN customers c ON c.id = l.customer_id
                            WHERE (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)
                            AND ' . $inner . '
                            ORDER BY l.id DESC
                            LIMIT ' . self::LIMIT;
                    $stmt = $pdo->prepare($sql);
                    $params[':uid'] = $consoleUserId;
                    $params[':uid2'] = $consoleUserId;
                    $stmt->execute($params);
                }
                $loans = $stmt->fetchAll();
            }
        }

        /** @var list<array<string, mixed>> $customers */
        /** @var list<array<string, mixed>> $loans */
        return ['customers' => $customers, 'loans' => $loans];
    }

    private static function positiveIntId(string $query): ?int
    {
        if (!ctype_digit($query)) {
            return null;
        }
        $n = (int) $query;
        return $n > 0 ? $n : null;
    }
}
