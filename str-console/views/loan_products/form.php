<?php
declare(strict_types=1);
/** @var array<string, mixed>|null $product */
/** @var mixed $error */
$basePath = Request::basePath();
$isEdit = is_array($product);
$err = is_string($error) ? $error : '';
$id = $isEdit ? (int) ($product['id'] ?? 0) : 0;
$name = $isEdit ? (string) ($product['name'] ?? '') : '';
$rate = $isEdit ? (string) ($product['rate_percent'] ?? '') : '';
$pm = $isEdit ? (int) ($product['period_months'] ?? 1) : 1;
$active = !$isEdit || (int) ($product['is_active'] ?? 1);
$defBasis = $isEdit ? (string) ($product['default_interest_basis'] ?? '') : LoanInterestBasis::REDUCING_BALANCE;
if (!in_array($defBasis, LoanInterestBasis::all(), true)) {
    $defBasis = LoanInterestBasis::REDUCING_BALANCE;
}
$allowR = !$isEdit || (int) ($product['allow_reducing_balance'] ?? 1) === 1;
$allowF = !$isEdit || (int) ($product['allow_flat_monthly'] ?? 1) === 1;
?>
<div class="container" style="padding:0; max-width:520px;">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;"><?= $isEdit ? 'Edit product' : 'New product' ?></h1>
  <p style="color: var(--muted); margin: 0 0 22px;"><a href="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>" style="color:var(--muted); font-weight:650;">← Products</a></p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($isEdit ? $basePath . '/loan-products/' . $id . '/update' : $basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap:14px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Name
        <input name="name" required maxlength="<?= (int) InputValidate::PERSON_NAME_MAX ?>" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Suggested monthly rate (%)
        <input name="rate_percent" type="number" step="0.0001" min="0.0001" required value="<?= htmlspecialchars($rate, ENT_QUOTES, 'UTF-8') ?>"
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <p style="margin:-6px 0 0; font-size:12px; color:var(--muted2);">Staff negotiate the actual rate on each loan. This value pre-fills new loans. Charges apply at most once per <strong>30-day period</strong> from disbursement when a period advances.</p>
      <fieldset style="border:1px solid var(--line2); border-radius:14px; padding:14px 16px; margin:0;">
        <legend style="font-size:13px; font-weight:650; color:var(--muted); padding:0 6px;">Interest types offered</legend>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px; margin-bottom:10px;">
          <input type="checkbox" name="allow_reducing_balance" value="1" <?= $allowR ? 'checked' : '' ?> />
          Reducing balance (rate × current balance each charge)
        </label>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px; margin-bottom:12px;">
          <input type="checkbox" name="allow_flat_monthly" value="1" <?= $allowF ? 'checked' : '' ?> />
          Flat monthly (rate × original principal each charge)
        </label>
        <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
          Default when booking a loan
          <select name="default_interest_basis" style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;">
            <option value="<?= htmlspecialchars(LoanInterestBasis::REDUCING_BALANCE, ENT_QUOTES, 'UTF-8') ?>" <?= $defBasis === LoanInterestBasis::REDUCING_BALANCE ? 'selected' : '' ?>><?= htmlspecialchars(LoanInterestBasis::label(LoanInterestBasis::REDUCING_BALANCE), ENT_QUOTES, 'UTF-8') ?></option>
            <option value="<?= htmlspecialchars(LoanInterestBasis::FLAT_MONTHLY, ENT_QUOTES, 'UTF-8') ?>" <?= $defBasis === LoanInterestBasis::FLAT_MONTHLY ? 'selected' : '' ?>><?= htmlspecialchars(LoanInterestBasis::label(LoanInterestBasis::FLAT_MONTHLY), ENT_QUOTES, 'UTF-8') ?></option>
          </select>
        </label>
      </fieldset>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Period (months, informational)
        <input name="period_months" type="number" min="1" required value="<?= (int) $pm ?>"
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <?php if ($isEdit): ?>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px; font-weight:650;">
          <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?> />
          Active (available for new loans)
        </label>
      <?php endif; ?>
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button type="submit" class="btn primary"><?= $isEdit ? 'Save' : 'Create' ?></button>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
