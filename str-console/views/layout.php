<?php
declare(strict_types=1);
/** @var string $content */
$basePath = Request::basePath();
$styles = Request::asset('assets/styles.css');
$logoUrl = Request::asset('assets/images/str-logo.png');
$u = ConsoleAuth::user();
$email = is_array($u) ? (string) ($u['email'] ?? '') : '';
$fullName = is_array($u) ? trim((string) ($u['full_name'] ?? '')) : '';
$authed = ConsoleAuth::check();
$g = ConsoleAuth::grants();
$path = Request::path();
$canSearch = str_console_authorize_route($g, 'search.index');
$maintenanceNotice = $authed ? PolicyService::maintenanceNotice() : '';
$profileInitial = $fullName !== '' ? strtoupper(mb_substr($fullName, 0, 1)) : strtoupper(mb_substr($email, 0, 1));
if ($profileInitial === '') {
    $profileInitial = '?';
}
$authSurface = !$authed && in_array($path, ['/login', '/forgot-password', '/reset-password'], true);
$docTitle = match (true) {
    $authSurface && $path === '/forgot-password' => 'Forgot password · STR Console',
    $authSurface && $path === '/reset-password' => 'New password · STR Console',
    $authSurface => 'Sign in · STR Console',
    default => 'STR Console',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#0f6a4a" />
  <title><?= htmlspecialchars($docTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" />
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" />
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
      display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--ink);
    }
    .console-sidebar__brand img { height: 36px; width: auto; display: block; }
    .console-sidebar__brand span.title {
      font-weight: 800; font-size: 15px; letter-spacing: -0.02em; line-height: 1.2;
    }
    .console-sidebar__brand span.sub {
      display: block; font-size: 11px; font-weight: 600; color: var(--muted);
      text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2px;
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
    .console-shell { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .console-topbar {
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
      padding: 12px 20px; border-bottom: 1px solid var(--line2);
      background: rgba(244,246,245,.97); backdrop-filter: blur(12px);
      box-shadow: 0 1px 0 rgba(255,255,255,.7) inset, 0 8px 32px rgba(13,15,18,.04);
      position: sticky; top: 0; z-index: 50;
    }
    .console-topbar__start { display: flex; align-items: center; gap: 12px; }
    .console-topbar__brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--ink); }
    .console-topbar__brand img { height: 32px; width: auto; }
    .console-topbar__search { flex: 1; min-width: 160px; max-width: 420px; margin: 0 auto; }
    .console-topbar__search input {
      width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--line2);
      font-size: 14px; background: var(--card); color: var(--ink);
    }
    .console-topbar__end { margin-left: auto; display: flex; align-items: center; gap: 12px; }
    [data-sidebar-open] {
      display: none; border: 1px solid var(--line); background: var(--card); border-radius: 12px;
      padding: 10px 14px; font-weight: 650; cursor: pointer; font-size: 14px;
    }
    .console-profile { position: relative; }
    .console-profile__btn {
      display: flex; align-items: center; justify-content: center;
      width: 40px; height: 40px; border-radius: 12px; border: 1px solid var(--line2);
      background: var(--card); cursor: pointer; font-weight: 800; font-size: 15px; color: var(--green2);
    }
    .console-profile__btn:hover { background: var(--green-soft); }
    .console-profile__menu {
      position: absolute; right: 0; top: calc(100% + 8px); min-width: 220px;
      background: var(--card); border: 1px solid var(--line2); border-radius: 14px;
      box-shadow: var(--shadow2); padding: 8px; z-index: 80;
    }
    .console-profile__menu[hidden] { display: none !important; }
    .console-profile__menu a {
      display: block; padding: 10px 12px; border-radius: 10px; font-size: 14px; font-weight: 650;
      color: var(--ink); text-decoration: none;
    }
    .console-profile__menu a:hover { background: rgba(13,15,18,.04); }
    .console-profile__menu form { margin: 0; padding: 4px 0 0; border-top: 1px solid var(--line2); }
    .console-profile__menu button[type="submit"] {
      width: 100%; text-align: left; padding: 10px 12px; margin-top: 4px; border: none; border-radius: 10px;
      background: transparent; font-size: 14px; font-weight: 650; color: #7f1d1d; cursor: pointer; font-family: inherit;
    }
    .console-profile__menu button[type="submit"]:hover { background: rgba(180, 40, 40, .08); }
    .console-banner {
      padding: 10px 20px; background: rgba(180, 120, 20, .12); border-bottom: 1px solid rgba(180, 120, 20, .25);
      color: #7a4a00; font-size: 14px; text-align: center;
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
      [data-sidebar-open] { display: inline-flex; }
      .console-topbar__brand { display: none; }
      .console-topbar__search { order: 3; flex: 1 1 100%; max-width: none; margin: 0; }
    }
  </style>
</head>
<body class="<?= $authed ? 'console-authed' : '' ?>">
  <?php if ($authed): ?>
    <div class="console-layout">
      <aside class="console-sidebar" id="console-sidebar" aria-label="Sidebar navigation">
        <div class="console-sidebar__brand">
          <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" width="120" height="36" alt="" />
            <span>
              <span class="title">STR Console</span>
              <span class="sub">Internal operations</span>
            </span>
          </a>
        </div>
        <nav class="console-sidebar__nav">
          <?php if (str_console_authorize_route($g, 'dashboard.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>" <?= $path === '/' ? 'aria-current="page"' : '' ?>>Dashboard</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'customers.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/customers', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/customers') ? 'aria-current="page"' : '' ?>>Customers</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'loans.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/loans', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/loans') ? 'aria-current="page"' : '' ?>>Loans</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'reports.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/reports', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/reports') ? 'aria-current="page"' : '' ?>>Reports</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'bulk_upload.customers')): ?>
            <a href="<?= htmlspecialchars($basePath . '/bulk-upload/customers', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/bulk-upload/customers') ? 'aria-current="page"' : '' ?>>Import customers</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'bulk_upload.loans')): ?>
            <a href="<?= htmlspecialchars($basePath . '/bulk-upload/loans', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/bulk-upload/loans') ? 'aria-current="page"' : '' ?>>Import loans</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'loan_products.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/loan-products', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/loan-products') ? 'aria-current="page"' : '' ?>>Loan products</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'settings.users')): ?>
            <a href="<?= htmlspecialchars($basePath . '/settings/users', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/settings/users') ? 'aria-current="page"' : '' ?>>Users</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'settings.roles')): ?>
            <a href="<?= htmlspecialchars($basePath . '/settings/roles', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/settings/roles') ? 'aria-current="page"' : '' ?>>Roles</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'settings.policies')): ?>
            <a href="<?= htmlspecialchars($basePath . '/settings/policies', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/settings/policies') ? 'aria-current="page"' : '' ?>>Policies</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'settings.system')): ?>
            <a href="<?= htmlspecialchars($basePath . '/settings/system', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/settings/system') ? 'aria-current="page"' : '' ?>>System</a>
          <?php endif; ?>
          <?php if (str_console_authorize_route($g, 'audit.index')): ?>
            <a href="<?= htmlspecialchars($basePath . '/audit', ENT_QUOTES, 'UTF-8') ?>" <?= str_starts_with($path, '/audit') ? 'aria-current="page"' : '' ?>>Audit log</a>
          <?php endif; ?>
        </nav>
      </aside>
      <div class="console-shell">
        <header class="console-topbar">
          <div class="console-topbar__start">
            <button type="button" data-sidebar-open aria-expanded="false" aria-controls="console-sidebar">Menu</button>
            <a class="console-topbar__brand" href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">
              <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" width="100" height="32" alt="STR" />
            </a>
          </div>
          <?php if ($canSearch): ?>
            <form class="console-topbar__search" method="get" action="<?= htmlspecialchars($basePath . '/search', ENT_QUOTES, 'UTF-8') ?>" role="search">
              <label class="visually-hidden" for="topbar-search-q">Search customers and loans</label>
              <input id="topbar-search-q" type="search" name="q" value="<?= htmlspecialchars((string) Request::query('q', ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Search customers & loans…" minlength="0" autocomplete="off" />
            </form>
          <?php endif; ?>
          <div class="console-topbar__end">
            <div class="console-profile" data-console-profile>
              <button type="button" class="console-profile__btn" data-profile-trigger aria-expanded="false" aria-haspopup="true" aria-label="Account menu" title="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($profileInitial, ENT_QUOTES, 'UTF-8') ?></button>
              <div class="console-profile__menu" data-profile-menu hidden>
                <a href="<?= htmlspecialchars($basePath . '/account/profile', ENT_QUOTES, 'UTF-8') ?>">Edit profile</a>
                <a href="<?= htmlspecialchars($basePath . '/account/password', ENT_QUOTES, 'UTF-8') ?>">Password settings</a>
                <form method="post" action="<?= htmlspecialchars($basePath . '/logout', ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit">Log out</button>
                </form>
              </div>
            </div>
          </div>
        </header>
        <?php if ($maintenanceNotice !== ''): ?>
          <div class="console-banner"><?= htmlspecialchars($maintenanceNotice, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <main class="console-main">
          <?= $content ?>
        </main>
      </div>
    </div>
    <style>
      .visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    </style>
    <script>
      (function () {
        var openBtn = document.querySelector('[data-sidebar-open]');
        if (openBtn) {
          openBtn.addEventListener('click', function () {
            var open = document.body.classList.toggle('console-sidebar-open');
            openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
          });
        }
        var wrap = document.querySelector('[data-console-profile]');
        if (!wrap) return;
        var trig = wrap.querySelector('[data-profile-trigger]');
        var menu = wrap.querySelector('[data-profile-menu]');
        if (!trig || !menu) return;
        function closeMenu() {
          menu.setAttribute('hidden', '');
          trig.setAttribute('aria-expanded', 'false');
        }
        function toggleMenu() {
          var open = menu.hasAttribute('hidden');
          if (open) {
            menu.removeAttribute('hidden');
            trig.setAttribute('aria-expanded', 'true');
          } else {
            closeMenu();
          }
        }
        trig.addEventListener('click', function (e) {
          e.stopPropagation();
          toggleMenu();
        });
        document.addEventListener('click', function () { closeMenu(); });
        menu.addEventListener('click', function (e) { e.stopPropagation(); });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') closeMenu();
        });
      })();
    </script>
  <?php elseif ($authSurface): ?>
    <div class="auth-layout">
      <div class="auth-layout__bg" aria-hidden="true"></div>
      <div class="auth-layout__inner">
        <header class="auth-masthead">
          <img class="auth-masthead__logo" src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="STR Investment" width="180" height="52" decoding="async" />
          <?php if ($path === '/login'): ?>
            <h1 class="auth-masthead__title">Welcome back</h1>
            <p class="auth-masthead__sub">Sign in to STR Console — staff access only.</p>
          <?php elseif ($path === '/forgot-password'): ?>
            <h1 class="auth-masthead__title">Forgot password</h1>
            <p class="auth-masthead__sub">We’ll email you a secure link to choose a new password.</p>
          <?php else: ?>
            <h1 class="auth-masthead__title">Set a new password</h1>
            <p class="auth-masthead__sub">Choose a strong password you don’t use elsewhere.</p>
          <?php endif; ?>
        </header>
        <div class="auth-card">
          <?= $content ?>
        </div>
        <?php if ($path === '/login'): ?>
          <p class="auth-foot" style="margin-top:20px;">Need an account? Contact your <strong>system administrator</strong>.</p>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <main class="console-main console-main--guest">
      <?= $content ?>
    </main>
  <?php endif; ?>
</body>
</html>
