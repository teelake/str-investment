<?php
declare(strict_types=1);
/** @var array<string, mixed> $customer */
/** @var list<array<string, mixed>> $documents */
/** @var array<string, string> $documentTypes */
/** @var bool $showSensitiveIds */
/** @var bool $canUpload */
/** @var bool $canDeleteDocs */
/** @var bool $canEdit */
/** @var mixed $docError */
/** @var mixed $docOk */
/** @var mixed $editOk */
/** @var mixed $editError */
$basePath = Request::basePath();
$id = (int) ($customer['id'] ?? 0);
$name = (string) ($customer['full_name'] ?? '');
$phone = (string) ($customer['phone'] ?? '');
$passportPhone = (string) ($customer['passport_phone'] ?? '');
$custEmail = (string) ($customer['email'] ?? '');
$address = (string) ($customer['address'] ?? '');
$nin = $customer['nin'] ?? null;
$bvn = $customer['bvn'] ?? null;
$documentTypes = is_array($documentTypes ?? null) ? $documentTypes : [];
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
$docOkStr = is_string($docOk) ? $docOk : (is_int($docOk) ? (string) $docOk : '');
$docOkCount = ctype_digit($docOkStr) ? (int) $docOkStr : 0;
$canEdit = $canEdit ?? false;
$editErr = is_string($editError ?? null) ? (string) $editError : '';
$editDone = $editOk === '1' || $editOk === 1;
$custInactive = (int) ($customer['is_active'] ?? 1) !== 1;
?>
<style>
  .customer-show-main-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    align-items: start;
    margin-bottom: 24px;
  }
  @media (max-width: 900px) {
    .customer-show-main-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="console-form-page console-form-page--wide">
  <div class="container" style="padding:0">
  <div style="margin-bottom: 20px;">
    <a href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" style="font-size: 13px; font-weight: 650; color: var(--muted); text-decoration: none;">← Customers</a>
    <h1 style="font-size: var(--h2); margin: 12px 0 6px;"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h1>
    <p style="color: var(--muted); margin: 0; font-size: 14px;">Customer #<?= (int) $id ?></p>
    <p style="margin: 12px 0 0; display:flex; flex-wrap: wrap; gap: 10px; align-items: center;">
      <?php if ($canEdit): ?>
        <a class="btn ghost" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/customers/' . $id . '/edit', ENT_QUOTES, 'UTF-8') ?>">Edit profile</a>
      <?php endif; ?>
      <?php if (str_console_authorize_route(ConsoleAuth::grants(), 'loans.create')): ?>
        <a class="btn primary" style="font-size: 14px;" href="<?= htmlspecialchars($basePath . '/loans/create?customer_id=' . $id, ENT_QUOTES, 'UTF-8') ?>">New loan</a>
      <?php endif; ?>
    </p>
  </div>

  <?php if ($custInactive): ?>
    <div style="background: rgba(180, 120, 20, .1); border: 1px solid rgba(180, 120, 20, .25); color: #7a4a00; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">This customer is <strong>deactivated</strong> and no longer appears on the main customer list.</div>
  <?php endif; ?>
  <?php if ($editDone): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">Profile updated.</div>
  <?php endif; ?>
  <?php if ($editErr !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($editErr, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if ($docOkCount > 0): ?>
    <div style="background: var(--green-soft); border: 1px solid rgba(15,106,74,.2); color: var(--green2); padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;">
      <?= $docOkCount === 1 ? '1 document uploaded.' : htmlspecialchars((string) $docOkCount, ENT_QUOTES, 'UTF-8') . ' documents uploaded.' ?>
    </div>
  <?php endif; ?>
  <?php if ($err !== ''): ?>
    <div style="background: rgba(180, 40, 40, .08); border: 1px solid rgba(180, 40, 40, .2); color: #7f1d1d; padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; font-size: 14px;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="customer-show-main-grid">
    <div style="background: var(--card); border: 1px solid var(--line2); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow2);">
      <h2 style="font-size: 15px; margin: 0 0 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted);">Profile</h2>
      <dl style="margin:0; display:grid; gap: 12px; font-size: 14px;">
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">Phone</dt><dd style="margin: 4px 0 0; font-weight: 650;"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></dd></div>
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">Passport phone</dt><dd style="margin: 4px 0 0; font-weight: 650;"><?= $passportPhone !== '' ? htmlspecialchars($passportPhone, ENT_QUOTES, 'UTF-8') : '—' ?></dd></div>
        <div><dt style="color: var(--muted2); font-size: 12px; font-weight: 650;">Email</dt><dd style="margin: 4px 0 0;"><?php
          if ($custEmail !== '') {
              echo '<a href="mailto:' . htmlspecialchars($custEmail, ENT_QUOTES, 'UTF-8') . '" style="color: inherit; font-weight: 650;">' . htmlspecialchars($custEmail, ENT_QUOTES, 'UTF-8') . '</a>';
          } else {
              echo '—';
          }
        ?></dd></div>
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
          <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
          <label style="display:grid; gap: 6px; font-size: 13px; font-weight: 650; color: var(--muted);">
            Document type
            <select name="document_type" required style="margin-top: 2px; padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line); background: #fff; color: var(--ink); font-size: 14px;">
              <option value="" selected>— Select type —</option>
              <?php foreach ($documentTypes as $typeKey => $typeLabel): ?>
                <option value="<?= htmlspecialchars((string) $typeKey, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $typeLabel, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label style="font-size: 13px; font-weight: 650; color: var(--muted);">
            Files (you can select several at once)
            <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.webp" multiple required style="margin-top: 6px; width: 100%; font-size: 14px;" />
          </label>
          <p style="margin:0; font-size: 12px; color: var(--muted2);">PDF, JPG, PNG, or WebP · max 8 MB per file · all files use the type selected above</p>
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
            $dtypeLabel = str_console_customer_document_type_label(isset($d['document_type']) ? (string) $d['document_type'] : null);
            $sz = isset($d['size_bytes']) ? (int) $d['size_bytes'] : 0;
            $szLabel = $sz > 0 ? number_format($sz / 1024, 1) . ' KB' : '';
            ?>
            <li style="display:flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-radius: 14px; border: 1px solid var(--line2); background: rgba(255,255,255,.6);">
              <div>
                <div style="font-weight: 650; font-size: 14px;"><?= htmlspecialchars($oname, ENT_QUOTES, 'UTF-8') ?></div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
                  <span style="font-weight: 650;"><?= htmlspecialchars($dtypeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  · <?= htmlspecialchars((string) ($d['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                  <?= $szLabel !== '' ? ' · ' . htmlspecialchars($szLabel, ENT_QUOTES, 'UTF-8') : '' ?>
                </div>
              </div>
              <div style="display:flex; gap: 8px; flex-wrap: wrap;">
                <a class="btn ghost" style="font-size: 13px; padding: 8px 12px;" href="<?= htmlspecialchars($basePath . '/customers/' . $id . '/documents/' . $did . '/file', ENT_QUOTES, 'UTF-8') ?>">Download</a>
                <?php if ($canDeleteDocs): ?>
                  <form method="post" action="<?= htmlspecialchars($basePath . '/customers/' . $id . '/documents/' . $did . '/delete', ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this file?');">
                    <?php require STR_CONSOLE_ROOT . '/views/partials/csrf.php'; ?>
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
</div>
