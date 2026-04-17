-- Surrogate serial id for settings rows (display/export); natural key remains setting_key.
ALTER TABLE console_settings
  ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD UNIQUE KEY uq_console_settings_id (id);

-- Deduplicate customer phones when comparing (ignores spaces, +, dashes, etc.).
-- Requires MySQL 8+ (REGEXP_REPLACE). NIN/BVN: one non-null value each across customers.
ALTER TABLE customers
  ADD COLUMN phone_digits VARCHAR(32)
    GENERATED ALWAYS AS (REGEXP_REPLACE(phone, '[^0-9]', '')) STORED
    AFTER phone,
  ADD UNIQUE KEY uq_customers_phone_digits (phone_digits),
  ADD UNIQUE KEY uq_customers_nin (nin),
  ADD UNIQUE KEY uq_customers_bvn (bvn);
