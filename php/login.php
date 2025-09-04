<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        // Check if user exists in students table
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            // Check if password is hashed with password_hash() or MD5
            if (password_verify($password, $student['password'])) {
                // Password hashed with password_hash()
                $_SESSION['user_id'] = $student['student_id'];
                $_SESSION['role'] = 'student';
                $_SESSION['first_name'] = $student['first_name'];
                // Debugging: Log session variables
                error_log("Session Variables: " . print_r($_SESSION, true));
                header('Location: ../Student/student_dashboard.php');
                exit();
            } elseif (md5($password) === $student['password']) {
                // Password hashed with MD5 (legacy support)
                $_SESSION['user_id'] = $student['student_id'];
                $_SESSION['role'] = 'student';
                $_SESSION['first_name'] = $student['first_name'];
                // Debugging: Log session variables
                error_log("Session Variables: " . print_r($_SESSION, true));
                header('Location: ../Student/student_dashboard.php');
                exit();
            }
        }

        // Check if user exists in professors table
        $stmt = $pdo->prepare("SELECT * FROM professors WHERE email = ?");
        $stmt->execute([$email]);
        $professor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($professor) {
            // Check if password is hashed with password_hash() or MD5
            if (password_verify($password, $professor['password'])) {
                // Password hashed with password_hash()
                $_SESSION['user_id'] = $professor['professor_id'];
                $_SESSION['role'] = 'professor';
                $_SESSION['first_name'] = $professor['first_name'];
                header('Location: ../Professor/professor_dashboard.php');
                exit();
            } elseif (md5($password) === $professor['password']) {
                // Password hashed with MD5 (legacy support)
                $_SESSION['user_id'] = $professor['professor_id'];
                $_SESSION['role'] = 'professor';
                $_SESSION['first_name'] = $professor['first_name'];
                header('Location: ../Professor/professor_dashboard.php');
                exit();
            }
        }

        // Check if user exists in administrators table
        $stmt = $pdo->prepare("SELECT * FROM administrators WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Check if password is hashed with password_hash() or MD5
            if (password_verify($password, $admin['password'])) {
                // Password hashed with password_hash()
                $_SESSION['user_id'] = $admin['admin_id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['first_name'] = $admin['first_name'];
                header('Location: ../Admin/admin_dashboard.php');
                exit();
            } elseif (md5($password) === $admin['password']) {
                // Password hashed with MD5 (legacy support)
                $_SESSION['user_id'] = $admin['admin_id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['first_name'] = $admin['first_name'];
                header('Location: ../Admin/admin_dashboard.php');
                exit();
            }
        }

        // If no user found
        error_log("Login failed for email: $email"); // Debugging statement
        header('Location: ../index.php?error=invalid_credentials');
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Display error for debugging
        error_log("Login error: " . $e->getMessage());
        header('Location: ../index.php?error=database_error');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
