<?php

declare(strict_types=1);

final class AuditLogRepository
{
    private const PER_PAGE = 40;

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(int $page, ?string $entityType): array
    {
        $page = max(1, $page);
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $pdo = Database::pdo();

        if ($entityType !== null && $entityType !== '') {
            $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c FROM audit_log WHERE entity_type = :t');
            $stmtCount->execute([':t' => $entityType]);
            $total = (int) ($stmtCount->fetch()['c'] ?? 0);
            $stmt = $pdo->prepare(
                'SELECT id, actor_user_id, action, entity_type, entity_id, payload_json, created_at
                 FROM audit_log WHERE entity_type = :t
                 ORDER BY id DESC LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':t', $entityType);
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $total = (int) $pdo->query('SELECT COUNT(*) AS c FROM audit_log')->fetch()['c'];
            $stmt = $pdo->prepare(
                'SELECT id, actor_user_id, action, entity_type, entity_id, payload_json, created_at
                 FROM audit_log
                 ORDER BY id DESC LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

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
