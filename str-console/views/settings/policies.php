<?php
declare(strict_types=1);
/** @var bool $scopeCustomers */
/** @var bool $scopeLoans */
/** @var bool $ledgerAutoAccrue */
/** @var mixed $flash */
/** @var mixed $error */
$basePath = Request::basePath();
$ok = is_string($flash) ? $flash : '';
$err = is_string($error) ? $error : '';
?>
<div class="container" style="padding:0; max-width:640px;">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">Policies</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">Choose who sees which customers and loans, and whether the system helps update interest on active loans. Staff who are allowed to see <strong>everything</strong> in the organization are not limited by the first two options below.</p>

  <?php if ($ok !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/settings/policies', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 18px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="scope_customers" value="1" <?= $scopeCustomers ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Each person only sees their own customers</strong><br />
          <span style="color: var(--muted); font-size: 13px;"><strong>On:</strong> someone only sees customers they are responsible for. <strong>Off:</strong> anyone who can open the customer list sees all customers. (People with full access still see everyone.)</span>
        </span>
      </label>
      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="scope_loans" value="1" <?= $scopeLoans ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Each person only sees their own loans</strong><br />
          <span style="color: var(--muted); font-size: 13px;"><strong>On:</strong> someone sees loans that belong to their customers or that are specifically assigned to them. <strong>Off:</strong> anyone who can open the loan list sees all loans. (People with full access still see everything.)</span>
        </span>
      </label>
      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="ledger_auto_accrue" value="1" <?= $ledgerAutoAccrue ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Let the system add unpaid interest to active loans</strong><br />
          <span style="color: var(--muted); font-size: 13px;">When this is on, you can press <strong>Apply accrual</strong> on a live loan (or use your nightly/automatic run) so the <strong>amount owed</strong> includes interest that has built up month by month. When it’s off, the system won’t do that step for you. <strong>Recording money the customer paid</strong> works the same in both cases.</span>
        </span>
      </label>
      <button type="submit" class="btn primary" style="justify-self: start;">Save policies</button>
    </form>
  </div>
</div>
