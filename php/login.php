<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashed_password = md5($password);

    try {
        // Check if user exists in students table
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && $student['password'] === $hashed_password) {
            $_SESSION['user_id'] = $student['student_id'];
            $_SESSION['role'] = 'student';
            $_SESSION['name'] = $student['first_name'] . ' ' . $student['last_name'];
            header('Location: ../student_dashboard.php');
            exit();
        }

        // Check if user exists in professors table
        $stmt = $pdo->prepare("SELECT * FROM professors WHERE email = ?");
        $stmt->execute([$email]);
        $professor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($professor && $professor['password'] === $hashed_password) {
            $_SESSION['user_id'] = $professor['professor_id'];
            $_SESSION['role'] = 'professor';
            $_SESSION['name'] = $professor['first_name'] . ' ' . $professor['last_name'];
            header('Location: ../professor_dashboard.php');
            exit();
        }

        // Check if user exists in administrators table
        $stmt = $pdo->prepare("SELECT * FROM administrators WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && $admin['password'] === $hashed_password) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            header('Location: ../admin_dashboard.php');
            exit();
        }

        // If no user found
        header('Location: ../index.php?error=invalid_credentials');
        exit();

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: ../index.php?error=database_error');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
