<?php

declare(strict_types=1);

final class CustomersController extends BaseController
{
    public function index(): void
    {
        $page = (int) Request::query('page', 1);
        if (!str_console_database_ready()) {
            $this->render('customers/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 20],
                'dbError' => 'Database not configured.',
            ]);
            return;
        }

        try {
            $repo = new CustomerRepository();
            $data = $repo->paginateForConsoleUser(ConsoleAuth::userId(), ConsoleAuth::grants(), $page);
            $this->render('customers/index', ['pagination' => $data, 'dbError' => null]);
        } catch (Throwable) {
            $this->render('customers/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 20],
                'dbError' => 'Could not load customers. Check the database connection and schema.',
            ]);
        }
    }

    public function create(): void
    {
        $this->render('customers/create', [
            'error' => Request::query('error'),
        ]);
    }

    public function store(): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/customers/create?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $name = trim((string) Request::post('full_name', ''));
        $phone = trim((string) Request::post('phone', ''));
        $address = trim((string) Request::post('address', ''));
        $nin = trim((string) Request::post('nin', ''));
        $bvn = trim((string) Request::post('bvn', ''));

        if ($name === '' || $phone === '') {
            $this->redirect('/customers/create?error=' . rawurlencode('Name and phone are required.'));
            return;
        }

        $addrVal = $address === '' ? null : $address;
        $ninVal = $nin === '' ? null : $nin;
        $bvnVal = $bvn === '' ? null : $bvn;

        try {
            $repo = new CustomerRepository();
            $assignee = ConsoleAuth::userId();
            $id = $repo->create($name, $phone, $addrVal, $ninVal, $bvnVal, $assignee);

            AuditLogger::log(ConsoleAuth::userId(), 'customer.create', 'customer', $id, [
                'full_name' => $name,
                'phone' => $phone,
            ]);

            $this->redirect('/customers?page=1');
        } catch (Throwable) {
            $this->redirect('/customers/create?error=' . rawurlencode('Could not save customer. Try again.'));
        }
    }
}
