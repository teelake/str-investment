-- Loan product names must be unique (case-insensitive per utf8mb4_unicode_ci).
-- If this fails on duplicate *data*, deduplicate loan_products.name first, then re-run.
-- Idempotent: safe if uq_loan_products_name already exists (MariaDB 10.5.2+ / MySQL 8.0.13+).

CREATE UNIQUE INDEX IF NOT EXISTS uq_loan_products_name ON loan_products (name);
