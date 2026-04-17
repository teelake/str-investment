<?php

declare(strict_types=1);

final class UserRepository
{
    public function findActiveByEmail(string $email): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, email, password_hash, role_key, full_name, is_active
             FROM console_users
             WHERE email = :email AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => mb_strtolower($email)]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function existsActiveUser(int $id): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM console_users WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetch();
    }

    /**
     * @return list<array{id: int, email: string, full_name: string|null}>
     */
    public function listActiveForAssign(int $limit = 200): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, email, full_name FROM console_users WHERE is_active = 1 ORDER BY email ASC LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        /** @var list<array{id: int, email: string, full_name: string|null}> */
        return $stmt->fetchAll();
    }
}
