<?php

declare(strict_types=1);

final class SearchController extends BaseController
{
    public function index(): void
    {
        $grants = ConsoleAuth::grants();
        if (!str_console_authorize($grants, ['customers.list']) && !str_console_authorize($grants, ['loans.list'])) {
            ErrorPage::respond(403, 'Access denied', 'You need permission to view customers or loans to use search.');
            return;
        }

        if (!str_console_database_ready()) {
            $this->render('search/index', [
                'q' => '',
                'customers' => [],
                'loans' => [],
                'customers_total' => 0,
                'loans_total' => 0,
                'customers_page' => 1,
                'loans_page' => 1,
                'per_page' => SearchRepository::PER_PAGE,
                'dbError' => 'Database not configured.',
            ]);
            return;
        }

        $q = trim((string) Request::query('q', ''));
        $pageC = Pagination::sanitizeRequestedPage(Request::query('pc', 1));
        $pageL = Pagination::sanitizeRequestedPage(Request::query('pl', 1));
        $customers = [];
        $loans = [];
        $customersTotal = 0;
        $loansTotal = 0;
        $customersPage = 1;
        $loansPage = 1;
        $perPage = SearchRepository::PER_PAGE;
        $error = null;

        if ($q !== '') {
            if (mb_strlen($q) < 2) {
                $error = 'Enter at least 2 characters.';
            } else {
                try {
                    $repo = new SearchRepository();
                    $res = $repo->run($q, ConsoleAuth::userId(), ConsoleAuth::grants(), $pageC, $pageL);
                    $customers = $res['customers'];
                    $loans = $res['loans'];
                    $customersTotal = (int) $res['customers_total'];
                    $loansTotal = (int) $res['loans_total'];
                    $customersPage = (int) $res['customers_page'];
                    $loansPage = (int) $res['loans_page'];
                    $perPage = (int) $res['per_page'];
                } catch (Throwable $e) {
                    error_log('[str-console] search.index: ' . $e->getMessage());
                    $error = 'Search failed. Try again.';
                    if (str_console_debug()) {
                        $error .= ' ' . $e->getMessage();
                    }
                }
            }
        }

        $this->render('search/index', [
            'q' => $q,
            'customers' => $customers,
            'loans' => $loans,
            'customers_total' => $customersTotal,
            'loans_total' => $loansTotal,
            'customers_page' => $customersPage,
            'loans_page' => $loansPage,
            'per_page' => $perPage,
            'dbError' => null,
            'error' => $error,
        ]);
    }
}
