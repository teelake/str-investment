<?php
declare(strict_types=1);
/** @var string $kind */
/** @var bool $canLoans */
/** @var bool $canCustomers */
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int} $pagination */
/** @var string $status */
/** @var string $from */
/** @var string $to */
/** @var string $q */
/** @var bool $statusInvalid */
/** @var bool $dateFromInvalid */
/** @var bool $dateToInvalid */
/** @var string $filterQuery */
/** @var string|null $dbError */
/** @var bool $canExport */
$basePath = Request::basePath();
$g = ConsoleAuth::grants();
$dbError = $dbError ?? null;
$q = $q ?? '';
$rows = $pagination['rows'];
$total = (int) $pagination['total'];
$page = (int) $pagination['page'];
$perPage = (int) $pagination['per_page'];

$qp = [];
if ($filterQuery !== '') {
    parse_str($filterQuery, $qp);
}

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
?>
<div class="container" style="padding:0">
  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="margin-bottom: 20px;">
    <h1 style="font-size: var(--h2); margin: 0 0 6px;">Reports</h1>
    <p style="color: var(--muted); margin: 0; font-size: 14px;">Filter data in your scope. Export respects the same filters (up to <?= (int) ReportRepository::EXPORT_MAX_ROWS ?> rows).</p>
  </div>

  <div style="display:flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
    <?php if ($canLoans): ?>
      <a class="btn <?= $kind === 'loans' ? 'primary' : 'ghost' ?>" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/reports?' . http_build_query(array_merge($qp, ['kind' => 'loans', 'page' => 1])), ENT_QUOTES, 'UTF-8') ?>">Loans</a>
    <?php endif; ?>
    <?php if ($canCustomers): ?>
      <a class="btn <?= $kind === 'customers' ? 'primary' : 'ghost' ?>" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/reports?' . http_build_query(array_merge($qp, ['kind' => 'customers', 'page' => 1])), ENT_QUOTES, 'UTF-8') ?>">Customers</a>
    <?php endif; ?>
  </div>

  <?php
  $invalidParts = [];
  if ($statusInvalid) {
      $invalidParts[] = 'status';
  }
  if ($dateFromInvalid) {
      $invalidParts[] = 'start date (use YYYY-MM-DD)';
  }
  if ($dateToInvalid) {
      $invalidParts[] = 'end date (use YYYY-MM-DD)';
  }
  ?>
  <?php if ($invalidParts !== []): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      Some filters were ignored (invalid <?= htmlspecialchars(implode(', ', $invalidParts), ENT_QUOTES, 'UTF-8') ?>).
    </div>
  <?php endif; ?>

  <form method="get" action="<?= htmlspecialchars($basePath . '/reports', ENT_QUOTES, 'UTF-8') ?>" style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; margin-bottom: 20px; box-shadow: var(--shadow2); display:grid; gap: 14px;">
    <input type="hidden" name="kind" value="<?= htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') ?>" />
    <div style="display:flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
      <?php if ($kind === 'loans'): ?>
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          Status
          <select name="status" style="padding: 10px 12px; border-radius: 14px; border: 1px solid var(--line); background: #fff; min-width: 180px;">
            <option value="">All</option>
            <?php foreach (ReportRepository::LOAN_STATUSES as $st): ?>
              <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>" <?= $status === $st ? ' selected' : '' ?>><?= htmlspecialchars($statusLabel($st), ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      <?php endif; ?>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        From (created)
        <input type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>" style="padding: 10px 12px; border-radius: 14px; border: 1px solid var(--line); background: #fff;" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        To (created)
        <input type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>" style="padding: 10px 12px; border-radius: 14px; border: 1px solid var(--line); background: #fff;" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted); flex: 1; min-width: 200px;">
        Search
        <input type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= $kind === 'loans' ? 'Customer name, loan id…' : 'Name, phone, id…' ?>" autocomplete="off" style="padding: 10px 12px; border-radius: 14px; border: 1px solid var(--line); background: #fff; width: 100%;" />
      </label>
      <button type="submit" class="btn primary" style="font-size: 14px;">Apply</button>
      <?php if ($canExport && ($kind === 'loans' ? $canLoans : $canCustomers)): ?>
        <a class="btn ghost" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/reports/export?' . $filterQuery, ENT_QUOTES, 'UTF-8') ?>">Download CSV</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if ($kind === 'loans' && $canLoans): ?>
    <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
      <table style="width:100%; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
            <th style="padding: 12px 14px; width: 1%; white-space: nowrap;">ID</th>
            <th style="padding: 12px 14px;">Loan</th>
            <th style="padding: 12px 14px;">Customer</th>
            <th style="padding: 12px 14px;">Status</th>
            <th style="padding: 12px 14px; text-align:right;">Principal</th>
            <th style="padding: 12px 14px;">Created</th>
            <th style="padding: 12px 14px; width: 1%; white-space: nowrap; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($rows) === 0): ?>
            <tr><td colspan="7" style="padding: 28px 14px; color: var(--muted);">No rows match.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $i => $r): ?>
              <?php
              $rowNum = ($page - 1) * $perPage + $i + 1;
              $rlid = (int) ($r['id'] ?? 0);
              $rst = (string) ($r['status'] ?? '');
              $canViewLoanRp = str_console_authorize_route($g, 'loans.show');
              $canEditLoanRp = str_console_authorize_route($g, 'loans.edit') && ($rst === 'draft' || $rst === 'rejected');
              $canCloseLoanRp = str_console_authorize_route($g, 'loans.close') && $rst === 'active';
              ?>
              <tr style="border-bottom: 1px solid var(--line2);">
                <td style="padding: 12px 14px; font-family: ui-monospace, monospace; color: var(--muted);"><?= $rowNum ?></td>
                <td style="padding: 12px 14px; font-weight: 650;">
                  <?php if ($canViewLoanRp): ?>
                    <a href="<?= htmlspecialchars($basePath . '/loans/' . $rlid, ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: none;">Open</a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 12px 14px;"><?= htmlspecialchars($statusLabel($rst), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 12px 14px; text-align:right;"><?= htmlspecialchars(number_format((float) ($r['principal_amount'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 12px 14px; color: var(--muted);"><?= htmlspecialchars((string) ($r['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding: 12px 14px; text-align: right;">
                  <?php
                  if ($canViewLoanRp) {
                      $viewHref = $basePath . '/loans/' . $rlid;
                      $editHref = $canEditLoanRp ? $basePath . '/loans/' . $rlid . '/edit' : null;
                      $dangerPost = $canCloseLoanRp ? [
                          'action' => $basePath . '/loans/' . $rlid . '/close',
                          'title' => 'Close loan',
                          'confirm' => 'Close this loan? It must have zero outstanding balance.',
                      ] : null;
                      require STR_CONSOLE_ROOT . '/views/partials/table_icon_actions.php';
                  } else {
                      echo '—';
                  }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php elseif ($kind === 'customers' && $canCustomers): ?>
    <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
      <table style="width:100%; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
            <th style="padding: 12px 14px; width: 1%; white-space: nowrap;">ID</th>
            <th style="padding: 12px 14px;">Name</th>
            <th style="padding: 12px 14px;">Phone</th>
            <th style="padding: 12px 14px;">Assigned</th>
            <th style="padding: 12px 14px;">Created</th>
            <th style="padding: 12px 14px; width: 1%; white-space: nowrap; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($rows) === 0): ?>
            <tr><td colspan="6" style="padding: 28px 14px; color: var(--muted);">No rows match.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $i => $r): ?>
              <?php
              $rowNum = ($page - 1) * $perPage + $i + 1;
              $rcid = (int) ($r['id'] ?? 0);
              $custActive = (int) ($r['is_active'] ?? 1) === 1;
              $canViewCustRp = str_console_authorize_route($g, 'customers.show');
              $canEditCustRp = str_console_authorize_route($g, 'customers.edit');
              ?>
              <tr style="border-bottom: 1px solid var(--line2);">
                <td style="padding: 12px 14px; font-family: ui-monospace, monospace; color: var(--muted);"><?= $rowNum ?></td>
                <td style="padding: 12px 14px; font-weight: 650;">
                  <?php if ($canViewCustRp): ?>
                    <a href="<?= htmlspecialchars($basePath . '/customers/' . $rcid, ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars((string) ($r['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                  <?php else: ?>
                    <?= htmlspecialchars((string) ($r['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <?php endif; ?>
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
                <td style="padding: 12px 14px; text-align: right;">
                  <?php
                  if ($canViewCustRp) {
                      $viewHref = $basePath . '/customers/' . $rcid;
                      $editHref = $canEditCustRp ? $basePath . '/customers/' . $rcid . '/edit' : null;
                      $dangerPost = ($canEditCustRp && $custActive) ? [
                          'action' => $basePath . '/customers/' . $rcid . '/deactivate',
                          'title' => 'Deactivate customer',
                          'confirm' => 'Deactivate this customer? They will be removed from the main customer list.',
                      ] : null;
                      require STR_CONSOLE_ROOT . '/views/partials/table_icon_actions.php';
                  } else {
                      echo '—';
                  }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <?php
  $reportQuery = $qp;
  unset($reportQuery['page']);
  $path = '/reports';
  $pageParam = 'page';
  $query = $reportQuery;
  require STR_CONSOLE_ROOT . '/views/partials/pagination.php';
  ?>
</div>
