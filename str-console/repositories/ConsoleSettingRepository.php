<?php

declare(strict_types=1);

final class ConsoleSettingRepository
{
    public static function get(string $key): ?string
    {
        if (!str_console_database_ready()) {
            return null;
        }
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare('SELECT setting_value FROM console_settings WHERE setting_key = :k LIMIT 1');
            $stmt->execute([':k' => $key]);
            $row = $stmt->fetch();
            if (!is_array($row)) {
                return null;
            }
            $v = $row['setting_value'] ?? null;
            return is_string($v) ? $v : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function set(string $key, string $value, ?int $updatedByUserId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO console_settings (setting_key, setting_value, updated_by_user_id, updated_at)
             VALUES (:k, :v, :u, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by_user_id = VALUES(updated_by_user_id), updated_at = NOW()'
        );
        $stmt->execute([':k' => $key, ':v' => $value, ':u' => $updatedByUserId]);
    }
}
