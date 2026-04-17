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
            ConsoleAuth::login(null, $email, $role, str_console_role_grants_for($role));
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
            if ($row === null || !password_verify($password, (string) $row['password_hash'])) {
                $this->redirect('/login?error=' . rawurlencode('Invalid email or password.'));
                return;
            }

            $roleKey = (string) $row['role_key'];
            $defaults = str_console_default_role_grants();
            if (!isset($defaults[$roleKey])) {
                $this->redirect('/login?error=' . rawurlencode('Account role is not recognized. Contact an administrator.'));
                return;
            }

            ConsoleAuth::login((int) $row['id'], (string) $row['email'], $roleKey, str_console_role_grants_for($roleKey));
            $this->redirect($next);
        } catch (Throwable) {
            $this->redirect('/login?error=' . rawurlencode('Could not reach the database. Try again shortly.'));
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
}
