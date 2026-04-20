<?php
declare(strict_types=1);
/**
 * Single searchable combobox (no separate filter input).
 *
 * @var array{
 *   id: string,
 *   name: string,
 *   label: string,
 *   options: list<array{id: int, label: string, rate?: string, basis?: string, allowR?: int, allowF?: int}>,
 *   selectedId: int,
 *   placeholder: string,
 *   disabled: bool,
 *   required: bool,
 *   syncProduct: bool
 * } $combobox
 */
$c = $combobox;
$cid = (string) ($c['id'] ?? 'combo');
$name = (string) ($c['name'] ?? '');
$label = (string) ($c['label'] ?? '');
$options = is_array($c['options'] ?? null) ? $c['options'] : [];
$selectedId = (int) ($c['selectedId'] ?? 0);
$placeholder = (string) ($c['placeholder'] ?? '');
$disabled = (bool) ($c['disabled'] ?? false);
$required = (bool) ($c['required'] ?? false);
$syncProduct = (bool) ($c['syncProduct'] ?? false);
$selectedLabel = '';
foreach ($options as $o) {
    if ((int) ($o['id'] ?? 0) === $selectedId && $selectedId > 0) {
        $selectedLabel = (string) ($o['label'] ?? '');
        break;
    }
}
$jsonId = 'str_combo_json_' . preg_replace('/[^a-z0-9_-]/i', '_', $cid);
$listId = 'str_combo_list_' . preg_replace('/[^a-z0-9_-]/i', '_', $cid);
$inputId = 'str_combo_in_' . preg_replace('/[^a-z0-9_-]/i', '_', $cid);
$wrapId = 'str_combo_wrap_' . preg_replace('/[^a-z0-9_-]/i', '_', $cid);
$hiddenName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$encOptions = json_encode($options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>
<label style="display:grid; gap:6px; font-size:13px; font-weight:650; color:var(--muted);">
  <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
  <div
    id="<?= htmlspecialchars($wrapId, ENT_QUOTES, 'UTF-8') ?>"
    class="str-combo"
    data-str-combo="1"
    data-str-combo-json-id="<?= htmlspecialchars($jsonId, ENT_QUOTES, 'UTF-8') ?>"
    data-str-combo-id="<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>"
    data-str-combo-sync-product="<?= $syncProduct ? '1' : '0' ?>"
    style="position:relative;"
  >
    <input type="hidden" name="<?= $hiddenName ?>" value="<?= $selectedId > 0 ? (string) $selectedId : '' ?>" id="<?= htmlspecialchars('str_combo_h_' . $cid, ENT_QUOTES, 'UTF-8') ?>" />
    <div style="position:relative;">
      <input
        type="text"
        id="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8') ?>"
        class="str-combo-input"
        autocomplete="off"
        spellcheck="false"
        role="combobox"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="<?= htmlspecialchars($listId, ENT_QUOTES, 'UTF-8') ?>"
        placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>"
        value="<?= htmlspecialchars($selectedLabel, ENT_QUOTES, 'UTF-8') ?>"
        <?= $disabled ? 'disabled' : '' ?>
        <?= $required && $selectedId <= 0 ? 'required' : '' ?>
        style="width:100%; box-sizing:border-box; padding:12px 36px 12px 14px; border-radius:14px; border:1px solid var(--line); background:#fff; font-size:14px; color:inherit;"
      />
      <span class="str-combo-chevron" aria-hidden="true" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none; color:var(--muted); font-size:11px;">▼</span>
    </div>
    <ul
      id="<?= htmlspecialchars($listId, ENT_QUOTES, 'UTF-8') ?>"
      class="str-combo-list"
      role="listbox"
      hidden
      style="display:none; position:absolute; left:0; right:0; top:100%; margin:4px 0 0; padding:4px 0; max-height:min(260px, 45vh); overflow:auto; list-style:none; z-index:20; border:1px solid var(--line2); border-radius:14px; background:var(--card); box-shadow:var(--shadow2); font-size:14px;"
    ></ul>
    <script type="application/json" id="<?= htmlspecialchars($jsonId, ENT_QUOTES, 'UTF-8') ?>"><?= $encOptions ?></script>
  </div>
</label>
