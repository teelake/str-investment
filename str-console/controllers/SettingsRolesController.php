<?php

declare(strict_types=1);

final class SettingsRolesController extends BaseController
{
    public function index(): void
    {
        $catalog = str_console_permission_catalog();
        $editableRoles = str_console_roles_with_editable_grants();
        /** @var array<string, list<string>> $grantsByRole */
        $grantsByRole = [];
        foreach ($editableRoles as $rk) {
            $grantsByRole[$rk] = str_console_role_grants_for($rk);
        }

        $this->render('settings/roles/index', [
            'catalog' => $catalog,
            'editableRoles' => $editableRoles,
            'grantsByRole' => $grantsByRole,
            'flash' => Request::query('flash'),
            'error' => Request::query('error'),
        ]);
    }

    public function save(): void
    {
        $this->requirePostedCsrf('/settings/roles');
        if (!str_console_database_ready()) {
            $this->redirect('/settings/roles?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $editable = str_console_roles_with_editable_grants();
        $uid = ConsoleAuth::userId();

        if (isset($_POST['reset_defaults']) && (string) $_POST['reset_defaults'] === '1') {
            try {
                $pdo = Database::pdo();
                foreach ($editable as $rk) {
                    $stmt = $pdo->prepare('DELETE FROM console_settings WHERE setting_key = :k');
                    $stmt->execute([':k' => 'roles.grants.' . $rk]);
                }
                AuditLogger::log($uid, 'settings.roles.reset', 'console_settings', null, ['roles' => $editable]);
                $this->redirect('/settings/roles?flash=' . rawurlencode('Restored code defaults for admin, manager, and credit officer.'));
            } catch (Throwable) {
                $this->redirect('/settings/roles?error=' . rawurlencode('Could not reset.'));
            }
            return;
        }

        try {
            foreach ($editable as $rk) {
                $field = 'grants_' . $rk;
                $posted = $_POST[$field] ?? [];
                if (!is_array($posted)) {
                    $this->redirect('/settings/roles?error=' . rawurlencode('Invalid form data.'));
                    return;
                }
                /** @var list<string> $keys */
                $keys = [];
                foreach ($posted as $p) {
                    if (is_string($p) && $p !== '') {
                        $keys[] = $p;
                    }
                }
                $keys[] = 'auth.session';
                $keys = array_values(array_unique($keys));
                if (!str_console_validate_permission_keys($keys)) {
                    $this->redirect('/settings/roles?error=' . rawurlencode('Unknown permission key for role: ' . $rk));
                    return;
                }
                $json = json_encode($keys, JSON_UNESCAPED_SLASHES);
                if ($json === false) {
                    $this->redirect('/settings/roles?error=' . rawurlencode('Could not encode permissions.'));
                    return;
                }
                ConsoleSettingRepository::set('roles.grants.' . $rk, $json, $uid);
            }
            AuditLogger::log($uid, 'settings.roles.update', 'console_settings', null, ['roles' => $editable]);
            $this->redirect('/settings/roles?flash=' . rawurlencode('Role permissions saved. Users must sign in again to apply changes.'));
        } catch (Throwable) {
            $this->redirect('/settings/roles?error=' . rawurlencode('Could not save.'));
        }
    }
}
