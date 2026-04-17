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
  <p style="color: var(--muted); margin: 0 0 22px;">Control how staff without “view all” permissions see customers and loans. Admins and managers with <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">data.view_all_*</code> grants are not limited by these toggles.</p>

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
          <strong>Scope customers by assignment</strong><br />
          <span style="color: var(--muted); font-size: 13px;">When checked, officers only see customers where they are the assigned handler (unless they have view-all). Uncheck to let all staff with customer access see every customer.</span>
        </span>
      </label>
      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="scope_loans" value="1" <?= $scopeLoans ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Scope loans by assignment</strong><br />
          <span style="color: var(--muted); font-size: 13px;">When checked, officers only see loans assigned to them or tied to customers assigned to them. Uncheck for institution-wide loan lists (for roles without view-all).</span>
        </span>
      </label>
      <label style="display:flex; gap: 12px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="ledger_auto_accrue" value="1" <?= $ledgerAutoAccrue ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span>
          <strong>Automatic monthly ledger accrual</strong><br />
          <span style="color: var(--muted); font-size: 13px;">When checked, staff can run <strong>Apply accrual</strong> on an active loan (POST only) and you can schedule <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">bin/accrue-active-loans.php</code> for batch runs. Accrual adds ledger lines with no payment (same interest-on-closing formula as payments), one calendar month after the last line, until <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">period_months</code> from disbursement or the chosen date. Uncheck to disable accrual (payments still work).</span>
        </span>
      </label>
      <button type="submit" class="btn primary" style="justify-self: start;">Save policies</button>
    </form>
  </div>
</div>
