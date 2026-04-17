<?php
declare(strict_types=1);
/** @var list<array<string, mixed>> $customers */
/** @var list<array<string, mixed>> $products */
/** @var int $preCustomerId */
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
?>
<div class="container" style="padding:0; max-width:560px;">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">New loan</h1>
  <p style="color: var(--muted); margin: 0 0 22px;"><a href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="color:var(--muted); font-weight:650;">← Loans</a></p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (count($products) === 0): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      No active loan products. <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loan_products.create')): ?>
        <a href="<?= htmlspecialchars($basePath . '/loan-products/create', ENT_QUOTES, 'UTF-8') ?>">Create a product first</a>.
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (count($customers) === 0): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">No customers in your scope yet. Register a customer first.</div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap:14px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Customer
        <select name="customer_id" required style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" <?= count($customers) === 0 ? 'disabled' : '' ?>>
          <?php foreach ($customers as $c): ?>
            <?php $cid = (int) ($c['id'] ?? 0); ?>
            <option value="<?= $cid ?>" <?= $cid === $preCustomerId ? 'selected' : '' ?>><?= htmlspecialchars((string) ($c['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Product
        <select id="loan_product_id" name="loan_product_id" required style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;">
          <option value="" selected disabled>Select a product…</option>
          <?php foreach ($products as $p): ?>
            <?php
            $pid = (int) ($p['id'] ?? 0);
            $pRate = htmlspecialchars((string) ($p['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8');
            $pBasis = htmlspecialchars((string) ($p['default_interest_basis'] ?? LoanInterestBasis::REDUCING_BALANCE), ENT_QUOTES, 'UTF-8');
            $pAr = (int) ($p['allow_reducing_balance'] ?? 1) ? '1' : '0';
            $pAf = (int) ($p['allow_flat_monthly'] ?? 1) ? '1' : '0';
            ?>
            <option value="<?= $pid ?>" data-product-rate="<?= $pRate ?>" data-product-basis="<?= $pBasis ?>" data-allow-r="<?= $pAr ?>" data-allow-f="<?= $pAf ?>"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — <?= $pRate ?>% suggested</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Monthly rate (%)
        <input name="rate_percent" type="number" step="0.0001" min="0.0001" required
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <fieldset style="border:1px solid var(--line2); border-radius:14px; padding:14px 16px; margin:0;">
        <legend style="font-size:13px; font-weight:650; color:var(--muted); padding:0 6px;">Interest on this loan</legend>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px; margin-bottom:8px;">
          <input type="radio" name="interest_basis" value="<?= htmlspecialchars(LoanInterestBasis::REDUCING_BALANCE, ENT_QUOTES, 'UTF-8') ?>" checked />
          <?= htmlspecialchars(LoanInterestBasis::label(LoanInterestBasis::REDUCING_BALANCE), ENT_QUOTES, 'UTF-8') ?>
        </label>
        <label style="display:flex; align-items:center; gap:10px; font-size:14px;">
          <input type="radio" name="interest_basis" value="<?= htmlspecialchars(LoanInterestBasis::FLAT_MONTHLY, ENT_QUOTES, 'UTF-8') ?>" />
          <?= htmlspecialchars(LoanInterestBasis::label(LoanInterestBasis::FLAT_MONTHLY), ENT_QUOTES, 'UTF-8') ?>
        </label>
      </fieldset>
      <label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
        Principal (₦)
        <input name="principal_amount" type="number" step="0.01" min="0.01" required
          style="padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff;" />
      </label>
      <script>
        (function () {
          var sel = document.getElementById('loan_product_id');
          var rateIn = document.querySelector('input[name="rate_percent"]');
          var rRed = document.querySelector('input[name="interest_basis"][value="<?= LoanInterestBasis::REDUCING_BALANCE ?>"]');
          var rFlat = document.querySelector('input[name="interest_basis"][value="<?= LoanInterestBasis::FLAT_MONTHLY ?>"]');
          if (!sel || !rateIn || !rRed || !rFlat) return;
          function sync() {
            var o = sel.options[sel.selectedIndex];
            if (!o || !o.value) return;
            var pr = o.getAttribute('data-product-rate');
            if (pr) rateIn.value = pr;
            var ar = o.getAttribute('data-allow-r') === '1';
            var af = o.getAttribute('data-allow-f') === '1';
            rRed.disabled = !ar;
            rFlat.disabled = !af;
            var b = o.getAttribute('data-product-basis');
            if (b === '<?= LoanInterestBasis::FLAT_MONTHLY ?>' && af) rFlat.checked = true;
            else if (ar) rRed.checked = true;
            else if (af) rFlat.checked = true;
          }
          sel.addEventListener('change', sync);
        })();
      </script>
      <button type="submit" class="btn primary" <?= (count($products) === 0 || count($customers) === 0) ? 'disabled' : '' ?>>Create draft loan</button>
    </form>
  </div>
</div>
