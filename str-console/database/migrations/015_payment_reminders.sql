-- Per-loan optional amount shown in borrower reminder emails (min with ledger step).
-- Sent-log prevents duplicate emails for the same loan + reminder type + scheduled payment date.

ALTER TABLE loans
  ADD COLUMN reminder_installment_amount DECIMAL(14,2) NULL DEFAULT NULL
  COMMENT 'Optional fixed installment for email; min with next ledger step amount' AFTER interest_basis;

CREATE TABLE IF NOT EXISTS loan_payment_reminder_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  loan_id BIGINT UNSIGNED NOT NULL,
  reminder_kind VARCHAR(16) NOT NULL COMMENT 'advance|due',
  anchor_date DATE NOT NULL COMMENT 'Scheduled payment date this reminder refers to',
  recipient_email VARCHAR(255) NOT NULL,
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_loan_reminder_anchor (loan_id, reminder_kind, anchor_date),
  KEY idx_reminder_sent (sent_at),
  CONSTRAINT fk_reminder_loan FOREIGN KEY (loan_id) REFERENCES loans (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
