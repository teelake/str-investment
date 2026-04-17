<?php
declare(strict_types=1);
/** @var array<string, mixed> $user */
/** @var list<string> $assignableRoles */
/** @var array<string, string> $permissionCatalog */
/** @var list<string> $extraGrantKeys */
/** @var bool $canEditExtraGrants */
/** @var mixed $error */
$basePath = Request::basePath();
$permissionCatalog = $permissionCatalog ?? [];
$extraGrantKeys = $extraGrantKeys ?? [];
$canEditExtraGrants = !empty($canEditExtraGrants);
$id = (int) ($user['id'] ?? 0);
$err = is_string($error) ? $error : '';
$roleNow = (string) ($user['role_key'] ?? '');
$active = (int) ($user['is_active'] ?? 0) === 1;
?>
<div class="container" style="padding:0; max-width: 520px;">
  <a href="<?= htmlspecialchars($basePath . '/settings/users', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Console users</a>
  <h1 style="font-size: var(--h2); margin: 12px 0 8px;">Edit user</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">User #<?= $id ?></p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/settings/users/' . $id . '/update', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Email
        <input name="email" type="email" required maxlength="<?= (int) InputValidate::EMAIL_MAX ?>" value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Full name (optional)
        <input name="full_name" maxlength="<?= (int) InputValidate::PERSON_NAME_MAX ?>" value="<?= htmlspecialchars((string) ($user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Phone (optional)
        <input name="phone" type="tel" maxlength="32" autocomplete="tel" value="<?= htmlspecialchars((string) ($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <p style="margin: -6px 0 0; font-size: 12px; color: var(--muted2); line-height: 1.45;">At least 8 digits if provided.</p>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Role
        <select name="role_key" required style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; font-size: 14px;">
          <?php foreach ($assignableRoles as $rk): ?>
            <option value="<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>"<?= $rk === $roleNow ? ' selected' : '' ?>><?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="display:flex; gap: 10px; align-items: flex-start; font-size: 14px; cursor: pointer;">
        <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?> style="margin-top: 3px;" />
        <span><strong>Active</strong><br /><span style="color: var(--muted); font-size: 13px;">Inactive users cannot sign in.</span></span>
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        New password (optional)
        <input name="password" type="password" minlength="10" maxlength="<?= (int) InputValidate::PASSWORD_MAX_BYTES ?>" autocomplete="new-password" placeholder="Leave blank to keep current"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <?php if ($canEditExtraGrants): ?>
        <div style="border-top:1px solid var(--line2); margin-top:4px; padding-top:16px;">
          <div style="font-size:13px; font-weight:800; margin-bottom:6px;">Extra permissions</div>
          <p style="margin:0 0 12px; font-size:12px; color:var(--muted2); line-height:1.45;">Merged at sign-in on top of the user’s role (from <strong>Roles</strong>). Use for one-off access without changing their role.</p>
          <div style="max-height:240px; overflow:auto; border:1px solid var(--line2); border-radius:12px; padding:12px; display:grid; gap:10px; background:rgba(13,15,18,.02);">
            <?php foreach ($permissionCatalog as $pkey => $plabel): ?>
              <label style="display:flex; gap:10px; align-items:flex-start; font-size:13px; cursor:pointer;">
                <input type="checkbox" name="extra_grants[]" value="<?= htmlspecialchars($pkey, ENT_QUOTES, 'UTF-8') ?>"<?= in_array($pkey, $extraGrantKeys, true) ? ' checked' : '' ?> style="margin-top:3px;" />
                <span><span style="font-family:ui-monospace,monospace; font-size:12px; font-weight:650;"><?= htmlspecialchars($pkey, ENT_QUOTES, 'UTF-8') ?></span><br /><span style="color:var(--muted); font-size:12px;"><?= htmlspecialchars($plabel, ENT_QUOTES, 'UTF-8') ?></span></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      <div style="display:flex; gap: 10px; flex-wrap: wrap;">
        <button type="submit" class="btn primary">Save changes</button>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/settings/users', ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
