-- Optional customer email (validated in app). Plain index here; 014 upgrades to UNIQUE.
-- Syntax note: indexes must use ADD KEY / ADD INDEX (bare KEY is invalid in ALTER TABLE).
-- Idempotent: MariaDB 10.3.3+ / MySQL 8.0.29+ (column); MariaDB 10.5.2+ (KEY IF NOT EXISTS).

ALTER TABLE customers
  ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER phone;

ALTER TABLE customers
  ADD KEY IF NOT EXISTS idx_customers_email (email);
