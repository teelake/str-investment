<?php
declare(strict_types=1);
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, per_page: int} $pagination */
/** @var string|null $dbError */
$dbError = $dbError ?? null;
$basePath = Request::basePath();
$rows = $pagination['rows'];
$total = (int) $pagination['total'];
$page = (int) $pagination['page'];
$perPage = (int) $pagination['per_page'];
$pages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
?>
<div class="container" style="padding:0">
  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="display:flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 20px;">
    <div>
      <h1 style="font-size: var(--h2); margin: 0 0 6px;">Customers</h1>
      <p style="color: var(--muted); margin: 0; font-size: 14px;"><?= (int) $total ?> total · page <?= (int) $page ?> of <?= max(1, $pages) ?></p>
    </div>
    <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'customers.create')): ?>
      <a class="btn primary" href="<?= htmlspecialchars($basePath . '/customers/create', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 14px;">Register customer</a>
    <?php endif; ?>
  </div>

  <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
          <th style="padding: 12px 14px;">Name</th>
          <th style="padding: 12px 14px;">Phone</th>
          <th style="padding: 12px 14px;">Assigned</th>
          <th style="padding: 12px 14px;">Created</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($rows) === 0): ?>
          <tr>
            <td colspan="4" style="padding: 28px 14px; color: var(--muted);">No customers in your scope yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr style="border-bottom: 1px solid var(--line2);">
              <td style="padding: 12px 14px; font-weight: 650;">
                <a href="<?= htmlspecialchars($basePath . '/customers/' . (int) ($r['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: underline; text-underline-offset: 4px;">
                  <?= htmlspecialchars((string) ($r['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>
              <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding: 12px 14px; color: var(--muted);"><?= $r['assigned_user_id'] === null ? '—' : (string) (int) $r['assigned_user_id'] ?></td>
              <td style="padding: 12px 14px; color: var(--muted);"><?= htmlspecialchars((string) ($r['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
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
      <a class="btn ghost" style="font-size: 13px; padding: 10px 14px;" href="<?= htmlspecialchars($basePath . '/customers?page=' . $prev, ENT_QUOTES, 'UTF-8') ?>">Previous</a>
      <a class="btn ghost" style="font-size: 13px; padding: 10px 14px;" href="<?= htmlspecialchars($basePath . '/customers?page=' . $next, ENT_QUOTES, 'UTF-8') ?>">Next</a>
    </div>
  <?php endif; ?>
</div>
