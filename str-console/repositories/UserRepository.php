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
}
