-- Soft-deactivate customers (hidden from main list; historical records preserved).

ALTER TABLE customers
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER assigned_user_id,
  ADD KEY idx_customers_active (is_active);
