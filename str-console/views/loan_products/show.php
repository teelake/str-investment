<?php
declare(strict_types=1);
/** @var array<string, mixed> $product */
$basePath = Request::basePath();
$id = (int) ($product['id'] ?? 0);
$active = (int) ($product['is_active'] ?? 0) === 1;
?>
<div class="container" style="padding:0; max-width: 640px;">
  <a href="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Loan products</a>
  <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:16px; margin-top:12px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;"><?= htmlspecialchars((string) ($product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;">Product #<?= $id ?></p>
    </div>
    <span style="display:inline-flex; align-items:center; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:700; background:var(--green-soft); color:var(--green2);"><?= $active ? 'Active' : 'Retired' ?></span>
  </div>

  <div style="margin-top:24px; background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2); display:grid; gap:14px;">
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
      <div>
        <div style="font-size:11px; font-weight:650; color:var(--muted); text-transform:uppercase;">Rate (booked %)</div>
        <div style="font-size:20px; font-weight:800; margin-top:6px;"><?= htmlspecialchars((string) ($product['rate_percent'] ?? ''), ENT_QUOTES, 'UTF-8') ?>%</div>
      </div>
      <div>
        <div style="font-size:11px; font-weight:650; color:var(--muted); text-transform:uppercase;">Term (months)</div>
        <div style="font-size:20px; font-weight:800; margin-top:6px;"><?= (int) ($product['period_months'] ?? 0) ?></div>
      </div>
    </div>
    <p style="margin:0; font-size:13px; color:var(--muted2);">Rates and term are copied onto each loan when it is created.</p>
    <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loan_products.edit')): ?>
      <a class="btn primary" style="justify-self:start;" href="<?= htmlspecialchars($basePath . '/loan-products/' . $id . '/edit', ENT_QUOTES, 'UTF-8') ?>">Edit product</a>
    <?php endif; ?>
  </div>
</div>
