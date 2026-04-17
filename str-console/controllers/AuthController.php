<?php

declare(strict_types=1);

final class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (ConsoleAuth::check()) {
            $this->redirect('/');
            return;
        }

        $roles = array_keys(str_console_default_role_grants());
        $this->render('auth/login', [
            'devLogin' => str_console_dev_login_enabled(),
            'dbReady' => str_console_database_ready(),
            'roles' => $roles,
            'next' => self::safeNextPath(Request::query('next')),
            'error' => Request::query('error'),
            'sent' => Request::query('sent'),
        ]);
    }

    public function login(): void
    {
        $email = trim((string) Request::post('email', ''));
        $next = self::safeNextPath(Request::post('next'));

        if ($email === '') {
            $this->redirect('/login?error=' . rawurlencode('Enter your work email.'));
            return;
        }

        if (str_console_dev_login_enabled()) {
            $role = (string) Request::post('role', '');
            $defaults = str_console_default_role_grants();
            if (!isset($defaults[$role])) {
                $this->redirect('/login?error=' . rawurlencode('Invalid role.'));
                return;
            }
            ConsoleAuth::login(null, $email, $role, str_console_user_login_grants($role, null), null, null);
            $this->redirect($next);
            return;
        }

        if (!str_console_database_ready()) {
            $this->redirect('/login?error=' . rawurlencode('Database is not configured. Add DB credentials or enable STR_CONSOLE_DEV_LOGIN=1 for demo access.'));
            return;
        }

        $password = (string) Request::post('password', '');
        if ($password === '') {
            $this->redirect('/login?error=' . rawurlencode('Enter your password.'));
            return;
        }

        try {
            $repo = new UserRepository();
            $row = $repo->findActiveByEmail($email);
            $ok = $row !== null && password_verify($password, (string) ($row['password_hash'] ?? ''));
            if (!$ok) {
                self::loginFailureDelay();
                $this->redirect('/login?error=' . rawurlencode('Invalid email or password.'));
                return;
            }

            $roleKey = (string) $row['role_key'];
            $defaults = str_console_default_role_grants();
            if (!isset($defaults[$roleKey])) {
                $this->redirect('/login?error=' . rawurlencode('Account role is not recognized. Contact an administrator.'));
                return;
            }

            $fname = $row['full_name'] ?? null;
            $ph = $row['phone'] ?? null;
            ConsoleAuth::login(
                (int) $row['id'],
                (string) $row['email'],
                $roleKey,
                str_console_user_login_grants($roleKey, $row['extra_grants_json'] ?? null),
                is_string($fname) && $fname !== '' ? $fname : null,
                is_string($ph) && $ph !== '' ? $ph : null
            );
            $this->redirect($next);
        } catch (Throwable $e) {
            self::loginFailureDelay();
            $this->redirect('/login?error=' . rawurlencode(self::friendlyDbFailureMessage($e, 'Could not reach the database. Try again shortly.')));
        }
    }

    public function showForgotPassword(): void
    {
        if (ConsoleAuth::check()) {
            $this->redirect('/');
            return;
        }
        if (str_console_dev_login_enabled()) {
            $this->redirect('/login?error=' . rawurlencode('Password reset is not available in demo login mode.'));
            return;
        }

        $devUrl = null;
        if (isset($_SESSION['str_console_dev_reset_url']) && is_string($_SESSION['str_console_dev_reset_url'])) {
            $devUrl = $_SESSION['str_console_dev_reset_url'];
            unset($_SESSION['str_console_dev_reset_url']);
        }

        $this->render('auth/forgot-password', [
            'dbReady' => str_console_database_ready(),
            'error' => Request::query('error'),
            'sent' => Request::query('sent'),
            'devResetUrl' => $devUrl,
        ]);
    }

    public function submitForgotPassword(): void
    {
        if (ConsoleAuth::check()) {
            $this->redirect('/');
            return;
        }
        if (str_console_dev_login_enabled()) {
            $this->redirect('/login?error=' . rawurlencode('Password reset is not available in demo login mode.'));
            return;
        }
        if (!str_console_database_ready()) {
            $this->redirect('/forgot-password?error=' . rawurlencode('Database is not configured.'));
            return;
        }

        $email = trim((string) Request::post('email', ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/forgot-password?error=' . rawurlencode('Enter a valid email address.'));
            return;
        }

        if (!self::forgotPasswordRateAllow()) {
            $this->redirect('/forgot-password?error=' . rawurlencode('Too many attempts. Please wait an hour and try again.'));
            return;
        }

        self::loginFailureDelay();

        $genericSent = '/forgot-password?sent=1';

        try {
            PasswordResetRepository::deleteExpired();
            $repo = new UserRepository();
            $row = $repo->findActiveByEmail($email);
            if ($row === null) {
                $this->redirect($genericSent);
                return;
            }

            $userId = (int) $row['id'];
            $pair = PasswordResetRepository::createTokenForUser($userId);
            $resetUrl = Request::publicBaseUrl() . '/reset-password?token=' . rawurlencode($pair['plain']);

            $mailed = PasswordResetNotifier::sendResetLink((string) $row['email'], $resetUrl);
            if (!$mailed && PasswordResetNotifier::devLinkInUiEnabled()) {
                $_SESSION['str_console_dev_reset_url'] = $resetUrl;
            }

            AuditLogger::log(null, 'auth.password_reset_request', 'console_user', $userId, [
                'email_sent' => $mailed,
            ]);

            $this->redirect($genericSent);
        } catch (Throwable $e) {
            $this->redirect('/forgot-password?error=' . rawurlencode(self::friendlyDbFailureMessage($e, 'Could not process the request. Ensure database migrations are up to date.')));
        }
    }

    public function showResetPassword(): void
    {
        if (ConsoleAuth::check()) {
            $this->redirect('/');
            return;
        }
        if (str_console_dev_login_enabled()) {
            $this->redirect('/login?error=' . rawurlencode('Password reset is not available in demo login mode.'));
            return;
        }

        $token = trim((string) Request::query('token', ''));
        $invalid = false;

        if ($token === '' || strlen($token) < 32) {
            $invalid = true;
        } else {
            try {
                $rec = PasswordResetRepository::findValidByPlainToken($token);
                if ($rec === null) {
                    $invalid = true;
                } else {
                    $_SESSION['str_pwd_reset_csrf'] = bin2hex(random_bytes(16));
                }
            } catch (Throwable) {
                $invalid = true;
            }
        }

        if ($invalid) {
            unset($_SESSION['str_pwd_reset_csrf']);
        }

        $this->render('auth/reset-password', [
            'dbReady' => str_console_database_ready(),
            'token' => $invalid ? '' : $token,
            'invalid' => $invalid,
            'csrf' => $invalid ? '' : (string) ($_SESSION['str_pwd_reset_csrf'] ?? ''),
            'error' => Request::query('error'),
        ]);
    }

    public function submitResetPassword(): void
    {
        if (ConsoleAuth::check()) {
            $this->redirect('/');
            return;
        }
        if (str_console_dev_login_enabled()) {
            $this->redirect('/login');
            return;
        }
        if (!str_console_database_ready()) {
            $this->redirect('/login?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $token = trim((string) Request::post('token', ''));
        $csrf = trim((string) Request::post('csrf', ''));
        $expect = (string) ($_SESSION['str_pwd_reset_csrf'] ?? '');
        $new = (string) Request::post('new_password', '');
        $confirm = (string) Request::post('confirm_password', '');

        if ($token === '' || $csrf === '' || $expect === '' || !hash_equals($expect, $csrf)) {
            $this->redirect('/reset-password?error=' . rawurlencode('This form expired. Open the link from your email again.'));
            return;
        }

        if (strlen($new) < 10) {
            $this->redirect('/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Password must be at least 10 characters.'));
            return;
        }
        if ($new !== $confirm) {
            $this->redirect('/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Passwords do not match.'));
            return;
        }

        try {
            $rec = PasswordResetRepository::findValidByPlainToken($token);
            if ($rec === null) {
                unset($_SESSION['str_pwd_reset_csrf']);
                $this->redirect('/forgot-password?error=' . rawurlencode('This reset link is invalid or has expired. Request a new one.'));
                return;
            }

            $userId = $rec['user_id'];
            $resetId = $rec['id'];
            $repo = new UserRepository();
            $user = $repo->findById($userId);
            if ($user === null || !(int) ($user['is_active'] ?? 0)) {
                unset($_SESSION['str_pwd_reset_csrf']);
                $this->redirect('/login?error=' . rawurlencode('This account is not active. Contact an administrator.'));
                return;
            }

            $hash = password_hash($new, PASSWORD_DEFAULT);
            $repo->updatePasswordHashForUser($userId, $hash);
            PasswordResetRepository::markUsed($resetId);
            PasswordResetRepository::invalidateAllForUser($userId);
            unset($_SESSION['str_pwd_reset_csrf']);

            AuditLogger::log(null, 'auth.password_reset_complete', 'console_user', $userId, []);

            $this->redirect('/login?sent=reset');
        } catch (Throwable) {
            $this->redirect('/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Could not save password. Try again.'));
        }
    }

    public function logout(): void
    {
        ConsoleAuth::logout();
        $this->redirect('/login');
    }

    private static function safeNextPath(mixed $next): string
    {
        if (!is_string($next) || $next === '') {
            return '/';
        }
        if (!str_starts_with($next, '/') || str_starts_with($next, '//')) {
            return '/';
        }
        return $next;
    }

    private static function friendlyDbFailureMessage(Throwable $e, string $generic): string
    {
        if ($e instanceof PDOException) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            $msg = $e->getMessage();
            if ($sqlState === '42S22' || str_contains($msg, 'Unknown column')) {
                return 'The database is missing columns the app expects (for example console_users.extra_grants_json). '
                    . 'Run SQL migrations from str-console/database/migrations/ on the server database, in order: '
                    . '005_console_users_extra_grants.sql, then 006_password_resets.sql, then 007_console_users_phone.sql. '
                    . 'Then try signing in again.';
            }
            if ($sqlState === '42S02' || str_contains($msg, "doesn't exist")) {
                return 'The database is missing tables the app expects. Run all SQL files in str-console/database/migrations/ '
                    . 'in numeric order (through the latest), then try again.';
            }
        }
        return $generic;
    }

    private static function loginFailureDelay(): void
    {
        usleep(random_int(120_000, 420_000));
    }

    private static function forgotPasswordRateAllow(): bool
    {
        $now = time();
        if (!isset($_SESSION['str_pwd_forgot_bucket']) || !is_array($_SESSION['str_pwd_forgot_bucket'])) {
            $_SESSION['str_pwd_forgot_bucket'] = ['start' => $now, 'count' => 0];
        }
        $b = &$_SESSION['str_pwd_forgot_bucket'];
        if ($now - (int) ($b['start'] ?? $now) > 3600) {
            $b = ['start' => $now, 'count' => 0];
        }
        if ((int) ($b['count'] ?? 0) >= 5) {
            return false;
        }
        $b['count'] = (int) ($b['count'] ?? 0) + 1;
        return true;
    }
}
