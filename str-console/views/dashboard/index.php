<?php
declare(strict_types=1);
/** @var int|null $customerCount */
/** @var array{active_loans: int, outstanding: float}|null $loanStats */
/** @var array<string, int>|null $loanByStatus */
/** @var float|null $bookedPrincipal */
/** @var list<array<string, mixed>>|null $recentLoans */
/** @var string|null $dbError */
$basePath = Request::basePath();
$fmt = static fn (float $n): string => '₦' . number_format($n, 2);
$g = ConsoleAuth::grants();
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
$totalLoans = 0;
$pipeline = 0;
if (is_array($loanByStatus)) {
    $totalLoans = array_sum($loanByStatus);
    $pipeline = (int) ($loanByStatus['draft'] ?? 0) + (int) ($loanByStatus['pending_approval'] ?? 0) + (int) ($loanByStatus['approved'] ?? 0);
}
$statusOrder = ['active', 'draft', 'pending_approval', 'approved', 'closed', 'rejected'];
?>
<div class="container console-dash" style="padding:0">
  <header class="console-dash__head">
    <h1 class="console-dash__title">Dashboard</h1>
    <p class="console-dash__sub">Portfolio overview · data reflects your access scope</p>
  </header>

  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 24px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
      <div style="margin-top:10px; font-size: 13px; color: var(--muted);">
        Import <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">str-console/database/schema.sql</code> (and migrations), then run
        <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">php str-console/bin/seed-admin.php</code>.
      </div>
    </div>
  <?php endif; ?>

  <section class="console-dash__kpis" aria-label="Key metrics">
    <div class="console-dash__kpi">
      <div class="console-dash__kpi-label">Customers</div>
      <div class="console-dash__kpi-value"><?= $customerCount === null ? '—' : (string) (int) $customerCount ?></div>
      <?php if (str_console_authorize_route($g, 'customers.index')): ?>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; padding: 8px 12px; display: inline-flex;">Open list</a>
      <?php endif; ?>
    </div>
    <div class="console-dash__kpi">
      <div class="console-dash__kpi-label">Loans</div>
      <div class="console-dash__kpi-value"><?= $loanByStatus === null ? '—' : (string) $totalLoans ?></div>
      <?php if ($loanByStatus !== null && $totalLoans > 0): ?>
        <div class="console-dash__kpi-meta"><?= (int) $pipeline ?> in pipeline (draft · pending · approved)</div>
      <?php endif; ?>
      <?php if (str_console_authorize_route($g, 'loans.index')): ?>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; padding: 8px 12px; display: inline-flex;">Open list</a>
      <?php endif; ?>
    </div>
    <div class="console-dash__kpi">
      <div class="console-dash__kpi-label">Active · Outstanding</div>
      <div class="console-dash__kpi-value"><?= $loanStats === null ? '—' : (string) (int) ($loanStats['active_loans'] ?? 0) ?></div>
      <div class="console-dash__kpi-meta"><?= $loanStats === null ? '' : $fmt((float) ($loanStats['outstanding'] ?? 0)) ?> ledger balance</div>
    </div>
    <div class="console-dash__kpi">
      <div class="console-dash__kpi-label">Booked principal (active)</div>
      <div class="console-dash__kpi-value"><?= $bookedPrincipal === null ? '—' : $fmt((float) $bookedPrincipal) ?></div>
    </div>
  </section>

  <div class="console-dash__grid2">
    <div class="console-dash__panel">
      <div class="console-dash__panel-h">Loans by status</div>
      <table class="console-dash__table">
        <thead>
          <tr><th>Status</th><th style="text-align:right;">Count</th></tr>
        </thead>
        <tbody>
          <?php if ($loanByStatus === null): ?>
            <tr><td colspan="2" class="console-dash__muted" style="padding:20px 16px;">—</td></tr>
          <?php else: ?>
            <?php foreach ($statusOrder as $key): ?>
              <?php $c = (int) ($loanByStatus[$key] ?? 0); ?>
              <tr>
                <td><?= htmlspecialchars($statusLabel($key), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align:right; font-weight:650;"><?= $c === 0 ? '<span class="console-dash__zero">0</span>' : (string) $c ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="console-dash__panel">
      <div class="console-dash__panel-h">Recently updated loans</div>
      <table class="console-dash__table">
        <thead>
          <tr>
            <th>Loan</th>
            <th>Customer</th>
            <th>Status</th>
            <th style="text-align:right;">Principal</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recentLoans === null): ?>
            <tr><td colspan="4" class="console-dash__muted" style="padding:20px 16px;">—</td></tr>
          <?php elseif (count($recentLoans) === 0): ?>
            <tr><td colspan="4" class="console-dash__muted" style="padding:20px 16px;">No loans in scope yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentLoans as $row): ?>
              <?php
              $lid = (int) ($row['id'] ?? 0);
              $st = (string) ($row['status'] ?? '');
              ?>
              <tr>
                <td>
                  <?php if (str_console_authorize_route($g, 'loans.show')): ?>
                    <a href="<?= htmlspecialchars($basePath . '/loans/' . $lid, ENT_QUOTES, 'UTF-8') ?>">#<?= $lid ?></a>
                  <?php else: ?>
                    #<?= $lid ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string) ($row['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="console-dash__muted"><?= htmlspecialchars($statusLabel($st), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align:right; font-weight:650;"><?= $fmt((float) ($row['principal_amount'] ?? 0)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
