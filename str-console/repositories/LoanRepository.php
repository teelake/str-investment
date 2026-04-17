<?php

declare(strict_types=1);

final class LoanRepository
{
    private const PER_PAGE = 20;

    public static function canAccessRow(array $loan, ?int $consoleUserId, array $grants): bool
    {
        if (str_console_authorize($grants, ['data.view_all_loans'])) {
            return true;
        }
        if ($consoleUserId === null) {
            return false;
        }
        $lAssign = isset($loan['assigned_user_id']) ? (int) $loan['assigned_user_id'] : null;
        $cAssign = isset($loan['customer_assigned_user_id']) ? (int) $loan['customer_assigned_user_id'] : null;
        if ($lAssign !== null && $lAssign === $consoleUserId) {
            return true;
        }
        if ($cAssign !== null && $cAssign === $consoleUserId) {
            return true;
        }
        return false;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginateForConsoleUser(?int $consoleUserId, array $grants, int $page): array
    {
        $page = max(1, $page);
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $viewAll = str_console_authorize($grants, ['data.view_all_loans']);
        $pdo = Database::pdo();

        if (!$viewAll && $consoleUserId === null) {
            return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }

        $baseFrom = 'FROM loans l INNER JOIN customers c ON c.id = l.customer_id';
        if ($viewAll) {
            $countSql = 'SELECT COUNT(*) AS c ' . $baseFrom;
            $total = (int) ($pdo->query($countSql)->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT l.*, c.full_name AS customer_name, c.assigned_user_id AS customer_assigned_user_id
                 ' . $baseFrom . '
                 ORDER BY l.id DESC LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmtCount = $pdo->prepare(
                'SELECT COUNT(*) AS c ' . $baseFrom . '
                 WHERE (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)'
            );
            $stmtCount->execute([':uid' => $consoleUserId, ':uid2' => $consoleUserId]);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT l.*, c.full_name AS customer_name, c.assigned_user_id AS customer_assigned_user_id
                 ' . $baseFrom . '
                 WHERE (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)
                 ORDER BY l.id DESC LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':uid', $consoleUserId, PDO::PARAM_INT);
            $stmt->bindValue(':uid2', $consoleUserId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT l.*, c.full_name AS customer_name, c.assigned_user_id AS customer_assigned_user_id
             FROM loans l
             INNER JOIN customers c ON c.id = l.customer_id
             WHERE l.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(
        int $customerId,
        int $loanProductId,
        float $principal,
        float $ratePercent,
        int $periodMonths,
        ?int $assignedUserId,
        ?int $createdByUserId
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO loans (customer_id, loan_product_id, status, principal_amount, rate_percent, period_months, assigned_user_id, created_by_user_id, created_at, updated_at)
             VALUES (:cid, :pid, \'draft\', :principal, :rate, :pm, :aid, :cby, NOW(), NOW())'
        );
        $stmt->execute([
            ':cid' => $customerId,
            ':pid' => $loanProductId,
            ':principal' => $principal,
            ':rate' => $ratePercent,
            ':pm' => $periodMonths,
            ':aid' => $assignedUserId,
            ':cby' => $createdByUserId,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function setStatus(int $loanId, string $status): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE loans SET status = :st, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':st' => $status, ':id' => $loanId]);
    }

    public function markSubmitted(int $loanId): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE loans SET status = \'pending_approval\', submitted_at = NOW(), updated_at = NOW() WHERE id = :id AND status = \'draft\''
        );
        $stmt->execute([':id' => $loanId]);
        return $stmt->rowCount() > 0;
    }

    public function markApproved(int $loanId, int $approverUserId): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE loans SET status = \'approved\', approved_by_user_id = :uid, approved_at = NOW(), updated_at = NOW()
             WHERE id = :id AND status = \'pending_approval\''
        );
        $stmt->execute([':uid' => $approverUserId, ':id' => $loanId]);
        return $stmt->rowCount() > 0;
    }

    public function markRejected(int $loanId, string $reason): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE loans SET status = \'rejected\', rejected_reason = :r, updated_at = NOW()
             WHERE id = :id AND status = \'pending_approval\''
        );
        $stmt->execute([':r' => $reason, ':id' => $loanId]);
        return $stmt->rowCount() > 0;
    }

    public function markDisbursed(int $loanId, string $disbursedDateYmd): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE loans SET status = \'active\', disbursed_at = :d, updated_at = NOW()
             WHERE id = :id AND status = \'approved\''
        );
        $stmt->execute([':d' => $disbursedDateYmd, ':id' => $loanId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * @return array{active_loans: int, outstanding: float}
     */
    public function dashboardTotals(?int $consoleUserId, array $grants): array
    {
        $viewAll = str_console_authorize($grants, ['data.view_all_loans']);
        $pdo = Database::pdo();

        if (!$viewAll && $consoleUserId === null) {
            return ['active_loans' => 0, 'outstanding' => 0.0];
        }

        $scope = '';
        $params = [];
        if (!$viewAll) {
            $scope = ' AND (l.assigned_user_id <=> :uid OR c.assigned_user_id <=> :uid2)';
            $params[':uid'] = $consoleUserId;
            $params[':uid2'] = $consoleUserId;
        }

        $sql = 'SELECT COUNT(*) AS c FROM loans l INNER JOIN customers c ON c.id = l.customer_id
                WHERE l.status = \'active\'' . $scope;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $active = (int) ($stmt->fetch()['c'] ?? 0);

        $sql2 = 'SELECT l.id FROM loans l INNER JOIN customers c ON c.id = l.customer_id
                 WHERE l.status = \'active\'' . $scope;
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute($params);
        $ids = $stmt2->fetchAll(PDO::FETCH_COLUMN);
        $out = 0.0;
        foreach ($ids as $lid) {
            $out += LoanLedgerService::outstandingForLoan((int) $lid);
        }

        return ['active_loans' => $active, 'outstanding' => round($out, 2)];
    }
}
