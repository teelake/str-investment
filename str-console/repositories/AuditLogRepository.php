<?php

declare(strict_types=1);

final class AuditLogRepository
{
    private const PER_PAGE = 40;

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(
        int $page,
        ?string $entityType,
        ?string $dateFromYmd = null,
        ?string $dateToYmd = null,
        bool $hideActionsBySystemAdminActors = false
    ): array {
        $page = Pagination::sanitizeRequestedPage($page);
        $perPage = self::PER_PAGE;
        $pdo = Database::pdo();

        $conds = [];
        $params = [];
        if ($entityType !== null && $entityType !== '') {
            $conds[] = 'al.entity_type = :t';
            $params[':t'] = $entityType;
        }
        if ($dateFromYmd !== null) {
            $conds[] = 'DATE(al.created_at) >= :dfrom';
            $params[':dfrom'] = $dateFromYmd;
        }
        if ($dateToYmd !== null) {
            $conds[] = 'DATE(al.created_at) <= :dto';
            $params[':dto'] = $dateToYmd;
        }
        if ($hideActionsBySystemAdminActors) {
            $conds[] = '(al.actor_user_id IS NULL OR NOT EXISTS (SELECT 1 FROM console_users cu WHERE cu.id = al.actor_user_id AND cu.role_key = :sar))';
            $params[':sar'] = 'system_admin';
        }
        $where = $conds === [] ? '1=1' : implode(' AND ', $conds);
        $from = 'FROM audit_log al';

        $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c ' . $from . ' WHERE ' . $where);
        $stmtCount->execute($params);
        $total = (int) ($stmtCount->fetch()['c'] ?? 0);
        $page = Pagination::normalizePage($page, $total, $perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT al.id, al.actor_user_id, al.action, al.entity_type, al.entity_id, al.payload_json, al.created_at
             ' . $from . ' WHERE ' . $where . '
             ORDER BY al.id DESC LIMIT :lim OFFSET :off'
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * @return list<string>
     */
    public function distinctEntityTypes(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT DISTINCT entity_type FROM audit_log ORDER BY entity_type ASC LIMIT 100');
        /** @var list<array{entity_type: string}> $raw */
        $raw = $stmt->fetchAll();
        $out = [];
        foreach ($raw as $r) {
            if (isset($r['entity_type']) && is_string($r['entity_type']) && $r['entity_type'] !== '') {
                $out[] = $r['entity_type'];
            }
        }
        return $out;
    }
}
