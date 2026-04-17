-- Per-user permission keys merged at login (additive to role grants from Settings → Roles).
ALTER TABLE console_users
  ADD COLUMN extra_grants_json JSON NULL
  COMMENT 'JSON array of permission keys; merged with role grants after login'
  AFTER role_key;
