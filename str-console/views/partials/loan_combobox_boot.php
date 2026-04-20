<?php
declare(strict_types=1);
if (!empty($GLOBALS['STR_CONSOLE_LOAN_COMBO_BOOT_DONE'])) {
    return;
}
$GLOBALS['STR_CONSOLE_LOAN_COMBO_BOOT_DONE'] = true;
$basisRed = LoanInterestBasis::REDUCING_BALANCE;
$basisFlat = LoanInterestBasis::FLAT_MONTHLY;
?>
<script>
(function () {
  var BASIS_RED = <?= json_encode($basisRed, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var BASIS_FLAT = <?= json_encode($basisFlat, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function strComboApplyProduct(o) {
    var rateIn = document.querySelector('input[name="rate_percent"]');
    var rRed = document.querySelector('input[name="interest_basis"][value="' + BASIS_RED + '"]');
    var rFlat = document.querySelector('input[name="interest_basis"][value="' + BASIS_FLAT + '"]');
    if (!rateIn || !rRed || !rFlat) return;
    if (!o) return;
    var pr = o.rate != null && o.rate !== '' ? String(o.rate) : '';
    if (pr) rateIn.value = pr;
    var ar = o.allowR === 1 || o.allowR === true;
    var af = o.allowF === 1 || o.allowF === true;
    rRed.disabled = !ar;
    rFlat.disabled = !af;
    var b = o.basis ? String(o.basis) : '';
    if (b === BASIS_FLAT && af) rFlat.checked = true;
    else if (ar) rRed.checked = true;
    else if (af) rFlat.checked = true;
  }

  function initCombo(wrap) {
    var jsonId = wrap.getAttribute('data-str-combo-json-id');
    var syncP = wrap.getAttribute('data-str-combo-sync-product') === '1';
    var el = jsonId ? document.getElementById(jsonId) : null;
    if (!el) return;
    var opts;
    try { opts = JSON.parse(el.textContent || '[]'); } catch (e) { return; }
    if (!Array.isArray(opts)) return;
    var hidden = wrap.querySelector('input[type="hidden"]');
    var inp = wrap.querySelector('.str-combo-input');
    var list = wrap.querySelector('.str-combo-list');
    if (!hidden || !inp || !list) return;

    var selectedId = parseInt(hidden.value || '0', 10) || 0;
    var highlightIdx = -1;
    var filtered = opts.slice();

    function byId(id) {
      var n = parseInt(String(id), 10);
      for (var i = 0; i < opts.length; i++) {
        if (parseInt(String(opts[i].id), 10) === n) return opts[i];
      }
      return null;
    }

    function filterList(q) {
      var t = (q || '').trim().toLowerCase();
      if (!t) { filtered = opts.slice(); return; }
      filtered = opts.filter(function (o) {
        return (o.label || '').toLowerCase().indexOf(t) !== -1;
      });
    }

    function renderList() {
      list.innerHTML = '';
      if (filtered.length === 0) {
        var empty = document.createElement('li');
        empty.style.cssText = 'padding:10px 14px; color:var(--muted); margin:0;';
        empty.textContent = 'No matches';
        list.appendChild(empty);
        return;
      }
      for (var i = 0; i < filtered.length; i++) {
        (function (row, idx) {
          var li = document.createElement('li');
          li.setAttribute('role', 'option');
          li.setAttribute('data-id', String(row.id));
          li.style.cssText = 'padding:10px 14px; cursor:pointer; margin:0;';
          li.textContent = row.label;
          if (idx === highlightIdx) li.style.background = 'rgba(15,106,74,.08)';
          li.addEventListener('mousedown', function (e) {
            e.preventDefault();
            select(parseInt(String(row.id), 10));
          });
          list.appendChild(li);
        })(filtered[i], i);
      }
    }

    function open() {
      list.hidden = false;
      list.style.display = 'block';
      inp.setAttribute('aria-expanded', 'true');
    }
    function close() {
      list.hidden = true;
      list.style.display = 'none';
      inp.setAttribute('aria-expanded', 'false');
      highlightIdx = -1;
    }

    function select(id) {
      var row = byId(id);
      if (!row) return;
      hidden.value = String(id);
      inp.value = row.label;
      selectedId = id;
      close();
      if (syncP) strComboApplyProduct(row);
      inp.removeAttribute('required');
    }

    function blurRestore() {
      if (selectedId) {
        var row = byId(selectedId);
        inp.value = row ? row.label : '';
        if (!row) {
          hidden.value = '';
          selectedId = 0;
        }
      } else {
        inp.value = '';
        hidden.value = '';
      }
    }

    function tryExactLabelMatch() {
      var t = inp.value.trim();
      if (!t) return;
      for (var i = 0; i < opts.length; i++) {
        if (opts[i].label === t) {
          select(parseInt(String(opts[i].id), 10));
          return;
        }
      }
    }

    inp.addEventListener('focus', function () {
      filterList(inp.value);
      highlightIdx = -1;
      renderList();
      open();
    });
    inp.addEventListener('input', function () {
      filterList(inp.value);
      highlightIdx = filtered.length ? 0 : -1;
      renderList();
      open();
    });
    inp.addEventListener('keydown', function (e) {
      var openList = list.style.display === 'block' && !list.hidden;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!openList) {
          filterList(inp.value);
          renderList();
          open();
        }
        highlightIdx = highlightIdx < 0 ? 0 : Math.min(highlightIdx + 1, Math.max(0, filtered.length - 1));
        renderList();
        return;
      }
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!openList) return;
        highlightIdx = Math.max(highlightIdx - 1, 0);
        renderList();
        return;
      }
      if (e.key === 'Escape') {
        e.preventDefault();
        close();
        blurRestore();
        return;
      }
      if (e.key === 'Enter') {
        if (openList && highlightIdx >= 0 && filtered[highlightIdx]) {
          e.preventDefault();
          select(parseInt(String(filtered[highlightIdx].id), 10));
        }
        return;
      }
    });
    inp.addEventListener('blur', function () {
      setTimeout(function () {
        close();
        tryExactLabelMatch();
        blurRestore();
      }, 180);
    });
  }

  document.querySelectorAll('[data-str-combo="1"]').forEach(initCombo);

  var prodWrap = document.querySelector('[data-str-combo-sync-product="1"]');
  if (prodWrap) {
    var h = prodWrap.querySelector('input[type="hidden"]');
    var pid = parseInt(h && h.value ? h.value : '0', 10);
    if (pid) {
      var jid = prodWrap.getAttribute('data-str-combo-json-id');
      var jel = jid ? document.getElementById(jid) : null;
      if (jel) {
        try {
          var po = JSON.parse(jel.textContent || '[]');
          for (var j = 0; j < po.length; j++) {
            if (parseInt(String(po[j].id), 10) === pid) {
              strComboApplyProduct(po[j]);
              break;
            }
          }
        } catch (e2) {}
      }
    }
  }
})();
</script>
