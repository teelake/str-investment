<?php
declare(strict_types=1);
/** @var list<string> $assignableRoles */
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
?>
<div class="container" style="padding:0; max-width: 520px;">
  <a href="<?= htmlspecialchars($basePath . '/settings/users', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Console users</a>
  <h1 style="font-size: var(--h2); margin: 12px 0 8px;">Add user</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">New users sign in with email and password. Role selects the default permission set.</p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/settings/users', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Email
        <input name="email" type="email" required maxlength="190" autocomplete="off"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Full name (optional)
        <input name="full_name" maxlength="190"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Phone (optional)
        <input name="phone" type="tel" maxlength="32" autocomplete="tel"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <p style="margin: -6px 0 0; font-size: 12px; color: var(--muted2); line-height: 1.45;">At least 8 digits if provided.</p>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Role
        <select name="role_key" required style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; font-size: 14px;">
          <?php foreach ($assignableRoles as $rk): ?>
            <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Password (min 10 characters)
        <input name="password" type="password" required minlength="10" autocomplete="new-password"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <div style="display:flex; gap: 10px; flex-wrap: wrap;">
        <button type="submit" class="btn primary">Create user</button>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/settings/users', ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
