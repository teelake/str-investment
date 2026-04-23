-- Optional calendar date for when funds were actually disbursed (bank/cash), if different from
-- disbursed_at (book / interest-ledger value date the client may use for their own rules).

ALTER TABLE loans
  ADD COLUMN disbursement_funds_on DATE NULL DEFAULT NULL
  COMMENT 'When funds were actually released; may differ from disbursed_at (book/interest start)'
  AFTER disbursed_at;
