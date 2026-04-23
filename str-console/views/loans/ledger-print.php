<?php
declare(strict_types=1);
/** @var array<string, mixed> $loan */
/** @var list<array<string, mixed>> $ledger */
/** @var float $outstanding */
/** @var string $customerName */
$basePath = Request::basePath();
$id = (int) ($loan['id'] ?? 0);
$st = (string) ($loan['status'] ?? '');
$fmt = static fn (float $n): string => '₦' . number_format($n, 2);
$title = 'Loan #' . $id . ' — Ledger';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — STR Console</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(Request::asset('assets/styles.css'), ENT_QUOTES, 'UTF-8') ?>" />
  <style>
    .ledger-doc { max-width: 1200px; margin: 0 auto; padding: 24px; font-size: 14px; }
    .ledger-doc h1 { font-size: 1.25rem; margin: 0 0 8px; }
    .ledger-doc .actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 16px; align-items: center; }
    .ledger-doc .meta { margin: 0 0 14px; font-size: 13px; }
    .ledger-doc .meta dt { font-weight: 700; }
    .ledger-doc .meta dd { margin: 0 0 6px; }
    .ledger-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 720px; }
    .ledger-table th, .ledger-table td { padding: 8px; border-bottom: 1px solid var(--line2, #e5e5e5); }
    .ledger-table th { text-align: right; color: var(--muted); font-size: 11px; text-transform: uppercase; }
    .ledger-table th:first-child, .ledger-table th:nth-child(2) { text-align: left; }
    .ledger-table td:first-child, .ledger-table td:nth-child(2) { text-align: left; }
    @media print {
      .no-print { display: none !important; }
      .ledger-doc { padding: 0; }
      body { background: #fff; }
    }
  </style>
</head>
<body style="background: var(--bg, #f4f6f5); min-height: 100vh; margin: 0;">
  <div class="ledger-doc">
    <div class="no-print actions">
      <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/loans/' . $id, ENT_QUOTES, 'UTF-8') ?>">Back to loan</a>
      <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/loans/' . $id . '/ledger-export', ENT_QUOTES, 'UTF-8') ?>">Download CSV</a>
      <button type="button" class="btn primary" onclick="window.print()">Print</button>
    </div>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
    <dl class="meta" style="display:grid; grid-template-columns: auto 1fr; gap: 4px 16px; align-items: baseline;">
      <dt>Customer</dt><dd><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></dd>
      <dt>Status</dt><dd><?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?></dd>
      <dt>Principal</dt><dd><?= $fmt((float) ($loan['principal_amount'] ?? 0)) ?></dd>
      <dt>Outstanding</dt><dd><?= $fmt($outstanding) ?></dd>
    </dl>
    <div style="overflow:auto;">
      <table class="ledger-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Opening</th>
            <th>Rate %</th>
            <th>Interest</th>
            <th>Total due</th>
            <th>Paid</th>
            <th>Closing</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($ledger) === 0): ?>
            <tr><td colspan="8" style="padding: 20px; color: var(--muted);">No lines yet.</td></tr>
          <?php else: ?>
            <?php foreach ($ledger as $row): ?>
              <tr>
                <td style="font-weight:650;"><?= (int) ($row['line_no'] ?? 0) ?></td>
                <td><?= htmlspecialchars((string) ($row['period_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align:right;"><?= $fmt((float) ($row['opening_balance'] ?? 0)) ?></td>
                <td style="text-align:right;"><?= htmlspecialchars((string) ($row['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="text-align:right;"><?= $fmt((float) ($row['interest_amount'] ?? 0)) ?></td>
                <td style="text-align:right; font-weight:650;"><?= $fmt((float) ($row['amount_due'] ?? 0)) ?></td>
                <td style="text-align:right;"><?= isset($row['payment_amount']) && $row['payment_amount'] !== null ? $fmt((float) $row['payment_amount']) : '—' ?></td>
                <td style="text-align:right; font-weight:700;"><?= $fmt((float) ($row['closing_balance'] ?? 0)) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
