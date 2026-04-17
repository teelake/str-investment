<?php
declare(strict_types=1);
/** @var string $content */
$basePath = Request::basePath();
$styles = Request::asset('assets/styles.css');
$u = ConsoleAuth::user();
$email = is_array($u) ? (string) ($u['email'] ?? '') : '';
$authed = ConsoleAuth::check();
$g = ConsoleAuth::grants();
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
    .console-layout { display: flex; min-height: 100vh; background: var(--bg); }
    .console-sidebar {
      width: 260px; flex-shrink: 0; background: var(--card);
      border-right: 1px solid var(--line2);
      display: flex; flex-direction: column; padding: 20px 0;
      position: sticky; top: 0; align-self: flex-start; min-height: 100vh;
    }
    .console-sidebar__brand {
      padding: 0 20px 20px; border-bottom: 1px solid var(--line2);
    }
    .console-sidebar__brand a {
      font-weight: 800; font-size: 16px; letter-spacing: -0.02em; color: var(--ink);
      text-decoration: none; display: block;
    }
    .console-sidebar__brand span {
      display: block; font-size: 11px; font-weight: 600; color: var(--muted);
      text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px;
    }
    .console-sidebar__nav { padding: 16px 12px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .console-sidebar__nav a {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 14px; border-radius: 12px; font-size: 14px; font-weight: 650;
      color: var(--muted); text-decoration: none;
    }
    .console-sidebar__nav a:hover { background: rgba(13,15,18,.04); color: var(--ink); }
    .console-sidebar__nav a[aria-current="page"] {
      background: var(--green-soft); color: var(--green2);
    }
    .console-sidebar__nav .nav-soon {
      padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 600;
      color: var(--muted2); cursor: default;
    }
    .console-sidebar__nav .nav-soon small { font-weight: 650; color: var(--muted); margin-left: 6px; }
    .console-sidebar__foot {
      padding: 16px 20px 0; border-top: 1px solid var(--line2); margin-top: auto;
    }
    .console-sidebar__email { font-size: 12px; color: var(--muted); word-break: break-all; margin-bottom: 10px; }
    .console-shell { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .console-mobilebar {
      display: none; align-items: center; justify-content: space-between;
      padding: 12px var(--gutter); border-bottom: 1px solid var(--line2);
      background: rgba(244,246,245,.95); backdrop-filter: blur(10px);
      position: sticky; top: 0; z-index: 30;
    }
    .console-mobilebar button {
      border: 1px solid var(--line); background: var(--card); border-radius: 12px;
      padding: 10px 14px; font-weight: 650; cursor: pointer; font-size: 14px;
    }
    .console-main {
      flex: 1; width: min(var(--container), calc(100% - (var(--gutter) * 2)));
      margin: 0 auto; padding: 28px 0 48px;
    }
    .console-main--guest { width: min(520px, calc(100% - (var(--gutter) * 2))); padding-top: 48px; }

    @media (max-width: 900px) {
      .console-layout { flex-direction: column; }
      .console-sidebar {
        position: fixed; left: 0; top: 0; bottom: 0; z-index: 40;
        min-height: 100dvh; transform: translateX(-100%);
        transition: transform .2s ease; box-shadow: var(--shadow);
      }
      body.console-sidebar-open .console-sidebar { transform: translateX(0); }
      .console-mobilebar { display: flex; }
      .console-sidebar__foot { padding-bottom: 20px; }
    }
  </style>
</head>
<body class="<?= $authed ? 'console-authed' : '' ?>">
  <?php if ($authed): ?>
    <div class="console-layout">
      <aside class="console-sidebar" id="console-sidebar" aria-label="Sidebar navigation">
        <div class="console-sidebar__brand">
          <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">STR Console</a>
          <span>Internal operations</span>
        </div>
        <nav class="console-sidebar__nav">
          <?php
          $path = Request::path();
          ?>
          <?php if (str_console_authorize_route($g, 'dashboard.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>" <?= $path === '/' ? 'aria-current="page"' : '' ?>>Dashboard</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'customers.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/customers') ? 'aria-current="page"' : '' ?>>Customers</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'loans.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/loans') ? 'aria-current="page"' : '' ?>>Loans</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'loan_products.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/loan-products') ? 'aria-current="page"' : '' ?>>Loan products</a>
          <?php endif; ?>
        </nav>
        <div class="console-sidebar__foot">
          <div class="console-sidebar__email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></div>
          <form method="post" action="<?= htmlspecialchars($basePath . '/logout', ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn ghost" style="width:100%; justify-content:center; font-size: 13px;">Sign out</button>
          </form>
        </div>
      </aside>
      <div class="console-shell">
        <header class="console-mobilebar">
          <strong style="font-size: 14px;">STR Console</strong>
          <button type="button" data-sidebar-open aria-expanded="false" aria-controls="console-sidebar">Menu</button>
        </header>
        <main class="console-main">
          <?= $content ?>
        </main>
      </div>
    </div>
    <script>
      (function () {
        var b = document.querySelector('[data-sidebar-open]');
        if (!b) return;
        b.addEventListener('click', function () {
          var open = document.body.classList.toggle('console-sidebar-open');
          b.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
      })();
    </script>
  <?php else: ?>
    <main class="console-main console-main--guest">
      <?= $content ?>
    </main>
  <?php endif; ?>
</body>
</html>
