-- Soft-deactivate customers (hidden from main list; historical records preserved).
-- Idempotent: safe if is_active / index already exist (MariaDB 10.3.3+ / MySQL 8.0.29+ for IF NOT EXISTS on columns;
-- MariaDB 10.5.2+ for ADD KEY IF NOT EXISTS). If your server is older, run only the parts you still need.

ALTER TABLE customers
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER assigned_user_id;

ALTER TABLE customers
  ADD KEY IF NOT EXISTS idx_customers_active (is_active);
