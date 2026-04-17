<?php
declare(strict_types=1);
/** @var mixed $error */
/** @var mixed $flash */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
$ok = is_string($flash) ? $flash : '';
?>
<div class="container" style="padding:0; max-width: 520px;">
  <a href="<?= htmlspecialchars($basePath . '/account/profile', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Your profile</a>
  <h1 style="font-size: var(--h2); margin: 12px 0 8px;">Password</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">Use at least 10 characters.</p>

  <?php if ($ok !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/account/password', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Current password
        <input name="current_password" type="password" required autocomplete="current-password"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        New password
        <input name="new_password" type="password" required minlength="10" autocomplete="new-password"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Confirm new password
        <input name="confirm_password" type="password" required minlength="10" autocomplete="new-password"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <button type="submit" class="btn primary">Update password</button>
    </form>
  </div>
</div>
