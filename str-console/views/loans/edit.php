<?php
declare(strict_types=1);
/** @var array<string, mixed> $loan */
/** @var list<array<string, mixed>> $customers */
/** @var list<array<string, mixed>> $products */
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
$lid = (int) ($loan['id'] ?? 0);
$curCid = (int) ($loan['customer_id'] ?? 0);
$curPid = (int) ($loan['loan_product_id'] ?? 0);
$principal = (string) ($loan['principal_amount'] ?? '');
$rateLoan = (string) ($loan['rate_percent'] ?? '');
$basisLoan = (string) ($loan['interest_basis'] ?? LoanInterestBasis::REDUCING_BALANCE);
if (!in_array($basisLoan, LoanInterestBasis::all(), true)) {
    $basisLoan = LoanInterestBasis::REDUCING_BALANCE;
}
$st = (string) ($loan['status'] ?? '');

$customerOptions = [];
foreach ($customers as $c) {
    $cid = (int) ($c['id'] ?? 0);
    if ($cid <= 0) {
        continue;
    }
    $cname = (string) ($c['full_name'] ?? '');
    $cphone = (string) ($c['phone'] ?? '');
    $customerOptions[] = [
        'id' => $cid,
        'label' => $cname . ($cphone !== '' ? ' — ' . $cphone : ''),
    ];
}

$productOptions = [];
foreach ($products as $p) {
    $pid = (int) ($p['id'] ?? 0);
    if ($pid <= 0) {
        continue;
    }
    $inactive = !(int) ($p['is_active'] ?? 0);
    $pRate = (string) ($p['rate_percent'] ?? '');
    $pBasis = (string) ($p['default_interest_basis'] ?? LoanInterestBasis::REDUCING_BALANCE);
    $pAr = (int) ($p['allow_reducing_balance'] ?? 1) ? 1 : 0;
    $pAf = (int) ($p['allow_flat_monthly'] ?? 1) ? 1 : 0;
    $pname = (string) ($p['name'] ?? '');
    $productOptions[] = [
        'id' => $pid,
        'label' => $pname . ' — ' . $pRate . '%' . ($inactive ? ' (inactive)' : ''),
        'rate' => $pRate,
        'basis' => $pBasis,
        'allowR' => $pAr,
        'allowF' => $pAf,
    ];
}

$noCust = count($customers) === 0;
$noProd = count($products) === 0;
?>
<div class="container" style="padding:0; max-width:560px;">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">Edit loan #<?= $lid ?></h1>
  <p style="color: var(--muted); margin: 0 0 22px;">
    <a href="<?= htmlspecialchars($basePath . '/loans/' . $lid, ENT_QUOTES, 'UTF-8') ?>" style="color:var(--muted); font-weight:650;">← Back to loan</a>
    <?php if ($st === 'rejected'): ?>
      <span style="display:block; margin-top:8px; font-size: 13px;">Rejected loans return to <strong>draft</strong> when you save, so you can submit again.</span>
    <?php endif; ?>
  </p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($noProd): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">No loan products available.</div>
  <?php endif; ?>
  <?php if ($noCust): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">No customers in your scope.</div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $lid . '/update', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap:14px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <?php
      $combobox = [
          'id' => 'loan_customer',
          'name' => 'customer_id',
          'label' => 'Customer',
          'options' => $customerOptions,
          'selectedId' => $curCid,
          'placeholder' => 'Search by name or phone, then pick…',
          'disabled' => $noCust,
          'required' => false,
          'syncProduct' => false,
      ];
      require STR_CONSOLE_ROOT . '/views/partials/loan_combobox.php';
      $combobox = [
          'id' => 'loan_product',
          'name' => 'loan_product_id',
          'label' => 'Product',
          'options' => $productOptions,
          'selectedId' => $curPid,
          'placeholder' => 'Search products, then pick…',
          'disabled' => $noProd,
          'required' => false,
          'syncProduct' => true,
      ];
      require STR_CONSOLE_ROOT . '/views/partials/loan_combobox.php';
      require STR_CONSOLE_ROOT . '/views/partials/loan_combobox_boot.php';
      ?>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Monthly rate (%)
        <input name="rate_percent" type="number" step="0.0001" min="0.0001" required value="<?= htmlspecialchars($rateLoan, ENT_QUOTES, 'UTF-8') ?>"
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <fieldset style="border:1px solid var(--line2); border-radius:14px; padding:14px 16px; margin:0;">
        <legend style="font-size:13px; font-weight:650; color:var(--muted); padding:0 6px;">Interest on this loan</legend>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px; margin-bottom:8px;">
          <input type="radio" name="interest_basis" value="<?= htmlspecialchars(LoanInterestBasis::REDUCING_BALANCE, ENT_QUOTES, 'UTF-8') ?>" <?= $basisLoan === LoanInterestBasis::REDUCING_BALANCE ? 'checked' : '' ?> />
          <?= htmlspecialchars(LoanInterestBasis::label(LoanInterestBasis::REDUCING_BALANCE), ENT_QUOTES, 'UTF-8') ?>
        </label>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px;">
          <input type="radio" name="interest_basis" value="<?= htmlspecialchars(LoanInterestBasis::FLAT_MONTHLY, ENT_QUOTES, 'UTF-8') ?>" <?= $basisLoan === LoanInterestBasis::FLAT_MONTHLY ? 'checked' : '' ?> />
          <?= htmlspecialchars(LoanInterestBasis::label(LoanInterestBasis::FLAT_MONTHLY), ENT_QUOTES, 'UTF-8') ?>
        </label>
      </fieldset>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Principal (₦)
        <input name="principal_amount" type="number" step="0.01" min="0.01" required value="<?= htmlspecialchars($principal, ENT_QUOTES, 'UTF-8') ?>"
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <div style="display:flex; gap: 10px; flex-wrap: wrap;">
        <button type="submit" class="btn primary" <?= ($noProd || $noCust) ? 'disabled' : '' ?>>Save changes</button>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/loans/' . $lid, ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
