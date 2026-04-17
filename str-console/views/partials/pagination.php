<?php
declare(strict_types=1);
/** @var string $basePath */
/** @var string $path */
/** @var array<string, string|int|float|bool> $query */
/** @var int $page */
/** @var int $total */
/** @var int $perPage */
$pageParam = $pageParam ?? 'page';
$perPage = max(1, $perPage);
$pages = $total > 0 ? (int) max(1, ceil($total / $perPage)) : 1;
$page = min(max(1, $page), $pages);
if ($total <= 0) {
    return;
}
$makeUrl = static function (int $p) use ($basePath, $path, $query, $pageParam): string {
    $q = array_merge($query, [$pageParam => $p]);
    $q = array_filter(
        $q,
        static fn (mixed $v): bool => $v !== null && $v !== ''
    );

    return $basePath . $path . '?' . http_build_query($q);
};
$prev = max(1, $page - 1);
$next = min($pages, $page + 1);
?>
<div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-top:16px;">
  <p style="margin:0; font-size:13px; color:var(--muted);">
    <?= (int) $total ?> result<?= $total === 1 ? '' : 's' ?> · page <?= (int) $page ?> of <?= (int) $pages ?>
  </p>
  <?php if ($pages > 1): ?>
    <div style="display:flex; flex-wrap:wrap; gap:8px;">
      <a class="btn ghost" style="font-size:13px; padding:10px 14px;" href="<?= htmlspecialchars($makeUrl($prev), ENT_QUOTES, 'UTF-8') ?>"<?= $page <= 1 ? ' aria-disabled="true" style="pointer-events:none; opacity:.5;"' : '' ?>>Previous</a>
      <a class="btn ghost" style="font-size:13px; padding:10px 14px;" href="<?= htmlspecialchars($makeUrl($next), ENT_QUOTES, 'UTF-8') ?>"<?= $page >= $pages ? ' aria-disabled="true" style="pointer-events:none; opacity:.5;"' : '' ?>>Next</a>
    </div>
  <?php endif; ?>
</div>
