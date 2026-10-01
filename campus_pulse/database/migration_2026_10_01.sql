-- Campus Pulse — migration for: notice delete, suspension / auto-block / probation system
-- Run AFTER schema.sql + seed.sql + migration_2026_09_26.sql:
--   mysql -u root -p campus_pulse < database/migration_2026_10_01.sql

USE campus_pulse;

-- moderation state lives on the user row
--   suspension_count : how many suspensions so far (3rd one => automatic block)
--   probation_count  : probation notices sent since last suspension (3rd one => auto 10-day suspension)
--   suspended_until  : NULL = not suspended; login is refused while this is in the future
--   is_blocked       : 1 = blocked. Only set automatically; only an admin can revoke it.
ALTER TABLE users
  ADD COLUMN suspension_count TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER is_active,
  ADD COLUMN probation_count  TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER suspension_count,
  ADD COLUMN suspended_until  DATETIME         NULL               AFTER probation_count,
  ADD COLUMN is_blocked       TINYINT(1)       NOT NULL DEFAULT 0 AFTER suspended_until;

-- personal notifications shown in the user's bell (e.g. "You are under watch.")
CREATE TABLE IF NOT EXISTS user_notices (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    kind        ENUM('Probation','Suspension') NOT NULL,
    message     VARCHAR(500) NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_notice_user (user_id, is_read),
    CONSTRAINT fk_notice_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- audit trail of every moderation action
CREATE TABLE IF NOT EXISTS moderation_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    admin_id    INT UNSIGNED NULL,          -- NULL = done automatically by the system
    action      ENUM('probation','suspend','auto_suspend','extend','cancel_suspension','auto_block','unblock') NOT NULL,
    days        SMALLINT UNSIGNED NULL,
    note        VARCHAR(255) NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_modlog_user (user_id),
    CONSTRAINT fk_modlog_user  FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_modlog_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
