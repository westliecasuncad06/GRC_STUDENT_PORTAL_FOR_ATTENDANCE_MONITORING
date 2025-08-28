<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    
    try {
        if ($role === 'student') {
            // Student registration
            $student_id = trim($_POST['student_id']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $middle_name = trim($_POST['middle_name']);
            $email = trim($_POST['email']);
            $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            $mobile = trim($_POST['mobile']);
            $address = trim($_POST['address']);
            $created_at = date('Y-m-d H:i:s');
            $updated_at = $created_at;

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                header('Location: ../register.php?error=email_exists&role=student');
                exit();
            }

            // Check if student ID already exists
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            if ($stmt->fetch()) {
                header('Location: ../register.php?error=id_exists&role=student');
                exit();
            }

            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, middle_name, email, password, mobile, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $first_name, $last_name, $middle_name, $email, $password, $mobile, $address, $created_at, $updated_at]);

            header('Location: ../index.php?success=student_registered');
            exit();

        } elseif ($role === 'professor') {
            // Professor registration
            $professor_id = trim($_POST['professor_id']);
            $employee_id = trim($_POST['employee_id']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            $department = trim($_POST['department']);
            $mobile = trim($_POST['mobile']);
            $created_at = date('Y-m-d H:i:s');
            $updated_at = $created_at;

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM professors WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                header('Location: ../register.php?error=email_exists&role=professor');
                exit();
            }

            // Check if professor ID already exists
            $stmt = $pdo->prepare("SELECT * FROM professors WHERE professor_id = ?");
            $stmt->execute([$professor_id]);
            if ($stmt->fetch()) {
                header('Location: ../register.php?error=id_exists&role=professor');
                exit();
            }

            // Insert professor
            $stmt = $pdo->prepare("INSERT INTO professors (professor_id, employee_id, first_name, last_name, email, password, department, mobile, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$professor_id, $employee_id, $first_name, $last_name, $email, $password, $department, $mobile, $created_at, $updated_at]);

            header('Location: ../index.php?success=professor_registered');
            exit();
        }

    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        header('Location: ../register.php?error=database_error&role=' . $role);
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
?>
