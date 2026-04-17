<?php

declare(strict_types=1);

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $customerCount = null;
        $dbError = null;

        $loanStats = null;
        $loanByStatus = null;
        $bookedPrincipal = null;
        $recentLoans = null;
        if (str_console_database_ready()) {
            try {
                $uid = ConsoleAuth::userId();
                $grants = ConsoleAuth::grants();
                $repo = new CustomerRepository();
                $customerCount = $repo->countScoped($uid, $grants);
                $loanRepo = new LoanRepository();
                $loanStats = $loanRepo->dashboardTotals($uid, $grants);
                $loanByStatus = $loanRepo->dashboardCountsByStatus($uid, $grants);
                $bookedPrincipal = $loanRepo->dashboardActiveBookedPrincipal($uid, $grants);
                $recentLoans = $loanRepo->dashboardRecentLoans($uid, $grants, 8);
            } catch (Throwable) {
                $customerCount = null;
                $loanStats = null;
                $loanByStatus = null;
                $bookedPrincipal = null;
                $recentLoans = null;
                $dbError = 'Database unreachable. Check credentials and that schema is installed.';
            }
        } else {
            $dbError = 'Database not configured. Add STR_CONSOLE_DB_DSN (see str-console/config/database.php).';
        }

        $this->render('dashboard/index', [
            'customerCount' => $customerCount,
            'loanStats' => $loanStats,
            'loanByStatus' => $loanByStatus,
            'bookedPrincipal' => $bookedPrincipal,
            'recentLoans' => $recentLoans,
            'dbError' => $dbError,
        ]);
    }
}
