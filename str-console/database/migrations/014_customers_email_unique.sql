-- Non-null customer emails must be unique (multiple NULLs still allowed).
-- Replaces plain index on email with a unique index.

ALTER TABLE customers
  DROP INDEX idx_customers_email,
  ADD UNIQUE KEY uq_customers_email (email);
