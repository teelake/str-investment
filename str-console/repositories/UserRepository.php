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

    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id, email, role_key, full_name, is_active, created_at, updated_at
             FROM console_users ORDER BY is_active DESC, email ASC'
        );
        /** @var list<array<string, mixed>> */
        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, email, password_hash, role_key, full_name, is_active, created_at, updated_at
             FROM console_users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function emailTakenByOther(string $email, int $exceptId): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT 1 FROM console_users WHERE email = :e AND id != :id LIMIT 1'
        );
        $stmt->execute([':e' => mb_strtolower(trim($email)), ':id' => $exceptId]);
        return (bool) $stmt->fetch();
    }

    public function emailExists(string $email): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM console_users WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => mb_strtolower(trim($email))]);
        return (bool) $stmt->fetch();
    }

    public function countActiveByRole(string $roleKey): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS c FROM console_users WHERE is_active = 1 AND role_key = :r'
        );
        $stmt->execute([':r' => $roleKey]);
        return (int) ($stmt->fetch()['c'] ?? 0);
    }

    public function create(string $email, string $passwordHash, string $roleKey, ?string $fullName): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO console_users (email, password_hash, role_key, full_name, is_active, created_at, updated_at)
             VALUES (:email, :hash, :role, :fname, 1, NOW(), NOW())'
        );
        $stmt->execute([
            ':email' => mb_strtolower(trim($email)),
            ':hash' => $passwordHash,
            ':role' => $roleKey,
            ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function update(
        int $id,
        string $email,
        string $roleKey,
        ?string $fullName,
        bool $isActive,
        ?string $newPasswordHash
    ): void {
        $pdo = Database::pdo();
        if ($newPasswordHash !== null) {
            $stmt = $pdo->prepare(
                'UPDATE console_users SET email = :email, role_key = :role, full_name = :fname,
                 is_active = :act, password_hash = :ph, updated_at = NOW() WHERE id = :id'
            );
            $stmt->execute([
                ':email' => mb_strtolower(trim($email)),
                ':role' => $roleKey,
                ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
                ':act' => $isActive ? 1 : 0,
                ':ph' => $newPasswordHash,
                ':id' => $id,
            ]);
            return;
        }
        $stmt = $pdo->prepare(
            'UPDATE console_users SET email = :email, role_key = :role, full_name = :fname,
             is_active = :act, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            ':email' => mb_strtolower(trim($email)),
            ':role' => $roleKey,
            ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
            ':act' => $isActive ? 1 : 0,
            ':id' => $id,
        ]);
    }
}
