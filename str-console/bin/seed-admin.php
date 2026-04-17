<?php

declare(strict_types=1);

/**
 * Create a console user. Usage:
 *   php bin/seed-admin.php [email] [password] [role_key]
 *
 * role_key defaults to system_admin. Requires DB env or config/local.php.
 */

$root = dirname(__DIR__);
require $root . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'CLI only.';
    exit(1);
}

if (!str_console_database_ready()) {
    fwrite(STDERR, "Database not configured. Set STR_CONSOLE_DB_* or define constants in config/local.php.\n");
    exit(1);
}

$email = isset($argv[1]) ? mb_strtolower(trim((string) $argv[1])) : 'admin@strinvestment.local';
$password = isset($argv[2]) ? (string) $argv[2] : 'ChangeMe!123';
$roleKey = isset($argv[3]) ? trim((string) $argv[3]) : 'system_admin';

$defaults = str_console_default_role_grants();
if (!isset($defaults[$roleKey])) {
    fwrite(STDERR, "Unknown role_key: {$roleKey}. Use one of: " . implode(', ', array_keys($defaults)) . "\n");
    exit(1);
}

try {
    $pdo = Database::pdo();
    $check = $pdo->prepare('SELECT id FROM console_users WHERE email = :e LIMIT 1');
    $check->execute([':e' => $email]);
    if ($check->fetch()) {
        fwrite(STDOUT, "User already exists: {$email}\n");
        exit(0);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO console_users (email, password_hash, role_key, full_name, is_active, created_at, updated_at)
         VALUES (:email, :hash, :role, :fname, 1, NOW(), NOW())'
    );
    $stmt->execute([
        ':email' => $email,
        ':hash' => $hash,
        ':role' => $roleKey,
        ':fname' => 'Console Admin',
    ]);

    fwrite(STDOUT, "Created {$roleKey}: {$email}\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
