-- Campus Pulse — migration for: profile picture, forgot-password
-- Run AFTER schema.sql + seed.sql (existing installs):
--   mysql -u root -p campus_pulse < database/migration_2026_09_26.sql

USE campus_pulse;

-- profile picture path (nullable — falls back to initial-letter avatar in the UI)
ALTER TABLE users
  ADD COLUMN avatar VARCHAR(255) NULL AFTER bio;

-- forgot-password tokens (one active reset per user; re-requesting overwrites the old token)
CREATE TABLE IF NOT EXISTS password_resets (
    user_id     INT UNSIGNED PRIMARY KEY,
    token       VARCHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    CONSTRAINT fk_pwreset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
