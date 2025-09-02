<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'professor' && $_SESSION['role'] !== 'admin')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access denied']);
    exit();
}

if (!isset($_GET['class_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Class ID is required']);
    exit();
}

$class_id = $_GET['class_id'];

try {
    // Get students enrolled in this class
    $query = "SELECT s.student_id, s.first_name, s.last_name, s.email
              FROM students s
              JOIN student_classes sc ON s.student_id = sc.student_id
              WHERE sc.class_id = ?
              ORDER BY s.last_name, s.first_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$class_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($students);
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
