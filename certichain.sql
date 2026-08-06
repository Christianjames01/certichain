-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 06, 2026 at 05:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `certichain`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(30) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `role`, `action`, `ip_address`, `created_at`) VALUES
(2, 2, 'student', 'register', '::1', '2026-07-29 14:31:53'),
(3, 2, 'student', 'login', '::1', '2026-07-29 14:42:14'),
(4, 2, 'student', 'login', '::1', '2026-07-30 11:45:16'),
(5, 2, 'student', 'login', '::1', '2026-07-30 11:56:14'),
(6, 3, 'employee', 'login', '::1', '2026-07-30 12:21:38'),
(7, 3, 'employee', 'login', '::1', '2026-07-30 12:24:15'),
(8, 3, 'employee', 'login', '::1', '2026-07-30 12:25:17'),
(9, 2, 'student', 'login', '::1', '2026-08-05 14:40:12'),
(10, 3, 'employee', 'login', '::1', '2026-08-05 14:41:08'),
(11, 2, 'student', 'Submitted request #1', '::1', '2026-08-05 14:41:23'),
(12, 2, 'student', 'Submitted request #2', '127.0.0.1', '2026-08-05 15:27:15'),
(13, 2, 'student', 'login', '127.0.0.1', '2026-08-06 13:06:53'),
(14, 2, 'student', 'login', '::1', '2026-08-06 13:26:27'),
(15, 2, 'student', 'login', '::1', '2026-08-06 13:34:01'),
(16, 3, 'employee', 'login', '::1', '2026-08-06 13:42:34'),
(17, 2, 'student', 'login', '::1', '2026-08-06 13:44:11'),
(18, 2, 'student', 'Submitted request #3', '::1', '2026-08-06 13:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `alumni_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_number` varchar(30) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `program_id` int(11) NOT NULL,
  `year_graduated` year(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(2, 'Academic Records'),
(6, 'Authentication & Verification'),
(9, 'Clearance & Payments'),
(7, 'Curriculum & Course'),
(4, 'Diploma'),
(1, 'Enrollment & Student Status'),
(3, 'Graduation'),
(11, 'Maritime (BSMT) Program Documents'),
(10, 'Printouts & Simple Requests'),
(8, 'Special Purpose'),
(5, 'Transfer & Withdrawal');

-- --------------------------------------------------------

--
-- Table structure for table `claim_schedules`
--

CREATE TABLE `claim_schedules` (
  `schedule_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `queue_number` varchar(30) NOT NULL,
  `claim_date` date NOT NULL,
  `time_slot_start` time NOT NULL,
  `time_slot_end` time NOT NULL,
  `location` varchar(150) DEFAULT 'Holy Cross of Davao College Registrar Office',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(11) NOT NULL,
  `college_code` varchar(20) NOT NULL,
  `college_name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`college_id`, `college_code`, `college_name`, `is_active`, `created_at`) VALUES
(1, 'CCJE', 'College of Criminal Justice Education', 1, '2026-07-28 16:12:22'),
(2, 'CET', 'College of Engineering Technology', 1, '2026-07-28 16:12:22'),
(3, 'CHTM', 'College of Hospitality and Tourism Management', 1, '2026-07-28 16:12:22'),
(4, 'HUSOCOM', 'Humanities, Social Sciences, and Communication', 1, '2026-07-28 16:12:22'),
(5, 'COME', 'College of Maritime Education', 1, '2026-07-28 16:12:22'),
(6, 'SBME', 'School of Business and Management Education', 1, '2026-07-28 16:12:22'),
(7, 'STE', 'School of Teacher Education', 1, '2026-07-28 16:12:22'),
(8, 'GS', 'Graduate School', 1, '2026-07-28 16:12:22');

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `document_type_id` int(11) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `document_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`document_type_id`, `category_id`, `document_name`, `description`, `is_active`) VALUES
(1, 2, 'Transcript of Records (TOR)', 'Official academic record containing subjects, grades, and academic units.', 1),
(2, 1, 'Certificate of Enrollment', 'Proof that a student is currently enrolled.', 1),
(3, 1, 'Certificate of Registration', 'Proof of registration for an academic term.', 1),
(4, 3, 'Certificate of Graduation', 'Official certification that academic requirements have been completed.', 1),
(5, 4, 'Diploma (Certified True Copy)', 'Certified copy of the original diploma.', 1),
(6, 2, 'Certification of Grades', 'Official certification of grades obtained.', 1),
(7, NULL, 'Certificate of Academic Standing', 'Certification of academic status.', 1),
(8, NULL, 'Certificate of Good Standing', 'Certification that the student has good academic standing.', 1),
(9, NULL, 'Certificate of Units Earned', 'Certification of completed academic units.', 1),
(10, 1, 'Certificate of Residency', 'Certification related to residency information.', 1),
(11, 5, 'Honorable Dismissal / Transfer Credential', 'Official document for students transferring to another institution.', 1),
(12, 7, 'Course Description / Syllabus', 'Official course descriptions and syllabus requests.', 1),
(13, 6, 'Authentication of Academic Documents', 'Verification and authentication of academic records.', 1),
(14, 6, 'Verification of Academic Credentials', 'Verification of academic credentials for employers or institutions.', 1),
(15, 6, 'Certified True Copies of Registrar Documents', 'Certified copies of official Registrar documents.', 1),
(16, 1, 'Certificate of Enrollment w/ Units Earned', 'Confirms enrollment together with the total units earned to date.', 1),
(17, 1, 'Certificate of Enrollment w/ Subjects Enrolled', 'Confirms enrollment together with the specific subjects currently taken.', 1),
(18, 1, 'Certificate of Student Status', 'Confirms current standing as an active student.', 1),
(19, 1, 'Certificate of Current Enrollment', 'Verifies enrollment status as of the current academic period.', 1),
(20, 1, 'Certificate of Attendance', 'Confirms attendance in a program, seminar, or academic period.', 1),
(21, 1, 'Certificate of Academic Load', 'States the total units/subjects officially carried this term.', 1),
(22, 1, 'Certificate of Active Student Status', 'Confirms an active, currently enrolled student in good standing.', 1),
(23, 1, 'Certificate of Irregular/Regular Status', 'States whether the student is classified as regular or irregular.', 1),
(24, 1, 'Certificate of Remaining Units/Subjects', 'Lists the units or subjects still needed to complete the program.', 1),
(25, 1, 'Certificate of Cross-Enroll Permit', 'Authorizes taking specific subjects at another institution.', 1),
(26, 2, 'Certified True Copy of Transcript of Records', 'A certified duplicate of the official TOR.', 1),
(27, 2, 'Reference Copy of Transcript of Records', 'A non-transferable copy of the transcript issued for reference only.', 1),
(28, 2, 'Transcript of Records for Employment Purposes', 'TOR issued specifically for job application requirements.', 1),
(29, 2, 'Transcript of Records for Board Examination Purposes', 'TOR formatted for submission to the PRC or licensure boards.', 1),
(30, 2, 'Transcript of Records for Foreign Evaluation', 'TOR prepared for credential evaluation abroad.', 1),
(31, 2, 'Certification of General Weighted Average (GWA)', 'States the computed GWA as of the latest term.', 1),
(32, 2, 'Certification of Academic Records', 'General certification summarizing the academic record.', 1),
(33, 2, 'Certification of Subjects Taken', 'Lists the specific subjects completed.', 1),
(34, 2, 'Certification of Completion of Academic Requirements', 'Confirms completion of all academic requirements for the program.', 1),
(35, 2, 'Certificate of Completed Academic Requirements (CAR)', 'Confirms completion of all academic requirements, with or without the comprehensive exam.', 1),
(36, 2, 'Certificate of Grade for Cross-Enrollee', 'Certifies the grade earned in a subject taken as a cross-enrollee.', 1),
(37, 2, 'Letter of Confirmation', 'Confirms specific academic details on request, in letter form.', 1),
(38, 2, 'Letter of No Objection', 'States the institution has no objection to a specific request or arrangement.', 1),
(39, 3, 'Certificate of Graduation Completion', 'Confirms completion of all graduation requirements.', 1),
(40, 3, 'Certificate of Candidacy for Graduation', 'Confirms official candidacy for the upcoming graduation.', 1),
(41, 3, 'Certificate of Degree Completion', 'Confirms completion of all requirements for the degree.', 1),
(42, 3, 'Certificate of Academic Completion', 'Confirms full academic completion of the program of study.', 1),
(43, 3, 'Certificate of Honors / Awards', 'Documents academic honors or awards received upon graduation.', 1),
(44, 4, 'Original Diploma', 'Official diploma issued upon graduation.', 1),
(45, 4, 'Replacement / Duplicate Diploma', 'Reissuance of a lost, damaged, or destroyed diploma.', 1),
(46, 4, 'Diploma Authentication Certificate', 'Confirms the authenticity of a diploma issued by the institution.', 1),
(47, 5, 'Certificate of Transfer Credential', 'Official credential needed to transfer to another institution.', 1),
(48, 5, 'Certificate of Withdrawal', 'Confirms official withdrawal from a program or term.', 1),
(49, 5, 'Certificate of No Objection for Transfer', 'States the institution has no objection to the transfer.', 1),
(50, 5, 'Certificate of No Record', 'Confirms no enrollment record exists for a given period.', 1),
(51, 6, 'Certification, Authentication & Verification (CAV)', 'Formal CAV confirming the authenticity of academic documents.', 1),
(52, 6, 'School Record Verification Certificate', 'Verifies details in school records upon request.', 1),
(53, 6, 'Certificate of Authenticity of Academic Records', 'Confirms academic records are genuine and unaltered.', 1),
(54, 6, 'Scanning of Documents', 'Digitizes a physical registrar document into a scanned copy for submission.', 1),
(55, 7, 'Certificate of Curriculum', 'Describes the curriculum the student was enrolled under.', 1),
(56, 7, 'Certification of Subjects & Units', 'Lists subjects and corresponding units under the curriculum.', 1),
(57, 7, 'Certification of Medium of Instruction', 'Confirms the language of instruction used in the program.', 1),
(58, 7, 'Certification of Program Completion', 'Confirms completion of a specific academic program.', 1),
(59, 8, 'Certificate for Employment Requirement', 'Prepared specifically to support a job application.', 1),
(60, 8, 'Certificate for Scholarship Requirement', 'Prepared specifically to support a scholarship application.', 1),
(61, 8, 'Certificate for Internship/OJT Requirement', 'Prepared specifically to support internship/OJT placement.', 1),
(62, 8, 'Certificate for Visa Requirement', 'Prepared specifically to support a visa application.', 1),
(63, 8, 'Certificate for Embassy Requirement', 'Prepared specifically for embassy submission requirements.', 1),
(64, 8, 'Certificate for Graduate School Admission', 'Prepared specifically to support graduate school applications.', 1),
(65, 8, 'Certificate for Professional Examination Requirement', 'Prepared specifically to support licensure/board exam applications.', 1),
(66, 8, 'Abu Dhabi Certificate', 'Certification formatted per Abu Dhabi authorities\' requirements for overseas use.', 1),
(67, 8, 'Qatar Certificate', 'Certification formatted per Qatar authorities\' requirements for overseas use.', 1),
(68, 9, 'Online Clearance Routing Across Departments', 'Routes the clearance form to every department online and tracks sign-offs.', 1),
(69, 9, 'Settle Outstanding Balances Online', 'Pay any outstanding fees or balances directly through the portal.', 1),
(70, 10, 'Print-out of Evaluation', 'A printed copy of the subject evaluation/curriculum checklist.', 1),
(71, 10, 'Print-out of Class Schedule', 'A printed copy of the official class schedule for the term.', 1),
(72, 10, 'Print-out of Grades (Report Card)', 'A printed copy of grades for the term.', 1),
(73, 11, 'CHED Memo Order (BSMT)', 'Official CHED memorandum order document required for BSMT students.', 1),
(74, 11, 'Certificate of Completion (BSMT)', 'Confirms completion of the BSMT program requirements.', 1),
(75, 11, 'Certificate of Registration/Competency (COR/COC – BSMT)', 'Registration/competency certificate required for the maritime licensure board exam.', 1),
(76, 11, 'Board Exam Certification w/ Scanned Picture', 'Certification with an attached scanned photo, required for board exam application.', 1),
(77, 11, 'TESDA Certificate', 'Certification of TESDA-related training or qualification.', 1),
(78, 11, 'Special Order (S.O.)', 'Confirms the Special Order number issued by CHED for the program/batch.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_number` varchar(30) NOT NULL,
  `assigned_college_id` int(11) NOT NULL,
  `position_title` varchar(100) DEFAULT 'Registrar Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `employee_number`, `assigned_college_id`, `position_title`) VALUES
(2, 3, 'EMP-0001', 1, 'Registrar Staff');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `message_body` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `message` varchar(500) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `official_receipts`
--

CREATE TABLE `official_receipts` (
  `or_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `or_number` varchar(50) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `program_name` varchar(200) NOT NULL,
  `degree_code` varchar(30) NOT NULL,
  `degree_level` enum('undergraduate','masters','doctoral') NOT NULL DEFAULT 'undergraduate',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `college_id`, `program_name`, `degree_code`, `degree_level`, `is_active`) VALUES
(1, 1, 'Bachelor of Science in Criminology', 'BSCrim', 'undergraduate', 1),
(2, 2, 'Bachelor of Library and Information Science', 'BLIS', 'undergraduate', 1),
(3, 2, 'Bachelor of Science in Computer Engineering', 'BSCpE', 'undergraduate', 1),
(4, 2, 'Bachelor of Science in Computer Science', 'BSCS', 'undergraduate', 1),
(5, 2, 'Bachelor of Science in Electronics Engineering', 'BSECE', 'undergraduate', 1),
(6, 2, 'Bachelor of Science in Information Technology', 'BSIT', 'undergraduate', 1),
(7, 3, 'Bachelor of Science in Hospitality Management', 'BSHM', 'undergraduate', 1),
(8, 3, 'Bachelor of Science in Tourism Management', 'BSTM', 'undergraduate', 1),
(9, 4, 'Bachelor of Arts in Political Science', 'AB Pol Sci', 'undergraduate', 1),
(10, 4, 'Bachelor of Arts in Communication - Journalism and Broadcasting', 'BA Comm-JB', 'undergraduate', 1),
(11, 4, 'Bachelor of Arts in Communication - New Media Studies', 'BA Comm-NMS', 'undergraduate', 1),
(12, 4, 'Bachelor of Arts in Communication - Social Communications', 'BA Comm-SC', 'undergraduate', 1),
(13, 4, 'Bachelor of Arts in Economics', 'AB Econ', 'undergraduate', 1),
(14, 4, 'Bachelor of Arts in English Language Studies', 'AB ELS', 'undergraduate', 1),
(15, 4, 'Bachelor of Arts in History', 'AB History', 'undergraduate', 1),
(16, 4, 'Bachelor of Arts in Philosophy', 'AB Philo', 'undergraduate', 1),
(17, 4, 'Bachelor of Science in Psychology', 'BS Psych', 'undergraduate', 1),
(18, 4, 'Bachelor of Science in Social Work', 'BSSW', 'undergraduate', 1),
(19, 5, 'Bachelor of Science in Marine Transportation', 'BSMT', 'undergraduate', 1),
(20, 6, 'Bachelor of Science in Accountancy', 'BSA', 'undergraduate', 1),
(21, 6, 'Bachelor of Science in Business Administration - Financial Management', 'BSBA-FM', 'undergraduate', 1),
(22, 6, 'Bachelor of Science in Business Administration - Human Resource Management', 'BSBA-HRM', 'undergraduate', 1),
(23, 6, 'Bachelor of Science in Business Administration - Marketing Management', 'BSBA-MM', 'undergraduate', 1),
(24, 6, 'Bachelor of Science in Customs Administration', 'BSCA', 'undergraduate', 1),
(25, 6, 'Bachelor of Science in Management Accounting', 'BSMA', 'undergraduate', 1),
(26, 6, 'Bachelor of Science in Real Estate Management', 'BSREM', 'undergraduate', 1),
(27, 7, 'Bachelor of Early Childhood Education', 'BECEd', 'undergraduate', 1),
(28, 7, 'Bachelor of Elementary Education', 'BEEd', 'undergraduate', 1),
(29, 7, 'Bachelor of Physical Education', 'BPEd', 'undergraduate', 1),
(30, 7, 'Bachelor of Secondary Education - English', 'BSEd-Eng', 'undergraduate', 1),
(31, 7, 'Bachelor of Secondary Education - Filipino', 'BSEd-Fil', 'undergraduate', 1),
(32, 7, 'Bachelor of Secondary Education - Mathematics', 'BSEd-Math', 'undergraduate', 1),
(33, 7, 'Bachelor of Secondary Education - Science', 'BSEd-Sci', 'undergraduate', 1),
(34, 7, 'Bachelor of Secondary Education - Social Studies', 'BSEd-SocStud', 'undergraduate', 1),
(35, 7, 'Bachelor of Secondary Education - Values Education with Catechetics', 'BSEd-ValEd', 'undergraduate', 1),
(36, 7, 'Bachelor of Special Needs Education Generalist', 'BSNEd', 'undergraduate', 1),
(37, 8, 'Doctor of Education major in Educational Management', 'EdD-EM', 'doctoral', 1),
(38, 8, 'Doctor of Philosophy in Theology', 'PhD Theology', 'doctoral', 1),
(39, 8, 'Doctor of Philosophy major in Educational Management', 'PhD-EM', 'doctoral', 1),
(40, 8, 'Master in Management (Non-Thesis Program)', 'MM', 'masters', 1),
(41, 8, 'Master of Arts in Education major in Early Childhood Education', 'MAEd-ECE', 'masters', 1),
(42, 8, 'Master of Arts in Education major in Educational Management', 'MAEd-EM', 'masters', 1),
(43, 8, 'Master of Arts in Education major in English Language Teaching', 'MAEd-ELT', 'masters', 1),
(44, 8, 'Master of Arts in Education major in SpEd Area 1', 'MAEd-SpEd1', 'masters', 1),
(45, 8, 'Master of Arts in Education major in SpEd Area 2', 'MAEd-SpEd2', 'masters', 1),
(46, 8, 'Master of Arts in Education major in SpEd Area 3', 'MAEd-SpEd3', 'masters', 1),
(47, 8, 'Master of Arts in Education major in SpEd Area 5', 'MAEd-SpEd5', 'masters', 1),
(48, 8, 'Master of Arts in Education major in Teaching Filipino', 'MAEd-Fil', 'masters', 1),
(49, 8, 'Master of Arts in Education major in Teaching General Science', 'MAEd-Sci', 'masters', 1),
(50, 8, 'Master of Arts in Education major in Teaching Mathematics', 'MAEd-Math', 'masters', 1),
(51, 8, 'Master of Arts in Education major in Teaching Social Studies', 'MAEd-SocStud', 'masters', 1),
(52, 8, 'Master of Arts in Education major in Teaching Physical Education', 'MAEd-PE', 'masters', 1),
(53, 8, 'Master of Arts in Guidance and Counseling', 'MA-GC', 'masters', 1),
(54, 8, 'Master of Arts in Theology major in Religious Education', 'MA Theo-RE', 'masters', 1),
(55, 8, 'Master of Arts in Theology major in Theological Studies', 'MA Theo-TS', 'masters', 1),
(56, 8, 'Master of Science in Economics', 'MS Econ', 'masters', 1);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `request_code` varchar(30) NOT NULL,
  `requester_user_id` int(11) NOT NULL,
  `requester_role` enum('student','alumni') NOT NULL,
  `program_id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `status` enum('pending_review','requirements_incomplete','under_verification','approved','awaiting_payment','payment_verified','scheduled','completed','rejected','cancelled') NOT NULL DEFAULT 'pending_review',
  `assigned_employee_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `request_code`, `requester_user_id`, `requester_role`, `program_id`, `college_id`, `document_type_id`, `status`, `assigned_employee_id`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'REQ-2026-00001', 2, 'student', 6, 2, 2, 'pending_review', NULL, 'asd', '2026-08-05 14:41:23', '2026-08-05 14:41:23'),
(2, 'REQ-2026-00002', 2, 'student', 6, 2, 7, 'pending_review', NULL, 'asd', '2026-08-05 15:27:15', '2026-08-05 15:27:15'),
(3, 'REQ-2026-00003', 2, 'student', 6, 2, 9, 'pending_review', NULL, 'asdasd', '2026-08-06 13:51:00', '2026-08-06 13:51:00');

--
-- Triggers `requests`
--
DELIMITER $$
CREATE TRIGGER `trg_requests_set_college` BEFORE INSERT ON `requests` FOR EACH ROW BEGIN
    DECLARE v_college_id INT;
    SELECT college_id INTO v_college_id FROM programs WHERE program_id = NEW.program_id;
    SET NEW.college_id = v_college_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_number` varchar(30) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `program_id` int(11) NOT NULL,
  `year_level` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `student_number`, `middle_name`, `phone`, `program_id`, `year_level`) VALUES
(1, 2, '59834547', 'Gerali', '09487970726', 6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_documents`
--

CREATE TABLE `uploaded_documents` (
  `upload_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','alumni','employee','registrar_head') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `failed_login_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `is_active`, `email_verified`, `failed_login_count`, `locked_until`, `created_at`, `updated_at`) VALUES
(2, 'eortouste58@gmail.com', '$2y$10$IHrJcaL3MORgKxdVRLaRseU8YZcLqvPNwo0YdaGYeXXVVs.VKJm22', 'student', 'Edwin', 'Ortouste', 1, 0, 0, NULL, '2026-07-29 14:31:53', '2026-07-29 14:31:53'),
(3, 'employee@hcdc.edu.ph', '$2y$10$qyDOOZoOYtIIbaETCrdNTOLiSrHaY6RlLSqOBaqpBMj28B8u4ayHS', 'employee', 'Registrar', 'Staff', 1, 1, 0, NULL, '2026-07-30 12:19:29', '2026-07-30 12:21:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_logs_user` (`user_id`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`alumni_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_alumni_program` (`program_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `claim_schedules`
--
ALTER TABLE `claim_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD UNIQUE KEY `queue_number` (`queue_number`),
  ADD KEY `idx_schedule_date` (`claim_date`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`),
  ADD UNIQUE KEY `college_code` (`college_code`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`document_type_id`),
  ADD KEY `fk_document_types_category` (`category_id`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_evt_user` (`user_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_number` (`employee_number`),
  ADD KEY `idx_employees_college` (`assigned_college_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_messages_request` (`request_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notif_user` (`user_id`);

--
-- Indexes for table `official_receipts`
--
ALTER TABLE `official_receipts`
  ADD PRIMARY KEY (`or_id`),
  ADD KEY `fk_or_request` (`request_id`),
  ADD KEY `fk_or_verifier` (`verified_by`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_prt_user` (`user_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `idx_programs_college` (`college_id`),
  ADD KEY `idx_programs_degree_code` (`degree_code`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `request_code` (`request_code`),
  ADD KEY `fk_requests_program` (`program_id`),
  ADD KEY `fk_requests_doctype` (`document_type_id`),
  ADD KEY `idx_requests_college_status` (`college_id`,`status`),
  ADD KEY `idx_requests_requester` (`requester_user_id`),
  ADD KEY `idx_requests_employee` (`assigned_employee_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `idx_students_program` (`program_id`);

--
-- Indexes for table `uploaded_documents`
--
ALTER TABLE `uploaded_documents`
  ADD PRIMARY KEY (`upload_id`),
  ADD KEY `fk_uploads_request` (`request_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `alumni_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `claim_schedules`
--
ALTER TABLE `claim_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `document_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `official_receipts`
--
ALTER TABLE `official_receipts`
  MODIFY `or_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `uploaded_documents`
--
ALTER TABLE `uploaded_documents`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni`
--
ALTER TABLE `alumni`
  ADD CONSTRAINT `fk_alumni_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `claim_schedules`
--
ALTER TABLE `claim_schedules`
  ADD CONSTRAINT `fk_schedule_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `document_types`
--
ALTER TABLE `document_types`
  ADD CONSTRAINT `fk_document_types_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `fk_evt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_college` FOREIGN KEY (`assigned_college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `official_receipts`
--
ALTER TABLE `official_receipts`
  ADD CONSTRAINT `fk_or_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_or_verifier` FOREIGN KEY (`verified_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_prt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `fk_programs_college` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`) ON UPDATE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_requests_college` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`),
  ADD CONSTRAINT `fk_requests_doctype` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`document_type_id`),
  ADD CONSTRAINT `fk_requests_employee` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `fk_requests_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `uploaded_documents`
--
ALTER TABLE `uploaded_documents`
  ADD CONSTRAINT `fk_uploads_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
