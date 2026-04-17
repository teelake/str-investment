-- Loan products, loans, and rolling ledger (interest on outstanding balance per line).

CREATE TABLE IF NOT EXISTS loan_products (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  rate_percent DECIMAL(8,4) NOT NULL,
  period_months INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Months between accrual lines (informational; ledger advances on disburse/payment)',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_loan_products_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loans (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id BIGINT UNSIGNED NOT NULL,
  loan_product_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'draft',
  principal_amount DECIMAL(14,2) NOT NULL,
  rate_percent DECIMAL(8,4) NOT NULL,
  period_months INT UNSIGNED NOT NULL DEFAULT 1,
  assigned_user_id BIGINT UNSIGNED NULL,
  created_by_user_id BIGINT UNSIGNED NULL,
  submitted_at DATETIME NULL,
  approved_by_user_id BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  rejected_reason VARCHAR(500) NULL,
  disbursed_at DATETIME NULL,
  closed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_loans_customer (customer_id),
  KEY idx_loans_status (status),
  KEY idx_loans_assigned (assigned_user_id),
  CONSTRAINT fk_loans_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT,
  CONSTRAINT fk_loans_product FOREIGN KEY (loan_product_id) REFERENCES loan_products (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loan_ledger_lines (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  loan_id BIGINT UNSIGNED NOT NULL,
  line_no INT UNSIGNED NOT NULL,
  period_date DATE NOT NULL,
  opening_balance DECIMAL(14,2) NOT NULL,
  rate_percent DECIMAL(8,4) NOT NULL,
  interest_amount DECIMAL(14,2) NOT NULL,
  amount_due DECIMAL(14,2) NOT NULL,
  payment_date DATE NULL,
  payment_amount DECIMAL(14,2) NULL,
  closing_balance DECIMAL(14,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_loan_line (loan_id, line_no),
  KEY idx_ledger_loan (loan_id),
  CONSTRAINT fk_ledger_loan FOREIGN KEY (loan_id) REFERENCES loans (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
