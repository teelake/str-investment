<?php

declare(strict_types=1);

final class AccountController extends BaseController
{
    public function profile(): void
    {
        $uid = ConsoleAuth::userId();
        if ($uid === null || !str_console_database_ready()) {
            $this->redirect('/');
            return;
        }
        $repo = new UserRepository();
        $row = $repo->findById($uid);
        if ($row === null) {
            $this->redirect('/');
            return;
        }
        $this->render('account/profile', [
            'user' => $row,
            'error' => Request::query('error'),
            'flash' => Request::query('flash'),
        ]);
    }

    public function saveProfile(): void
    {
        $uid = ConsoleAuth::userId();
        if ($uid === null || !str_console_database_ready()) {
            $this->redirect('/');
            return;
        }
        $repo = new UserRepository();
        $row = $repo->findById($uid);
        if ($row === null) {
            $this->redirect('/');
            return;
        }

        $email = trim((string) Request::post('email', ''));
        $fullName = trim((string) Request::post('full_name', ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/account/profile?error=' . rawurlencode('Enter a valid email.'));
            return;
        }
        if ($repo->emailTakenByOther($email, $uid)) {
            $this->redirect('/account/profile?error=' . rawurlencode('That email is used by another account.'));
            return;
        }

        try {
            $repo->updateSelfProfile($uid, $email, $fullName === '' ? null : $fullName);
            AuditLogger::log($uid, 'console_user.self_profile', 'console_user', $uid, ['email' => $email]);
            $roleKey = (string) ($row['role_key'] ?? '');
            $fresh = $repo->findById($uid);
            if ($fresh !== null) {
                $fn = $fresh['full_name'] ?? null;
                ConsoleAuth::login(
                    $uid,
                    $email,
                    $roleKey,
                    str_console_user_login_grants($roleKey, $fresh['extra_grants_json'] ?? null),
                    is_string($fn) && $fn !== '' ? $fn : null
                );
            }
            $this->redirect('/account/profile?flash=' . rawurlencode('Profile updated.'));
        } catch (Throwable) {
            $this->redirect('/account/profile?error=' . rawurlencode('Could not save.'));
        }
    }

    public function password(): void
    {
        $uid = ConsoleAuth::userId();
        if ($uid === null) {
            $this->redirect('/');
            return;
        }
        $this->render('account/password', [
            'error' => Request::query('error'),
            'flash' => Request::query('flash'),
        ]);
    }

    public function savePassword(): void
    {
        $uid = ConsoleAuth::userId();
        if ($uid === null || !str_console_database_ready()) {
            $this->redirect('/');
            return;
        }
        $repo = new UserRepository();
        $row = $repo->findById($uid);
        if ($row === null) {
            $this->redirect('/');
            return;
        }

        $current = (string) Request::post('current_password', '');
        $new = (string) Request::post('new_password', '');
        $confirm = (string) Request::post('confirm_password', '');

        if ($current === '' || $new === '' || $confirm === '') {
            $this->redirect('/account/password?error=' . rawurlencode('Fill in all password fields.'));
            return;
        }
        if ($new !== $confirm) {
            $this->redirect('/account/password?error=' . rawurlencode('New passwords do not match.'));
            return;
        }
        if (strlen($new) < 10) {
            $this->redirect('/account/password?error=' . rawurlencode('New password must be at least 10 characters.'));
            return;
        }
        if (!password_verify($current, (string) ($row['password_hash'] ?? ''))) {
            $this->redirect('/account/password?error=' . rawurlencode('Current password is incorrect.'));
            return;
        }

        try {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $repo->updatePasswordHashForUser($uid, $hash);
            AuditLogger::log($uid, 'console_user.self_password', 'console_user', $uid, []);
            $email = (string) ($row['email'] ?? '');
            $roleKey = (string) ($row['role_key'] ?? '');
            $fresh = $repo->findById($uid);
            if ($fresh !== null) {
                $fn = $fresh['full_name'] ?? null;
                ConsoleAuth::login(
                    $uid,
                    $email,
                    $roleKey,
                    str_console_user_login_grants($roleKey, $fresh['extra_grants_json'] ?? null),
                    is_string($fn) && $fn !== '' ? $fn : null
                );
            }
            $this->redirect('/account/password?flash=' . rawurlencode('Password updated.'));
        } catch (Throwable) {
            $this->redirect('/account/password?error=' . rawurlencode('Could not update password.'));
        }
    }
}
