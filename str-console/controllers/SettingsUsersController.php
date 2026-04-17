<?php

declare(strict_types=1);

final class SettingsUsersController extends BaseController
{
    public function index(): void
    {
        if (!str_console_database_ready()) {
            $this->render('settings/users/index', ['rows' => [], 'dbError' => 'Database not configured.']);
            return;
        }
        try {
            $rows = (new UserRepository())->listAll();
            $this->render('settings/users/index', ['rows' => $rows, 'dbError' => null]);
        } catch (Throwable) {
            $this->render('settings/users/index', ['rows' => [], 'dbError' => 'Could not load users.']);
        }
    }

    public function create(): void
    {
        $actorRole = (string) (ConsoleAuth::user()['role'] ?? '');
        $roles = str_console_assignable_role_keys($actorRole);
        if ($roles === []) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $this->render('settings/users/create', [
            'assignableRoles' => $roles,
            'error' => Request::query('error'),
        ]);
    }

    public function store(): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/settings/users/create?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $actorRole = (string) (ConsoleAuth::user()['role'] ?? '');
        $allowed = str_console_assignable_role_keys($actorRole);
        if ($allowed === []) {
            $this->redirect('/settings/users');
            return;
        }

        $email = trim((string) Request::post('email', ''));
        $password = (string) Request::post('password', '');
        $roleKey = trim((string) Request::post('role_key', ''));
        $fullName = trim((string) Request::post('full_name', ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/settings/users/create?error=' . rawurlencode('Enter a valid email.'));
            return;
        }
        if (strlen($password) < 10) {
            $this->redirect('/settings/users/create?error=' . rawurlencode('Password must be at least 10 characters.'));
            return;
        }
        if (!in_array($roleKey, $allowed, true)) {
            $this->redirect('/settings/users/create?error=' . rawurlencode('Invalid role.'));
            return;
        }

        $repo = new UserRepository();
        if ($repo->emailExists($email)) {
            $this->redirect('/settings/users/create?error=' . rawurlencode('That email is already registered.'));
            return;
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $id = $repo->create($email, $hash, $roleKey, $fullName === '' ? null : $fullName);
            AuditLogger::log(ConsoleAuth::userId(), 'console_user.create', 'console_user', $id, [
                'email' => $email,
                'role_key' => $roleKey,
            ]);
            $this->redirect('/settings/users?flash=' . rawurlencode('User created.'));
        } catch (Throwable) {
            $this->redirect('/settings/users/create?error=' . rawurlencode('Could not save user.'));
        }
    }

    public function edit(int $userId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/settings/users');
            return;
        }
        $repo = new UserRepository();
        $row = $repo->findById($userId);
        if ($row === null) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $actorRole = (string) (ConsoleAuth::user()['role'] ?? '');
        $allowed = str_console_assignable_role_keys($actorRole);
        if ($allowed === []) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $targetRole = (string) ($row['role_key'] ?? '');
        if (!in_array($targetRole, $allowed, true) && $userId !== ConsoleAuth::userId()) {
            http_response_code(403);
            echo 'You cannot edit this account’s role level.';
            return;
        }

        $this->render('settings/users/edit', [
            'user' => $row,
            'assignableRoles' => $allowed,
            'error' => Request::query('error'),
            'permissionCatalog' => str_console_permission_catalog(),
            'extraGrantKeys' => str_console_parse_extra_grants_json($row['extra_grants_json'] ?? null),
            'canEditExtraGrants' => ((string) (ConsoleAuth::user()['role'] ?? '')) === 'system_admin',
        ]);
    }

    public function update(int $userId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/settings/users');
            return;
        }

        $repo = new UserRepository();
        $row = $repo->findById($userId);
        if ($row === null) {
            $this->redirect('/settings/users');
            return;
        }

        $actorId = ConsoleAuth::userId();
        $actorRole = (string) (ConsoleAuth::user()['role'] ?? '');
        $allowed = str_console_assignable_role_keys($actorRole);
        if ($allowed === []) {
            $this->redirect('/settings/users');
            return;
        }

        $targetRole = (string) ($row['role_key'] ?? '');
        if (!in_array($targetRole, $allowed, true) && $userId !== $actorId) {
            $this->redirect('/settings/users?error=' . rawurlencode('You cannot edit this user.'));
            return;
        }

        $email = trim((string) Request::post('email', ''));
        $roleKey = trim((string) Request::post('role_key', ''));
        $fullName = trim((string) Request::post('full_name', ''));
        $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1';
        $password = (string) Request::post('password', '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('Enter a valid email.'));
            return;
        }
        if (!in_array($roleKey, $allowed, true)) {
            $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('Invalid role.'));
            return;
        }

        if ($repo->emailTakenByOther($email, $userId)) {
            $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('That email is used by another account.'));
            return;
        }

        if ($actorId !== null && $userId === $actorId && $roleKey !== $targetRole) {
            $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('You cannot change your own role here.'));
            return;
        }

        if ($actorId !== null && $userId === $actorId && !$isActive) {
            $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('You cannot deactivate your own account.'));
            return;
        }

        if (!$isActive && $targetRole === 'system_admin') {
            if ($repo->countActiveByRole('system_admin') <= 1) {
                $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('Cannot deactivate the only active system admin.'));
                return;
            }
        }

        $newHash = null;
        if ($password !== '') {
            if (strlen($password) < 10) {
                $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('Password must be at least 10 characters or leave blank.'));
                return;
            }
            $newHash = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $repo->update(
                $userId,
                $email,
                $roleKey,
                $fullName === '' ? null : $fullName,
                $isActive,
                $newHash
            );
            if (((string) (ConsoleAuth::user()['role'] ?? '')) === 'system_admin') {
                $extraPosted = $_POST['extra_grants'] ?? [];
                if (!is_array($extraPosted)) {
                    $extraPosted = [];
                }
                $extraKeys = [];
                foreach ($extraPosted as $k) {
                    if (is_string($k) && $k !== '') {
                        $extraKeys[] = $k;
                    }
                }
                $extraKeys = array_values(array_unique($extraKeys));
                if ($extraKeys !== [] && !str_console_validate_permission_keys($extraKeys)) {
                    $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('Invalid extra permission selection.'));
                    return;
                }
                $repo->updateExtraGrants($userId, $extraKeys === [] ? null : json_encode($extraKeys));
            }
            AuditLogger::log(ConsoleAuth::userId(), 'console_user.update', 'console_user', $userId, [
                'email' => $email,
                'role_key' => $roleKey,
                'is_active' => $isActive,
                'password_changed' => $newHash !== null,
            ]);
            if ($actorId !== null && $userId === $actorId) {
                $fresh = $repo->findById($userId);
                if ($fresh !== null) {
                    $fn = $fresh['full_name'] ?? null;
                    ConsoleAuth::login(
                        $userId,
                        $email,
                        $roleKey,
                        str_console_user_login_grants($roleKey, $fresh['extra_grants_json'] ?? null),
                        is_string($fn) && $fn !== '' ? $fn : null
                    );
                }
            }
            $this->redirect('/settings/users?flash=' . rawurlencode('User updated.'));
        } catch (Throwable) {
            $this->redirect('/settings/users/' . $userId . '/edit?error=' . rawurlencode('Could not save changes.'));
        }
    }
}
