-- One-time password reset tokens (hashed). Expire after 1 hour; invalidated on use.
CREATE TABLE IF NOT EXISTS console_password_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pwd_reset_user (user_id),
  KEY idx_pwd_reset_hash (token_hash),
  KEY idx_pwd_reset_expires (expires_at),
  CONSTRAINT fk_pwd_reset_user FOREIGN KEY (user_id) REFERENCES console_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
