-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: sql12.freesqldatabase.com
-- Generation Time: Nov 23, 2025 at 05:21 AM
-- Server version: 5.5.62-0ubuntu0.14.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sql12808059`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `role_target` enum('all','student','teacher') DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `timestamp`, `ip_address`) VALUES
(1, 4, 'APPROVE_STUDENT_ACCOUNT', 'auth', 6, '2025-11-18 12:07:13', '::1'),
(2, 5, 'Updated grade: student_id=3, subject_id=1, period=1, grade=89', 'grades', NULL, '2025-11-18 12:09:07', NULL),
(3, 5, 'Updated grade: student_id=3, subject_id=1, period=2, grade=87', 'grades', NULL, '2025-11-18 12:09:13', NULL),
(4, 5, 'Updated grade: student_id=3, subject_id=1, period=3, grade=91', 'grades', NULL, '2025-11-18 12:09:18', NULL),
(5, 5, 'Updated grade: student_id=0, subject_id=1, period=0, grade=Submitted grades for review', 'grades', NULL, '2025-11-18 12:09:28', NULL),
(6, 4, 'Updated grade: student_id=0, subject_id=0, period=0, grade=Approved grade ID: 1', 'grades', NULL, '2025-11-18 12:10:28', NULL),
(7, 5, 'Updated grade: student_id=3, subject_id=1, period=3, grade=91', 'grades', NULL, '2025-11-18 12:15:21', NULL),
(8, 5, 'Updated grade: student_id=3, subject_id=1, period=2, grade=89', 'grades', NULL, '2025-11-18 12:16:01', NULL),
(9, 5, 'Updated grade: student_id=3, subject_id=1, period=3, grade=90', 'grades', NULL, '2025-11-18 12:18:42', NULL),
(10, 5, 'Updated grade: student_id=3, subject_id=1, period=3, grade=9', 'grades', NULL, '2025-11-18 12:19:36', NULL),
(11, 5, 'Updated grade: student_id=3, subject_id=1, period=3, grade=92', 'grades', NULL, '2025-11-18 12:19:44', NULL),
(12, 4, 'APPROVE_STUDENT_ACCOUNT', 'auth', 7, '2025-11-21 11:55:20', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

CREATE TABLE `auth` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `password` varchar(250) NOT NULL,
  `role` enum('student','admin','teacher','user') DEFAULT 'user',
  `profile_image` mediumblob,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `student_id`, `username`, `teacher_id`, `password`, `role`, `profile_image`, `created_at`, `updated_at`, `status`) VALUES
(4, 2, 'admin', NULL, '$2y$10$EsiuNgdR.5JaKNfSj.2j7eGiIvS3zXNqh23Bqjx84OSkH2xalj0fe', 'admin', NULL, '2025-11-09 17:28:30', NULL, 'active'),
(5, NULL, 'Clarence', 1, '$2y$10$XSQQigPp8NjJ01H3B7sEPeXP7ZZUTTzcJ7HPg/Nt4Wj9J0aMGHKWS', 'teacher', NULL, '2025-11-18 12:02:20', NULL, 'active'),
(6, 3, 'enchong', NULL, '$2y$10$OwFIN4IVgHRTberZ/yMZpOQ7KBH4TniBMp6jTbd19Bbup8OpAAhvC', 'student', NULL, '2025-11-18 12:05:55', '2025-11-18 12:07:09', 'active'),
(7, 4, 'nik', NULL, '$2y$10$GobNHLpFp3JJIlP54oUIc.r9hj2gLhGHjHtPFetigUcnvXXxnbjxq', 'student', NULL, '2025-11-21 11:54:00', '2025-11-21 11:55:16', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `class_assignments`
--

CREATE TABLE `class_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `school_year` varchar(15) NOT NULL,
  `semester` enum('1st','2nd') DEFAULT '1st',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `school_year` varchar(15) NOT NULL,
  `semester` enum('1st','2nd') DEFAULT '1st',
  `prelim` decimal(5,2) DEFAULT '0.00',
  `midterm` decimal(5,2) DEFAULT '0.00',
  `finals` decimal(5,2) DEFAULT '0.00',
  `final_grade` decimal(5,2) DEFAULT NULL,
  `remarks` enum('Passed','Failed','Incomplete') DEFAULT 'Passed',
  `status` enum('Draft','Submitted','Reviewed') DEFAULT 'Draft',
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `teacher_id`, `subject_id`, `school_year`, `semester`, `prelim`, `midterm`, `finals`, `final_grade`, `remarks`, `status`, `updated_at`) VALUES
(1, 3, 1, 1, '2025-2026', '1st', '89.00', '89.00', '92.00', NULL, 'Passed', 'Draft', '2025-11-18 12:19:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipient_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `thread_id` int(10) UNSIGNED DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_id`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(1, 6, 5, 'Your grade for subject ID 1 has been updated. Please check the portal.', 0, '2025-11-18 12:16:11');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `school_year` varchar(15) NOT NULL,
  `adviser_id` int(10) UNSIGNED DEFAULT NULL,
  `max_capacity` int(11) DEFAULT 40,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `profile_image` varchar(150) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `last_name`, `email`, `profile_image`, `deleted_at`, `created_at`, `updated_at`, `gender`, `birthdate`, `address`, `grade_level`, `section_id`, `middle_name`) VALUES
(2, 'Admin', 'User', 'admin@gmail.com', NULL, NULL, '2025-11-09 17:28:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Enchong', 'Miranda', 'e65434913@gmail.com', NULL, NULL, '2025-11-18 12:05:54', NULL, NULL, NULL, NULL, '11', NULL, NULL),
(4, 'Nik', 'Soriano', 'nikaldabasoriano@gmail.com', NULL, NULL, '2025-11-21 11:54:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `subject_bundles`
--

CREATE TABLE `subject_bundles` (
  `id` int(10) UNSIGNED NOT NULL,
  `bundle_name` varchar(100) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `semester` enum('1st','2nd') DEFAULT '1st',
  `school_year` varchar(15) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `subject_bundle_items`
--

CREATE TABLE `subject_bundle_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `bundle_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`id`, `student_id`, `subject_id`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2025-11-18 12:07:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text,
  `grade_level` varchar(20) DEFAULT NULL,
  `semester` enum('1st','2nd') DEFAULT '1st',
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `description`, `grade_level`, `semester`, `teacher_id`, `created_at`, `updated_at`) VALUES
(1, 'Math 001', 'Basic Math', 'mag math tayo tuwing umaga', '11', '1st', 1, '2025-11-18 12:04:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `profile_image` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `specialization`, `contact_number`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'John Clarence', 'Mangaran', 'Miranda', 'johnclarencemiranda382@gmail.com', 'Mag Relapse', '09627875334', NULL, '2025-11-18 12:02:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `two_factor_codes`
--

CREATE TABLE `two_factor_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `two_factor_codes`
--

INSERT INTO `two_factor_codes` (`id`, `user_id`, `code`, `expires_at`, `used`, `created_at`) VALUES
(1, 6, '967110', '2025-11-18 04:15:57', 1, '2025-11-18 12:05:57'),
(2, 7, '272500', '2025-11-21 04:04:01', 1, '2025-11-21 11:54:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_table_name` (`table_name`);

--
-- Indexes for table `auth`
--
ALTER TABLE `auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_username` (`username`),
  ADD UNIQUE KEY `uk_student_id` (`student_id`);

--
-- Indexes for table `class_assignments`
--
ALTER TABLE `class_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adviser_id` (`adviser_id`),
  ADD KEY `idx_grade_year` (`grade_level`, `school_year`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_student_section` (`section_id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`subject_id`),
  ADD KEY `fk_ss_subject` (`subject_id`);

--
-- Indexes for table `subject_bundles`
--
ALTER TABLE `subject_bundles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_grade_semester` (`grade_level`, `semester`, `school_year`);

--
-- Indexes for table `subject_bundle_items`
--
ALTER TABLE `subject_bundle_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bundle_subject` (`bundle_id`, `subject_id`),
  ADD KEY `fk_sbi_subject` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `subjects_teacher_fk` (`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_code` (`user_id`,`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `auth`
--
ALTER TABLE `auth`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `class_assignments`
--
ALTER TABLE `class_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `subject_bundles`
--
ALTER TABLE `subject_bundles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `subject_bundle_items`
--
ALTER TABLE `subject_bundle_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth`
--
ALTER TABLE `auth`
  ADD CONSTRAINT `fk_auth_student_id` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `class_assignments`
--
ALTER TABLE `class_assignments`
  ADD CONSTRAINT `fk_ca_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ca_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_teacher_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_subject_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notif_recipient_fk` FOREIGN KEY (`recipient_id`) REFERENCES `auth` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notif_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `auth` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_section_adviser` FOREIGN KEY (`adviser_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `fk_ss_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ss_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_bundles`
-- (No foreign keys - self-contained table)

--
-- Constraints for table `subject_bundle_items`
--
ALTER TABLE `subject_bundle_items`
  ADD CONSTRAINT `fk_sbi_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `subject_bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sbi_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_teacher_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
