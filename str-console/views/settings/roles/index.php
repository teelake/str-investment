<?php
declare(strict_types=1);
/** @var array<string, string> $catalog */
/** @var list<string> $editableRoles */
/** @var array<string, list<string>> $grantsByRole */
$basePath = Request::basePath();
$flash = Request::query('flash');
$qerr = Request::query('error');
$ok = is_string($flash) ? $flash : '';
$err = is_string($qerr) ? $qerr : '';

/** @var array<string, array<string, bool>> $checked */
$checked = [];
foreach ($editableRoles as $rk) {
    $checked[$rk] = array_fill_keys(str_console_expand_grants($grantsByRole[$rk] ?? []), true);
}

$permRows = [];
foreach ($catalog as $key => $desc) {
    if ($key === 'auth.session') {
        continue;
    }
    $permRows[] = ['key' => $key, 'desc' => $desc];
}
?>
<div class="container" style="padding:0">
  <?php if ($ok !== ''): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="margin-bottom: 20px;">
    <h1 style="font-size: var(--h2); margin: 0;">Role permissions</h1>
  </div>

  <form method="post" action="<?= htmlspecialchars($basePath . '/settings/roles', ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom: 24px;">
    <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
    <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2); margin-bottom: 16px;">
      <table style="width:100%; border-collapse: collapse; font-size: 13px; min-width: 720px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 11px; text-transform: uppercase;">
            <th style="padding: 12px 14px; min-width: 220px;">Permission</th>
            <?php foreach ($editableRoles as $rk): ?>
              <th style="padding: 12px 10px; text-align: center;"><?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($permRows as $pr): ?>
            <?php $pk = $pr['key']; ?>
            <tr style="border-bottom: 1px solid var(--line2); vertical-align: top;">
              <td style="padding: 10px 14px;">
                <code style="font-size: 11px;"><?= htmlspecialchars($pk, ENT_QUOTES, 'UTF-8') ?></code>
                <div style="color: var(--muted2); font-size: 12px; margin-top: 4px;"><?= htmlspecialchars($pr['desc'], ENT_QUOTES, 'UTF-8') ?></div>
              </td>
              <?php foreach ($editableRoles as $rk): ?>
                <td style="padding: 10px; text-align: center;">
                  <input type="checkbox" name="grants_<?= htmlspecialchars($rk, ENT_QUOTES, 'UTF-8') ?>[]" value="<?= htmlspecialchars($pk, ENT_QUOTES, 'UTF-8') ?>"
                    <?= isset($checked[$rk][$pk]) ? 'checked' : '' ?> title="<?= htmlspecialchars($pk, ENT_QUOTES, 'UTF-8') ?>" />
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="display:flex; flex-wrap: wrap; gap: 10px; align-items: center;">
      <button type="submit" class="btn primary">Save role permissions</button>
    </div>
  </form>

  <form method="post" action="<?= htmlspecialchars($basePath . '/settings/roles', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Remove all custom role bundles and restore code defaults?');">
    <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
    <input type="hidden" name="reset_defaults" value="1" />
    <button type="submit" class="btn ghost" style="color: #7f1d1d;">Reset admin, manager &amp; credit officer to code defaults</button>
  </form>
</div>
