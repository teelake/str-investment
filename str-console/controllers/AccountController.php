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
        $this->requirePostedCsrf('/account/profile');
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
        $fullNameRaw = (string) Request::post('full_name', '');
        $fullNameStripped = trim(str_replace(["\0", "\r"], '', $fullNameRaw));
        $fullNameOpt = InputValidate::optionalPersonName($fullNameRaw);
        $phoneRaw = trim((string) Request::post('phone', ''));

        if ($email === '' || !InputValidate::emailOk($email)) {
            $this->redirect('/account/profile?error=' . rawurlencode('Enter a valid email.'));
            return;
        }
        if ($fullNameStripped !== '' && $fullNameOpt === null) {
            $this->redirect('/account/profile?error=' . rawurlencode('Full name is too long (max ' . InputValidate::PERSON_NAME_MAX . ' characters).'));
            return;
        }
        $phoneNorm = UserRepository::normalizeOptionalPhone($phoneRaw);
        if ($phoneRaw !== '' && $phoneNorm === null) {
            $this->redirect('/account/profile?error=' . rawurlencode('Enter a valid phone number (at least 8 digits), or leave blank.'));
            return;
        }
        if ($repo->emailTakenByOther($email, $uid)) {
            $this->redirect('/account/profile?error=' . rawurlencode('That email is used by another account.'));
            return;
        }

        try {
            $repo->updateSelfProfile($uid, $email, $fullNameOpt, $phoneNorm);
            AuditLogger::log($uid, 'console_user.self_profile', 'console_user', $uid, ['email' => $email]);
            $roleKey = (string) ($row['role_key'] ?? '');
            $fresh = $repo->findById($uid);
            if ($fresh !== null) {
                $fn = $fresh['full_name'] ?? null;
                $ph = $fresh['phone'] ?? null;
                ConsoleAuth::login(
                    $uid,
                    $email,
                    $roleKey,
                    str_console_user_login_grants($roleKey, $fresh['extra_grants_json'] ?? null),
                    is_string($fn) && $fn !== '' ? $fn : null,
                    is_string($ph) && $ph !== '' ? $ph : null
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
        $this->requirePostedCsrf('/account/password');
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
        if (strlen($current) > InputValidate::PASSWORD_MAX_BYTES || strlen($new) > InputValidate::PASSWORD_MAX_BYTES || strlen($confirm) > InputValidate::PASSWORD_MAX_BYTES) {
            $this->redirect('/account/password?error=' . rawurlencode('Password field is too long.'));
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
                $ph = $fresh['phone'] ?? null;
                ConsoleAuth::login(
                    $uid,
                    $email,
                    $roleKey,
                    str_console_user_login_grants($roleKey, $fresh['extra_grants_json'] ?? null),
                    is_string($fn) && $fn !== '' ? $fn : null,
                    is_string($ph) && $ph !== '' ? $ph : null
                );
            }
            FormGuard::rotate();
            $this->redirect('/account/password?flash=' . rawurlencode('Password updated.'));
        } catch (Throwable) {
            $this->redirect('/account/password?error=' . rawurlencode('Could not update password.'));
        }
    }
}
