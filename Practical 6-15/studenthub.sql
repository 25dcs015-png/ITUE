CREATE DATABASE IF NOT EXISTS studenthub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studenthub;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    last_login_at DATETIME NULL,
    remember_token_hash CHAR(64) NULL,
    remember_token_expires_at DATETIME NULL,
    course VARCHAR(100) NOT NULL,
    year VARCHAR(20) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    gpa DECIMAL(3, 2) NULL,
    status ENUM('active', 'deleted') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE students
    ADD COLUMN IF NOT EXISTS role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS remember_token_hash CHAR(64) NULL,
    ADD COLUMN IF NOT EXISTS remember_token_expires_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'deleted') NOT NULL DEFAULT 'active';

CREATE TABLE IF NOT EXISTS events (
    event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    event_time VARCHAR(30) NOT NULL,
    venue VARCHAR(150) NOT NULL,
    event_format VARCHAR(30) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'closed', 'deleted') NOT NULL DEFAULT 'open',
    poster_path VARCHAR(255) NULL,
    UNIQUE KEY unique_event (title, event_date)
) ENGINE=InnoDB;

ALTER TABLE events
    ADD COLUMN IF NOT EXISTS status ENUM('open', 'closed', 'deleted') NOT NULL DEFAULT 'open',
    ADD COLUMN IF NOT EXISTS poster_path VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS event_audit (
    audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NULL,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(30) NOT NULL,
    details VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_event_audit_created_at (created_at),
    CONSTRAINT fk_event_audit_admin FOREIGN KEY (admin_id)
        REFERENCES students (student_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_event (student_id, event_id),
    CONSTRAINT fk_registration_student FOREIGN KEY (student_id)
        REFERENCES students (student_id) ON DELETE CASCADE,
    CONSTRAINT fk_registration_event FOREIGN KEY (event_id)
        REFERENCES events (event_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registration_audit (
    audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL,
    outcome VARCHAR(30) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_registration_audit_created_at (created_at),
    KEY idx_registration_audit_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    details VARCHAR(500) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_logs_created_at (created_at),
    KEY idx_audit_logs_admin_id (admin_id),
    CONSTRAINT fk_audit_logs_admin FOREIGN KEY (admin_id)
        REFERENCES students (student_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO students
    (student_code, name, email, mobile, course, year, gender, gpa)
VALUES
    ('25DCS001', 'Aarav Sharma', 'aarav.sharma@studenthub.edu', '9876543210', 'Computer Science and Engineering', '3rd Year', 'Male', 8.95),
    ('25DCS002', 'Ananya Patel', 'ananya.patel@studenthub.edu', '9823456781', 'Information Technology', '3rd Year', 'Female', 9.20),
    ('25DCS003', 'Rohan Gupta', 'rohan.gupta@studenthub.edu', '9812345678', 'Computer Science and Engineering', '2nd Year', 'Male', 8.40),
    ('25DCS004', 'Diya Joshi', 'diya.joshi@studenthub.edu', '9845612378', 'Electronics and Communication', '4th Year', 'Female', 9.05),
    ('25DCS005', 'Kabir Mehta', 'kabir.mehta@studenthub.edu', '9876123450', 'Mechanical Engineering', '2nd Year', 'Male', 7.80),
    ('25DCS006', 'Isha Verma', 'isha.verma@studenthub.edu', '9834567891', 'Information Technology', '1st Year', 'Female', 8.65),
    ('25DCS007', 'Aditya Singh', 'aditya.singh@studenthub.edu', '9890123456', 'Computer Science and Engineering', '4th Year', 'Male', 9.45),
    ('25DCS008', 'Sneha Nair', 'sneha.nair@studenthub.edu', '9811223344', 'Civil Engineering', '3rd Year', 'Female', 8.10),
    ('25DCS009', 'Vivaan Desai', 'vivaan.desai@studenthub.edu', '9822334455', 'Computer Science and Engineering', '1st Year', 'Male', 8.35),
    ('25DCS010', 'Pooja Reddy', 'pooja.reddy@studenthub.edu', '9833445566', 'Electronics and Communication', '2nd Year', 'Female', 8.75),
    ('25DCS011', 'Karan Malhotra', 'karan.malhotra@studenthub.edu', '9844556677', 'Mechanical Engineering', '4th Year', 'Male', 7.95),
    ('25DCS012', 'Tanvi Shah', 'tanvi.shah@studenthub.edu', '9855667788', 'Information Technology', '3rd Year', 'Female', 9.15),
    ('25DCS013', 'Yash Kulkarni', 'yash.kulkarni@studenthub.edu', '9866778899', 'Civil Engineering', '2nd Year', 'Male', 8.20),
    ('25DCS014', 'Meera Iyer', 'meera.iyer@studenthub.edu', '9877889900', 'Computer Science and Engineering', '3rd Year', 'Female', 9.60),
    ('25DCS015', 'Dev Choudhury', 'dev.choudhury@studenthub.edu', '9888990011', 'Electronics and Communication', '1st Year', 'Male', 8.50),
    ('25DCS016', 'Nisha Kapoor', 'nisha.kapoor@studenthub.edu', '9891234567', 'Information Technology', '2nd Year', 'Female', 8.70),
    ('25DCS017', 'Arjun Rao', 'arjun.rao@studenthub.edu', '9765432109', 'Civil Engineering', '4th Year', 'Male', 8.30),
    ('25DCS018', 'Mira Deshmukh', 'mira.deshmukh@studenthub.edu', '9654321098', 'Mechanical Engineering', '1st Year', 'Female', 9.10),
    ('25DCS019', 'Reyansh Shah', 'reyansh.shah@studenthub.edu', '9543210987', 'Computer Science and Engineering', '2nd Year', 'Male', 8.55),
    ('25DCS020', 'Aditi Menon', 'aditi.menon@studenthub.edu', '9432109876', 'Electronics and Communication', '3rd Year', 'Female', 9.25);

INSERT IGNORE INTO events
    (title, category, event_date, event_time, venue, event_format, description)
VALUES
    ('Coder''s Arcade Hackathon', 'Technical', '2026-09-28', '10:00 AM', 'Main Auditorium', 'Offline', '24-hour national level hackathon testing real-world problem solving and web development skills.'),
    ('Supernova Tech Expo', 'Technical', '2026-10-05', '09:30 AM', 'Campus Central Lawn', 'Hybrid', 'Showcase innovative engineering projects, IoT prototypes, and robotics demonstrations.'),
    ('Coding Battle Championship', 'Technical', '2026-10-12', '11:00 AM', 'Computer Lab 3 & 4', 'Online', 'Fast-paced competitive programming contest with data structures and algorithmic puzzles.'),
    ('Annual Cultural Fest - Tarang', 'Cultural', '2026-10-20', '05:00 PM', 'Open Air Theater', 'Offline', 'Grand cultural evening featuring musical performances, dance battles, and drama skits.'),
    ('Valorant & BGMI Esports Arena', 'Sports', '2026-10-28', '10:00 AM', 'Campus Gaming Lounge', 'Hybrid', 'Inter-college multiplayer gaming showdown with live projection and team brackets.'),
    ('Inter-Department Football Cup', 'Sports', '2026-11-02', '04:00 PM', 'University Sports Complex', 'Offline', 'Knockout football tournament between Engineering, Science, and Management departments.'),
    ('Cybersecurity CTF Quest', 'Technical', '2026-11-10', '02:00 PM', 'Virtual Meet (Zoom)', 'Online', 'Capture the Flag (CTF) security challenge and vulnerability discovery contest.'),
    ('National Parliamentary Debate', 'Cultural', '2026-11-18', '10:00 AM', 'Senate Hall', 'Offline', 'Competitive Oxford-style parliamentary debate on national technology and youth policies.'),
    ('Campus Treasure Hunt', 'Cultural', '2026-11-25', '01:30 PM', 'Central Quadrangle', 'Offline', 'Clue-based campus exploration puzzle race across departments for student squads.'),
    ('Robotics Maze Solver Challenge', 'Technical', '2026-12-01', '10:30 AM', 'Robotics Research Lab', 'Offline', 'Autonomous line-follower and maze-solving robot competition for all engineering branches.'),
    ('Battle of the Bands', 'Cultural', '2026-12-08', '06:00 PM', 'Student Amphitheatre', 'Offline', 'Live rock and acoustic band performances competing for the annual music championship title.'),
    ('Table Tennis & Badminton Tournament', 'Sports', '2026-12-14', '03:00 PM', 'Indoor Sports Arena', 'Offline', 'Singles and doubles table tennis and badminton matches for undergraduate students.'),
    ('Speed Typing & Code Relay', 'Technical', '2026-12-20', '09:00 AM', 'Computer Lab 5', 'Offline', 'Fun technical mini-games featuring blind typing, bug-hunt relay, and syntax sprints.'),
    ('Youth Poetry & Story Slam', 'Cultural', '2027-01-08', '11:00 AM', 'Library Reading Lawn', 'Offline', 'Open mic poetry and creative writing recitation for student writers and poets.'),
    ('Photography & Short Film Gala', 'Cultural', '2027-01-15', '10:00 AM', 'Art Gallery Hall', 'Offline', 'Exhibition of campus life photographs, short student films, and digital artwork.');

INSERT IGNORE INTO registrations (student_id, event_id)
SELECT students.student_id, events.event_id
FROM students
CROSS JOIN events
WHERE students.student_code = '25DCS001'
  AND events.title = 'Coder''s Arcade Hackathon'
  AND events.event_date = '2026-09-28';

INSERT IGNORE INTO registrations (student_id, event_id)
SELECT students.student_id, events.event_id
FROM students
CROSS JOIN events
WHERE students.student_code = '25DCS002'
  AND events.title = 'Supernova Tech Expo'
  AND events.event_date = '2026-10-05';

DROP PROCEDURE IF EXISTS get_events_with_registration_counts;
DELIMITER //
CREATE PROCEDURE get_events_with_registration_counts()
BEGIN
    SELECT events.event_id AS id,
           events.title,
           events.category,
           DATE_FORMAT(events.event_date, '%Y-%m-%d') AS date,
           events.event_time AS time,
           events.venue,
           events.event_format AS format,
           events.description,
           events.status,
           events.poster_path AS poster,
           COALESCE(registration_counts.registration_count, 0) AS registration_count
    FROM events
    LEFT JOIN (
        SELECT event_id, COUNT(*) AS registration_count
        FROM registrations
        GROUP BY event_id
    ) AS registration_counts ON registration_counts.event_id = events.event_id
    WHERE events.status = 'open'
    ORDER BY events.event_date, events.event_id;
END//
DELIMITER ;
