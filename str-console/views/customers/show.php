<?php
declare(strict_types=1);
/** @var array<string, mixed> $customer */
/** @var list<array<string, mixed>> $documents */
/** @var bool $showSensitiveIds */
/** @var bool $canUpload */
/** @var bool $canDeleteDocs */
/** @var mixed $docError */
/** @var mixed $docOk */
$basePath = Request::basePath();
$id = (int) ($customer['id'] ?? 0);
$name = (string) ($customer['full_name'] ?? '');
$phone = (string) ($customer['phone'] ?? '');
$address = (string) ($customer['address'] ?? '');
$nin = $customer['nin'] ?? null;
$bvn = $customer['bvn'] ?? null;
$mask = static function (?string $v): string {
    if ($v === null || $v === '') {
        return '—';
    }
    $s = (string) $v;
    if (strlen($s) <= 4) {
        return '••••';
    }
    return str_repeat('•', max(0, strlen($s) - 4)) . substr($s, -4);
};
$ninHtml = $showSensitiveIds
    ? ($nin !== null && $nin !== '' ? htmlspecialchars((string) $nin, ENT_QUOTES, 'UTF-8') : '—')
    : htmlspecialchars($mask($nin !== null ? (string) $nin : null), ENT_QUOTES, 'UTF-8');
$bvnHtml = $showSensitiveIds
    ? ($bvn !== null && $bvn !== '' ? htmlspecialchars((string) $bvn, ENT_QUOTES, 'UTF-8') : '—')
    : htmlspecialchars($mask($bvn !== null ? (string) $bvn : null), ENT_QUOTES, 'UTF-8');
$err = is_string($docError) ? $docError : '';
$ok = $docOk === '1' || $docOk === 1;
?>
<div class="container" style="padding:0">
  <div style="margin-bottom: 20px;">
    <a href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Customers</a>
    <h1 style="font-size: var(--h2); margin: 12px 0 6px;"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h1>
    <p style="color: var(--muted); margin: 0; font-size: 14px;">Customer #<?= (int) $id ?></p>
  </div>

  <?php if ($ok): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">Document uploaded.</div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2);">
      <h2 style="font-size: 15px; margin: 0 0 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted);">Profile</h2>
      <dl style="margin:0; display:grid; gap: 12px; font-size: 14px;">
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">Phone</dt><dd style="margin: 4px 0 0; font-weight: 650;"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></dd></div>
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">Address</dt><dd style="margin: 4px 0 0;"><?= $address !== '' ? nl2br(htmlspecialchars($address, ENT_QUOTES, 'UTF-8')) : '—' ?></dd></div>
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">NIN</dt><dd style="margin: 4px 0 0; font-family: ui-monospace, monospace;"><?= $ninHtml ?></dd></div>
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">BVN</dt><dd style="margin: 4px 0 0; font-family: ui-monospace, monospace;"><?= $bvnHtml ?></dd></div>
      </dl>
      <?php if (!$showSensitiveIds && (($nin !== null && $nin !== '') || ($bvn !== null && $bvn !== ''))): ?>
        <p style="margin: 14px 0 0; font-size: 12px; color: var(--muted2);">Identifiers are masked. Users with the right permission see full values.</p>
      <?php endif; ?>
    </div>

    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2);">
      <h2 style="font-size: 15px; margin: 0 0 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted);">Documents</h2>
      <?php if ($canUpload): ?>
        <form method="post" action="<?= htmlspecialchars($basePath . '/customers/' . $id . '/documents', ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" style="display:grid; gap: 10px; margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid var(--line2);">
          <label style="font-size: 13px; font-weight: 650; color: var(--muted);">
            Upload ID or supporting file
            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp" required style="margin-top: 6px; width: 100%; font-size: 14px;" />
          </label>
          <p style="margin:0; font-size: 12px; color: var(--muted2);">PDF, JPG, PNG, or WebP · max 8 MB</p>
          <button type="submit" class="btn primary" style="justify-self: start; font-size: 14px;">Upload</button>
        </form>
      <?php endif; ?>

      <?php if (count($documents) === 0): ?>
        <p style="margin:0; color: var(--muted); font-size: 14px;">No documents yet.</p>
      <?php else: ?>
        <ul style="list-style: none; margin: 0; padding: 0; display: grid; gap: 10px;">
          <?php foreach ($documents as $d): ?>
            <?php
            $did = (int) ($d['id'] ?? 0);
            $oname = (string) ($d['original_name'] ?? '');
            $sz = isset($d['size_bytes']) ? (int) $d['size_bytes'] : 0;
            $szLabel = $sz > 0 ? number_format($sz / 1024, 1) . ' KB' : '';
            ?>
            <li style="display:flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line2); background: rgba(255,255,255,.6);">
              <div>
                <div style="font-weight: 650; font-size: 14px;"><?= htmlspecialchars($oname, ENT_QUOTES, 'UTF-8') ?></div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
                  <?= htmlspecialchars((string) ($d['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <?= $szLabel !== '' ? ' · ' . htmlspecialchars($szLabel, ENT_QUOTES, 'UTF-8') : '' ?>
                </div>
              </div>
              <div style="display:flex; gap: 8px; flex-wrap: wrap;">
                <a class="btn ghost" style="font-size: 13px; padding: 8px 12px;" href="<?= htmlspecialchars($basePath . '/customers/' . $id . '/documents/' . $did . '/file', ENT_QUOTES, 'UTF-8') ?>">Download</a>
                <?php if ($canDeleteDocs): ?>
                  <form method="post" action="<?= htmlspecialchars($basePath . '/customers/' . $id . '/documents/' . $did . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this file?');">
                    <button type="submit" class="btn ghost" style="font-size: 13px; padding: 8px 12px; color: #7f1d1d;">Delete</button>
                  </form>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
