<?php
declare(strict_types=1);
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int} $pagination */
/** @var string $filterActivity */
/** @var string|null $dbError */
$basePath = Request::basePath();
$dbError = $dbError ?? null;
$filterActivity = $filterActivity ?? '';
$rows = $pagination['rows'];
$page = (int) $pagination['page'];
$total = (int) $pagination['total'];
$perPage = (int) $pagination['per_page'];
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

  <form method="get" action="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 16px;">
    <label style="display:grid; gap: 6px; font-size: 13px; font-weight: 650; color: var(--muted);">
      Activity
      <select name="status" style="padding: 10px 12px; border-radius: 14px; border: 1px solid var(--line2); background: var(--card); color: inherit; min-width: 160px; font-size: 14px;">
        <option value=""<?= $filterActivity === '' ? ' selected' : '' ?>>All</option>
        <option value="active"<?= $filterActivity === 'active' ? ' selected' : '' ?>>Active</option>
        <option value="retired"<?= $filterActivity === 'retired' ? ' selected' : '' ?>>Retired</option>
      </select>
    </label>
    <button type="submit" class="btn primary" style="font-size: 14px;">Apply</button>
    <?php if ($filterActivity !== ''): ?>
      <a class="btn ghost" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>">Clear</a>
    <?php endif; ?>
  </form>

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
        <?php if (count($rows) === 0): ?>
          <tr><td colspan="6" style="padding:28px 14px; color:var(--muted);"><?= $filterActivity !== '' ? 'No products match this filter.' : 'No products yet.' ?></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $p): ?>
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

  <?php
  $path = '/loan-products';
  $pageParam = 'page';
  $query = $filterActivity !== '' ? ['status' => $filterActivity] : [];
  require STR_CONSOLE_ROOT . '/views/partials/pagination.php';
  ?>
</div>
