-- Optional customer email (validated when present; not unique).

ALTER TABLE customers
  ADD COLUMN email VARCHAR(190) NULL AFTER phone,
  KEY idx_customers_email (email);
