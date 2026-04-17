<?php

declare(strict_types=1);

final class PasswordResetRepository
{
    public static function deleteExpired(): void
    {
        if (!str_console_database_ready()) {
            return;
        }
        try {
            $pdo = Database::pdo();
            $pdo->exec('DELETE FROM console_password_resets WHERE expires_at < NOW()');
        } catch (Throwable) {
            // ignore
        }
    }

    /**
     * @return array{plain: string, hash: string}
     */
    public static function createTokenForUser(int $userId): array
    {
        $plain = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plain);
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM console_password_resets WHERE user_id = :uid')->execute([':uid' => $userId]);
        $ins = $pdo->prepare(
            'INSERT INTO console_password_resets (user_id, token_hash, expires_at, created_at)
             VALUES (:uid, :th, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())'
        );
        $ins->execute([':uid' => $userId, ':th' => $hash]);
        return ['plain' => $plain, 'hash' => $hash];
    }

    /**
     * @return array{id: int, user_id: int}|null
     */
    public static function findValidByPlainToken(string $plainToken): ?array
    {
        if (strlen($plainToken) < 32) {
            return null;
        }
        $hash = hash('sha256', $plainToken);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, user_id FROM console_password_resets
             WHERE token_hash = :h AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([':h' => $hash]);
        $row = $stmt->fetch();
        return is_array($row) ? ['id' => (int) $row['id'], 'user_id' => (int) $row['user_id']] : null;
    }

    public static function markUsed(int $resetId): void
    {
        $pdo = Database::pdo();
        $pdo->prepare(
            'UPDATE console_password_resets SET used_at = NOW() WHERE id = :id'
        )->execute([':id' => $resetId]);
    }

    public static function invalidateAllForUser(int $userId): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM console_password_resets WHERE user_id = :uid')->execute([':uid' => $userId]);
    }
}
