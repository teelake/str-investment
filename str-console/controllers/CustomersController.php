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
        $this->requirePostedCsrf('/customers/create');
        if (!str_console_database_ready()) {
            $this->redirect('/customers/create?error=' . rawurlencode('Database not configured.'));
            return;
        }

        $name = trim(str_replace(["\0", "\r"], '', (string) Request::post('full_name', '')));
        $phone = trim(str_replace(["\0", "\r"], '', (string) Request::post('phone', '')));
        $address = trim(str_replace(["\0", "\r"], '', (string) Request::post('address', '')));
        $ninRaw = (string) Request::post('nin', '');
        $bvnRaw = (string) Request::post('bvn', '');
        $ninNorm = InputValidate::optionalNinBvn($ninRaw);
        if ($ninNorm === false) {
            $this->redirect('/customers/create?error=' . rawurlencode('NIN must be exactly 11 digits (Nigeria NIMC), or leave blank.'));
            return;
        }
        $bvnNorm = InputValidate::optionalNinBvn($bvnRaw);
        if ($bvnNorm === false) {
            $this->redirect('/customers/create?error=' . rawurlencode('BVN must be exactly 11 digits (Nigeria CBN), or leave blank.'));
            return;
        }

        if ($name === '' || $phone === '') {
            $this->redirect('/customers/create?error=' . rawurlencode('Name and phone are required.'));
            return;
        }
        if (mb_strlen($name) > InputValidate::PERSON_NAME_MAX) {
            $this->redirect('/customers/create?error=' . rawurlencode('Name is too long.'));
            return;
        }
        if (strlen($phone) > 32) {
            $this->redirect('/customers/create?error=' . rawurlencode('Phone is too long.'));
            return;
        }

        $addrVal = $address === '' ? null : $address;
        $ninVal = $ninNorm;
        $bvnVal = $bvnNorm;

        $repo = new CustomerRepository();
        $dupMsg = $repo->validateOnboardingUniqueness($phone, $ninVal, $bvnVal, null);
        if ($dupMsg !== null) {
            $this->redirect('/customers/create?error=' . rawurlencode($dupMsg));
            return;
        }

        try {
            $assignee = ConsoleAuth::userId();
            $id = $repo->create($name, $phone, $addrVal, $ninVal, $bvnVal, $assignee);

            AuditLogger::log(ConsoleAuth::userId(), 'customer.create', 'customer', $id, [
                'full_name' => $name,
                'phone' => $phone,
            ]);

            $this->redirect('/customers/' . $id);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->redirect('/customers/create?error=' . rawurlencode('Another customer already uses this phone, NIN, or BVN.'));
                return;
            }
            $this->redirect('/customers/create?error=' . rawurlencode('Could not save customer. Try again.'));
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
                'documentTypes' => str_console_customer_document_types(),
                'showSensitiveIds' => str_console_authorize($grants, ['customers.view_sensitive_ids']),
                'canUpload' => str_console_authorize($grants, ['documents.upload']),
                'canDeleteDocs' => str_console_authorize($grants, ['documents.delete']),
                'canEdit' => str_console_authorize($grants, ['customers.edit']),
                'docError' => Request::query('doc_error'),
                'docOk' => Request::query('doc_ok'),
                'editOk' => Request::query('edit_ok'),
                'editError' => Request::query('edit_error'),
            ]);
        } catch (Throwable) {
            $this->redirect('/customers');
        }
    }

    public function edit(int $customerId): void
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

            $grants = ConsoleAuth::grants();
            $assignUsers = [];
            if (str_console_authorize($grants, ['customers.assign'])) {
                try {
                    $assignUsers = (new UserRepository())->listActiveForAssign();
                } catch (Throwable) {
                    $assignUsers = [];
                }
            }

            $this->render('customers/edit', [
                'customer' => $customer,
                'assignUsers' => $assignUsers,
                'canAssign' => str_console_authorize($grants, ['customers.assign']),
                'error' => Request::query('error'),
            ]);
        } catch (Throwable) {
            $this->redirect('/customers');
        }
    }

    public function update(int $customerId): void
    {
        $this->requirePostedCsrf('/customers/' . $customerId . '/edit');
        if (!str_console_database_ready()) {
            $this->redirect('/customers/' . $customerId . '?edit_error=' . rawurlencode('Database not configured.'));
            return;
        }

        $repo = new CustomerRepository();
        $customer = $repo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants());
        if ($customer === null) {
            $this->redirect('/customers');
            return;
        }

        $name = trim(str_replace(["\0", "\r"], '', (string) Request::post('full_name', '')));
        $phone = trim(str_replace(["\0", "\r"], '', (string) Request::post('phone', '')));
        $address = trim(str_replace(["\0", "\r"], '', (string) Request::post('address', '')));
        $ninNorm = InputValidate::optionalNinBvn((string) Request::post('nin', ''));
        if ($ninNorm === false) {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('NIN must be exactly 11 digits, or leave blank.'));
            return;
        }
        $bvnNorm = InputValidate::optionalNinBvn((string) Request::post('bvn', ''));
        if ($bvnNorm === false) {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('BVN must be exactly 11 digits, or leave blank.'));
            return;
        }

        if ($name === '' || $phone === '') {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Name and phone are required.'));
            return;
        }
        if (mb_strlen($name) > InputValidate::PERSON_NAME_MAX) {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Name is too long.'));
            return;
        }
        if (strlen($phone) > 32) {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Phone is too long.'));
            return;
        }

        $addrVal = $address === '' ? null : $address;
        $ninVal = $ninNorm;
        $bvnVal = $bvnNorm;

        $dupMsg = $repo->validateOnboardingUniqueness($phone, $ninVal, $bvnVal, $customerId);
        if ($dupMsg !== null) {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode($dupMsg));
            return;
        }

        $grants = ConsoleAuth::grants();
        $setAssignee = str_console_authorize($grants, ['customers.assign']);
        $assignedUserId = null;
        if ($setAssignee) {
            $raw = trim((string) Request::post('assigned_user_id', ''));
            if ($raw === '') {
                $assignedUserId = null;
            } elseif (ctype_digit($raw)) {
                $uid = (int) $raw;
                $userRepo = new UserRepository();
                if (!$userRepo->existsActiveUser($uid)) {
                    $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Choose a valid console user for assignment.'));
                    return;
                }
                $assignedUserId = $uid;
            } else {
                $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Invalid assignment value.'));
                return;
            }
        }

        try {
            $repo->update($customerId, $name, $phone, $addrVal, $ninVal, $bvnVal, $setAssignee, $assignedUserId);
            AuditLogger::log(ConsoleAuth::userId(), 'customer.update', 'customer', $customerId, [
                'full_name' => $name,
                'phone' => $phone,
                'assigned_changed' => $setAssignee,
            ]);
            $this->redirect('/customers/' . $customerId . '?edit_ok=1');
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Another customer already uses this phone, NIN, or BVN.'));
                return;
            }
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Could not save changes. Try again.'));
        } catch (Throwable) {
            $this->redirect('/customers/' . $customerId . '/edit?error=' . rawurlencode('Could not save changes. Try again.'));
        }
    }

    public function documentStore(int $customerId): void
    {
        $this->requirePostedCsrf('/customers/' . $customerId);
        if (!str_console_database_ready()) {
            $this->redirect('/customers');
            return;
        }

        $repo = new CustomerRepository();
        if ($repo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants()) === null) {
            $this->redirect('/customers');
            return;
        }

        $typeKey = trim((string) Request::post('document_type', ''));
        $allowedTypes = str_console_customer_document_types();
        if (!isset($allowedTypes[$typeKey])) {
            $this->redirect('/customers/' . $customerId . '?doc_error=' . rawurlencode('Select a document type.'));
            return;
        }

        $files = self::normalizeUploadedFiles('documents');
        $toProcess = [];
        foreach ($files as $file) {
            if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $toProcess[] = $file;
        }
        if ($toProcess === []) {
            $this->redirect('/customers/' . $customerId . '?doc_error=' . rawurlencode('Choose one or more files to upload.'));
            return;
        }

        $docRepo = new CustomerDocumentRepository();
        $uploaded = 0;
        $lastErr = 'Upload failed. Try again.';
        foreach ($toProcess as $file) {
            if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $lastErr = 'One or more files could not be uploaded.';
                continue;
            }
            try {
                $stored = CustomerDocumentStorage::store($customerId, $file);
                $newId = $docRepo->create(
                    $customerId,
                    ConsoleAuth::userId(),
                    $typeKey,
                    $stored['original_name'],
                    $stored['relative_path'],
                    $stored['mime'],
                    $stored['size']
                );
                ++$uploaded;
                AuditLogger::log(ConsoleAuth::userId(), 'customer_document.upload', 'customer_document', $newId, [
                    'customer_id' => $customerId,
                    'document_type' => $typeKey,
                    'name' => $stored['original_name'],
                ]);
            } catch (InvalidArgumentException $e) {
                $lastErr = $e->getMessage();
            } catch (Throwable) {
                $lastErr = 'Upload failed. Try again.';
            }
        }

        if ($uploaded === 0) {
            $this->redirect('/customers/' . $customerId . '?doc_error=' . rawurlencode($lastErr));
            return;
        }
        $this->redirect('/customers/' . $customerId . '?doc_ok=' . (string) $uploaded);
    }

    /**
     * @return list<array{name: string, type: string, tmp_name: string, error: int, size: int}>
     */
    private static function normalizeUploadedFiles(string $field): array
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return [];
        }
        $f = $_FILES[$field];
        if (!isset($f['name'])) {
            return [];
        }
        if (!is_array($f['name'])) {
            return [[
                'name' => (string) ($f['name'] ?? ''),
                'type' => (string) ($f['type'] ?? ''),
                'tmp_name' => (string) ($f['tmp_name'] ?? ''),
                'error' => (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($f['size'] ?? 0),
            ]];
        }
        $out = [];
        foreach ($f['name'] as $i => $name) {
            $out[] = [
                'name' => (string) $name,
                'type' => (string) ($f['type'][$i] ?? ''),
                'tmp_name' => (string) ($f['tmp_name'][$i] ?? ''),
                'error' => (int) ($f['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($f['size'][$i] ?? 0),
            ];
        }
        return $out;
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
        $this->requirePostedCsrf('/customers/' . $customerId);
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
