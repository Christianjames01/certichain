-- =====================================================================
-- CERTICHAIN DATABASE SCHEMA
-- College/Program-based Request Routing Extension
-- HCDC Registrar Services System
-- =====================================================================

CREATE DATABASE IF NOT EXISTS certichain CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE certichain;

-- ---------------------------------------------------------------------
-- 1. COLLEGES  (a.k.a. Departments)
-- ---------------------------------------------------------------------
CREATE TABLE colleges (
    college_id      INT AUTO_INCREMENT PRIMARY KEY,
    college_code    VARCHAR(20)  NOT NULL UNIQUE,   -- e.g. CET, SBME, STE
    college_name    VARCHAR(150) NOT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO colleges (college_code, college_name) VALUES
('CCJE',    'College of Criminal Justice Education'),
('CET',     'College of Engineering Technology'),
('CHTM',    'College of Hospitality and Tourism Management'),
('HUSOCOM', 'Humanities, Social Sciences, and Communication'),
('COME',    'College of Maritime Education'),
('SBME',    'School of Business and Management Education'),
('STE',     'School of Teacher Education'),
('GS',      'Graduate School');

-- ---------------------------------------------------------------------
-- 2. PROGRAMS  (courses students register under)
-- Every program belongs to exactly ONE college -> this is the routing key
-- ---------------------------------------------------------------------
CREATE TABLE programs (
    program_id      INT AUTO_INCREMENT PRIMARY KEY,
    college_id      INT NOT NULL,
    program_name    VARCHAR(200) NOT NULL,           -- full name
    degree_code     VARCHAR(30)  NOT NULL,            -- e.g. BSIT, BSCpE
    degree_level    ENUM('undergraduate','masters','doctoral') NOT NULL DEFAULT 'undergraduate',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_programs_college FOREIGN KEY (college_id)
        REFERENCES colleges(college_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_programs_college (college_id),
    INDEX idx_programs_degree_code (degree_code)
) ENGINE=InnoDB;

-- Seed: CCJE
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='CCJE'), 'Bachelor of Science in Criminology', 'BSCrim', 'undergraduate');

-- Seed: CET
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='CET'), 'Bachelor of Library and Information Science', 'BLIS', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='CET'), 'Bachelor of Science in Computer Engineering', 'BSCpE', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='CET'), 'Bachelor of Science in Computer Science', 'BSCS', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='CET'), 'Bachelor of Science in Electronics Engineering', 'BSECE', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='CET'), 'Bachelor of Science in Information Technology', 'BSIT', 'undergraduate');

-- Seed: CHTM
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='CHTM'), 'Bachelor of Science in Hospitality Management', 'BSHM', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='CHTM'), 'Bachelor of Science in Tourism Management', 'BSTM', 'undergraduate');

-- Seed: HUSOCOM
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in Political Science', 'AB Pol Sci', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in Communication - Journalism and Broadcasting', 'BA Comm-JB', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in Communication - New Media Studies', 'BA Comm-NMS', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in Communication - Social Communications', 'BA Comm-SC', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in Economics', 'AB Econ', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in English Language Studies', 'AB ELS', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in History', 'AB History', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Arts in Philosophy', 'AB Philo', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Science in Psychology', 'BS Psych', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='HUSOCOM'), 'Bachelor of Science in Social Work', 'BSSW', 'undergraduate');

-- Seed: COME
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='COME'), 'Bachelor of Science in Marine Transportation', 'BSMT', 'undergraduate');

-- Seed: SBME
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Accountancy', 'BSA', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Business Administration - Financial Management', 'BSBA-FM', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Business Administration - Human Resource Management', 'BSBA-HRM', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Business Administration - Marketing Management', 'BSBA-MM', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Customs Administration', 'BSCA', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Management Accounting', 'BSMA', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='SBME'), 'Bachelor of Science in Real Estate Management', 'BSREM', 'undergraduate');

-- Seed: STE
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Early Childhood Education', 'BECEd', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Elementary Education', 'BEEd', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Physical Education', 'BPEd', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Secondary Education - English', 'BSEd-Eng', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Secondary Education - Filipino', 'BSEd-Fil', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Secondary Education - Mathematics', 'BSEd-Math', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Secondary Education - Science', 'BSEd-Sci', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Secondary Education - Social Studies', 'BSEd-SocStud', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Secondary Education - Values Education with Catechetics', 'BSEd-ValEd', 'undergraduate'),
((SELECT college_id FROM colleges WHERE college_code='STE'), 'Bachelor of Special Needs Education Generalist', 'BSNEd', 'undergraduate');

-- Seed: Graduate School - Doctoral
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Doctor of Education major in Educational Management', 'EdD-EM', 'doctoral'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Doctor of Philosophy in Theology', 'PhD Theology', 'doctoral'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Doctor of Philosophy major in Educational Management', 'PhD-EM', 'doctoral');

-- Seed: Graduate School - Master's
INSERT INTO programs (college_id, program_name, degree_code, degree_level) VALUES
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master in Management (Non-Thesis Program)', 'MM', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Early Childhood Education', 'MAEd-ECE', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Educational Management', 'MAEd-EM', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in English Language Teaching', 'MAEd-ELT', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in SpEd Area 1', 'MAEd-SpEd1', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in SpEd Area 2', 'MAEd-SpEd2', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in SpEd Area 3', 'MAEd-SpEd3', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in SpEd Area 5', 'MAEd-SpEd5', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Teaching Filipino', 'MAEd-Fil', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Teaching General Science', 'MAEd-Sci', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Teaching Mathematics', 'MAEd-Math', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Teaching Social Studies', 'MAEd-SocStud', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Education major in Teaching Physical Education', 'MAEd-PE', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Guidance and Counseling', 'MA-GC', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Theology major in Religious Education', 'MA Theo-RE', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Arts in Theology major in Theological Studies', 'MA Theo-TS', 'masters'),
((SELECT college_id FROM colleges WHERE college_code='GS'), 'Master of Science in Economics', 'MS Econ', 'masters');

-- ---------------------------------------------------------------------
-- 3. USERS  (base auth table shared by all roles)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id         INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('student','alumni','employee','registrar_head') NOT NULL,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. STUDENTS  (extends users) - program_id drives routing
-- ---------------------------------------------------------------------
CREATE TABLE students (
    student_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL UNIQUE,
    student_number  VARCHAR(30) NOT NULL UNIQUE,
    program_id      INT NOT NULL,
    year_level      TINYINT DEFAULT 1,
    CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_students_program FOREIGN KEY (program_id) REFERENCES programs(program_id),
    INDEX idx_students_program (program_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. ALUMNI  (extends users) - also carries the program they graduated from
-- ---------------------------------------------------------------------
CREATE TABLE alumni (
    alumni_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL UNIQUE,
    student_number  VARCHAR(30) NOT NULL,
    program_id      INT NOT NULL,
    year_graduated  YEAR,
    CONSTRAINT fk_alumni_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_alumni_program FOREIGN KEY (program_id) REFERENCES programs(program_id),
    INDEX idx_alumni_program (program_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. EMPLOYEES  (extends users) - assigned_college_id = the ONLY college
--    whose requests this employee is allowed to see/process
-- ---------------------------------------------------------------------
CREATE TABLE employees (
    employee_id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id             INT NOT NULL UNIQUE,
    employee_number     VARCHAR(30) NOT NULL UNIQUE,
    assigned_college_id INT NOT NULL,
    position_title      VARCHAR(100) DEFAULT 'Registrar Staff',
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_employees_college FOREIGN KEY (assigned_college_id) REFERENCES colleges(college_id),
    INDEX idx_employees_college (assigned_college_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. DOCUMENT TYPES
-- ---------------------------------------------------------------------
CREATE TABLE document_types (
    document_type_id   INT AUTO_INCREMENT PRIMARY KEY,
    document_name       VARCHAR(150) NOT NULL,
    description          TEXT,
    is_active            TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO document_types (document_name, description) VALUES
('Transcript of Records (TOR)', 'Official academic record containing subjects, grades, and academic units.'),
('Certificate of Enrollment', 'Proof that a student is currently enrolled.'),
('Certificate of Registration', 'Proof of registration for an academic term.'),
('Certificate of Graduation', 'Official certification that academic requirements have been completed.'),
('Diploma (Certified True Copy)', 'Certified copy of the original diploma.'),
('Certification of Grades', 'Official certification of grades obtained.'),
('Certificate of Academic Standing', 'Certification of academic status.'),
('Certificate of Good Standing', 'Certification that the student has good academic standing.'),
('Certificate of Units Earned', 'Certification of completed academic units.'),
('Certificate of Residency', 'Certification related to residency information.'),
('Honorable Dismissal / Transfer Credential', 'Official document for students transferring to another institution.'),
('Course Description / Syllabus', 'Official course descriptions and syllabus requests.'),
('Authentication of Academic Documents', 'Verification and authentication of academic records.'),
('Verification of Academic Credentials', 'Verification of academic credentials for employers or institutions.'),
('Certified True Copies of Registrar Documents', 'Certified copies of official Registrar documents.');

-- ---------------------------------------------------------------------
-- 8. REQUESTS
--    college_id is DENORMALIZED (copied from the requester's program) at
--    insert time. This is the single field every employee-facing query
--    filters on -> it's what makes "only see requests for your college" work.
-- ---------------------------------------------------------------------
CREATE TABLE requests (
    request_id          INT AUTO_INCREMENT PRIMARY KEY,
    request_code         VARCHAR(30) NOT NULL UNIQUE,          -- e.g. REQ-2026-00015
    requester_user_id    INT NOT NULL,                          -- student or alumni user_id
    requester_role       ENUM('student','alumni') NOT NULL,
    program_id           INT NOT NULL,                          -- program at time of request
    college_id           INT NOT NULL,                          -- ROUTING KEY (derived from program_id)
    document_type_id     INT NOT NULL,
    status                ENUM(
        'pending_review','requirements_incomplete','under_verification',
        'approved','awaiting_payment','payment_verified',
        'scheduled','completed','rejected','cancelled'
    ) NOT NULL DEFAULT 'pending_review',
    assigned_employee_id  INT DEFAULT NULL,                     -- must belong to `college_id`
    remarks               TEXT,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_requests_program FOREIGN KEY (program_id) REFERENCES programs(program_id),
    CONSTRAINT fk_requests_college FOREIGN KEY (college_id) REFERENCES colleges(college_id),
    CONSTRAINT fk_requests_doctype FOREIGN KEY (document_type_id) REFERENCES document_types(document_type_id),
    CONSTRAINT fk_requests_employee FOREIGN KEY (assigned_employee_id) REFERENCES employees(employee_id),

    -- This index is what makes the "employee sees only their college" queries fast
    INDEX idx_requests_college_status (college_id, status),
    INDEX idx_requests_requester (requester_user_id),
    INDEX idx_requests_employee (assigned_employee_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. Supporting tables (uploads, OR, scheduling, messaging, logs)
-- ---------------------------------------------------------------------
CREATE TABLE uploaded_documents (
    upload_id      INT AUTO_INCREMENT PRIMARY KEY,
    request_id     INT NOT NULL,
    file_name      VARCHAR(255) NOT NULL,
    file_path      VARCHAR(500) NOT NULL,
    uploaded_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_uploads_request FOREIGN KEY (request_id) REFERENCES requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE official_receipts (
    or_id          INT AUTO_INCREMENT PRIMARY KEY,
    request_id     INT NOT NULL,
    or_number      VARCHAR(50),
    file_path      VARCHAR(500) NOT NULL,
    verified       TINYINT(1) DEFAULT 0,
    verified_by    INT DEFAULT NULL,
    verified_at    TIMESTAMP NULL,
    uploaded_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_or_request FOREIGN KEY (request_id) REFERENCES requests(request_id) ON DELETE CASCADE,
    CONSTRAINT fk_or_verifier FOREIGN KEY (verified_by) REFERENCES employees(employee_id)
) ENGINE=InnoDB;

CREATE TABLE claim_schedules (
    schedule_id     INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL UNIQUE,
    queue_number    VARCHAR(30) NOT NULL UNIQUE,
    claim_date      DATE NOT NULL,
    time_slot_start TIME NOT NULL,
    time_slot_end   TIME NOT NULL,
    location        VARCHAR(150) DEFAULT 'Holy Cross of Davao College Registrar Office',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_schedule_request FOREIGN KEY (request_id) REFERENCES requests(request_id) ON DELETE CASCADE,
    INDEX idx_schedule_date (claim_date)
) ENGINE=InnoDB;

CREATE TABLE messages (
    message_id      INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    sender_user_id  INT NOT NULL,
    message_body    TEXT NOT NULL,
    sent_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_request FOREIGN KEY (request_id) REFERENCES requests(request_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    request_id       INT DEFAULT NULL,
    message          VARCHAR(500) NOT NULL,
    is_read          TINYINT(1) DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    log_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    role         VARCHAR(30) NOT NULL,
    action        VARCHAR(255) NOT NULL,
    ip_address    VARCHAR(45),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. TRIGGER: auto-populate requests.college_id from the requester's
--     program whenever a new request is inserted. This guarantees the
--     routing key can never be set incorrectly by application code.
-- ---------------------------------------------------------------------
DELIMITER $$

CREATE TRIGGER trg_requests_set_college
BEFORE INSERT ON requests
FOR EACH ROW
BEGIN
    DECLARE v_college_id INT;
    SELECT college_id INTO v_college_id FROM programs WHERE program_id = NEW.program_id;
    SET NEW.college_id = v_college_id;
END$$

DELIMITER ;
