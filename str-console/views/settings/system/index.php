<?php
declare(strict_types=1);
/** @var string $maintenanceNotice */
/** @var string $phpVersion */
/** @var bool $dbReady */
/** @var mixed $flash */
/** @var mixed $error */
$basePath = Request::basePath();
$ok = is_string($flash) ? $flash : '';
$err = is_string($error) ? $error : '';
?>
<div class="container" style="padding:0; max-width: 640px;">
  <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Dashboard</a>
  <h1 style="font-size: var(--h2); margin: 12px 0 8px;">System</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">Platform-wide options (system administrators).</p>

  <?php if ($ok !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2); margin-bottom: 20px;">
    <h2 style="font-size: 14px; margin: 0 0 12px; font-weight: 800;">Environment</h2>
    <ul style="margin:0; padding-left:18px; color:var(--muted); font-size:14px; line-height:1.6;">
      <li>PHP <?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></li>
      <li>Database <?= $dbReady ? 'configured' : 'not configured' ?></li>
    </ul>
  </div>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <h2 style="font-size: 14px; margin: 0 0 8px; font-weight: 800;">Maintenance notice</h2>
    <p style="margin: 0 0 16px; font-size: 13px; color: var(--muted2);">Optional banner shown to all signed-in users (plain text).</p>
    <form method="post" action="<?= htmlspecialchars($basePath . '/settings/system', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Message
        <textarea name="maintenance_notice" rows="4" maxlength="2000" placeholder="e.g. Scheduled maintenance tonight 10pm–11pm."
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); resize: vertical;"><?= htmlspecialchars($maintenanceNotice, ENT_QUOTES, 'UTF-8') ?></textarea>
      </label>
      <button type="submit" class="btn primary" style="justify-self:start;">Save</button>
    </form>
  </div>
</div>
