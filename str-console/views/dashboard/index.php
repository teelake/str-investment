<?php
declare(strict_types=1);
/** @var array<string, mixed>|null $user */
$email = is_array($user) ? (string) ($user['email'] ?? '') : '';
$role = is_array($user) ? (string) ($user['role'] ?? '') : '';
?>
<div class="container" style="padding:0">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">Dashboard</h1>
  <p style="color: var(--muted); margin: 0 0 24px;">Signed in as <strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong> · <strong><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></strong></p>

  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow2);">
      <div style="color: var(--muted); font-size: 13px; font-weight: 600;">Total customers</div>
      <div style="font-size: 28px; font-weight: 800; margin-top: 8px;">—</div>
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

  <p style="color: var(--muted2); margin-top: 28px; font-size: 14px;">Connect this dashboard to your database to populate KPIs. Routing and authorization are live.</p>
</div>
