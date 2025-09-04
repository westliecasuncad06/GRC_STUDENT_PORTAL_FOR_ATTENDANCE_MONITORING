-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 04:28 AM
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
-- Database: `grc_student_portal_for_attendance_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `admin_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`admin_id`, `first_name`, `last_name`, `email`, `password`, `created_at`, `updated_at`) VALUES
('ADM001', 'Westlie', 'Casuncad', 'west@gmail.com', '25f9e794323b453885f5181f1b624d0b', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('ADM002', 'Sarah', 'Johnson', 'sarah.johnson@grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', '2025-08-28 07:13:53', '2025-08-28 07:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `class_id` varchar(20) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `class_id`, `date`, `status`, `remarks`, `created_at`) VALUES
(1, 'STU001', 'CLASS001', '2024-01-15', 'Present', 'On time', '2025-08-28 07:13:53'),
(2, 'STU001', 'CLASS001', '2024-01-17', 'Present', 'On time', '2025-08-28 07:13:53'),
(3, 'STU001', 'CLASS002', '2024-01-16', 'Late', 'Arrived 10 minutes late', '2025-08-28 07:13:53'),
(4, 'STU002', 'CLASS001', '2024-01-15', 'Present', 'On time', '2025-08-28 07:13:53'),
(5, 'STU002', 'CLASS001', '2024-01-17', 'Absent', 'Sick leave', '2025-08-28 07:13:53'),
(6, 'STU002', 'CLASS003', '2024-01-16', 'Present', 'On time', '2025-08-28 07:13:53'),
(7, 'STU003', 'CLASS002', '2024-01-16', 'Present', 'On time', '2025-08-28 07:13:53'),
(8, 'STU003', 'CLASS004', '2024-01-18', 'Present', 'On time', '2025-08-28 07:13:53'),
(9, 'STU004', 'CLASS003', '2024-01-16', 'Late', 'Arrived 5 minutes late', '2025-08-28 07:13:53'),
(10, 'STU004', 'CLASS005', '2024-01-15', 'Present', 'On time', '2025-09-02 06:43:58'),
(11, 'STU005', 'CLASS004', '2024-01-18', 'Present', 'On time', '2025-08-28 07:13:53'),
(12, 'STU005', 'CLASS005', '2024-01-15', 'Excused', 'Family emergency', '2025-09-02 06:43:58'),
(15, 'STU002', 'CLASS003', '2025-08-30', 'Present', '', '2025-08-30 03:07:05'),
(16, 'STU004', 'CLASS003', '2025-08-30', 'Present', '', '2025-08-30 03:07:05'),
(17, 'STU001', 'CLASS1756441963', '2025-08-29', 'Absent', 'Pogi mo po', '2025-08-31 00:04:33'),
(18, 'STU001', 'CLASSTEST1', '2025-08-30', 'Absent', '', '2025-08-30 16:06:04'),
(19, 'STU001', 'CLASSTEST1', '2025-08-31', 'Absent', '', '2025-08-30 16:06:33'),
(20, 'STU001', 'CLASS1756542883', '2025-08-30', 'Absent', '', '2025-08-30 16:36:06'),
(23, 'STU001', 'CLASS001', '2023-08-30', 'Present', '', '2025-08-30 17:06:39'),
(24, 'STU001', 'CLASS1756441963', '2025-08-31', 'Present', 'Congrats pumasok din', '2025-09-02 05:39:32'),
(25, 'STU001', 'CLASS005', '2024-01-15', 'Present', '', '2025-09-02 06:43:58'),
(26, 'STU004', 'CLASS005', '2025-09-02', 'Present', '', '2025-09-02 06:53:25'),
(27, 'STU005', 'CLASS005', '2025-09-02', 'Present', 'DAPAT HINDI KA KASAMA SA DATA NI DENMAR', '2025-09-02 06:53:25'),
(28, 'STU001', 'CLASS005', '2025-09-02', 'Present', 'DAPAT KAY DENMAR KA LANG', '2025-09-02 06:53:25'),
(29, 'STU001', 'CLASS1756767458', '2025-09-02', 'Present', '', '2025-09-02 07:01:03'),
(30, 'STU002', 'CLASS1756767458', '2025-09-02', 'Late', '', '2025-09-02 07:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` varchar(20) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `class_code` varchar(20) NOT NULL,
  `subject_id` varchar(20) DEFAULT NULL,
  `professor_id` varchar(20) DEFAULT NULL,
  `schedule` varchar(100) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `class_code`, `subject_id`, `professor_id`, `schedule`, `room`, `created_at`, `updated_at`) VALUES
('CLASS001', 'CS101 Section A', '5OK7ZE0C', 'SUB001', 'PROF001', 'MWF 8:00-9:30 AM', 'Room 101', '2025-08-28 07:13:53', '2025-08-29 12:19:16'),
('CLASS002', 'MATH101 Section B', 'MATH101-B', 'SUB002', 'PROF002', 'TTH 10:00-11:30 AM', 'Room 202', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('CLASS003', 'CS201 Section C', 'NOW0G94U', 'SUB003', 'PROF001', 'MWF 1:00-2:30 PM', 'Room 303', '2025-08-28 07:13:53', '2025-08-30 14:50:30'),
('CLASS004', 'CS301 Section A', 'CS301-A', 'SUB004', 'PROF003', 'TTH 2:00-3:30 PM', 'Room 404', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('CLASS005', 'ENG101 Section D', '6XL8WS9V', 'SUB005', 'PROF001', 'MWF 3:00-4:30 PM', 'Room 505', '2025-08-28 07:13:53', '2025-09-02 06:47:27'),
('CLASS1756423371', 'Database Management System. Class', '4553218', 'SUB1756423371', 'PF0F004', 'Bahala ka na', 'LAB 3', '2025-08-29 07:22:51', '2025-08-29 07:22:51'),
('CLASS1756425193', 'System Architecture Class', 'N2X1QVPI', 'SUB1756425193', 'PROF001', '321354', 'LAB 81', '2025-08-29 07:53:13', '2025-09-02 06:24:46'),
('CLASS1756441963', 'HOW TO BE HOTDOG Class', 'A3U3ZXL6', 'SUB1756441963', 'PROF001', 'ANYTIME', 'ANYWHERE', '2025-08-29 12:32:43', '2025-08-30 14:50:14'),
('CLASS1756494311', 'HOW TO BE POGI Class', 'AS8O992R', 'SUB1756494311', 'PROF001', 'CCF', 'CCF', '2025-08-30 03:05:11', '2025-08-30 03:05:11'),
('CLASS1756542883', 'EWAN Class', 'WLCV0T8N', 'SUB1756542883', 'PROF001', 'ANY', 'SA LABAS', '2025-08-30 16:34:43', '2025-09-02 06:24:30'),
('CLASS1756767458', 'TUMESTING KA Class', '5EDRKR1R', 'SUB1756767458', 'PROF001', 'Not sure', 'KALSADA', '2025-09-02 06:57:38', '2025-09-02 08:20:16'),
('CLASSTEST1', 'Test Subject Class', 'BSBJK30I', 'SUBTEST1', 'PROF001', 'MWF 9:00-10:00', 'Room 101', '2025-08-30 15:40:29', '2025-09-02 06:24:37');

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `professor_id` varchar(20) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`professor_id`, `employee_id`, `first_name`, `last_name`, `email`, `password`, `department`, `mobile`, `created_at`, `updated_at`) VALUES
('PF0F004', '32165498', 'HATDOG', 'JUMBO', 'g@Gmail.com', '25f9e794323b453885f5181f1b624d0b', 'Unknown', '0999554214', '2025-08-28 13:57:57', '2025-09-02 06:22:59'),
('PROF001', 'EMP001', 'Danhil', 'Baluyot', 'dbaluyot@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'Computer Science', '+639123456789', '2025-08-28 07:13:53', '2025-08-28 08:29:54'),
('PROF002', 'EMP002', 'Maria', 'Santos', 'maria.santos@grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', 'Mathematics', '+639234567890', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('PROF003', 'EMP003', 'Robert', 'Garcia', 'robert.garcia@grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', 'Engineering', '+639345678901', '2025-08-28 07:13:53', '2025-08-28 07:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `professor_subjects`
--

CREATE TABLE `professor_subjects` (
  `assignment_id` int(11) NOT NULL,
  `professor_id` varchar(20) DEFAULT NULL,
  `subject_id` varchar(20) DEFAULT NULL,
  `assigned_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professor_subjects`
--

INSERT INTO `professor_subjects` (`assignment_id`, `professor_id`, `subject_id`, `assigned_at`) VALUES
(1, 'PROF001', 'SUB001', '2025-08-28 07:13:53'),
(2, 'PROF001', 'SUB003', '2025-08-28 07:13:53'),
(3, 'PROF002', 'SUB002', '2025-08-28 07:13:53'),
(4, 'PROF003', 'SUB004', '2025-08-28 07:13:53'),
(5, 'PROF003', 'SUB005', '2025-08-28 07:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `mobile`, `address`, `created_at`, `updated_at`) VALUES
('2017-11547-57', 'KKKK', 'KKKK', 'KKKK', 'KKK@gmail.com', '25f9e794323b453885f5181f1b624d0b', '09123547315', 'ewan', '2025-09-02 06:24:12', '2025-09-02 06:24:12'),
('STU001', 'Denmar', 'Curtivo', 'R', 'dcurtivo@gmail.com', '25f9e794323b453885f5181f1b624d0b', '+639456789012', 'GEDLI LANG', '2025-08-28 07:13:53', '2025-09-02 06:23:24'),
('STU002', 'Jane', 'Smith', 'Anne', 'jane.smith@student.grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', '+639567890123', '456 Oak St, Quezon City', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('STU003', 'David', 'Lee', 'James', 'david.lee@student.grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', '+639678901234', '789 Pine St, Makati', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('STU004', 'Sarah', 'Wilson', 'Marie', 'sarah.wilson@student.grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', '+639789012345', '321 Elm St, Pasig', '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('STU005', 'Mike', 'Brown', 'Thomas', 'mike.brown@student.grc.edu', '5f4dcc3b5aa765d61d8327deb882cf99', '+639890123456', '654 Maple St, Taguig', '2025-08-28 07:13:53', '2025-08-28 07:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `class_id` varchar(20) DEFAULT NULL,
  `enrolled_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`enrollment_id`, `student_id`, `class_id`, `enrolled_at`) VALUES
(1, 'STU001', 'CLASS001', '2025-08-28 07:13:53'),
(2, 'STU001', 'CLASS002', '2025-08-28 07:13:53'),
(3, 'STU002', 'CLASS001', '2025-08-28 07:13:53'),
(4, 'STU002', 'CLASS003', '2025-08-28 07:13:53'),
(5, 'STU003', 'CLASS002', '2025-08-28 07:13:53'),
(6, 'STU003', 'CLASS004', '2025-08-28 07:13:53'),
(7, 'STU004', 'CLASS003', '2025-08-28 07:13:53'),
(8, 'STU004', 'CLASS005', '2025-08-28 07:13:53'),
(9, 'STU005', 'CLASS004', '2025-08-28 07:13:53'),
(10, 'STU005', 'CLASS005', '2025-08-28 07:13:53'),
(11, 'STU001', 'CLASS1756441963', '2025-08-29 12:33:12'),
(12, 'STU001', 'CLASSTEST1', '2025-08-30 15:42:04'),
(13, 'STU001', 'CLASS1756542883', '2025-08-30 16:35:48'),
(35, 'STU001', 'CLASS1756494311', '2025-08-31 00:40:34'),
(36, 'STU001', 'CLASS005', '2025-09-02 06:41:39'),
(37, 'STU001', 'CLASS1756767458', '2025-09-02 06:57:51'),
(38, 'STU002', 'CLASS1756767458', '2025-09-02 06:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `grievances`
--

CREATE TABLE `grievances` (
  `grievance_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','In Progress','Resolved','Rejected') DEFAULT 'Pending',
  `submitted_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `admin_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`, `description`, `credits`, `created_at`, `updated_at`) VALUES
('SUB001', 'Introduction to Programming', 'CS101', 'Fundamentals of programming concepts and logic', 3, '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('SUB002', 'Calculus I', 'MATH101', 'Differential and integral calculus', 4, '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('SUB003', 'Database Systems', 'CS201', 'Relational database design and SQL', 3, '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('SUB004', 'Web Development', 'CS301', 'Front-end and back-end web technologies', 3, '2025-08-28 07:13:53', '2025-08-28 07:13:53'),
('SUB005', 'Engineering Mathematics', 'ENG101', 'Mathematical methods for engineering', 4, '2025-08-28 07:13:53', '2025-09-01 04:44:04'),
('SUB1756423371', 'Database Management System.', 'DBMS', NULL, 3, '2025-08-29 07:22:51', '2025-08-29 07:22:51'),
('SUB1756425193', 'System Architecture', 'SYSARCH', NULL, 3, '2025-08-29 07:53:13', '2025-09-02 06:24:46'),
('SUB1756441963', 'HOW TO BE HOTDOG', 'HD12324', NULL, 3, '2025-08-29 12:32:43', '2025-08-29 12:32:43'),
('SUB1756494311', 'HOW TO BE POGI', 'IT 304', NULL, 3, '2025-08-30 03:05:11', '2025-08-30 03:05:11'),
('SUB1756542883', 'EWAN', '305', NULL, 3, '2025-08-30 16:34:43', '2025-09-02 06:24:30'),
('SUB1756767458', 'TUMESTING KA', 'IT101', NULL, 3, '2025-09-02 06:57:38', '2025-09-02 06:57:38'),
('SUBTEST1', 'Test Subject', 'TS101', NULL, 3, '2025-08-30 15:35:29', '2025-09-02 06:24:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`professor_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `professor_subjects`
--
ALTER TABLE `professor_subjects`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_assignment` (`professor_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `professor_subjects`
--
ALTER TABLE `professor_subjects`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`);

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`professor_id`);

--
-- Constraints for table `professor_subjects`
--
ALTER TABLE `professor_subjects`
  ADD CONSTRAINT `professor_subjects_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`professor_id`),
  ADD CONSTRAINT `professor_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
