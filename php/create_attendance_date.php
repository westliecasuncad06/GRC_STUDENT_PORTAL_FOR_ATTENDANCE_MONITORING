<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['class_id']) || !isset($data['date'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Class ID and Date are required']);
    exit();
}

$class_id = $data['class_id'];
$date = $data['date'];

try {
    // Check if date already exists for this class
    $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE class_id = ? AND date = ?");
    $check_stmt->execute([$class_id, $date]);
    $date_exists = $check_stmt->fetch()['count'] > 0;
    
    if ($date_exists) {
        echo json_encode(['success' => true, 'message' => 'Date already exists', 'exists' => true]);
        exit();
    }
    
    // Get all students in the class
    $students_stmt = $pdo->prepare("SELECT student_id FROM student_classes WHERE class_id = ?");
    $students_stmt->execute([$class_id]);
    $students = $students_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert attendance records with default status (Absent)
    $pdo->beginTransaction();
    
    foreach ($students as $student_id) {
        $insert_stmt = $pdo->prepare("INSERT INTO attendance (student_id, class_id, date, status, remarks, created_at) VALUES (?, ?, ?, 'Absent', '', NOW())");
        $insert_stmt->execute([$student_id, $class_id, $date]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Attendance date created successfully', 'exists' => false]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
