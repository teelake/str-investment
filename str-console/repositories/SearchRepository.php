<?php

declare(strict_types=1);

final class SearchRepository
{
    public const PER_PAGE = 20;

    /**
     * @param list<string> $grants
     * @return array{
     *   customers: list<array<string, mixed>>,
     *   loans: list<array<string, mixed>>,
     *   customers_total: int,
     *   loans_total: int,
     *   customers_page: int,
     *   loans_page: int,
     *   per_page: int
     * }
     */
    public function run(
        string $query,
        ?int $consoleUserId,
        array $grants,
        int $pageCustomers,
        int $pageLoans
    ): array {
        $perPage = self::PER_PAGE;
        $query = trim($query);
        $empty = [
            'customers' => [],
            'loans' => [],
            'customers_total' => 0,
            'loans_total' => 0,
            'customers_page' => 1,
            'loans_page' => 1,
            'per_page' => $perPage,
        ];
        if (mb_strlen($query) < 2) {
            return $empty;
        }

        $pageCustomers = Pagination::sanitizeRequestedPage($pageCustomers);
        $pageLoans = Pagination::sanitizeRequestedPage($pageLoans);

        $like = '%' . addcslashes($query, '%_\\') . '%';
        $pdo = Database::pdo();

        $custWide = PolicyService::customersWideAccess($grants);
        $loanWide = PolicyService::loansWideAccess($grants);

        $customers = [];
        $customersTotal = 0;
        $customersPage = 1;
        if (str_console_authorize($grants, ['customers.list']) && ($custWide || $consoleUserId !== null)) {
            $conds = ['c.full_name LIKE :q', 'c.phone LIKE :q', 'c.nin LIKE :q', 'c.bvn LIKE :q'];
            $params = [':q' => $like];
            $n = self::positiveIntId($query);
            if ($n !== null) {
                $conds[] = 'c.id = :cid';
                $params[':cid'] = $n;
            }
            $dig = preg_replace('/\D/', '', $query) ?? '';
            if (strlen($dig) >= 2) {
                $conds[] = "REGEXP_REPLACE(c.phone, '[^0-9]', '') LIKE :qdig";
                $params[':qdig'] = '%' . addcslashes($dig, '%_\\') . '%';
            }
            $match = '(' . implode(' OR ', $conds) . ')';
            $from = 'FROM customers c
                     LEFT JOIN console_users cu ON cu.id = c.assigned_user_id';
            $selectList = 'SELECT c.id, c.full_name, c.phone, c.assigned_user_id,
                           COALESCE(NULLIF(TRIM(cu.full_name), \'\'), cu.email) AS assigned_user_label ';

            if ($custWide) {
                $stmtCt = $pdo->prepare('SELECT COUNT(*) AS c ' . $from . ' WHERE ' . $match);
                $stmtCt->execute($params);
                $customersTotal = (int) ($stmtCt->fetch()['c'] ?? 0);
                $customersPage = Pagination::normalizePage($pageCustomers, $customersTotal, $perPage);
                $off = ($customersPage - 1) * $perPage;
                $stmt = $pdo->prepare(
                    $selectList . $from . '
                    WHERE ' . $match . '
                    ORDER BY c.id DESC
                    LIMIT :lim OFFSET :off'
                );
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v);
                }
                $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':off', $off, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $where = $match . ' AND c.assigned_user_id <=> :uid';
                $p2 = array_merge($params, [':uid' => $consoleUserId]);
                $stmtCt = $pdo->prepare('SELECT COUNT(*) AS c ' . $from . ' WHERE ' . $where);
                $stmtCt->execute($p2);
                $customersTotal = (int) ($stmtCt->fetch()['c'] ?? 0);
                $customersPage = Pagination::normalizePage($pageCustomers, $customersTotal, $perPage);
                $off = ($customersPage - 1) * $perPage;
                $stmt = $pdo->prepare(
                    $selectList . $from . '
                    WHERE ' . $where . '
                    ORDER BY c.id DESC
                    LIMIT :lim OFFSET :off'
                );
                foreach ($p2 as $k => $v) {
                    if ($k === ':uid') {
                        $stmt->bindValue($k, $v, $consoleUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                        continue;
                    }
                    $stmt->bindValue($k, $v);
                }
                $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':off', $off, PDO::PARAM_INT);
                $stmt->execute();
            }
            $customers = $stmt->fetchAll();
        }

        $loans = [];
        $loansTotal = 0;
        $loansPage = 1;
        if (str_console_authorize($grants, ['loans.list']) && ($loanWide || $consoleUserId !== null)) {
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
            $base = 'FROM loans l INNER JOIN customers c ON c.id = l.customer_id';
            $sel = 'SELECT l.id, l.status, l.principal_amount, l.customer_id, c.full_name AS customer_name ';

            if ($loanWide) {
                $stmtCt = $pdo->prepare('SELECT COUNT(*) AS c ' . $base . ' WHERE ' . $inner);
                $stmtCt->execute($params);
                $loansTotal = (int) ($stmtCt->fetch()['c'] ?? 0);
                $loansPage = Pagination::normalizePage($pageLoans, $loansTotal, $perPage);
                $off = ($loansPage - 1) * $perPage;
                $stmt = $pdo->prepare(
                    $sel . $base . ' WHERE ' . $inner . ' ORDER BY l.id DESC LIMIT :lim OFFSET :off'
                );
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':off', $off, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $where = '(l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2) AND ' . $inner;
                $p2 = array_merge($params, [':uid' => $consoleUserId, ':uid2' => $consoleUserId]);
                $stmtCt = $pdo->prepare('SELECT COUNT(*) AS c ' . $base . ' WHERE ' . $where);
                foreach ($p2 as $k => $v) {
                    $stmtCt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmtCt->execute();
                $loansTotal = (int) ($stmtCt->fetch()['c'] ?? 0);
                $loansPage = Pagination::normalizePage($pageLoans, $loansTotal, $perPage);
                $off = ($loansPage - 1) * $perPage;
                $stmt = $pdo->prepare(
                    $sel . $base . ' WHERE ' . $where . ' ORDER BY l.id DESC LIMIT :lim OFFSET :off'
                );
                foreach ($p2 as $k => $v) {
                    $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':off', $off, PDO::PARAM_INT);
                $stmt->execute();
            }
            $loans = $stmt->fetchAll();
        }

        /** @var list<array<string, mixed>> $customers */
        /** @var list<array<string, mixed>> $loans */
        return [
            'customers' => $customers,
            'loans' => $loans,
            'customers_total' => $customersTotal,
            'loans_total' => $loansTotal,
            'customers_page' => $customersPage,
            'loans_page' => $loansPage,
            'per_page' => $perPage,
        ];
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
