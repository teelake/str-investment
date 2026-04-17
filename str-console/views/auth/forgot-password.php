<?php
declare(strict_types=1);
/** @var bool $dbReady */
/** @var mixed $error */
/** @var mixed $sent */
/** @var string|null $devResetUrl */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
$sentOk = is_string($sent) && $sent === '1';
$devUrl = (isset($devResetUrl) && is_string($devResetUrl) && $devResetUrl !== '') ? $devResetUrl : null;
?>
<?php if ($sentOk): ?>
  <div class="auth-alert auth-alert--ok">
    If an account exists for that email, we’ve sent instructions. Check your inbox and spam folder. The link expires in one hour.
  </div>
  <p class="auth-foot" style="margin-top:0;"><a href="<?= htmlspecialchars($basePath . '/forgot-password', ENT_QUOTES, 'UTF-8') ?>">Use a different email</a></p>
<?php endif; ?>

<?php if ($err !== ''): ?>
  <div class="auth-alert auth-alert--error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($devUrl !== null): ?>
  <div class="auth-alert auth-alert--warn">
    <strong>Development mode:</strong> email is not configured. Use this link once:
    <a href="<?= htmlspecialchars($devUrl, ENT_QUOTES, 'UTF-8') ?>" style="word-break:break-all; color:inherit; font-weight:700;"><?= htmlspecialchars($devUrl, ENT_QUOTES, 'UTF-8') ?></a>
    <span style="display:block; margin-top:8px; font-size:12px; opacity:.9;">Set <code style="background:rgba(0,0,0,.06); padding:2px 6px; border-radius:6px;">STR_CONSOLE_MAIL_FROM</code> for real mail. Remove <code style="background:rgba(0,0,0,.06); padding:2px 6px; border-radius:6px;">STR_CONSOLE_DEV_RESET_LINK</code> in production.</span>
  </div>
<?php endif; ?>

<?php if (!$dbReady): ?>
  <p class="auth-dev-hint">Database is not configured. Password reset requires a working database.</p>
<?php elseif (!$sentOk): ?>
  <form method="post" action="<?= htmlspecialchars($basePath . '/forgot-password', ENT_QUOTES, 'UTF-8') ?>">
    <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
    <?php require STR_CONSOLE_ROOT . '/views/partials/honeypot.php'; ?>
    <div class="auth-field">
      <label for="forgot-email">Work email</label>
      <input id="forgot-email" name="email" type="email" required autocomplete="email" inputmode="email" />
    </div>
    <div class="auth-actions">
      <button type="submit" class="btn primary">Send reset link</button>
    </div>
  </form>
<?php endif; ?>

<p class="auth-foot" style="margin-top:20px;"><a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>">← Back to sign in</a></p>
