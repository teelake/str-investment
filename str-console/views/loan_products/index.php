<?php
declare(strict_types=1);
/** @var list<array<string, mixed>> $products */
/** @var string|null $dbError */
$basePath = Request::basePath();
$dbError = $dbError ?? null;
?>
<div class="container" style="padding:0">
  <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:20px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;">Loan products</h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;">Rates are snapshotted onto each loan at creation.</p>
    </div>
    <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loan_products.create')): ?>
      <a class="btn primary" href="<?= htmlspecialchars($basePath . '/loan-products/create', ENT_QUOTES, 'UTF-8') ?>">New product</a>
    <?php endif; ?>
  </div>

  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px;"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
    <table style="width:100%; border-collapse:collapse; font-size:14px;">
      <thead>
        <tr style="text-align:left; border-bottom:1px solid var(--line2); color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">
          <th style="padding:12px 14px; width:1%; white-space:nowrap;">ID</th>
          <th style="padding:12px 14px;">Name</th>
          <th style="padding:12px 14px;">Rate %</th>
          <th style="padding:12px 14px;">Period (mo)</th>
          <th style="padding:12px 14px;">Status</th>
          <th style="padding:12px 14px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($products) === 0): ?>
          <tr><td colspan="6" style="padding:28px 14px; color:var(--muted);">No products yet.</td></tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr style="border-bottom:1px solid var(--line2);">
              <td style="padding:12px 14px; font-family:ui-monospace,monospace; color:var(--muted);"><?= (int) ($p['id'] ?? 0) ?></td>
              <td style="padding:12px 14px; font-weight:650;"><?= htmlspecialchars((string) ($p['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:12px 14px;"><?= htmlspecialchars((string) ($p['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:12px 14px;"><?= (int) ($p['period_months'] ?? 0) ?></td>
              <td style="padding:12px 14px;"><?= (int) ($p['is_active'] ?? 0) ? 'Active' : 'Retired' ?></td>
              <td style="padding:12px 14px; text-align:right;">
                <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loan_products.show')): ?>
                  <a class="btn ghost" style="font-size:13px; padding:8px 12px;" href="<?= htmlspecialchars($basePath . '/loan-products/' . (int) $p['id'], ENT_QUOTES, 'UTF-8') ?>">View</a>
                <?php endif; ?>
                <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loan_products.edit')): ?>
                  <a class="btn ghost" style="font-size:13px; padding:8px 12px;" href="<?= htmlspecialchars($basePath . '/loan-products/' . (int) $p['id'] . '/edit', ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                <?php endif; ?>
                <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loan_products.retire') && (int) ($p['is_active'] ?? 0)): ?>
                  <form method="post" action="<?= htmlspecialchars($basePath . '/loan-products/' . (int) $p['id'] . '/retire', ENT_QUOTES, 'UTF-8') ?>" style="display:inline;" onsubmit="return confirm('Retire this product?');">
                    <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
                    <button type="submit" class="btn ghost" style="font-size:13px; padding:8px 12px;">Retire</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
