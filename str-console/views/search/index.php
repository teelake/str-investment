<?php
declare(strict_types=1);
/** @var string $q */
/** @var list<array<string, mixed>> $customers */
/** @var list<array<string, mixed>> $loans */
/** @var int $customers_total */
/** @var int $loans_total */
/** @var int $customers_page */
/** @var int $loans_page */
/** @var int $per_page */
/** @var string|null $dbError */
/** @var string|null $error */
$dbError = $dbError ?? null;
$error = $error ?? null;
$customers_total = (int) ($customers_total ?? 0);
$loans_total = (int) ($loans_total ?? 0);
$customers_page = (int) ($customers_page ?? 1);
$loans_page = (int) ($loans_page ?? 1);
$per_page = (int) ($per_page ?? SearchRepository::PER_PAGE);
$basePath = Request::basePath();
$hasQuery = trim($q) !== '';
?>
<div class="container" style="padding:0">
  <?php if (is_string($dbError) && $dbError !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <div style="margin-bottom: 20px;">
    <h1 style="font-size: var(--h2); margin: 0 0 6px;">Search</h1>
    <p style="color: var(--muted); margin: 0; font-size: 14px;">Search by name, phone (11-digit local or partial), NIN, BVN, customer #, loan #, or customer id on a loan. Results respect assignment and “view all” policies.</p>
  </div>

  <form method="get" action="<?= htmlspecialchars($basePath . '/search', ENT_QUOTES, 'UTF-8') ?>" style="display:flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 24px;">
    <label class="sr-only" for="search-q">Search</label>
    <input id="search-q" type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Type at least 2 characters…" autocomplete="off" style="flex: 1; min-width: 220px; padding: 12px 14px; border: 1px solid var(--line2); border-radius: var(--radius); font-size: 14px; background: var(--card); color: inherit;">
    <button type="submit" class="btn primary" style="font-size: 14px;">Search</button>
  </form>

  <?php if (is_string($error) && $error !== ''): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 14px 16px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if ($hasQuery && $error === null): ?>
    <div style="display: grid; gap: 28px;">
      <section>
        <h2 style="font-size: 16px; margin: 0 0 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em;">Customers</h2>
        <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
          <table style="width:100%; border-collapse: collapse; font-size: 14px;">
            <thead>
              <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
                <th style="padding: 12px 14px;">Name</th>
                <th style="padding: 12px 14px;">Phone</th>
                <th style="padding: 12px 14px;">Assigned</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($customers) === 0): ?>
                <tr>
                  <td colspan="3" style="padding: 28px 14px; color: var(--muted);">No matching customers.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($customers as $r): ?>
                  <tr style="border-bottom: 1px solid var(--line2);">
                    <td style="padding: 12px 14px; font-weight: 650;">
                      <a href="<?= htmlspecialchars($basePath . '/customers/' . (int) ($r['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: underline; text-underline-offset: 4px;">
                        <?= htmlspecialchars((string) ($r['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </td>
                    <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding: 12px 14px; color: var(--muted);"><?php
                $alabel = trim((string) ($r['assigned_user_label'] ?? ''));
                if ($alabel !== '') {
                    echo htmlspecialchars($alabel, ENT_QUOTES, 'UTF-8');
                } elseif (($r['assigned_user_id'] ?? null) !== null && $r['assigned_user_id'] !== '') {
                    echo 'Console user #' . (int) $r['assigned_user_id'];
                } else {
                    echo '—';
                }
              ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php
        $path = '/search';
        $pageParam = 'pc';
        $query = ['q' => $q, 'pl' => $loans_page];
        $page = $customers_page;
        $total = $customers_total;
        $perPage = $per_page;
        require STR_CONSOLE_ROOT . '/views/partials/pagination.php';
        ?>
      </section>

      <section>
        <h2 style="font-size: 16px; margin: 0 0 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em;">Loans</h2>
        <div style="overflow:auto; border: 1px solid var(--line2); border-radius: var(--radius); background: var(--card); box-shadow: var(--shadow2);">
          <table style="width:100%; border-collapse: collapse; font-size: 14px;">
            <thead>
              <tr style="text-align:left; border-bottom: 1px solid var(--line2); color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em;">
                <th style="padding: 12px 14px;">Loan</th>
                <th style="padding: 12px 14px;">Customer</th>
                <th style="padding: 12px 14px;">Status</th>
                <th style="padding: 12px 14px; text-align:right;">Principal</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($loans) === 0): ?>
                <tr>
                  <td colspan="4" style="padding: 28px 14px; color: var(--muted);">No matching loans.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($loans as $r): ?>
                  <tr style="border-bottom: 1px solid var(--line2);">
                    <td style="padding: 12px 14px; font-weight: 650;">
                      <a href="<?= htmlspecialchars($basePath . '/loans/' . (int) ($r['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: underline; text-underline-offset: 4px;">
                        #<?= (int) ($r['id'] ?? 0) ?>
                      </a>
                    </td>
                    <td style="padding: 12px 14px;">
                      <a href="<?= htmlspecialchars($basePath . '/customers/' . (int) ($r['customer_id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" style="color: inherit; text-decoration: underline; text-underline-offset: 4px;">
                        <?= htmlspecialchars((string) ($r['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                      </a>
                    </td>
                    <td style="padding: 12px 14px;"><?= htmlspecialchars((string) ($r['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td style="padding: 12px 14px; text-align:right;"><?= htmlspecialchars(number_format((float) ($r['principal_amount'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php
        $path = '/search';
        $pageParam = 'pl';
        $query = ['q' => $q, 'pc' => $customers_page];
        $page = $loans_page;
        $total = $loans_total;
        $perPage = $per_page;
        require STR_CONSOLE_ROOT . '/views/partials/pagination.php';
        ?>
      </section>
    </div>
  <?php endif; ?>
</div>
