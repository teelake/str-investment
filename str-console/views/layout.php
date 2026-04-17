<?php
declare(strict_types=1);
/** @var string $content */
/** @var string $basePath */
$basePath = Request::basePath();
$styles = Request::asset('assets/styles.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>STR Console</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= htmlspecialchars($styles, ENT_QUOTES, 'UTF-8') ?>" />
  <style>
    .console-shell { min-height: 100vh; display: flex; flex-direction: column; }
    .console-top {
      border-bottom: 1px solid var(--line2);
      background: rgba(244,246,245,.92);
      backdrop-filter: blur(12px);
      position: sticky; top: 0; z-index: 20;
    }
    .console-top__inner {
      width: min(var(--container), calc(100% - (var(--gutter) * 2)));
      margin: 0 auto;
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px; padding: 14px 0;
    }
    .console-brand { font-weight: 800; letter-spacing: -0.02em; font-size: 15px; }
    .console-brand span { color: var(--muted); font-weight: 600; margin-left: 8px; font-size: 13px; }
    .console-actions { display: flex; align-items: center; gap: 10px; }
    .console-main {
      flex: 1;
      width: min(var(--container), calc(100% - (var(--gutter) * 2)));
      margin: 0 auto;
      padding: 28px 0 48px;
    }
  </style>
</head>
<body>
  <div class="console-shell">
    <header class="console-top">
      <div class="console-top__inner">
        <div class="console-brand">STR Console<span>Internal</span></div>
        <div class="console-actions">
          <?php if (ConsoleAuth::check()): ?>
            <form method="post" action="<?= htmlspecialchars($basePath . '/logout', ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="btn ghost" style="font-size: 13px; padding: 10px 14px;">Sign out</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </header>
    <main class="console-main">
      <?= $content ?>
    </main>
  </div>
</body>
</html>
