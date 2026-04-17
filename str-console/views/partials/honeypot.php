<?php
declare(strict_types=1);
?>
<div class="sr-only" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
  <label for="<?= htmlspecialchars(FormGuard::HONEYPOT_NAME, ENT_QUOTES, 'UTF-8') ?>">Leave blank</label>
  <input id="<?= htmlspecialchars(FormGuard::HONEYPOT_NAME, ENT_QUOTES, 'UTF-8') ?>" type="text" name="<?= htmlspecialchars(FormGuard::HONEYPOT_NAME, ENT_QUOTES, 'UTF-8') ?>" value="" tabindex="-1" autocomplete="off" />
</div>
