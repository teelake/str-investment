<?php

declare(strict_types=1);

final class LoanProductsController extends BaseController
{
    public function index(): void
    {
        if (!str_console_database_ready()) {
            $this->render('loan_products/index', ['products' => [], 'dbError' => 'Database not configured.']);
            return;
        }
        try {
            $repo = new LoanProductRepository();
            $this->render('loan_products/index', ['products' => $repo->listAll(), 'dbError' => null]);
        } catch (Throwable) {
            $this->render('loan_products/index', ['products' => [], 'dbError' => 'Could not load products.']);
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
        if ($name === '' || $rate <= 0) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Name and a positive rate are required.'));
            return;
        }
        if (mb_strlen($name) > InputValidate::PERSON_NAME_MAX) {
            $this->redirect('/loan-products/create?error=' . rawurlencode('Name is too long.'));
            return;
        }
        try {
            $repo = new LoanProductRepository();
            $repo->create($name, $rate, $pm);
            AuditLogger::log(ConsoleAuth::userId(), 'loan_product.create', 'loan_product', null, ['name' => $name]);
            $this->redirect('/loan-products');
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
        if ($name === '' || $rate <= 0) {
            $this->redirect('/loan-products/' . $id . '/edit?error=' . rawurlencode('Invalid fields.'));
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
            $repo->update($id, $name, $rate, $pm, $active);
            AuditLogger::log(ConsoleAuth::userId(), 'loan_product.update', 'loan_product', $id, ['name' => $name]);
            $this->redirect('/loan-products');
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
