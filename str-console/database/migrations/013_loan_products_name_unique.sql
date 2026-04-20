-- Loan product names must be unique (case-insensitive per utf8mb4_unicode_ci).
-- If this fails, deduplicate existing `loan_products.name` values first, then re-run.

ALTER TABLE loan_products
  ADD UNIQUE KEY uq_loan_products_name (name);
