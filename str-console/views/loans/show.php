<?php
declare(strict_types=1);
/** @var array<string, mixed> $loan */
/** @var list<array<string, mixed>> $ledger */
/** @var float $outstanding */
/** @var bool $canSubmit */
/** @var bool $canApprove */
/** @var bool $canReject */
/** @var bool $canDisburse */
/** @var bool $canPay */
/** @var bool $canAccrue */
/** @var bool $canClose */
/** @var bool $canVoidPayment */
/** @var bool $canAdjustPayment */
/** @var float $lastLineAmountDue */
/** @var float|null $lastLinePayment */
/** @var float|null $paymentAmountDueMax */
/** @var bool $canEditLoan */
/** @var bool $canReminderInstallment */
/** @var array<string, mixed>|null $reminderProjection */
/** @var mixed $flash */
/** @var mixed $flashError */
$basePath = Request::basePath();
$id = (int) ($loan['id'] ?? 0);
$st = (string) ($loan['status'] ?? '');
$cid = (int) ($loan['customer_id'] ?? 0);
$cname = (string) ($loan['customer_name'] ?? '');
$fmt = static fn (float $n): string => '₦' . number_format($n, 2);
$statusLabel = match ($st) {
    'draft' => 'Draft',
    'pending_approval' => 'Pending approval',
    'approved' => 'Approved',
    'active' => 'Active',
    'closed' => 'Closed',
    'rejected' => 'Rejected',
    default => $st,
};
$flashOk = is_string($flash) ? $flash : '';
$err = is_string($flashError) ? $flashError : '';
$canAccrue = $canAccrue ?? false;
$canClose = $canClose ?? false;
$canVoidPayment = $canVoidPayment ?? false;
$canAdjustPayment = $canAdjustPayment ?? false;
$lastLineAmountDue = $lastLineAmountDue ?? 0.0;
$lastLinePayment = $lastLinePayment ?? null;
$paymentAmountDueMax = $paymentAmountDueMax ?? null;
$canEditLoan = $canEditLoan ?? false;
$canReminderInstallment = $canReminderInstallment ?? false;
$reminderProjection = isset($reminderProjection) && is_array($reminderProjection) ? $reminderProjection : null;
$today = InputValidate::todayYmd();
$loanCreatedDay = substr((string) ($loan['created_at'] ?? ''), 0, 10);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $loanCreatedDay)) {
    $loanCreatedDay = InputValidate::LOAN_EVENT_DATE_MIN;
}
$disburseDateMin = max(InputValidate::LOAN_EVENT_DATE_MIN, $loanCreatedDay);
$disburseDefault = $today;
if ($disburseDefault < $disburseDateMin) {
    $disburseDefault = $disburseDateMin;
}

$disbursedDay = substr((string) ($loan['disbursed_at'] ?? ''), 0, 10);
$paymentDateMin = preg_match('/^\d{4}-\d{2}-\d{2}$/', $disbursedDay)
    ? max(InputValidate::LOAN_EVENT_DATE_MIN, $disbursedDay)
    : InputValidate::LOAN_EVENT_DATE_MIN;
$paymentDefault = $today;
if ($paymentDefault < $paymentDateMin) {
    $paymentDefault = $paymentDateMin;
}
?>
<div class="container" style="padding:0">
  <div style="margin-bottom: 20px;">
    <a href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Loans</a>
    <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:16px; margin-top:12px;">
      <div>
        <h1 style="font-size: var(--h2); margin: 0 0 6px;">Loan #<?= $id ?></h1>
        <p style="color: var(--muted); margin: 0; font-size: 14px;">
          <a href="<?= htmlspecialchars($basePath . '/customers/' . $cid, ENT_QUOTES, 'UTF-8') ?>" style="color: var(--green2); font-weight: 650;"><?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?></a>
        </p>
      </div>
      <span style="display:inline-flex; align-items:center; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700; background:var(--green-soft); color:var(--green2);"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <?php if ($flashOk !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px;">
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Principal</div>
      <div style="font-size: 22px; font-weight: 800; margin-top: 8px;"><?= $fmt((float) ($loan['principal_amount'] ?? 0)) ?></div>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Rate (booked, monthly)</div>
      <div style="font-size: 22px; font-weight: 800; margin-top: 8px;"><?= htmlspecialchars((string) ($loan['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?>%</div>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Interest type</div>
      <?php $basisShow = LoanInterestBasis::normalize((string) ($loan['interest_basis'] ?? '')) ?? LoanInterestBasis::REDUCING_BALANCE; ?>
      <div style="font-size: 15px; font-weight: 700; margin-top: 8px; line-height: 1.35;"><?= htmlspecialchars(LoanInterestBasis::label($basisShow), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Outstanding</div>
      <div style="font-size: 22px; font-weight: 800; margin-top: 8px;"><?= $fmt($outstanding) ?></div>
    </div>
  </div>

  <div style="display:flex; flex-wrap:wrap; gap: 10px; margin-bottom: 28px;">
    <?php if ($canEditLoan): ?>
      <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/loans/' . $id . '/edit', ENT_QUOTES, 'UTF-8') ?>">Edit loan</a>
    <?php endif; ?>
    <?php if ($canSubmit): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/submit', ENT_QUOTES, 'UTF-8') ?>">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <button type="submit" class="btn primary">Submit for approval</button>
      </form>
    <?php endif; ?>
    <?php if ($canApprove): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/approve', ENT_QUOTES, 'UTF-8') ?>">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <button type="submit" class="btn primary">Approve</button>
      </form>
    <?php endif; ?>
    <?php if ($canReject): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/reject', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Rejection reason
          <input name="reason" required maxlength="<?= (int) InputValidate::REJECTION_REASON_MAX ?>" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); min-width:220px;" />
        </label>
        <button type="submit" class="btn ghost" style="color:#7f1d1d;">Reject</button>
      </form>
    <?php endif; ?>
    <?php if ($canDisburse): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/disburse', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Disbursement date
          <input type="date" name="disbursed_on" value="<?= htmlspecialchars($disburseDefault, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($disburseDateMin, ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
        </label>
        <button type="submit" class="btn primary">Disburse &amp; open ledger</button>
      </form>
      <p style="margin:8px 0 0; font-size:12px; color:var(--muted2); max-width:560px;">Line 1 records <strong>principal only</strong> (no interest yet). Interest runs in <strong>30-day steps</strong> from this disbursement date—first charge in period 2 (day 30 onward), via payment or accrual.</p>
    <?php endif; ?>
    <?php if ($canClose): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/close', ENT_QUOTES, 'UTF-8') ?>" style="display:inline;" onsubmit="return confirm('Close this loan? It must have zero outstanding balance.');">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <button type="submit" class="btn ghost" style="border-color:rgba(15,106,74,.35); color:var(--green2);">Close loan</button>
      </form>
    <?php endif; ?>
    <?php if ($canAccrue && $st === 'active'): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/accrue', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Accrue through
          <input type="date" name="as_of" value="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($paymentDateMin, ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
        </label>
        <button type="submit" class="btn ghost">Apply accrual (30-day)</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if ($canPay && $st === 'active'): ?>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2); margin-bottom: 28px;">
      <h2 style="font-size: 15px; margin: 0 0 14px; font-weight: 800;">Record payment</h2>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/payment', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Amount (₦)
          <input name="amount" type="number" step="0.01" min="0.01"<?= $paymentAmountDueMax !== null ? ' max="' . htmlspecialchars((string) $paymentAmountDueMax, ENT_QUOTES, 'UTF-8') . '"' : '' ?> required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); width:140px;" />
        </label>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Paid on
          <input type="date" name="paid_on" value="<?= htmlspecialchars($paymentDefault, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($paymentDateMin, ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
        </label>
        <button type="submit" class="btn primary">Apply payment</button>
      </form>
      <?php if ($paymentAmountDueMax !== null): ?>
        <p style="margin: 10px 0 0; font-size: 12px; color: var(--muted2);">Maximum for this payment: within the <strong>same 30-day period</strong> from disbursement as the last line = balance only; <strong>new period</strong> = balance + one charge at the booked monthly rate. Matches the default payment date; the server rechecks if you change it. Larger amounts are rejected.</p>
      <?php endif; ?>
      <p style="margin: 12px 0 0; font-size: 12px; color: var(--muted2);">Interest runs in <strong>30-day steps</strong> from the disbursement date. A payment in the <strong>same</strong> step as the last line pays down the balance only; the <strong>next</strong> step adds one charge at the booked monthly rate, then your payment. Use <strong>Apply accrual</strong> (or cron) to insert unpaid period lines when that policy is on.</p>
    </div>
  <?php endif; ?>

  <?php if ($canReminderInstallment && $st === 'active'): ?>
    <?php
      $rInst = $loan['reminder_installment_amount'] ?? null;
      $rInstVal = $rInst !== null && $rInst !== '' ? (float) $rInst : null;
      $rp = $reminderProjection;
    ?>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2); margin-bottom: 28px;">
      <h2 style="font-size: 15px; margin: 0 0 8px; font-weight: 800;">Borrower payment reminders</h2>
      <p style="margin: 0 0 14px; font-size: 13px; color: var(--muted2); line-height: 1.45;">
        If your team turns on automatic emails under <strong>Settings → Payment reminders</strong>, the customer receives plain-language messages before each due date and on the due day (when their profile has an email).
        Optionally set the <strong>amount to mention</strong> for this loan (for example a fixed installment that is smaller than the full ledger step).
      </p>
      <?php if ($rp !== null): ?>
        <p style="margin: 0 0 14px; font-size: 13px; color: var(--muted);">
          Next scheduled payment date (from ledger / term): <strong><?= htmlspecialchars((string) ($rp['next_due_ymd'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>.
          Ledger amount for that step: <strong><?= $fmt((float) ($rp['ledger_amount_due'] ?? 0)) ?></strong>.
        </p>
      <?php endif; ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/reminder-installment', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
        <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Amount to show in reminder emails (optional, ₦)
          <input name="reminder_installment_amount" type="text" inputmode="decimal" placeholder="e.g. 10000" autocomplete="off"
            value="<?= $rInstVal !== null ? htmlspecialchars((string) $rInstVal, ENT_QUOTES, 'UTF-8') : '' ?>"
            style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); width:160px;" />
        </label>
        <button type="submit" class="btn ghost">Save reminder amount</button>
      </form>
      <p style="margin: 12px 0 0; font-size: 12px; color: var(--muted2);">Leave blank to use only the calculated ledger amount. Email wording is edited under Settings (not here).</p>
    </div>
  <?php endif; ?>

  <?php if ($st === 'active' && ($canVoidPayment || $canAdjustPayment)): ?>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2); margin-bottom: 28px;">
      <h2 style="font-size: 15px; margin: 0 0 14px; font-weight: 800;">Ledger corrections</h2>
      <p style="margin: 0 0 14px; font-size: 13px; color: var(--muted2);">Applies only to the <strong>last</strong> ledger line if it records a payment. Void removes that line; adjust changes the paid amount (closing balance is recalculated).</p>
      <div style="display:flex; flex-wrap:wrap; gap: 20px; align-items:flex-end;">
        <?php if ($canVoidPayment): ?>
          <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/payment-void', ENT_QUOTES, 'UTF-8') ?>" style="display:inline;" onsubmit="return confirm('Remove the last payment line from the ledger?');">
            <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
            <button type="submit" class="btn ghost" style="color:#7f1d1d;">Void last payment line</button>
          </form>
        <?php endif; ?>
        <?php if ($canAdjustPayment): ?>
          <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/payment-adjust', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
            <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
            <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
              Adjusted payment (₦)
              <input name="adjusted_amount" type="number" step="0.01" min="0" max="<?= htmlspecialchars((string) $lastLineAmountDue, ENT_QUOTES, 'UTF-8') ?>" required
                value="<?= htmlspecialchars((string) ($lastLinePayment ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); width:140px;" />
            </label>
            <button type="submit" class="btn ghost">Save adjustment</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2);">
    <h2 style="font-size: 15px; margin: 0 0 16px; font-weight: 800;">Ledger</h2>
    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse; font-size:13px; min-width:720px;">
        <thead>
          <tr style="text-align:right; border-bottom:1px solid var(--line2); color:var(--muted); font-size:11px; text-transform:uppercase;">
            <th style="padding:10px 8px; text-align:left;">#</th>
            <th style="padding:10px 8px; text-align:left;">Date</th>
            <th style="padding:10px 8px;">Opening</th>
            <th style="padding:10px 8px;">Rate %</th>
            <th style="padding:10px 8px;">Interest</th>
            <th style="padding:10px 8px;">Total due</th>
            <th style="padding:10px 8px;">Paid</th>
            <th style="padding:10px 8px;">Closing</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($ledger) === 0): ?>
            <tr><td colspan="8" style="padding:24px; color:var(--muted); text-align:left;">No lines yet. Disburse this loan to create the first line.</td></tr>
          <?php else: ?>
            <?php foreach ($ledger as $row): ?>
              <tr style="border-bottom:1px solid var(--line2);">
                <td style="padding:10px 8px; text-align:left; font-weight:650;"><?= (int) ($row['line_no'] ?? 0) ?></td>
                <td style="padding:10px 8px; text-align:left;"><?= htmlspecialchars((string) ($row['period_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= $fmt((float) ($row['opening_balance'] ?? 0)) ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= htmlspecialchars((string) ($row['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= $fmt((float) ($row['interest_amount'] ?? 0)) ?></td>
                <td style="padding:10px 8px; text-align:right; font-weight:650;"><?= $fmt((float) ($row['amount_due'] ?? 0)) ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= isset($row['payment_amount']) && $row['payment_amount'] !== null ? $fmt((float) $row['payment_amount']) : '—' ?></td>
                <td style="padding:10px 8px; text-align:right; font-weight:700;"><?= $fmt((float) ($row['closing_balance'] ?? 0)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($st === 'rejected' && ($loan['rejected_reason'] ?? '') !== ''): ?>
    <div style="margin-top:20px; padding:14px 16px; border-radius:14px; background:rgba(180,40,40,.06); border:1px solid rgba(180,40,40,.15); font-size:14px;">
      <strong>Reason:</strong> <?= htmlspecialchars((string) $loan['rejected_reason'], ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
</div>
