<?php
declare(strict_types=1);
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int} $pagination */
/** @var list<string> $entityTypes */
/** @var string $filterType */
/** @var string|null $dbError */
$dbError = $dbError ?? null;
$basePath = Request::basePath();
$rows = $pagination['rows'];
$total = (int) $pagination['total'];
$page = (int) $pagination['page'];
$perPage = (int) $pagination['per_page'];
$pages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

$auditQuery = static function (int $p, string $type): string {
    $q = ['page' => (string) $p];
    if ($type !== '') {
        $q['type'] = $type;
    }
    return '?' . http_build_query($q);
};
?>
<div class="container" style="padding:0">
  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="display:flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 20px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;">Audit log</h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;"><?= (int) $total ?> entries · page <?= (int) $page ?> of <?= max(1, $pages) ?></p>
    </div>
    <form method="get" action="<?= htmlspecialchars($basePath . '/audit', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: center;">
      <label for="audit-type" style="font-size: 13px; color: var(--muted);">Entity type</label>
      <select id="audit-type" name="type" onchange="this.form.submit()" style="padding: 10px 12px; border: 1px solid var(--line2); border-radius: var(--radius); font-size: 14px; background: var(--card); color: inherit; min-width: 180px;">
        <option value="">All types</option>
        <?php foreach ($entityTypes as $t): ?>
          <option value="<?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>" <?= $filterType === $t ? ' selected' : '' ?>><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
      <noscript><button type="submit" class="btn ghost" style="font-size: 13px;">Apply</button></noscript>
    </form>
  </div>

  <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
          <th style="padding: 12px 14px;">When</th>
          <th style="padding: 12px 14px;">Actor</th>
          <th style="padding: 12px 14px;">Action</th>
          <th style="padding: 12px 14px;">Entity</th>
          <th style="padding: 12px 14px;">Details</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows) === 0): ?>
          <tr>
            <td colspan="5" style="padding: 28px 14px; color: var(--muted);">No audit entries yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
            $payload = $r['payload_json'] ?? null;
            $payloadStr = '';
            if (is_string($payload) && $payload !== '') {
                $payloadStr = $payload;
            } elseif ($payload !== null && !is_string($payload)) {
                $payloadStr = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $payloadPreview = $payloadStr;
            if (mb_strlen($payloadPreview) > 120) {
                $payloadPreview = mb_substr($payloadPreview, 0, 117) . '…';
            }
            ?>
            <tr style="border-bottom: 1px solid var(--line2); vertical-align: top;">
              <td style="padding: 12px 14px; color: var(--muted); white-space: nowrap;"><?= htmlspecialchars((string) ($r['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 12px 14px;"><?= $r['actor_user_id'] === null ? '—' : (string) (int) $r['actor_user_id'] ?></td>
              <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 12px 14px;">
                <span style="color: var(--muted);"><?= htmlspecialchars((string) ($r['entity_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (isset($r['entity_id']) && $r['entity_id'] !== null && $r['entity_id'] !== ''): ?>
                  <span style="margin-left: 6px;">#<?= htmlspecialchars((string) $r['entity_id'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </td>
              <td style="padding: 12px 14px; color: var(--muted); font-size: 13px; word-break: break-word; max-width: 360px;">
                <?= $payloadPreview === '' ? '—' : htmlspecialchars($payloadPreview, ENT_QUOTES, 'UTF-8') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
    <div style="display:flex; gap: 10px; justify-content: flex-end; margin-top: 16px; flex-wrap: wrap;">
      <?php
      $prev = max(1, $page - 1);
      $next = min($pages, $page + 1);
      ?>
      <a class="btn ghost" style="font-size: 13px; padding: 10px 14px;" href="<?= htmlspecialchars($basePath . '/audit' . $auditQuery($prev, $filterType), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
      <a class="btn ghost" style="font-size: 13px; padding: 10px 14px;" href="<?= htmlspecialchars($basePath . '/audit' . $auditQuery($next, $filterType), ENT_QUOTES, 'UTF-8') ?>">Next</a>
    </div>
  <?php endif; ?>
</div>
