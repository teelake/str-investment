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
                'dbError' => 'Database not configured.',
            ]);
            return;
        }

        $q = trim((string) Request::query('q', ''));
        $customers = [];
        $loans = [];
        $error = null;

        if ($q !== '') {
            if (mb_strlen($q) < 2) {
                $error = 'Enter at least 2 characters.';
            } else {
                try {
                    $repo = new SearchRepository();
                    $res = $repo->run($q, ConsoleAuth::userId(), ConsoleAuth::grants());
                    $customers = $res['customers'];
                    $loans = $res['loans'];
                } catch (Throwable) {
                    $error = 'Search failed. Try again.';
                }
            }
        }

        $this->render('search/index', [
            'q' => $q,
            'customers' => $customers,
            'loans' => $loans,
            'dbError' => null,
            'error' => $error,
        ]);
    }
}
