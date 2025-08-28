<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    try {
        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($mobile)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }

        // Check if password is provided and matches confirmation
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
                exit();
            }
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
                exit();
            }
        }

        // Update user based on role
        if ($role === 'student') {
            $table = 'students';
            $id_field = 'student_id';
            $update_fields = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'mobile' => $mobile,
                'address' => $address,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        } elseif ($role === 'professor') {
            $table = 'professors';
            $id_field = 'professor_id';
            $update_fields = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'mobile' => $mobile,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid user role']);
            exit();
        }

        // Add password to update if provided
        if (!empty($password)) {
            $update_fields['password'] = md5($password);
        }

        // Build update query
        $set_parts = [];
        $params = [];
        foreach ($update_fields as $field => $value) {
            $set_parts[] = "$field = ?";
            $params[] = $value;
        }
        $params[] = $user_id;

        $sql = "UPDATE $table SET " . implode(', ', $set_parts) . " WHERE $id_field = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update session name
        $_SESSION['name'] = $first_name . ' ' . $last_name;

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        
    } catch (PDOException $e) {
        error_log("Update error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
