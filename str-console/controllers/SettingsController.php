<?php

declare(strict_types=1);

final class SettingsController extends BaseController
{
    public function policies(): void
    {
        $this->render('settings/policies', [
            'scopeCustomers' => PolicyService::scopeCustomersByAssignment(),
            'scopeLoans' => PolicyService::scopeLoansByAssignment(),
            'ledgerAutoAccrue' => PolicyService::ledgerAutoAccrue(),
            'flash' => Request::query('flash'),
            'error' => Request::query('error'),
        ]);
    }

    public function savePolicies(): void
    {
        $this->requirePostedCsrf('/settings/policies');
        if (!str_console_database_ready()) {
            $this->redirect('/settings/policies?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $scopeCustomers = isset($_POST['scope_customers']) && (string) $_POST['scope_customers'] === '1';
        $scopeLoans = isset($_POST['scope_loans']) && (string) $_POST['scope_loans'] === '1';
        $ledgerAutoAccrue = isset($_POST['ledger_auto_accrue']) && (string) $_POST['ledger_auto_accrue'] === '1';

        try {
            $uid = ConsoleAuth::userId();
            ConsoleSettingRepository::set('scope.customers_by_assignment', $scopeCustomers ? '1' : '0', $uid);
            ConsoleSettingRepository::set('scope.loans_by_assignment', $scopeLoans ? '1' : '0', $uid);
            ConsoleSettingRepository::set('ledger.auto_accrue', $ledgerAutoAccrue ? '1' : '0', $uid);
            AuditLogger::log($uid, 'settings.policies.update', 'console_settings', null, [
                'scope.customers_by_assignment' => $scopeCustomers,
                'scope.loans_by_assignment' => $scopeLoans,
                'ledger.auto_accrue' => $ledgerAutoAccrue,
            ]);
            $this->redirect('/settings/policies?flash=' . rawurlencode('Policies saved.'));
        } catch (Throwable) {
            $this->redirect('/settings/policies?error=' . rawurlencode('Could not save policies.'));
        }
    }
}
