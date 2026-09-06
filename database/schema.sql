-- Campus Pulse database schema

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS campus_pulse
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_pulse;

-- users: student, faculty or admin (login & signup)
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(120)        NOT NULL,
    username        VARCHAR(50)         NOT NULL UNIQUE,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    password_hash   VARCHAR(255)        NOT NULL,
    role            ENUM('student','faculty','admin') NOT NULL DEFAULT 'student',
    department      VARCHAR(100)        NULL,
    bio             TEXT                NULL,
    avatar_url      VARCHAR(255)        NULL,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- notification_settings: toggles on the profile page
CREATE TABLE notification_settings (
    user_id             INT UNSIGNED PRIMARY KEY,
    traffic_alerts      TINYINT(1) NOT NULL DEFAULT 1,
    weather_alerts      TINYINT(1) NOT NULL DEFAULT 1,
    event_reminders     TINYINT(1) NOT NULL DEFAULT 1,
    research_alerts     TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- news: home feed campus news
CREATE TABLE news (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200)    NOT NULL,
    body            TEXT            NULL,
    tag             VARCHAR(50)     NULL,          -- e.g. "Research", "Notice"
    category        ENUM('Academic','Admin','Club','Competition') NOT NULL DEFAULT 'Academic',
    posted_by       INT UNSIGNED    NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_news_user FOREIGN KEY (posted_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- events: create/browse/filter events
CREATE TABLE events (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200)    NOT NULL,
    category        ENUM('Academic','Club','Competition') NOT NULL DEFAULT 'Academic',
    event_date      DATE            NULL,
    venue           VARCHAR(150)    NULL,
    description     TEXT            NULL,
    created_by      INT UNSIGNED    NULL,          -- faculty/admin who posted
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_user FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- resources: study hub notes / question bank uploads
CREATE TABLE resources (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code     VARCHAR(20)     NOT NULL,      -- e.g. "CSE 4165"
    kind            ENUM('notes','qbank') NOT NULL DEFAULT 'notes',
    title           VARCHAR(200)    NOT NULL,
    file_path       VARCHAR(255)    NULL,
    uploaded_by     INT UNSIGNED    NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resource_user FOREIGN KEY (uploaded_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- quick_links: UCAM, ELMS, notice board, etc.
CREATE TABLE quick_links (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(100)    NOT NULL,
    url             VARCHAR(255)    NOT NULL,
    sort_order      INT UNSIGNED    NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- research_grants: faculty submissions + admin review
CREATE TABLE research_grants (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200)    NOT NULL,
    description     TEXT            NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    submitted_by    INT UNSIGNED    NOT NULL,      -- faculty user id
    reviewed_by     INT UNSIGNED    NULL,          -- admin user id
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_grant_submitter FOREIGN KEY (submitted_by)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_grant_reviewer FOREIGN KEY (reviewed_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- achievements: student & faculty spotlight
CREATE TABLE achievements (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200)    NOT NULL,
    description     TEXT            NULL,
    achieved_on     DATE            NULL,
    related_user_id INT UNSIGNED    NULL,          -- student/faculty being spotlighted
    posted_by       INT UNSIGNED    NULL,          -- admin/faculty who posted it
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_achieve_related FOREIGN KEY (related_user_id)
        REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_achieve_poster FOREIGN KEY (posted_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- alerts: admin posts to the ticker
CREATE TABLE alerts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200)    NOT NULL,
    type            ENUM('Traffic','Weather','Campus notice') NOT NULL DEFAULT 'Campus notice',
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    created_by      INT UNSIGNED    NULL,          -- admin user id
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alert_user FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- campus_status: history log, app reads the latest row
CREATE TABLE campus_status (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status          ENUM('normal','alert','critical') NOT NULL DEFAULT 'normal',
    updated_by      INT UNSIGNED    NULL,          -- admin user id
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_user FOREIGN KEY (updated_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- indexes for filtering/search used in the UI
CREATE INDEX idx_news_category      ON news(category);
CREATE INDEX idx_events_category    ON events(category);
CREATE INDEX idx_events_date        ON events(event_date);
CREATE INDEX idx_resources_course   ON resources(course_code);
CREATE INDEX idx_resources_kind     ON resources(kind);
CREATE INDEX idx_grants_status      ON research_grants(status);
CREATE INDEX idx_alerts_active      ON alerts(is_active);
CREATE INDEX idx_users_role         ON users(role);

SET FOREIGN_KEY_CHECKS = 1;