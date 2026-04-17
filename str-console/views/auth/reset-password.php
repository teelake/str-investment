<?php
declare(strict_types=1);
/** @var bool $dbReady */
/** @var bool $invalid */
/** @var string $token */
/** @var string $csrf */
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
$invalid = !empty($invalid);
?>
<?php if (!$dbReady): ?>
  <div class="auth-alert auth-alert--error">Database is not configured.</div>
<?php elseif ($invalid): ?>
  <div class="auth-alert auth-alert--error">
    This reset link is invalid or has expired. Request a new link from the forgot password page.
  </div>
  <p class="auth-foot"><a href="<?= htmlspecialchars($basePath . '/forgot-password', ENT_QUOTES, 'UTF-8') ?>">Forgot password</a></p>
<?php else: ?>
  <?php if ($err !== ''): ?>
    <div class="auth-alert auth-alert--error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="post" action="<?= htmlspecialchars($basePath . '/reset-password', ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>" />
    <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
    <?php require STR_CONSOLE_ROOT . '/views/partials/honeypot.php'; ?>

    <div class="auth-field">
      <label for="reset-pw">New password</label>
      <input id="reset-pw" name="new_password" type="password" required minlength="<?= (int) InputValidate::PASSWORD_MIN_LENGTH ?>" maxlength="<?= (int) InputValidate::PASSWORD_MAX_BYTES ?>" autocomplete="new-password" />
    </div>
    <div class="auth-field">
      <label for="reset-pw2">Confirm password</label>
      <input id="reset-pw2" name="confirm_password" type="password" required minlength="<?= (int) InputValidate::PASSWORD_MIN_LENGTH ?>" maxlength="<?= (int) InputValidate::PASSWORD_MAX_BYTES ?>" autocomplete="new-password" />
    </div>
    <div class="auth-actions">
      <button type="submit" class="btn primary">Save new password</button>
    </div>
  </form>
  <p class="auth-foot" style="margin-top:20px;"><a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>">← Back to sign in</a></p>
<?php endif; ?>
