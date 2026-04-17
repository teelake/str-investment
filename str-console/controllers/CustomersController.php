<?php

declare(strict_types=1);

final class CustomersController extends BaseController
{
    public function index(): void
    {
        $page = (int) Request::query('page', 1);
        $repo = new CustomerRepository();
        $data = $repo->paginateForConsoleUser(ConsoleAuth::userId(), ConsoleAuth::grants(), $page);

        $this->render('customers/index', [
            'pagination' => $data,
        ]);
    }

    public function create(): void
    {
        $this->render('customers/create', [
            'error' => Request::query('error'),
        ]);
    }

    public function store(): void
    {
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

        $repo = new CustomerRepository();
        $assignee = ConsoleAuth::userId();
        $id = $repo->create($name, $phone, $addrVal, $ninVal, $bvnVal, $assignee);

        AuditLogger::log(ConsoleAuth::userId(), 'customer.create', 'customer', $id, [
            'full_name' => $name,
            'phone' => $phone,
        ]);

        $this->redirect('/customers?page=1');
    }
}
