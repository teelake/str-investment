<?php
declare(strict_types=1);
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int} $pagination */
/** @var string $filterStatus */
/** @var bool $statusInvalid */
/** @var string|null $dbError */
$basePath = Request::basePath();
$rows = $pagination['rows'];
$total = (int) $pagination['total'];
$page = (int) $pagination['page'];
$perPage = (int) $pagination['per_page'];
$dbError = $dbError ?? null;
$filterStatus = $filterStatus ?? '';
$statusInvalid = $statusInvalid ?? false;
$g = ConsoleAuth::grants();
$canBulkLoans = str_console_authorize_route($g, 'bulk_upload.loans');
$hasStatusFilter = trim($filterStatus) !== '';

$statusLabel = static function (string $s): string {
    return match ($s) {
        'draft' => 'Draft',
        'pending_approval' => 'Pending approval',
        'approved' => 'Approved',
        'active' => 'Active',
        'closed' => 'Closed',
        'rejected' => 'Rejected',
        default => $s,
    };
};
$fmt = static fn (float $n): string => '₦' . number_format($n, 2);
?>
<div class="container" style="padding:0">
  <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:20px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;">Loans</h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;">Loans in your scope<?= ($hasStatusFilter && !$statusInvalid) ? ' (filtered by status)' : '' ?>.</p>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:flex-end;">
      <?php if ($canBulkLoans): ?>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/bulk-upload/loans', ENT_QUOTES, 'UTF-8') ?>" style="font-size:14px;">Import CSV</a>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/downloads/loans-import-template.csv', ENT_QUOTES, 'UTF-8') ?>" download style="font-size:14px;">Download template</a>
      <?php endif; ?>
      <?php if (str_console_authorize_route($g, 'loans.create')): ?>
        <a class="btn primary" href="<?= htmlspecialchars($basePath . '/loans/create', ENT_QUOTES, 'UTF-8') ?>">New loan</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px;"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($statusInvalid): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      Unknown status filter was ignored; showing all statuses.
    </div>
  <?php endif; ?>

  <form method="get" action="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 16px;">
    <label style="display:grid; gap: 6px; font-size: 13px; font-weight: 650; color: var(--muted);">
      Status
      <select name="status" style="padding: 10px 12px; border-radius: 14px; border: 1px solid var(--line2); background: var(--card); color: inherit; min-width: 200px; font-size: 14px;">
        <option value=""<?= !$hasStatusFilter || $statusInvalid ? ' selected' : '' ?>>All</option>
        <?php foreach (ReportRepository::LOAN_STATUSES as $st): ?>
          <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>"<?= !$statusInvalid && $filterStatus === $st ? ' selected' : '' ?>><?= htmlspecialchars($statusLabel($st), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit" class="btn primary" style="font-size: 14px;">Apply</button>
    <?php if ($hasStatusFilter && !$statusInvalid): ?>
      <a class="btn ghost" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>">Clear</a>
    <?php endif; ?>
  </form>

  <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
    <table style="width:100%; border-collapse:collapse; font-size:14px;">
      <thead>
        <tr style="text-align:left; border-bottom:1px solid var(--line2); color:var(--muted); font-size:12px; text-transform:uppercase;">
          <th style="padding:12px 14px;">ID</th>
          <th style="padding:12px 14px;">Customer</th>
          <th style="padding:12px 14px;">Principal</th>
          <th style="padding:12px 14px;">Rate</th>
          <th style="padding:12px 14px;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows) === 0): ?>
          <tr><td colspan="5" style="padding:28px 14px; color:var(--muted);"><?= ($hasStatusFilter && !$statusInvalid) ? 'No loans match this status.' : 'No loans yet.' ?></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
            $lid = (int) ($r['id'] ?? 0);
            $st = (string) ($r['status'] ?? '');
            ?>
            <tr style="border-bottom:1px solid var(--line2);">
              <td style="padding:12px 14px;">
                <a href="<?= htmlspecialchars($basePath . '/loans/' . $lid, ENT_QUOTES, 'UTF-8') ?>" style="font-weight:650; color:inherit;">#<?= $lid ?></a>
              </td>
              <td style="padding:12px 14px;"><?= htmlspecialchars((string) ($r['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:12px 14px;"><?= $fmt((float) ($r['principal_amount'] ?? 0)) ?></td>
              <td style="padding:12px 14px;"><?= htmlspecialchars((string) ($r['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?>%</td>
              <td style="padding:12px 14px;"><?= htmlspecialchars($statusLabel($st), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
  $path = '/loans';
  $pageParam = 'page';
  $query = ($hasStatusFilter && !$statusInvalid) ? ['status' => $filterStatus] : [];
  require STR_CONSOLE_ROOT . '/views/partials/pagination.php';
  ?>
</div>
