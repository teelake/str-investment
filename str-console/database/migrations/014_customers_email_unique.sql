-- Non-null customer emails must be unique (multiple NULLs still allowed).
-- Replaces plain index idx_customers_email with a unique index.
-- Idempotent: MariaDB 10.1.4+ (DROP INDEX IF EXISTS); 10.5.2+ / MySQL 8.0.13+ (CREATE INDEX IF NOT EXISTS).

ALTER TABLE customers DROP INDEX IF EXISTS idx_customers_email;

CREATE UNIQUE INDEX IF NOT EXISTS uq_customers_email ON customers (email);
