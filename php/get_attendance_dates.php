<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/db.php';

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    
    try {
        // Get distinct dates for attendance records in this class
        $stmt = $pdo->prepare("SELECT DISTINCT date FROM attendance WHERE class_id = ? ORDER BY date DESC");
        $stmt->execute([$class_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($dates);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Class ID is required']);
}
?>
