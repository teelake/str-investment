<?php
declare(strict_types=1);
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int} $pagination */
/** @var string $filterQ */
/** @var string|null $dbError */
$dbError = $dbError ?? null;
$filterQ = $filterQ ?? '';
$basePath = Request::basePath();
$rows = $pagination['rows'];
$total = (int) $pagination['total'];
$page = (int) $pagination['page'];
$perPage = (int) $pagination['per_page'];
$g = ConsoleAuth::grants();
$canBulkCustomers = str_console_authorize_route($g, 'bulk_upload.customers');
$hasFilter = trim($filterQ) !== '';
?>
<div class="container" style="padding:0">
  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="display:flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 20px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;">Customers</h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;">Customers in your scope<?= $hasFilter ? ' (filtered)' : '' ?>.</p>
    </div>
    <div style="display:flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: flex-end;">
      <?php if ($canBulkCustomers): ?>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/bulk-upload/customers', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 14px;">Import CSV</a>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/downloads/customers-import-template.csv', ENT_QUOTES, 'UTF-8') ?>" download style="font-size: 14px;">Download template</a>
      <?php endif; ?>
      <?php if (str_console_authorize_route($g, 'customers.create')): ?>
        <a class="btn primary" href="<?= htmlspecialchars($basePath . '/customers/create', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 14px;">Register customer</a>
      <?php endif; ?>
    </div>
  </div>

  <form method="get" action="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 16px;">
    <label style="display:grid; gap: 6px; font-size: 13px; font-weight: 650; color: var(--muted); flex: 1; min-width: 200px;">
      Search
      <input type="search" name="q" value="<?= htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Name, phone, NIN, BVN, id…" autocomplete="off" style="padding: 10px 12px; border: 1px solid var(--line2); border-radius: var(--radius); font-size: 14px; background: var(--card); color: inherit; width: 100%;">
    </label>
    <button type="submit" class="btn primary" style="font-size: 14px;">Apply</button>
    <?php if ($hasFilter): ?>
      <a class="btn ghost" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>">Clear</a>
    <?php endif; ?>
  </form>

  <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
          <th style="padding: 12px 14px; width: 1%; white-space: nowrap;">ID</th>
          <th style="padding: 12px 14px;">Name</th>
          <th style="padding: 12px 14px;">Phone</th>
          <th style="padding: 12px 14px;">Assigned</th>
          <th style="padding: 12px 14px;">Created</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows) === 0): ?>
          <tr>
            <td colspan="5" style="padding: 28px 14px; color: var(--muted);"><?= $hasFilter ? 'No customers match your search.' : 'No customers in your scope yet.' ?></td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr style="border-bottom: 1px solid var(--line2);">
              <td style="padding: 12px 14px; font-family: ui-monospace, monospace; color: var(--muted);"><?= (int) ($r['id'] ?? 0) ?></td>
              <td style="padding: 12px 14px; font-weight: 650;">
                <a href="<?= htmlspecialchars($basePath . '/customers/' . (int) ($r['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: underline; text-underline-offset: 4px;">
                  <?= htmlspecialchars((string) ($r['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>
              <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 12px 14px; color: var(--muted);"><?php
                $alabel = trim((string) ($r['assigned_user_label'] ?? ''));
                if ($alabel !== '') {
                    echo htmlspecialchars($alabel, ENT_QUOTES, 'UTF-8');
                } elseif (($r['assigned_user_id'] ?? null) !== null && $r['assigned_user_id'] !== '') {
                    echo 'Console user #' . (int) $r['assigned_user_id'];
                } else {
                    echo '—';
                }
              ?></td>
              <td style="padding: 12px 14px; color: var(--muted);"><?= htmlspecialchars((string) ($r['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
  $path = '/customers';
  $pageParam = 'page';
  $query = $hasFilter ? ['q' => $filterQ] : [];
  require STR_CONSOLE_ROOT . '/views/partials/pagination.php';
  ?>
</div>
