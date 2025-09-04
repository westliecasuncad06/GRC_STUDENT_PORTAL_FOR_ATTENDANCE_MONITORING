<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: ../index.php');
    exit();
}

require_once 'db.php';

$professor_id = $_SESSION['user_id'];

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'add_student':
                $student_id = trim($_POST['student_id']);
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name']);
                $middle_name = trim($_POST['middle_name']);
                $email = trim($_POST['email']);
                $password = md5($_POST['password']);
                $mobile = trim($_POST['mobile']);
                $address = trim($_POST['address']);

                try {
                    // Check if student ID already exists
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE student_id = ?");
                    $stmt->execute([$student_id]);
                    if ($stmt->fetch()['count'] > 0) {
                        $error = "Student ID already exists!";
                        break;
                    }

                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()['count'] > 0) {
                        $error = "Email already exists!";
                        break;
                    }

                    $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, middle_name, email, password, mobile, address, created_at, updated_at)
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$student_id, $first_name, $last_name, $middle_name, $email, $password, $mobile, $address]);
                    $success = "Student added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding student: " . $e->getMessage();
                }
                break;

            case 'edit_student':
                $student_id = trim($_POST['student_id']);
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name']);
                $middle_name = trim($_POST['middle_name']);
                $email = trim($_POST['email']);
                $mobile = trim($_POST['mobile']);
                $address = trim($_POST['address']);

                try {
                    // Check if email already exists for another student
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE email = ? AND student_id != ?");
                    $stmt->execute([$email, $student_id]);
                    if ($stmt->fetch()['count'] > 0) {
                        $error = "Email already exists for another student!";
                        break;
                    }

                    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, middle_name = ?, email = ?, mobile = ?, address = ?, updated_at = NOW()
                                          WHERE student_id = ?");
                    $stmt->execute([$first_name, $last_name, $middle_name, $email, $mobile, $address, $student_id]);
                    $success = "Student updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating student: " . $e->getMessage();
                }
                break;

            case 'delete_student':
                $student_id = trim($_POST['student_id']);

                try {
                    // Check if student has attendance records
                    $check_attendance = $pdo->prepare("SELECT COUNT(*) as attendance_count FROM attendance WHERE student_id = ?");
                    $check_attendance->execute([$student_id]);
                    $attendance_count = $check_attendance->fetch()['attendance_count'];

                    // Check if student has class enrollments
                    $check_enrollment = $pdo->prepare("SELECT COUNT(*) as enrollment_count FROM student_classes WHERE student_id = ?");
                    $check_enrollment->execute([$student_id]);
                    $enrollment_count = $check_enrollment->fetch()['enrollment_count'];

                    if ($attendance_count > 0 || $enrollment_count > 0) {
                        $error = "Cannot delete student: Student has $attendance_count attendance records and $enrollment_count class enrollments. Please remove these records first.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
                        $stmt->execute([$student_id]);
                        $success = "Student deleted successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Error deleting student: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get students enrolled in professor's classes
$query = "SELECT DISTINCT s.*
          FROM students s
          JOIN student_classes sc ON s.student_id = sc.student_id
          JOIN classes c ON sc.class_id = c.class_id
          WHERE c.professor_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$professor_id]);
$students = $stmt->fetchAll();

// Calculate stats
$total_students = count($students);
$new_students_this_month = count(array_filter($students, function($s) {
    return strtotime($s['created_at']) > strtotime('-30 days');
}));
$unique_emails = count(array_unique(array_column($students, 'email')));

// Return JSON response for AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => isset($success),
        'error' => isset($error) ? $error : null,
        'message' => isset($success) ? $success : (isset($error) ? $error : null),
        'students' => $students,
        'stats' => [
            'total' => $total_students,
            'new_this_month' => $new_students_this_month,
            'unique_emails' => $unique_emails
        ]
    ]);
    exit();
}

// For regular page loads, redirect back to the manage students page
if (isset($success) || isset($error)) {
    $message = isset($success) ? 'success=' . urlencode($success) : 'error=' . urlencode($error);
    header('Location: ../UI_UX/Professor/manage_students.html?' . $message);
    exit();
}
?>
