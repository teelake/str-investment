<?php
declare(strict_types=1);
/** @var bool $devLogin */
/** @var bool $dbReady */
/** @var list<string> $roles */
/** @var string $next */
/** @var mixed $error */
/** @var mixed $sent */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
$sentFlash = is_string($sent) ? $sent : '';
?>
<?php if ($sentFlash === 'reset'): ?>
  <div class="auth-alert auth-alert--ok">Your password was updated. Sign in with your new password.</div>
<?php endif; ?>

<?php if ($err !== ''): ?>
  <div class="auth-alert auth-alert--error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>" class="auth-form">
  <input type="hidden" name="next" value="<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8') ?>" />
  <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
  <?php require STR_CONSOLE_ROOT . '/views/partials/honeypot.php'; ?>

  <div class="auth-field">
    <label for="login-email">Work email</label>
    <input id="login-email" name="email" type="email" required autocomplete="username" inputmode="email" />
  </div>

  <?php if ($devLogin): ?>
    <div class="auth-field">
      <label for="login-role">Role (demo only)</label>
      <select id="login-role" name="role" required>
        <option value="" selected disabled>Select a role…</option>
        <?php foreach ($roles as $r): ?>
          <option value="<?= htmlspecialchars($r, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($r, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <p class="auth-dev-hint">Demo mode: set <code>STR_CONSOLE_DEV_LOGIN=1</code>. Use real authentication and email before production.</p>
  <?php elseif ($dbReady): ?>
    <div class="auth-field">
      <label for="login-password">Password</label>
      <input id="login-password" name="password" type="password" required minlength="1" maxlength="<?= (int) InputValidate::PASSWORD_MAX_BYTES ?>" autocomplete="current-password" />
    </div>
    <div style="text-align:right; margin: -8px 0 8px;">
      <a href="<?= htmlspecialchars($basePath . '/forgot-password', ENT_QUOTES, 'UTF-8') ?>" style="font-size:13px; font-weight:650; color:var(--green2); text-decoration:underline; text-underline-offset:3px;">Forgot password?</a>
    </div>
  <?php else: ?>
    <p class="auth-dev-hint">Configure the database in <code>config/local.php</code> or enable <code>STR_CONSOLE_DEV_LOGIN=1</code> for demo access.</p>
  <?php endif; ?>

  <div class="auth-actions">
    <button type="submit" class="btn primary">Continue</button>
  </div>
</form>
