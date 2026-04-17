<?php

declare(strict_types=1);

final class UserRepository
{
    private const PER_PAGE = 25;

    /**
     * @param ''|'active'|'inactive' $status
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(int $page, ?string $searchQ, string $status = '', bool $excludeSystemAdminAccounts = false): array
    {
        $page = Pagination::sanitizeRequestedPage($page);
        $perPage = self::PER_PAGE;
        $pdo = Database::pdo();

        $where = ['1=1'];
        $params = [];
        if ($excludeSystemAdminAccounts) {
            $where[] = 'role_key <> :hide_sys_role';
            $params[':hide_sys_role'] = 'system_admin';
        }
        if ($status === 'active') {
            $where[] = 'is_active = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'is_active = 0';
        }

        $t = trim((string) $searchQ);
        if ($t !== '') {
            if (mb_strlen($t) > 120) {
                $t = mb_substr($t, 0, 120);
            }
            $like = '%' . addcslashes($t, '%_\\') . '%';
            $parts = ['email LIKE :uq', 'full_name LIKE :uq', 'phone LIKE :uq'];
            $params[':uq'] = $like;
            if (ctype_digit($t) && (int) $t > 0) {
                $parts[] = 'id = :uid';
                $params[':uid'] = (int) $t;
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
        }

        $whereSql = implode(' AND ', $where);
        $stmtCount = $pdo->prepare('SELECT COUNT(*) AS c FROM console_users WHERE ' . $whereSql);
        $stmtCount->execute($params);
        $total = (int) ($stmtCount->fetch()['c'] ?? 0);
        $page = Pagination::normalizePage($page, $total, $perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            'SELECT id, email, role_key, extra_grants_json, full_name, phone, is_active, created_at, updated_at
             FROM console_users WHERE ' . $whereSql . '
             ORDER BY id DESC
             LIMIT :lim OFFSET :off'
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        /** @var list<array<string, mixed>> */
        $rows = $stmt->fetchAll();

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * Optional profile phone: empty → null; otherwise 11-digit local number only (no country code), digits-only canonical stored.
     */
    public static function normalizeOptionalPhone(string $raw): ?string
    {
        $t = trim(str_replace(["\0", "\r"], '', $raw));
        if ($t === '') {
            return null;
        }
        $v = InputValidate::optionalPhone11($t);
        return $v === false ? null : $v;
    }

    public function findActiveByEmail(string $email): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, email, password_hash, role_key, extra_grants_json, full_name, phone, is_active
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
            'SELECT id, email, role_key, extra_grants_json, full_name, phone, is_active, created_at, updated_at
             FROM console_users ORDER BY id DESC'
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
            'SELECT id, email, password_hash, role_key, extra_grants_json, full_name, phone, is_active, created_at, updated_at
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

    public function create(string $email, string $passwordHash, string $roleKey, ?string $fullName, ?string $phone = null): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO console_users (email, password_hash, role_key, full_name, phone, is_active, created_at, updated_at)
             VALUES (:email, :hash, :role, :fname, :phone, 1, NOW(), NOW())'
        );
        $stmt->execute([
            ':email' => mb_strtolower(trim($email)),
            ':hash' => $passwordHash,
            ':role' => $roleKey,
            ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
            ':phone' => $phone,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function update(
        int $id,
        string $email,
        string $roleKey,
        ?string $fullName,
        ?string $phone,
        bool $isActive,
        ?string $newPasswordHash
    ): void {
        $pdo = Database::pdo();
        if ($newPasswordHash !== null) {
            $stmt = $pdo->prepare(
                'UPDATE console_users SET email = :email, role_key = :role, full_name = :fname, phone = :phone,
                 is_active = :act, password_hash = :ph, updated_at = NOW() WHERE id = :id'
            );
            $stmt->execute([
                ':email' => mb_strtolower(trim($email)),
                ':role' => $roleKey,
                ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
                ':phone' => $phone,
                ':act' => $isActive ? 1 : 0,
                ':ph' => $newPasswordHash,
                ':id' => $id,
            ]);
            return;
        }
        $stmt = $pdo->prepare(
            'UPDATE console_users SET email = :email, role_key = :role, full_name = :fname, phone = :phone,
             is_active = :act, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            ':email' => mb_strtolower(trim($email)),
            ':role' => $roleKey,
            ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
            ':phone' => $phone,
            ':act' => $isActive ? 1 : 0,
            ':id' => $id,
        ]);
    }

    public function updateExtraGrants(int $id, ?string $jsonEncodedArrayOrNull): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE console_users SET extra_grants_json = :j, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':j' => $jsonEncodedArrayOrNull, ':id' => $id]);
    }

    public function updateSelfProfile(int $id, string $email, ?string $fullName, ?string $phone): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE console_users SET email = :email, full_name = :fname, phone = :phone, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            ':email' => mb_strtolower(trim($email)),
            ':fname' => $fullName === null || $fullName === '' ? null : $fullName,
            ':phone' => $phone,
            ':id' => $id,
        ]);
    }

    public function updatePasswordHashForUser(int $id, string $passwordHash): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE console_users SET password_hash = :ph, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':ph' => $passwordHash, ':id' => $id]);
    }

    public function setActive(int $id, bool $active): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE console_users SET is_active = :a, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':a' => $active ? 1 : 0, ':id' => $id]);
    }
}
