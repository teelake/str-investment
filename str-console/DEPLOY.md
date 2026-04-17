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

Run SQL migrations in `database/migrations/` in **numeric filename order** against the target database. New installs should apply **`database/schema.sql`** first (or equivalent), then every migration through the latest.

**Ordered checklist (current set):**

| File | Purpose |
|------|---------|
| `002_customer_documents.sql` | Customer document storage |
| `003_loans.sql` | Loans / ledger (if not already in base schema) |
| `004_console_settings.sql` | Settings key/value store |
| `005_console_users_extra_grants.sql` | **`console_users.extra_grants_json`** — required for current code; login and profile queries fail without it |
| `006_password_resets.sql` | Password reset tokens table |
| `007_console_users_phone.sql` | **`console_users.phone`** |
| `008_customer_documents_document_type.sql` | **`customer_documents.document_type`** — KYC category per file; required for current upload code |
| `009_customers_uniques_settings_serial.sql` | **`customers.phone_digits`** (generated), **unique** phone digits / NIN / BVN; **`console_settings.id`** auto-increment surrogate |

Before **`009`**, resolve duplicate customer phones (after stripping non-digits), NINs, or BVNs or the migration will fail. Example checks:

```sql
SELECT phone_digits, COUNT(*) c FROM customers GROUP BY phone_digits HAVING c > 1;
SELECT nin, COUNT(*) c FROM customers WHERE nin IS NOT NULL GROUP BY nin HAVING c > 1;
SELECT bvn, COUNT(*) c FROM customers WHERE bvn IS NOT NULL GROUP BY bvn HAVING c > 1;
```

If you see **Unknown column 'extra_grants_json'** in PHP logs, **`005` was not applied** on that database. Run it (and any later migrations you have not run) via phpMyAdmin, MySQL client, or your host’s SQL tool.

The `console_settings` table stores org policy toggles and the role permission matrix (`roles.grants.{role_key}` JSON arrays) edited under **Settings → Roles**, plus optional **`system.maintenance_notice`** (plain-text banner) from **Settings → System**.

Apply **`006_password_resets.sql`** for self-service password reset (forgot-password flow).

**Password reset email:** set `STR_CONSOLE_MAIL_FROM` to a valid `From:` address (and ensure the host can send mail). For local development only, you may set `STR_CONSOLE_DEV_RESET_LINK=1` to show the reset URL in the UI / error log when mail is not sent.

**Public URL for reset links:** if the app is behind a reverse proxy or cron, set `STR_CONSOLE_PUBLIC_URL` (e.g. `https://your-domain.com/str-console`) so emailed links point to the correct origin.

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
