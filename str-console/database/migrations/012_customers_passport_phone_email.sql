-- Optional second phone (e.g. passport-linked) and optional contact email.

ALTER TABLE customers
  ADD COLUMN passport_phone VARCHAR(32) NULL AFTER phone,
  ADD COLUMN email VARCHAR(190) NULL AFTER passport_phone,
  ADD KEY idx_customers_email (email(64));
