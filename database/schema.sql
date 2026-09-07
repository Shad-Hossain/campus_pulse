-- ============================================================
-- Campus Pulse — database schema (merged)
-- Import with:  mysql -u root -p < database/schema.sql
--
-- This merges two schema drafts. Table/column names follow the
-- version already wired into the PHP app (config/db.php,
-- includes/helpers.php, public/api/*.php) so nothing breaks —
-- e.g. `grants` (not `research_grants`), `alerts.active` (not
-- `is_active`), `campus_status` as a single upserted row (not a
-- history log). The richer additions from the second draft
-- (avatar_url/is_active/updated_at on users, extra achievement
-- fields, event_date/venue on events, quick_links table,
-- indexes) are folded in as additive, non-breaking columns.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS campus_pulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_pulse;

-- ---------------------------------------------------------------
-- Users
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(120)        NOT NULL,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    username        VARCHAR(60)         NOT NULL UNIQUE,
    password_hash   VARCHAR(255)        NOT NULL,
    role            ENUM('student','faculty','admin') NOT NULL DEFAULT 'student',
    department      VARCHAR(100)        DEFAULT NULL,
    bio             TEXT                DEFAULT NULL,
    avatar_url      VARCHAR(255)        DEFAULT NULL,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    last_active     DATETIME            DEFAULT NULL,   -- drives the "live now" counter
    created_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_users_role ON users(role);

-- ---------------------------------------------------------------
-- Notification preferences (one row per user)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notification_settings (
    user_id             INT PRIMARY KEY,
    traffic_alerts      TINYINT(1) NOT NULL DEFAULT 1,
    weather_alerts      TINYINT(1) NOT NULL DEFAULT 1,
    event_reminders     TINYINT(1) NOT NULL DEFAULT 1,
    research_grants     TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Campus news
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS news (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    tag         VARCHAR(60)  DEFAULT 'Notice',
    category    ENUM('Academic','Admin','Club','Competition') NOT NULL DEFAULT 'Academic',
    body        TEXT         DEFAULT NULL,
    created_by  INT          DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_news_category ON news(category);

-- ---------------------------------------------------------------
-- Events
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    category    ENUM('Academic','Club','Competition') NOT NULL DEFAULT 'Academic',
    meta        VARCHAR(150) DEFAULT NULL,     -- freeform display string, e.g. "Sept 20 . Auditorium"
    event_date  DATE         DEFAULT NULL,     -- optional structured date (not yet surfaced in the UI)
    venue       VARCHAR(150) DEFAULT NULL,     -- optional structured venue
    description TEXT         DEFAULT NULL,
    created_by  INT          DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_events_category ON events(category);
CREATE INDEX idx_events_date     ON events(event_date);

-- ---------------------------------------------------------------
-- Study hub resources (notes / question banks)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS resources (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(30)  NOT NULL,
    title       VARCHAR(255) NOT NULL,
    kind        ENUM('notes','qbank') NOT NULL DEFAULT 'notes',
    file_path   VARCHAR(255) DEFAULT NULL,
    uploaded_by INT          DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_resources_course ON resources(course_code);
CREATE INDEX idx_resources_kind   ON resources(kind);

-- ---------------------------------------------------------------
-- Quick links (UCAM, ELMS, notice board, etc.) — previously
-- hardcoded in assets/js/app.js, now data-driven.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quick_links (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(100) NOT NULL,
    url         VARCHAR(255) NOT NULL,
    sort_order  INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Achievements (student / faculty spotlight)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS achievements (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    meta            VARCHAR(150) DEFAULT NULL,   -- freeform display string, e.g. "Aug 2026"
    description     TEXT         DEFAULT NULL,
    achieved_on     DATE         DEFAULT NULL,
    related_user_id INT          DEFAULT NULL,   -- the student/faculty being spotlighted
    created_by      INT          DEFAULT NULL,   -- admin/faculty who posted it
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Research grants (faculty submits, admin approves)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS grants (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(255) NOT NULL,
    description   TEXT         DEFAULT NULL,
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    submitted_by  INT          DEFAULT NULL,
    reviewed_by   INT          DEFAULT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    reviewed_at   DATETIME     DEFAULT NULL,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_grants_status ON grants(status);

-- ---------------------------------------------------------------
-- Location-based alerts (traffic / weather / campus notice ticker)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alerts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    type        ENUM('Traffic','Weather','Campus notice') NOT NULL DEFAULT 'Campus notice',
    active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  INT          DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_alerts_active ON alerts(active);

-- ---------------------------------------------------------------
-- Campus-wide status (single row, admin controlled)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS campus_status (
    id          INT PRIMARY KEY DEFAULT 1,
    status      ENUM('normal','alert','critical') NOT NULL DEFAULT 'normal',
    updated_by  INT       DEFAULT NULL,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Seed data
-- ============================================================

-- Demo accounts — password for all three is: 1234
-- (bcrypt hash, verified against PHP's password_verify())
INSERT IGNORE INTO users (full_name, email, username, password_hash, role, department, bio) VALUES
('MD Nasimul Hoque Foysal', 'foysal@uiu.ac.bd', 'foysal', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'CSE', 'CSE student at UIU, working on Web and Tech projects.'),
('Labib', 'labib@uiu.ac.bd', 'labib', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'CSE', 'CSE student and Competitive Programmer.'),
('Faisal Nion', 'nion@uiu.ac.bd', 'nion', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'CSE', 'Tech enthusiast and Software Engineering student.'),
('Nasimul Hoque', 'nasimul@uiu.ac.bd', 'nasimul', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'CSE', 'Undergraduate student at Department of CSE.'),
('Shad Hossain', 'student1@uiu.ac.bd', 'student1', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'CSE', 'CSE student, building civic-tech side projects.'),
('Abrar Zahin', 'abrar@uiu.ac.bd', 'abrar', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'EEE', 'EEE undergraduate student.'),
('Sabbir Rahman', 'sabbir@uiu.ac.bd', 'sabbir', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'CSE', 'Passionate about Web Development.'),
('Tanvir Ahmed', 'tanvir@uiu.ac.bd', 'tanvir', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'student', 'BBA', 'BBA undergraduate student.'),
('Dr. Farhana', 'faculty1@uiu.ac.bd', 'faculty1', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'faculty', 'CSE', 'Assistant Professor, Dept. of CSE.'),
('Admin User', 'admin1@uiu.ac.bd', 'admin1', '$2b$10$MRBAPt9M2HoxlFQC7po2H.dkx3ZIx7oY0Yy643YVhiX8ELNIQhTI.', 'admin', NULL, 'Campus Pulse platform administrator.');
INSERT INTO notification_settings (user_id, traffic_alerts, weather_alerts, event_reminders, research_grants)
SELECT id, 1, 1, 1, 0 FROM users;

INSERT INTO news (title, tag, category, created_by) VALUES
('Spring 2027 registration opens Sept 15', 'Academic', 'Academic', 3),
('New research grant call for CSE dept', 'Research', 'Academic', 3),
('Campus wifi maintenance this weekend', 'Notice', 'Admin', 3);

INSERT INTO events (title, category, meta, event_date, venue, created_by) VALUES
('Tech Fest 2026', 'Competition', 'Sept 20 . Auditorium', '2026-09-20', 'Auditorium', 3),
('Career Fair', 'Academic', 'Oct 2 . Main Hall', '2026-10-02', 'Main Hall', 3),
('Robotics Club Meetup', 'Club', 'Sept 10 . Room 305', '2026-09-10', 'Room 305', 2);

INSERT INTO resources (course_code, title, kind, uploaded_by) VALUES
('CSE 4165', 'CSE 4165 Midterm Notes', 'notes', 1),
('CSE 3521', 'CSE 3521 Previous Year Question', 'qbank', 1);

INSERT INTO quick_links (title, url, sort_order) VALUES
('UCAM (Student Portal)', 'https://ucam.uiu.ac.bd/Security/Login.aspx', 1),
('ELMS', 'https://elms.uiu.ac.bd/login/index.php', 2),
('UIU Notice Board', 'https://www.uiu.ac.bd/notice/', 3),
('Examcon', 'https://examcon.uiu.ac.bd/', 4),
('CGPA Calculator', 'https://naiimur.me/UIU-CGPA-Calculator/', 5);

INSERT INTO achievements (title, meta, achieved_on, related_user_id, created_by) VALUES
('UIU team wins national hackathon', 'Aug 2026', '2026-08-15', 1, 3);

INSERT INTO grants (title, description, status, submitted_by, reviewed_by, reviewed_at) VALUES
('Low-cost water sensor network', 'IoT-based water quality monitoring for rural areas.', 'approved', 2, 3, NOW()),
('Applied ML for crop yield prediction', '1-2 lines about the research', 'pending', 2, NULL, NULL);

INSERT INTO alerts (title, type, created_by) VALUES
('Heavy traffic near Gate 2', 'Traffic', 3),
('Light rain expected this evening', 'Weather', 3);

INSERT INTO campus_status (id, status, updated_by) VALUES (1, 'normal', 3);

SET FOREIGN_KEY_CHECKS = 1;
