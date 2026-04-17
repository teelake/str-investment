<?php
declare(strict_types=1);
/** @var array{type: string, imported: int, errors: list<array{line: int, message: string}>}|null $flash */
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
?>
<div class="container" style="padding:0; max-width: 640px;">
  <a href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Loans</a>
  <h1 style="font-size: var(--h2); margin: 12px 0 8px;">Bulk import — loans</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">Creates <strong>draft</strong> loans (same as manual create). Customer must exist and be in your scope.</p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (is_array($flash) && ($flash['type'] ?? '') === 'loans'): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      Imported <?= (int) ($flash['imported'] ?? 0) ?> loan(s).
      <?php if (!empty($flash['errors'])): ?>
        <span style="display:block; margin-top:8px; color: #7a4a00;">Some rows failed — see below.</span>
      <?php endif; ?>
    </div>
    <?php if (!empty($flash['errors'])): ?>
      <ul style="margin: 0 0 22px; padding-left: 18px; font-size: 14px; color: var(--ink);">
        <?php foreach ($flash['errors'] as $e): ?>
          <li>Line <?= (int) ($e['line'] ?? 0) ?>: <?= htmlspecialchars((string) ($e['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endif; ?>

  <div style="background: rgba(0,0,0,.03); border: 1px solid var(--line2); border-radius: var(--radius); padding: 16px; margin-bottom: 20px; font-size: 13px;">
    <strong style="display:block; margin-bottom:8px;">Expected columns</strong>
    <code style="display:block; white-space: pre-wrap; word-break: break-all;">customer_id,loan_product_id,principal_amount</code>
    <p style="margin: 10px 0 0; color: var(--muted2);">Use <code>principal</code> as an alias for <code>principal_amount</code>. Rate and term come from the active product.</p>
    <p style="margin: 12px 0 0;">
      <a class="btn ghost" style="font-size: 13px; padding: 10px 14px;" href="<?= htmlspecialchars($basePath . '/downloads/loans-import-template.csv', ENT_QUOTES, 'UTF-8') ?>" download>Download CSV template</a>
    </p>
  </div>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/bulk-upload/loans', ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" style="display:grid; gap: 14px;">
      <label style="font-size: 13px; font-weight: 650; color: var(--muted);">
        CSV file (max 2 MB)
        <input type="file" name="csv" accept=".csv,text/csv" required style="margin-top: 6px; width: 100%;" />
      </label>
      <button type="submit" class="btn primary">Import</button>
    </form>
  </div>
</div>
