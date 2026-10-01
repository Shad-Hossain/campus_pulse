-- Campus Pulse demo data. Run AFTER schema.sql:
--   mysql -u root -p < database/schema.sql
--   mysql -u root -p campus_pulse < database/seed.sql
-- Demo login (all 3 accounts): password  Demo@1234   -- DELETE/CHANGE these before any real deployment.

SET NAMES utf8mb4;
USE campus_pulse;

INSERT INTO users (full_name, username, email, password_hash, role, department) VALUES
 ('Shad Hossain',  'student1', 'student1@uiu.ac.bd', '$2y$10$Tq5Ozgmo9mvh4HUVh3DNf.cBTR3KhhZ4G1y/3qlJ1/Phx33zN8JtC', 'student', 'CSE'),
 ('Dr. Farhana',   'faculty1', 'faculty1@uiu.ac.bd', '$2y$10$Tq5Ozgmo9mvh4HUVh3DNf.cBTR3KhhZ4G1y/3qlJ1/Phx33zN8JtC', 'faculty', 'CSE'),
 ('Admin User',    'admin1',   'admin1@uiu.ac.bd',   '$2y$10$Tq5Ozgmo9mvh4HUVh3DNf.cBTR3KhhZ4G1y/3qlJ1/Phx33zN8JtC', 'admin',   NULL);

INSERT INTO notification_settings (user_id) SELECT id FROM users;

INSERT INTO quick_links (title, url, sort_order) VALUES
 ('UCAM (Student Portal)', 'https://ucam.uiu.ac.bd/Security/Login.aspx', 1),
 ('ELMS',                  'https://elms.uiu.ac.bd/login/index.php',     2),
 ('UIU Notice Board',      'https://www.uiu.ac.bd/notice/',              3),
 ('Examcon',               'https://examcon.uiu.ac.bd/',                 4),
 ('CGPA Calculator',       'https://naiimur.me/UIU-CGPA-Calculator/',    5);

INSERT INTO news (title, tag, category, posted_by) VALUES
 ('Spring 2027 registration opens Sept 15', 'Academic', 'Academic', 3),
 ('New research grant call for CSE dept',   'Research', 'Academic', 3),
 ('Campus wifi maintenance this weekend',   'Notice',   'Admin',    3);

INSERT INTO events (title, category, event_date, venue, created_by) VALUES
 ('Tech Fest 2026',        'Competition', '2026-10-20', 'Auditorium', 2),
 ('Career Fair',           'Academic',    '2026-11-02', 'Main Hall',  2),
 ('Robotics Club Meetup',  'Club',        '2026-10-10', 'Room 305',   2);

INSERT INTO achievements (title, achieved_on, posted_by) VALUES
 ('UIU team wins national hackathon', '2026-08-15', 3);

INSERT INTO research_grants (title, status, submitted_by, reviewed_by) VALUES
 ('Low-cost water sensor network', 'approved', 2, 3);

INSERT INTO alerts (title, type, created_by) VALUES
 ('Heavy traffic near Gate 2',        'Traffic', 3),
 ('Light rain expected this evening', 'Weather', 3);

INSERT INTO campus_status (status, updated_by) VALUES ('normal', 3);