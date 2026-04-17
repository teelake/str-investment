<?php

declare(strict_types=1);

final class LoansController extends BaseController
{
    public function index(): void
    {
        $page = (int) Request::query('page', 1);
        if (!str_console_database_ready()) {
            $this->render('loans/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 20],
                'dbError' => 'Database not configured.',
            ]);
            return;
        }
        try {
            $repo = new LoanRepository();
            $data = $repo->paginateForConsoleUser(ConsoleAuth::userId(), ConsoleAuth::grants(), $page);
            $this->render('loans/index', ['pagination' => $data, 'dbError' => null]);
        } catch (Throwable) {
            $this->render('loans/index', [
                'pagination' => ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 20],
                'dbError' => 'Could not load loans.',
            ]);
        }
    }

    public function create(): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loans');
            return;
        }
        $preCustomer = (int) Request::query('customer_id', 0);
        try {
            $custRepo = new CustomerRepository();
            $customers = $custRepo->listNamesForConsoleUser(ConsoleAuth::userId(), ConsoleAuth::grants());
            $prodRepo = new LoanProductRepository();
            $products = $prodRepo->listActive();
            $this->render('loans/create', [
                'customers' => $customers,
                'products' => $products,
                'preCustomerId' => $preCustomer,
                'error' => Request::query('error'),
            ]);
        } catch (Throwable) {
            $this->redirect('/loans?error=' . rawurlencode('Could not open form.'));
        }
    }

    public function store(): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loans');
            return;
        }
        $customerId = (int) Request::post('customer_id', 0);
        $productId = (int) Request::post('loan_product_id', 0);
        $principal = (float) Request::post('principal_amount', 0);
        if ($customerId <= 0 || $productId <= 0 || $principal <= 0) {
            $this->redirect('/loans/create?error=' . rawurlencode('Select customer and product, and enter principal.'));
            return;
        }

        $custRepo = new CustomerRepository();
        $customer = $custRepo->find($customerId, ConsoleAuth::userId(), ConsoleAuth::grants());
        if ($customer === null) {
            $this->redirect('/loans/create?error=' . rawurlencode('Customer not available.'));
            return;
        }

        $prodRepo = new LoanProductRepository();
        $product = $prodRepo->find($productId);
        if ($product === null || !(int) ($product['is_active'] ?? 0)) {
            $this->redirect('/loans/create?error=' . rawurlencode('Invalid product.'));
            return;
        }

        $rate = (float) $product['rate_percent'];
        $pm = (int) ($product['period_months'] ?? 1);
        $assignLoan = ConsoleAuth::userId();
        if ($assignLoan === null) {
            $assignLoan = isset($customer['assigned_user_id']) ? (int) $customer['assigned_user_id'] : null;
        }

        try {
            $loanRepo = new LoanRepository();
            $lid = $loanRepo->create(
                $customerId,
                $productId,
                $principal,
                $rate,
                $pm,
                $assignLoan,
                ConsoleAuth::userId()
            );
            AuditLogger::log(ConsoleAuth::userId(), 'loan.create', 'loan', $lid, [
                'customer_id' => $customerId,
                'principal' => $principal,
            ]);
            $this->redirect('/loans/' . $lid);
        } catch (Throwable) {
            $this->redirect('/loans/create?error=' . rawurlencode('Could not create loan.'));
        }
    }

    public function show(int $loanId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loans');
            return;
        }
        $loanRepo = new LoanRepository();
        $loan = $loanRepo->find($loanId);
        if ($loan === null || !LoanRepository::canAccessRow($loan, ConsoleAuth::userId(), ConsoleAuth::grants())) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $lines = [];
        try {
            $lines = (new LoanLedgerRepository())->listByLoan($loanId);
        } catch (Throwable) {
            $lines = [];
        }
        $outstanding = LoanLedgerService::outstandingForLoan($loanId);
        $grants = ConsoleAuth::grants();
        $active = ($loan['status'] ?? '') === 'active';

        $this->render('loans/show', [
            'loan' => $loan,
            'ledger' => $lines,
            'outstanding' => $outstanding,
            'canSubmit' => str_console_authorize($grants, ['loans.submit']) && ($loan['status'] ?? '') === 'draft',
            'canApprove' => str_console_authorize($grants, ['loans.approve']) && ($loan['status'] ?? '') === 'pending_approval',
            'canReject' => str_console_authorize($grants, ['loans.reject']) && ($loan['status'] ?? '') === 'pending_approval',
            'canDisburse' => str_console_authorize($grants, ['loans.disburse']) && ($loan['status'] ?? '') === 'approved',
            'canPay' => str_console_authorize($grants, ['payments.record']) && $active,
            'canAccrue' => str_console_authorize($grants, ['payments.record'])
                && $active
                && !empty($loan['disbursed_at'])
                && PolicyService::ledgerAutoAccrue(),
            'flash' => Request::query('flash'),
            'flashError' => Request::query('error'),
        ]);
    }

    public function submit(int $loanId): void
    {
        $this->transitionLoan($loanId, function (LoanRepository $r) use ($loanId): bool {
            return $r->markSubmitted($loanId);
        }, 'loan.submit', 'Submitted for approval.', 'Could not submit.');
    }

    public function approve(int $loanId): void
    {
        $uid = ConsoleAuth::userId();
        if ($uid === null) {
            $this->redirect('/loans/' . $loanId);
            return;
        }
        $this->transitionLoan($loanId, function (LoanRepository $r) use ($loanId, $uid): bool {
            return $r->markApproved($loanId, $uid);
        }, 'loan.approve', 'Loan approved.', 'Could not approve.');
    }

    public function reject(int $loanId): void
    {
        $reason = trim((string) Request::post('reason', ''));
        if ($reason === '') {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode('Enter a rejection reason.'));
            return;
        }
        $this->transitionLoan($loanId, function (LoanRepository $r) use ($loanId, $reason): bool {
            return $r->markRejected($loanId, $reason);
        }, 'loan.reject', 'Loan rejected.', 'Could not reject.');
    }

    public function disburse(int $loanId): void
    {
        $date = trim((string) Request::post('disbursed_on', ''));
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode('Use a valid disbursement date (YYYY-MM-DD).'));
            return;
        }
        $loanRepo = new LoanRepository();
        $loan = $loanRepo->find($loanId);
        if ($loan === null || !LoanRepository::canAccessRow($loan, ConsoleAuth::userId(), ConsoleAuth::grants())) {
            $this->redirect('/loans');
            return;
        }
        try {
            LoanLedgerService::completeDisbursement($loanId, $date);
            AuditLogger::log(ConsoleAuth::userId(), 'loan.disburse', 'loan', $loanId, ['date' => $date]);
            $this->redirect('/loans/' . $loanId . '?flash=' . rawurlencode('Disbursed. First ledger line created.'));
        } catch (Throwable $e) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode($e->getMessage()));
        }
    }

    public function accrue(int $loanId): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loans');
            return;
        }
        if (!PolicyService::ledgerAutoAccrue()) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode('Monthly accrual is turned off in Policies.'));
            return;
        }
        $loanRepo = new LoanRepository();
        $loan = $loanRepo->find($loanId);
        if ($loan === null || !LoanRepository::canAccessRow($loan, ConsoleAuth::userId(), ConsoleAuth::grants())) {
            $this->redirect('/loans');
            return;
        }
        if (($loan['status'] ?? '') !== 'active' || empty($loan['disbursed_at'])) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode('Loan must be active and disbursed.'));
            return;
        }

        $asOf = trim((string) Request::post('as_of', ''));
        if ($asOf === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOf)) {
            $asOf = (new DateTimeImmutable('now'))->format('Y-m-d');
        }

        try {
            $n = LoanLedgerService::runPeriodicAccrualThrough($loanId, $asOf);
            if ($n > 0) {
                AuditLogger::log(ConsoleAuth::userId(), 'loan.ledger.accrual', 'loan', $loanId, [
                    'lines_added' => $n,
                    'through' => $asOf,
                    'source' => 'manual_post',
                ]);
                $this->redirect('/loans/' . $loanId . '?flash=' . rawurlencode('Applied ' . $n . ' accrual line(s) through ' . $asOf . '.'));
                return;
            }
            $this->redirect('/loans/' . $loanId . '?flash=' . rawurlencode('No new accrual lines were due (already caught up through that date, or past term).'));
        } catch (Throwable $e) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode($e->getMessage()));
        }
    }

    public function payment(int $loanId): void
    {
        $amount = (float) Request::post('amount', 0);
        $paidOn = trim((string) Request::post('paid_on', ''));
        if ($amount <= 0 || $paidOn === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paidOn)) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode('Enter amount and payment date.'));
            return;
        }
        $loanRepo = new LoanRepository();
        $loan = $loanRepo->find($loanId);
        if ($loan === null || !LoanRepository::canAccessRow($loan, ConsoleAuth::userId(), ConsoleAuth::grants())) {
            $this->redirect('/loans');
            return;
        }
        try {
            LoanLedgerService::applyPayment($loanId, $amount, $paidOn);
            AuditLogger::log(ConsoleAuth::userId(), 'loan.payment', 'loan', $loanId, ['amount' => $amount, 'date' => $paidOn]);
            $this->redirect('/loans/' . $loanId . '?flash=' . rawurlencode('Payment recorded.'));
        } catch (Throwable $e) {
            $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * @param callable(LoanRepository): bool $fn
     */
    private function transitionLoan(int $loanId, callable $fn, string $auditAction, string $okMsg, string $failMsg): void
    {
        if (!str_console_database_ready()) {
            $this->redirect('/loans');
            return;
        }
        $loanRepo = new LoanRepository();
        $loan = $loanRepo->find($loanId);
        if ($loan === null || !LoanRepository::canAccessRow($loan, ConsoleAuth::userId(), ConsoleAuth::grants())) {
            $this->redirect('/loans');
            return;
        }
        try {
            if ($fn($loanRepo)) {
                AuditLogger::log(ConsoleAuth::userId(), $auditAction, 'loan', $loanId, []);
                $this->redirect('/loans/' . $loanId . '?flash=' . rawurlencode($okMsg));
                return;
            }
        } catch (Throwable) {
            // fallthrough
        }
        $this->redirect('/loans/' . $loanId . '?error=' . rawurlencode($failMsg));
    }
}
