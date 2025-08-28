-- Sample Data for Global Reciprocal College Student Portal
-- Run this after running setup_database.php

-- Insert sample administrators
INSERT INTO administrators (admin_id, first_name, last_name, email, password, created_at, updated_at) VALUES
('ADM001', 'System', 'Administrator', 'admin@grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('ADM002', 'Sarah', 'Johnson', 'sarah.johnson@grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

-- Insert sample professors
INSERT INTO professors (professor_id, employee_id, first_name, last_name, email, password, department, mobile, created_at, updated_at) VALUES
('PROF001', 'EMP001', 'Michael', 'Chen', 'michael.chen@grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Computer Science', '+639123456789', NOW(), NOW()),
('PROF002', 'EMP002', 'Maria', 'Santos', 'maria.santos@grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mathematics', '+639234567890', NOW(), NOW()),
('PROF003', 'EMP003', 'Robert', 'Garcia', 'robert.garcia@grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Engineering', '+639345678901', NOW(), NOW());

-- Insert sample students
INSERT INTO students (student_id, first_name, last_name, middle_name, email, password, mobile, address, created_at, updated_at) VALUES
('STU001', 'John', 'Doe', 'Michael', 'john.doe@student.grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+639456789012', '123 Main St, Manila', NOW(), NOW()),
('STU002', 'Jane', 'Smith', 'Anne', 'jane.smith@student.grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+639567890123', '456 Oak St, Quezon City', NOW(), NOW()),
('STU003', 'David', 'Lee', 'James', 'david.lee@student.grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+639678901234', '789 Pine St, Makati', NOW(), NOW()),
('STU004', 'Sarah', 'Wilson', 'Marie', 'sarah.wilson@student.grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+639789012345', '321 Elm St, Pasig', NOW(), NOW()),
('STU005', 'Mike', 'Brown', 'Thomas', 'mike.brown@student.grc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+639890123456', '654 Maple St, Taguig', NOW(), NOW());

-- Insert sample subjects
INSERT INTO subjects (subject_id, subject_name, subject_code, description, credits, created_at, updated_at) VALUES
('SUB001', 'Introduction to Programming', 'CS101', 'Fundamentals of programming concepts and logic', 3, NOW(), NOW()),
('SUB002', 'Calculus I', 'MATH101', 'Differential and integral calculus', 4, NOW(), NOW()),
('SUB003', 'Database Systems', 'CS201', 'Relational database design and SQL', 3, NOW(), NOW()),
('SUB004', 'Web Development', 'CS301', 'Front-end and back-end web technologies', 3, NOW(), NOW()),
('SUB005', 'Engineering Mathematics', 'ENG101', 'Mathematical methods for engineering', 4, NOW(), NOW());

-- Insert sample classes
INSERT INTO classes (class_id, class_name, class_code, subject_id, professor_id, schedule, room, created_at, updated_at) VALUES
('CLASS001', 'CS101 Section A', 'CS101-A', 'SUB001', 'PROF001', 'MWF 8:00-9:30 AM', 'Room 101', NOW(), NOW()),
('CLASS002', 'MATH101 Section B', 'MATH101-B', 'SUB002', 'PROF002', 'TTH 10:00-11:30 AM', 'Room 202', NOW(), NOW()),
('CLASS003', 'CS201 Section C', 'CS201-C', 'SUB003', 'PROF001', 'MWF 1:00-2:30 PM', 'Room 303', NOW(), NOW()),
('CLASS004', 'CS301 Section A', 'CS301-A', 'SUB004', 'PROF003', 'TTH 2:00-3:30 PM', 'Room 404', NOW(), NOW()),
('CLASS005', 'ENG101 Section D', 'ENG101-D', 'SUB005', 'PROF003', 'MWF 3:00-4:30 PM', 'Room 505', NOW(), NOW());

-- Insert student-class enrollments
INSERT INTO student_classes (student_id, class_id, enrolled_at) VALUES
('STU001', 'CLASS001', NOW()),
('STU001', 'CLASS002', NOW()),
('STU002', 'CLASS001', NOW()),
('STU002', 'CLASS003', NOW()),
('STU003', 'CLASS002', NOW()),
('STU003', 'CLASS004', NOW()),
('STU004', 'CLASS003', NOW()),
('STU004', 'CLASS005', NOW()),
('STU005', 'CLASS004', NOW()),
('STU005', 'CLASS005', NOW());

-- Insert professor-subject assignments
INSERT INTO professor_subjects (professor_id, subject_id, assigned_at) VALUES
('PROF001', 'SUB001', NOW()),
('PROF001', 'SUB003', NOW()),
('PROF002', 'SUB002', NOW()),
('PROF003', 'SUB004', NOW()),
('PROF003', 'SUB005', NOW());

-- Insert sample attendance records
INSERT INTO attendance (student_id, class_id, date, status, remarks, created_at) VALUES
('STU001', 'CLASS001', '2024-01-15', 'Present', 'On time', NOW()),
('STU001', 'CLASS001', '2024-01-17', 'Present', 'On time', NOW()),
('STU001', 'CLASS002', '2024-01-16', 'Late', 'Arrived 10 minutes late', NOW()),
('STU002', 'CLASS001', '2024-01-15', 'Present', 'On time', NOW()),
('STU002', 'CLASS001', '2024-01-17', 'Absent', 'Sick leave', NOW()),
('STU002', 'CLASS003', '2024-01-16', 'Present', 'On time', NOW()),
('STU003', 'CLASS002', '2024-01-16', 'Present', 'On time', NOW()),
('STU003', 'CLASS004', '2024-01-18', 'Present', 'On time', NOW()),
('STU004', 'CLASS003', '2024-01-16', 'Late', 'Arrived 5 minutes late', NOW()),
('STU004', 'CLASS005', '2024-01-15', 'Present', 'On time', NOW()),
('STU005', 'CLASS004', '2024-01-18', 'Present', 'On time', NOW()),
('STU005', 'CLASS005', '2024-01-15', 'Absent', 'Family emergency', NOW());

-- Note: All passwords are hashed versions of "password"
