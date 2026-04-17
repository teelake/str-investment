<?php
declare(strict_types=1);
/** @var array<string, mixed> $user */
/** @var mixed $error */
/** @var mixed $flash */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
$ok = is_string($flash) ? $flash : '';
$id = (int) ($user['id'] ?? 0);
?>
<div class="console-form-page">
  <div class="container" style="padding:0;">
    <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Dashboard</a>
    <h1 style="font-size: var(--h2); margin: 12px 0 8px;">Your profile</h1>
    <p style="color: var(--muted); margin: 0 0 22px;">Account #<?= $id ?> · <?= htmlspecialchars((string) ($user['role_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <?php if ($ok !== ''): ?>
      <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($err !== ''): ?>
      <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
      <form method="post" action="<?= htmlspecialchars($basePath . '/account/profile', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
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
        <p style="margin: 0; font-size: 12px; color: var(--muted2); line-height: 1.45;">If set, use at least 8 digits (spaces and symbols are allowed).</p>
        <button type="submit" class="btn primary">Save profile</button>
      </form>
      <p style="margin: 16px 0 0; font-size: 13px; color: var(--muted2);">Role changes and org-wide access are managed by an administrator under <strong>Users</strong>.</p>
    </div>
  </div>
</div>
