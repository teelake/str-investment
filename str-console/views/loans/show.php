<?php
declare(strict_types=1);
/** @var array<string, mixed> $loan */
/** @var list<array<string, mixed>> $ledger */
/** @var float $outstanding */
/** @var bool $canSubmit */
/** @var bool $canApprove */
/** @var bool $canReject */
/** @var bool $canDisburse */
/** @var bool $canPay */
/** @var mixed $flash */
/** @var mixed $flashError */
$basePath = Request::basePath();
$id = (int) ($loan['id'] ?? 0);
$st = (string) ($loan['status'] ?? '');
$cid = (int) ($loan['customer_id'] ?? 0);
$cname = (string) ($loan['customer_name'] ?? '');
$fmt = static fn (float $n): string => '₦' . number_format($n, 2);
$statusLabel = match ($st) {
    'draft' => 'Draft',
    'pending_approval' => 'Pending approval',
    'approved' => 'Approved',
    'active' => 'Active',
    'closed' => 'Closed',
    'rejected' => 'Rejected',
    default => $st,
};
$flashOk = is_string($flash) ? $flash : '';
$err = is_string($flashError) ? $flashError : '';
$today = (new DateTimeImmutable('now'))->format('Y-m-d');
?>
<div class="container" style="padding:0">
  <div style="margin-bottom: 20px;">
    <a href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Loans</a>
    <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:16px; margin-top:12px;">
      <div>
        <h1 style="font-size: var(--h2); margin: 0 0 6px;">Loan #<?= $id ?></h1>
        <p style="color: var(--muted); margin: 0; font-size: 14px;">
          <a href="<?= htmlspecialchars($basePath . '/customers/' . $cid, ENT_QUOTES, 'UTF-8') ?>" style="color: var(--green2); font-weight: 650;"><?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?></a>
        </p>
      </div>
      <span style="display:inline-flex; align-items:center; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700; background:var(--green-soft); color:var(--green2);"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <?php if ($flashOk !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px;">
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Principal</div>
      <div style="font-size: 22px; font-weight: 800; margin-top: 8px;"><?= $fmt((float) ($loan['principal_amount'] ?? 0)) ?></div>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Rate (booked)</div>
      <div style="font-size: 22px; font-weight: 800; margin-top: 8px;"><?= htmlspecialchars((string) ($loan['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?>%</div>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="font-size: 12px; font-weight: 650; color: var(--muted); text-transform: uppercase;">Outstanding</div>
      <div style="font-size: 22px; font-weight: 800; margin-top: 8px;"><?= $fmt($outstanding) ?></div>
    </div>
  </div>

  <div style="display:flex; flex-wrap:wrap; gap: 10px; margin-bottom: 28px;">
    <?php if ($canSubmit): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/submit', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn primary">Submit for approval</button>
      </form>
    <?php endif; ?>
    <?php if ($canApprove): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/approve', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn primary">Approve</button>
      </form>
    <?php endif; ?>
    <?php if ($canReject): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/reject', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Rejection reason
          <input name="reason" required maxlength="500" style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); min-width:220px;" />
        </label>
        <button type="submit" class="btn ghost" style="color:#7f1d1d;">Reject</button>
      </form>
    <?php endif; ?>
    <?php if ($canDisburse): ?>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/disburse', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Disbursement date
          <input type="date" name="disbursed_on" value="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
        </label>
        <button type="submit" class="btn primary">Disburse &amp; open ledger</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if ($canPay && $st === 'active'): ?>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2); margin-bottom: 28px;">
      <h2 style="font-size: 15px; margin: 0 0 14px; font-weight: 800;">Record payment</h2>
      <form method="post" action="<?= htmlspecialchars($basePath . '/loans/' . $id . '/payment', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Amount (₦)
          <input name="amount" type="number" step="0.01" min="0.01" required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line); width:140px;" />
        </label>
        <label style="display:grid; gap:4px; font-size:12px; font-weight:650; color:var(--muted);">
          Paid on
          <input type="date" name="paid_on" value="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>" required style="padding:10px 12px; border-radius:12px; border:1px solid var(--line);" />
        </label>
        <button type="submit" class="btn primary">Apply payment</button>
      </form>
      <p style="margin: 12px 0 0; font-size: 12px; color: var(--muted2);">Adds a ledger line: interest on the previous closing, then applies your payment (same logic as your spreadsheet).</p>
    </div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2);">
    <h2 style="font-size: 15px; margin: 0 0 16px; font-weight: 800;">Ledger</h2>
    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse; font-size:13px; min-width:720px;">
        <thead>
          <tr style="text-align:right; border-bottom:1px solid var(--line2); color:var(--muted); font-size:11px; text-transform:uppercase;">
            <th style="padding:10px 8px; text-align:left;">#</th>
            <th style="padding:10px 8px; text-align:left;">Date</th>
            <th style="padding:10px 8px;">Opening</th>
            <th style="padding:10px 8px;">Rate %</th>
            <th style="padding:10px 8px;">Interest</th>
            <th style="padding:10px 8px;">Total due</th>
            <th style="padding:10px 8px;">Paid</th>
            <th style="padding:10px 8px;">Closing</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($ledger) === 0): ?>
            <tr><td colspan="8" style="padding:24px; color:var(--muted); text-align:left;">No lines yet. Disburse this loan to create the first line.</td></tr>
          <?php else: ?>
            <?php foreach ($ledger as $row): ?>
              <tr style="border-bottom:1px solid var(--line2);">
                <td style="padding:10px 8px; text-align:left; font-weight:650;"><?= (int) ($row['line_no'] ?? 0) ?></td>
                <td style="padding:10px 8px; text-align:left;"><?= htmlspecialchars((string) ($row['period_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= $fmt((float) ($row['opening_balance'] ?? 0)) ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= htmlspecialchars((string) ($row['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= $fmt((float) ($row['interest_amount'] ?? 0)) ?></td>
                <td style="padding:10px 8px; text-align:right; font-weight:650;"><?= $fmt((float) ($row['amount_due'] ?? 0)) ?></td>
                <td style="padding:10px 8px; text-align:right;"><?= isset($row['payment_amount']) && $row['payment_amount'] !== null ? $fmt((float) $row['payment_amount']) : '—' ?></td>
                <td style="padding:10px 8px; text-align:right; font-weight:700;"><?= $fmt((float) ($row['closing_balance'] ?? 0)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($st === 'rejected' && ($loan['rejected_reason'] ?? '') !== ''): ?>
    <div style="margin-top:20px; padding:14px 16px; border-radius:14px; background:rgba(180,40,40,.06); border:1px solid rgba(180,40,40,.15); font-size:14px;">
      <strong>Reason:</strong> <?= htmlspecialchars((string) $loan['rejected_reason'], ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
</div>
