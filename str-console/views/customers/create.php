<?php
declare(strict_types=1);
/** @var mixed $error */
$basePath = Request::basePath();
$err = is_string($error) ? $error : '';
?>
<div class="console-form-page">
  <div class="container" style="padding:0;">
    <h1 style="font-size: var(--h2); margin: 0 0 8px;">Register customer</h1>
    <p style="color: var(--muted); margin: 0 0 22px;">Capture core KYC fields. Upload supporting documents from the customer profile after save.</p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Full name
        <input name="full_name" required maxlength="<?= (int) InputValidate::PERSON_NAME_MAX ?>"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Phone
        <input name="phone" required maxlength="32"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Address
        <textarea name="address" rows="3"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); resize: vertical;"></textarea>
      </label>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          NIN (optional)
          <input name="nin" type="text" inputmode="numeric" autocomplete="off" maxlength="11" pattern="[0-9]{0,11}"
            title="11 digits, or leave blank"
            style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); font-family: ui-monospace, monospace;" />
          <span style="font-size: 12px; font-weight: 500; color: var(--muted2);">Nigeria NIMC: 11 digits</span>
        </label>
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          BVN (optional)
          <input name="bvn" type="text" inputmode="numeric" autocomplete="off" maxlength="11" pattern="[0-9]{0,11}"
            title="11 digits, or leave blank"
            style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); font-family: ui-monospace, monospace;" />
          <span style="font-size: 12px; font-weight: 500; color: var(--muted2);">Nigeria CBN: 11 digits</span>
        </label>
      </div>
      <div style="display:flex; gap: 10px; flex-wrap: wrap; margin-top: 6px;">
        <button type="submit" class="btn primary">Save customer</button>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
      </div>
    </form>
  </div>
  </div>
</div>
