<?php
declare(strict_types=1);
/**
 * Row action icons: view (link), edit (optional link), danger (optional POST with CSRF).
 *
 * @var string $viewHref
 * @var string|null $editHref
 * @var array{action:string,confirm?:string,title?:string}|null $dangerPost
 */
$editHref = $editHref ?? null;
$dangerPost = $dangerPost ?? null;
$dangerTitle = is_array($dangerPost) ? (string) ($dangerPost['title'] ?? 'Deactivate') : '';
$dangerConfirm = is_array($dangerPost) ? (string) ($dangerPost['confirm'] ?? 'Continue?') : '';
$btnStyle = 'padding:6px 8px; min-width:0; line-height:0;';
?>
<div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
  <a class="btn ghost" style="<?= htmlspecialchars($btnStyle, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($viewHref, ENT_QUOTES, 'UTF-8') ?>" title="View" aria-label="View">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  </a>
  <?php if (is_string($editHref) && $editHref !== ''): ?>
    <a class="btn ghost" style="<?= htmlspecialchars($btnStyle, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($editHref, ENT_QUOTES, 'UTF-8') ?>" title="Edit" aria-label="Edit">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
      </svg>
    </a>
  <?php endif; ?>
  <?php if (is_array($dangerPost) && ($dangerPost['action'] ?? '') !== ''): ?>
    <form method="post" action="<?= htmlspecialchars((string) $dangerPost['action'], ENT_QUOTES, 'UTF-8') ?>" style="display:inline; margin:0;" onsubmit="return confirm(<?= htmlspecialchars(json_encode($dangerConfirm, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>);">
      <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
      <button type="submit" class="btn ghost" style="<?= htmlspecialchars($btnStyle, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($dangerTitle, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($dangerTitle, ENT_QUOTES, 'UTF-8') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
      </button>
    </form>
  <?php endif; ?>
</div>
