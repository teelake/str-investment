<?php
declare(strict_types=1);
/** @var string $filterInputId */
/** @var string $selectId */
?>
<script>
(function () {
  var fi = document.getElementById(<?= json_encode($filterInputId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
  var sel = document.getElementById(<?= json_encode($selectId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
  if (!fi || !sel) return;
  function run() {
    var q = (fi.value || '').toLowerCase().trim();
    for (var i = 0; i < sel.options.length; i++) {
      var opt = sel.options[i];
      if (!opt.value) {
        opt.hidden = q !== '' && (opt.textContent || '').toLowerCase().indexOf(q) === -1;
        continue;
      }
      var txt = (opt.textContent || '').toLowerCase();
      var hide = q !== '' && txt.indexOf(q) === -1 && !opt.selected;
      opt.hidden = hide;
    }
  }
  fi.addEventListener('input', run);
  fi.addEventListener('search', run);
})();
</script>
