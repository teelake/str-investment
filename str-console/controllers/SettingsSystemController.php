<?php

declare(strict_types=1);

final class SettingsSystemController extends BaseController
{
    public function index(): void
    {
        $notice = '';
        if (str_console_database_ready()) {
            $notice = ConsoleSettingRepository::get('system.maintenance_notice') ?? '';
        }
        $this->render('settings/system/index', [
            'maintenanceNotice' => $notice,
            'phpVersion' => PHP_VERSION,
            'dbReady' => str_console_database_ready(),
            'flash' => Request::query('flash'),
            'error' => Request::query('error'),
        ]);
    }

    public function save(): void
    {
        $this->requirePostedCsrf('/settings/system');
        if (!str_console_database_ready()) {
            $this->redirect('/settings/system?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $notice = trim(str_replace(["\0", "\r"], '', (string) Request::post('maintenance_notice', '')));
        if (strlen($notice) > 2000) {
            $this->redirect('/settings/system?error=' . rawurlencode('Maintenance notice is too long (max 2000 characters).'));
            return;
        }

        try {
            $uid = ConsoleAuth::userId();
            ConsoleSettingRepository::set('system.maintenance_notice', $notice, $uid);
            AuditLogger::log($uid, 'settings.system.update', 'console_settings', null, [
                'system.maintenance_notice' => $notice !== '',
            ]);
            $this->redirect('/settings/system?flash=' . rawurlencode('System settings saved.'));
        } catch (Throwable) {
            $this->redirect('/settings/system?error=' . rawurlencode('Could not save.'));
        }
    }
}
