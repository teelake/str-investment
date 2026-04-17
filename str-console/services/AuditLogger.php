<?php

declare(strict_types=1);

final class AuditLogger
{
    /**
     * @param array<string, mixed>|null $payload
     */
    public static function log(?int $actorUserId, string $action, string $entityType, ?int $entityId, ?array $payload = null): void
    {
        if (!str_console_database_ready()) {
            return;
        }

        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare(
                'INSERT INTO audit_log (actor_user_id, action, entity_type, entity_id, payload_json, created_at)
                 VALUES (:actor, :action, :etype, :eid, :payload, NOW())'
            );
            $json = $payload === null ? null : json_encode($payload, JSON_THROW_ON_ERROR);
            $stmt->bindValue(':actor', $actorUserId, $actorUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':action', $action);
            $stmt->bindValue(':etype', $entityType);
            $stmt->bindValue(':eid', $entityId, $entityId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            if ($json === null) {
                $stmt->bindValue(':payload', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':payload', $json);
            }
            $stmt->execute();
        } catch (Throwable) {
            // Never break primary flow on audit failure
        }
    }
}
