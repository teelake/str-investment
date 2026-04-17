-- Per-booking interest model + product defaults/allowances (reducing balance vs flat monthly on original principal).

ALTER TABLE loan_products
  ADD COLUMN default_interest_basis VARCHAR(32) NOT NULL DEFAULT 'reducing_balance' COMMENT 'reducing_balance | flat_monthly' AFTER period_months,
  ADD COLUMN allow_reducing_balance TINYINT(1) NOT NULL DEFAULT 1 AFTER default_interest_basis,
  ADD COLUMN allow_flat_monthly TINYINT(1) NOT NULL DEFAULT 1 AFTER allow_reducing_balance;

ALTER TABLE loans
  ADD COLUMN interest_basis VARCHAR(32) NOT NULL DEFAULT 'reducing_balance' COMMENT 'Snapshot at booking: reducing_balance | flat_monthly' AFTER rate_percent;
