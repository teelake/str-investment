<?php

declare(strict_types=1);

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $customerCount = null;
        $dbError = null;

        if (str_console_database_ready()) {
            try {
                $repo = new CustomerRepository();
                $customerCount = $repo->countScoped(ConsoleAuth::userId(), ConsoleAuth::grants());
            } catch (Throwable) {
                $dbError = 'Database unreachable. Check credentials and that schema is installed.';
            }
        } else {
            $dbError = 'Database not configured. Add STR_CONSOLE_DB_DSN (see str-console/config/database.php).';
        }

        $this->render('dashboard/index', [
            'user' => ConsoleAuth::user(),
            'customerCount' => $customerCount,
            'dbError' => $dbError,
        ]);
    }
}
