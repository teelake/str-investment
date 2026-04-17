-- Optional KYC category per uploaded file (see config/customer_documents.php).
ALTER TABLE customer_documents
  ADD COLUMN document_type VARCHAR(64) NULL
  COMMENT 'Key from str_console_customer_document_types()'
  AFTER uploaded_by_user_id;
