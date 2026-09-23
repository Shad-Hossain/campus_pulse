-- Campus Pulse — database schema
-- Run this FIRST, then load demo data with seed.sql:
--   mysql -u root -p < database/schema.sql
--   mysql -u root -p campus_pulse < database/seed.sql

CREATE DATABASE IF NOT EXISTS campus_pulse
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_pulse;

-- ---------- users ----------
CREATE TABLE users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(120)                        NOT NULL,
    username       VARCHAR(50)                         NOT NULL,
    email          VARCHAR(150)                        NOT NULL,
    password_hash  VARCHAR(255)                        NOT NULL,
    role           ENUM('student','faculty','admin')   NOT NULL DEFAULT 'student',
    department     VARCHAR(100)                        NULL,
    bio            VARCHAR(1000)                       NULL,
    is_active      TINYINT(1)                          NOT NULL DEFAULT 1,
    created_at     TIMESTAMP                           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ---------- notification_settings ----------
CREATE TABLE notification_settings (
    user_id           INT UNSIGNED PRIMARY KEY,
    traffic_alerts    TINYINT(1) NOT NULL DEFAULT 0,
    weather_alerts    TINYINT(1) NOT NULL DEFAULT 0,
    event_reminders   TINYINT(1) NOT NULL DEFAULT 0,
    research_alerts   TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_ns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- quick_links (Study Hub) ----------
CREATE TABLE quick_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    url         VARCHAR(500) NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------- news ----------
CREATE TABLE news (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    body        TEXT NULL,
    tag         VARCHAR(50) NULL,
    category    ENUM('Academic','Admin','Club','Competition') NOT NULL DEFAULT 'Academic',
    posted_by   INT UNSIGNED NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_news_user FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- events ----------
CREATE TABLE events (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    category     ENUM('Academic','Club','Competition') NOT NULL,
    event_date   DATE NOT NULL,
    venue        VARCHAR(150) NULL,
    description  TEXT NULL,
    created_by   INT UNSIGNED NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- achievements ----------
CREATE TABLE achievements (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(200) NOT NULL,
    description      TEXT NULL,
    achieved_on      DATE NULL,
    related_user_id  INT UNSIGNED NULL,
    posted_by        INT UNSIGNED NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ach_related_user FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_ach_posted_by FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- research_grants ----------
CREATE TABLE research_grants (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NOT NULL,
    description   TEXT NULL,
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    submitted_by  INT UNSIGNED NOT NULL,
    reviewed_by   INT UNSIGNED NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_grants_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_grants_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- alerts ----------
CREATE TABLE alerts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    type        ENUM('Traffic','Weather','Campus notice') NOT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_by  INT UNSIGNED NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alerts_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- campus_status ----------
CREATE TABLE campus_status (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status      ENUM('normal','alert','critical') NOT NULL DEFAULT 'normal',
    updated_by  INT UNSIGNED NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_user FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- resources (Study Hub uploads) ----------
CREATE TABLE resources (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code  VARCHAR(20) NOT NULL,
    kind         ENUM('notes','qbank') NOT NULL,
    title        VARCHAR(200) NOT NULL,
    file_path    VARCHAR(255) NULL,
    uploaded_by  INT UNSIGNED NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resources_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;