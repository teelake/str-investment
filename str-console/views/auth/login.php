<?php
declare(strict_types=1);
/** @var bool $devLogin */
/** @var list<string> $roles */
/** @var string $next */
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
?>
<div style="max-width: 420px; margin: 0 auto;">
  <h1 style="font-size: var(--h2); margin: 0 0 8px;">Sign in</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">STR Console — staff access only.</p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>" style="display: grid; gap: 14px;">
      <input type="hidden" name="next" value="<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8') ?>" />

      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Work email
        <input name="email" type="email" required autocomplete="username"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>

      <?php if ($devLogin): ?>
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          Role (demo only)
          <select name="role" required
            style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);">
            <?php foreach ($roles as $r): ?>
              <option value="<?= htmlspecialchars($r, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <p style="margin:0; font-size: 12px; color: var(--muted2);">Demo login: set environment variable <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">STR_CONSOLE_DEV_LOGIN=1</code>. Replace with real authentication before production.</p>
      <?php else: ?>
        <p style="margin:0; font-size: 13px; color: var(--muted);">Password-based sign-in is not wired yet. Enable demo login for local testing or implement your auth in <code style="background: rgba(13,15,18,.06); padding: 2px 6px; border-radius: 8px;">AuthController::login()</code>.</p>
      <?php endif; ?>

      <button type="submit" class="btn primary" style="justify-content: center; margin-top: 4px;">Continue</button>
    </form>
  </div>
</div>
