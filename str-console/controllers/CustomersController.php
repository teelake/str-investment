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

            $this->redirect('/customers/' . $id);
        } catch (Throwable) {
            $this->redirect('/customers/create?error=' . rawurlencode('Could not save customer. Try again.'));
        }
    }

    public function show(int $customerId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/customers');
            return;
        }

        try {
            $repo = new CustomerRepository();
            $customer = $repo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants());
            if ($customer === null) {
                http_response_code(404);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Not found</title></head><body><p>Customer not found.</p></body></html>';
                return;
            }

            $documents = [];
            try {
                $docRepo = new CustomerDocumentRepository();
                $documents = $docRepo->listByCustomer($customerId);
            } catch (Throwable) {
                $documents = [];
            }

            $grants = ConsoleAuth::grants();
            $this->render('customers/show', [
                'customer' => $customer,
                'documents' => $documents,
                'showSensitiveIds' => str_console_authorize($grants, ['customers.view_sensitive_ids']),
                'canUpload' => str_console_authorize($grants, ['documents.upload']),
                'canDeleteDocs' => str_console_authorize($grants, ['documents.delete']),
                'docError' => Request::query('doc_error'),
                'docOk' => Request::query('doc_ok'),
            ]);
        } catch (Throwable) {
            $this->redirect('/customers');
        }
    }

    public function documentStore(int $customerId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/customers');
            return;
        }

        $repo = new CustomerRepository();
        if ($repo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants()) === null) {
            $this->redirect('/customers');
            return;
        }

        $file = $_FILES['document'] ?? null;
        if (!is_array($file)) {
            $this->redirect('/customers/' . $customerId . '?doc_error=' . rawurlencode('Choose a file to upload.'));
            return;
        }

        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int} $file */
        try {
            $stored = CustomerDocumentStorage::store($customerId, $file);
            $docRepo = new CustomerDocumentRepository();
            $newId = $docRepo->create(
                $customerId,
                ConsoleAuth::userId(),
                $stored['original_name'],
                $stored['relative_path'],
                $stored['mime'],
                $stored['size']
            );
            AuditLogger::log(ConsoleAuth::userId(), 'customer_document.upload', 'customer_document', $newId, [
                'customer_id' => $customerId,
                'name' => $stored['original_name'],
            ]);
            $this->redirect('/customers/' . $customerId . '?doc_ok=1');
        } catch (InvalidArgumentException $e) {
            $this->redirect('/customers/' . $customerId . '?doc_error=' . rawurlencode($e->getMessage()));
        } catch (Throwable) {
            $this->redirect('/customers/' . $customerId . '?doc_error=' . rawurlencode('Upload failed. Try again.'));
        }
    }

    public function documentDownload(int $customerId, int $documentId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/customers');
            return;
        }

        $repo = new CustomerRepository();
        if ($repo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants()) === null) {
            http_response_code(404);
            return;
        }

        $docRepo = new CustomerDocumentRepository();
        $row = $docRepo->findForCustomer($documentId, $customerId);
        if ($row === null) {
            http_response_code(404);
            return;
        }

        $abs = CustomerDocumentStorage::absolutePathFromRelative((string) $row['storage_path']);
        if (!is_file($abs)) {
            http_response_code(404);
            return;
        }

        $name = (string) $row['original_name'];
        $mime = (string) ($row['mime_type'] ?: 'application/octet-stream');

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
        header('Content-Length: ' . (string) filesize($abs));
        readfile($abs);
        exit;
    }

    public function documentDestroy(int $customerId, int $documentId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/customers');
            return;
        }

        $repo = new CustomerRepository();
        if ($repo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants()) === null) {
            $this->redirect('/customers');
            return;
        }

        $docRepo = new CustomerDocumentRepository();
        if ($docRepo->delete($documentId, $customerId)) {
            AuditLogger::log(ConsoleAuth::userId(), 'customer_document.delete', 'customer_document', $documentId, [
                'customer_id' => $customerId,
            ]);
        }

        $this->redirect('/customers/' . $customerId);
    }
}
