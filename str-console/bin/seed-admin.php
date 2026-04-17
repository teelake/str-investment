<?php

declare(strict_types=1);

/**
 * Create a console user. Usage:
 *   php bin/seed-admin.php [email] [password] [role_key]
 *
 * role_key defaults to system_admin. Requires DB env or config/local.php.
 *
 * Do not open this URL in a browser — seeding must run on the server CLI so
 * random visitors cannot create admin accounts.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Seed script</title></head><body>';
    echo '<h1>Command line only</h1>';
    echo '<p>This script cannot be run in the browser (by design). It must run on the server with PHP’s CLI.</p>';
    echo '<h2>How to run it</h2>';
    echo '<ol>';
    echo '<li><strong>SSH</strong> (best): connect to your host, then:<br><code>cd ~/public_html/str-console &amp;&amp; php bin/seed-admin.php your@email.com YourSecurePassword system_admin</code></li>';
    echo '<li><strong>cPanel → Terminal</strong> (or “Run PHP”): same command as above from your account home or <code>public_html</code>.</li>';
    echo '<li><strong>Your PC</strong> only works if PHP can reach the live database (usually not); use the host’s terminal instead.</li>';
    echo '</ol>';
    echo '<p>After the first admin exists, sign in at <code>/str-console/login</code> with that email and password.</p>';
    echo '</body></html>';
    exit(1);
}

$root = dirname(__DIR__);
require $root . '/bootstrap.php';

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
        'INSERT INTO console_users (email, password_hash, role_key, full_name, phone, is_active, created_at, updated_at)
         VALUES (:email, :hash, :role, :fname, NULL, 1, NOW(), NOW())'
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
