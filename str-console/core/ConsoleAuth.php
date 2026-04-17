<?php

declare(strict_types=1);

final class ConsoleAuth
{
    private const SESSION_KEY = 'str_console_user';

    /**
     * @return list<string>
     */
    public static function grants(): array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($user)) {
            return [];
        }
        $keys = $user['permission_keys'] ?? [];
        if (!is_array($keys)) {
            return [];
        }
        /** @var list<string> $out */
        $out = [];
        foreach ($keys as $k) {
            if (is_string($k) && $k !== '') {
                $out[] = $k;
            }
        }
        return $out;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]) && is_array($_SESSION[self::SESSION_KEY]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        $u = $_SESSION[self::SESSION_KEY] ?? null;
        return is_array($u) ? $u : null;
    }

    public static function userId(): ?int
    {
        $u = self::user();
        if ($u === null || !isset($u['user_id'])) {
            return null;
        }
        return (int) $u['user_id'];
    }

    /**
     * @param list<string> $permissionKeys
     */
    public static function login(
        ?int $userId,
        string $email,
        string $roleKey,
        array $permissionKeys,
        ?string $fullName = null
    ): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $row = [
            'email' => $email,
            'role' => $roleKey,
            'permission_keys' => str_console_expand_grants($permissionKeys),
        ];
        if ($userId !== null) {
            $row['user_id'] = $userId;
        }
        if ($fullName !== null && $fullName !== '') {
            $row['full_name'] = $fullName;
        }
        $_SESSION[self::SESSION_KEY] = $row;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
