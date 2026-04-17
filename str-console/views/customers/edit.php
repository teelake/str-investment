<?php
declare(strict_types=1);
/** @var array<string, mixed> $customer */
/** @var list<array{id: int, email: string, full_name: string|null}> $assignUsers */
/** @var bool $canAssign */
/** @var mixed $error */
$basePath = Request::basePath();
$id = (int) ($customer['id'] ?? 0);
$err = is_string($error) ? $error : '';
$aid = $customer['assigned_user_id'] ?? null;
$aidVal = $aid === null || $aid === '' ? '' : (string) (int) $aid;
?>
<div class="console-form-page">
  <div class="container" style="padding:0;">
  <a href="<?= htmlspecialchars($basePath . '/customers/' . $id, ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Back to customer</a>
  <h1 style="font-size: var(--h2); margin: 12px 0 8px;">Edit customer</h1>
  <p style="color: var(--muted); margin: 0 0 22px;">Update profile details<?= $canAssign ? ' and assignment.' : '.' ?></p>

  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow2);">
    <form method="post" action="<?= htmlspecialchars($basePath . '/customers/' . $id . '/update', ENT_QUOTES, 'UTF-8') ?>" style="display:grid; gap: 14px;">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Full name
        <input name="full_name" required maxlength="<?= (int) InputValidate::PERSON_NAME_MAX ?>" value="<?= htmlspecialchars((string) ($customer['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Phone
        <input name="phone" required maxlength="32" value="<?= htmlspecialchars((string) ($customer['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink);" />
      </label>
      <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
        Address
        <textarea name="address" rows="3"
          style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); resize: vertical;"><?= htmlspecialchars((string) ($customer['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      </label>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          NIN (optional)
          <input name="nin" type="text" inputmode="numeric" autocomplete="off" maxlength="11" pattern="[0-9]{0,11}"
            title="11 digits, or leave blank"
            value="<?= htmlspecialchars((string) ($customer['nin'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
            style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); font-family: ui-monospace, monospace;" />
          <span style="font-size: 12px; font-weight: 500; color: var(--muted2);">11 digits (NIMC)</span>
        </label>
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          BVN (optional)
          <input name="bvn" type="text" inputmode="numeric" autocomplete="off" maxlength="11" pattern="[0-9]{0,11}"
            title="11 digits, or leave blank"
            value="<?= htmlspecialchars((string) ($customer['bvn'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
            style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); font-family: ui-monospace, monospace;" />
          <span style="font-size: 12px; font-weight: 500; color: var(--muted2);">11 digits (CBN)</span>
        </label>
      </div>
      <?php if ($canAssign): ?>
        <label style="display:grid; gap:6px; font-size: 13px; font-weight: 650; color: var(--muted);">
          Assigned console user
          <select name="assigned_user_id" style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); font-size: 14px;">
            <option value="">— Unassigned —</option>
            <?php foreach ($assignUsers as $u): ?>
              <?php
              $uid = (int) ($u['id'] ?? 0);
              $label = (string) ($u['email'] ?? '');
              $fn = $u['full_name'] ?? null;
              if (is_string($fn) && $fn !== '') {
                  $label = $fn . ' · ' . $label;
              }
              ?>
              <option value="<?= $uid ?>"<?= $aidVal !== '' && (int) $aidVal === $uid ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <p style="margin:0; font-size: 12px; color: var(--muted2);">Only active console users are listed.</p>
      <?php endif; ?>
      <div style="display:flex; gap: 10px; flex-wrap: wrap; margin-top: 6px;">
        <button type="submit" class="btn primary">Save changes</button>
        <a class="btn ghost" href="<?= htmlspecialchars($basePath . '/customers/' . $id, ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
      </div>
    </form>
  </div>
  </div>
</div>
