<?php

declare(strict_types=1);

final class LoanProductsController extends BaseController
{
    public function index(): void
    {
        $page = Pagination::sanitizeRequestedPage(Request::query('page', 1));
        $activity = trim((string) Request::query('status', ''));
        if (!in_array($activity, ['', 'active', 'retired'], true)) {
            $activity = '';
        }
        if (!str_console_database_ready()) {
            $this->render('loan_products/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 25],
                'filterActivity' => $activity,
                'dbError' => 'Database not configured.',
            ]);
            return;
        }
        try {
            $repo = new LoanProductRepository();
            $data = $repo->paginate($page, $activity);
            $this->render('loan_products/index', ['pagination' => $data, 'filterActivity' => $activity, 'dbError' => null]);
        } catch (Throwable) {
            $this->render('loan_products/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 25],
                'filterActivity' => $activity,
                'dbError' => 'Could not load products.',
            ]);
        }
    }

    public function show(int $id): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loan-products');
            return;
        }
        $repo = new LoanProductRepository();
        $p = $repo->find($id);
        if ($p === null) {
            ErrorPage::respond(404, 'Product not found', 'This loan product does not exist.');
            return;
        }
        $this->render('loan_products/show', ['product' => $p]);
    }

    public function create(): void
    {
        $this->render('loan_products/form', [
            'product' => null,
            'error' => Request::query('error'),
        ]);
    }

    public function store(): void
    {
        $this->requirePostedCsrf('/loan-products/create');
        if (!str_console_database_ready()) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Database not configured.'));
            return;
        }
        $name = trim(str_replace(["\0", "\r"], '', (string) Request::post('name', '')));
        $rate = (float) Request::post('rate_percent', 0);
        $pm = max(1, (int) Request::post('period_months', 1));
        $allowR = (string) Request::post('allow_reducing_balance', '') === '1';
        $allowF = (string) Request::post('allow_flat_monthly', '') === '1';
        $defBasis = LoanInterestBasis::normalize((string) Request::post('default_interest_basis', ''));
        if ($defBasis === null) {
            $defBasis = LoanInterestBasis::REDUCING_BALANCE;
        }
        if ($name === '' || $rate <= 0) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Name and a positive rate are required.'));
            return;
        }
        if (!$allowR && !$allowF) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Allow at least one interest type (reducing or flat).'));
            return;
        }
        if (!LoanInterestBasis::isBasisAllowed($defBasis, [
            'allow_reducing_balance' => $allowR ? 1 : 0,
            'allow_flat_monthly' => $allowF ? 1 : 0,
        ])) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Default interest type must be allowed for this product.'));
            return;
        }
        if (mb_strlen($name) > InputValidate::PERSON_NAME_MAX) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Name is too long.'));
            return;
        }
        try {
            $repo = new LoanProductRepository();
            if ($repo->nameExists($name, null)) {
                $this->redirect('/loan-products/create?error=' . rawurlencode('A loan product with this name already exists.'));
                return;
            }
            $repo->create($name, $rate, $pm, $defBasis, $allowR, $allowF);
            AuditLogger::log(ConsoleAuth::userId(), 'loan_product.create', 'loan_product', null, ['name' => $name]);
            $this->redirect('/loan-products');
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->redirect('/loan-products/create?error=' . rawurlencode('A loan product with this name already exists.'));
                return;
            }
            $this->redirect('/loan-products/create?error=' . rawurlencode('Could not save product.'));
        } catch (Throwable) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Could not save product.'));
        }
    }

    public function edit(int $id): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loan-products');
            return;
        }
        $repo = new LoanProductRepository();
        $p = $repo->find($id);
        if ($p === null) {
            ErrorPage::respond(404, 'Product not found', 'This loan product does not exist.');
            return;
        }
        $this->render('loan_products/form', [
            'product' => $p,
            'error' => Request::query('error'),
        ]);
    }

    public function update(int $id): void
    {
        $this->requirePostedCsrf('/loan-products/' . $id . '/edit');
        if (!str_console_database_ready()) {
            $this->redirect('/loan-products');
            return;
        }
        $name = trim(str_replace(["\0", "\r"], '', (string) Request::post('name', '')));
        $rate = (float) Request::post('rate_percent', 0);
        $pm = max(1, (int) Request::post('period_months', 1));
        $active = (string) Request::post('is_active', '') === '1';
        $allowR = (string) Request::post('allow_reducing_balance', '') === '1';
        $allowF = (string) Request::post('allow_flat_monthly', '') === '1';
        $defBasis = LoanInterestBasis::normalize((string) Request::post('default_interest_basis', ''));
        if ($defBasis === null) {
            $defBasis = LoanInterestBasis::REDUCING_BALANCE;
        }
        if ($name === '' || $rate <= 0) {
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Invalid fields.'));
            return;
        }
        if (!$allowR && !$allowF) {
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Allow at least one interest type.'));
            return;
        }
        if (!LoanInterestBasis::isBasisAllowed($defBasis, [
            'allow_reducing_balance' => $allowR ? 1 : 0,
            'allow_flat_monthly' => $allowF ? 1 : 0,
        ])) {
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Default interest type must be allowed.'));
            return;
        }
        if (mb_strlen($name) > InputValidate::PERSON_NAME_MAX) {
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Name is too long.'));
            return;
        }
        try {
            $repo = new LoanProductRepository();
            if ($repo->find($id) === null) {
                $this->redirect('/loan-products');
                return;
            }
            if ($repo->nameExists($name, $id)) {
                $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('A loan product with this name already exists.'));
                return;
            }
            $repo->update($id, $name, $rate, $pm, $defBasis, $allowR, $allowF, $active);
            AuditLogger::log(ConsoleAuth::userId(), 'loan_product.update', 'loan_product', $id, ['name' => $name]);
            $this->redirect('/loan-products');
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('A loan product with this name already exists.'));
                return;
            }
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Could not update.'));
        } catch (Throwable) {
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Could not update.'));
        }
    }

    public function retire(int $id): void
    {
        $this->requirePostedCsrf('/loan-products');
        if (!str_console_database_ready()) {
            $this->redirect('/loan-products');
            return;
        }
        try {
            $repo = new LoanProductRepository();
            if ($repo->find($id) !== null) {
                $repo->retire($id);
                AuditLogger::log(ConsoleAuth::userId(), 'loan_product.retire', 'loan_product', $id, []);
            }
        } catch (Throwable) {
            // ignore
        }
        $this->redirect('/loan-products');
    }
}
