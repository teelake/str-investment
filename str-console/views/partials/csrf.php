<?php
declare(strict_types=1);
$csrfToken = isset($csrfToken) && is_string($csrfToken) ? $csrfToken : FormGuard::token();
?>
<input type="hidden" name="<?= htmlspecialchars(FormGuard::POST_KEY, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
