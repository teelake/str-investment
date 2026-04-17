<?php
declare(strict_types=1);
/** @var list<array<string, mixed>> $rows */
/** @var string|null $dbError */
$basePath = Request::basePath();
$dbError = $dbError ?? null;
$flash = Request::query('flash');
$qerr = Request::query('error');
$ok = is_string($flash) ? $flash : '';
$err = is_string($qerr) ? $qerr : '';
?>
<div class="container" style="padding:0">
  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($ok !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:20px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;">Console users</h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;">Sign-in accounts for STR Console. Each role loads the default permission bundle from code (same as at login). If you change someone’s role, they must <strong>sign out and sign in again</strong> to pick up the new access.</p>
    </div>
    <a class="btn primary" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/settings/users/create', ENT_QUOTES, 'UTF-8') ?>">Add user</a>
  </div>

  <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase;">
          <th style="padding: 12px 14px;">Email</th>
          <th style="padding: 12px 14px;">Name</th>
          <th style="padding: 12px 14px;">Role</th>
          <th style="padding: 12px 14px;">Status</th>
          <th style="padding: 12px 14px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows) === 0): ?>
          <tr><td colspan="5" style="padding: 28px 14px; color: var(--muted);">No users yet. Use the seed script or Add user.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
            $uid = (int) ($r['id'] ?? 0);
            $active = (int) ($r['is_active'] ?? 0) === 1;
            ?>
            <tr style="border-bottom: 1px solid var(--line2);">
              <td style="padding: 12px 14px; font-weight: 650;"><?= htmlspecialchars((string) ($r['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 12px 14px;"><code style="font-size: 12px;"><?= htmlspecialchars((string) ($r['role_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
              <td style="padding: 12px 14px;"><?= $active ? '<span style="color:var(--green2); font-weight:650;">Active</span>' : '<span style="color:var(--muted);">Inactive</span>' ?></td>
              <td style="padding: 12px 14px;">
                <a class="btn ghost" style="font-size: 13px; padding: 8px 12px;" href="<?= htmlspecialchars($basePath . '/settings/users/' . $uid . '/edit', ENT_QUOTES, 'UTF-8') ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
