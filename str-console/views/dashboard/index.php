<?php
declare(strict_types=1);
/** @var array<string, mixed>|null $user */
/** @var int|null $customerCount */
/** @var string|null $dbError */
$email = is_array($user) ? (string) ($user['email'] ?? '') : '';
$role = is_array($user) ? (string) ($user['role'] ?? '') : '';
$basePath = Request::basePath();
?>
<div class="container" style="padding:0">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">Dashboard</h1>
  <p style="color: var(--muted); margin: 0 0 24px;">Signed in as <strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong> · <strong><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></strong></p>

  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 20px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
      <div style="margin-top:10px; font-size: 13px; color: var(--muted);">
        Import <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">str-console/database/schema.sql</code>, then run
        <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">php str-console/bin/seed-admin.php</code>.
      </div>
    </div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="color: var(--muted); font-size: 13px; font-weight: 600;">Customers (your scope)</div>
      <div style="font-size: 28px; font-weight: 800; margin-top: 8px;">
        <?= $customerCount === null ? '—' : (int) $customerCount ?>
      </div>
      <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'customers.index')): ?>
        <a href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" class="btn ghost" style="margin-top: 14px; font-size: 13px; padding: 10px 14px; display: inline-flex;">View customers</a>
      <?php endif; ?>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="color: var(--muted); font-size: 13px; font-weight: 600;">Active loans</div>
      <div style="font-size: 28px; font-weight: 800; margin-top: 8px;">—</div>
    </div>
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="color: var(--muted); font-size: 13px; font-weight: 600;">Outstanding</div>
      <div style="font-size: 28px; font-weight: 800; margin-top: 8px;">—</div>
    </div>
  </div>

  <p style="color: var(--muted2); margin-top: 28px; font-size: 14px;">Loan portfolio KPIs will appear here once the loans module is connected.</p>
</div>
