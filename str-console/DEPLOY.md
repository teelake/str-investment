# Deploying STR Console

## Requirements

- PHP 8.1+ with PDO MySQL, `json`, `session`, and typical extensions used by your host.
- MySQL 8 (or compatible) with a database and user granted DDL/DML for the console schema.

## Configuration

1. Create `config/local.php` with:
   - `STR_CONSOLE_DB_DSN`, `STR_CONSOLE_DB_USER`, `STR_CONSOLE_DB_PASS` (or environment variables `STR_CONSOLE_DB_DSN`, etc.).
   - Optional: `STR_CONSOLE_ERROR_LOG` for PHP error log path.
2. **Do not** commit `config/local.php`; it is gitignored.

## Database

Run SQL migrations in `database/migrations/` in filename order (e.g. `001_…sql`, `002_…sql`, …) against the target database. New installs must include all files through the latest migration.

The `console_settings` table stores org policy toggles and the role permission matrix (`roles.grants.{role_key}` JSON arrays) edited under **Settings → Roles**, plus optional **`system.maintenance_notice`** (plain-text banner) from **Settings → System**.

Apply migration **`005_console_users_extra_grants.sql`** on existing databases so `console_users.extra_grants_json` exists (per-user additive permissions merged at login).

## Web server

- Point the site **document root** at `str-console/public` (or the directory that contains `index.php` for this app), not the repository root.
- Ensure PHP can write only what it needs (e.g. no write access to `config/` in production).
- Use HTTPS in production; session cookies are `HttpOnly` and `SameSite=Lax` by default.

## Cron: ledger accrual

Optional batch interest accrual uses the same policy flag as the UI (`ledger.auto_accrue`). From the `str-console` directory:

```bash
STR_CONSOLE_ACCRUE_CRON=1 php bin/accrue-active-loans.php
```

Pass an optional `YYYY-MM-DD` argument to accrue through that date. Set `STR_CONSOLE_ACCRUE_CRON=1` only in the scheduled job (or one-off SSH), not in the web environment.

## Automated tests (optional)

From `str-console`:

```bash
composer install
vendor/bin/phpunit
```

Tests do not require a database; they cover permission helpers and pure ledger rounding.

On Windows, enable `extension=zip` in the CLI `php.ini` Composer uses so packages can install from **dist** archives. Without zip (and without `unzip`/`7z` on `PATH`), Composer may fall back to cloning from GitHub, which is slower and can fail if Git or SSH is not set up. If a previous attempt left a broken clone, remove the matching folder under `%LOCALAPPDATA%\Composer\vcs\` (or run `composer clear-cache`) and try again.

## Demo / development login

When `STR_CONSOLE_DEV_LOGIN=1` (environment or constant), the login form allows picking a role without a password. **Disable in production.**
