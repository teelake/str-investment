-- Optional work phone for console users (profile + admin user forms).
ALTER TABLE console_users
  ADD COLUMN phone VARCHAR(32) NULL
  AFTER full_name;
