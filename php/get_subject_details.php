<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if (!isset($_GET['subject_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Subject ID required']);
    exit();
}

$subject_id = $_GET['subject_id'];
$professor_id = $_SESSION['user_id'];

try {
    // Get subject details
    $stmt = $pdo->prepare("SELECT s.*, c.class_code, c.schedule, c.room 
                          FROM subjects s 
                          JOIN classes c ON s.subject_id = c.subject_id 
                          WHERE s.subject_id = ? AND c.professor_id = ?");
    $stmt->execute([$subject_id, $professor_id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Subject not found']);
        exit();
    }
    
    // Get enrolled students
    $stmt = $pdo->prepare("SELECT s.student_id, s.first_name, s.last_name, s.email, s.mobile 
                          FROM students s 
                          JOIN student_classes sc ON s.student_id = sc.student_id 
                          JOIN classes c ON sc.class_id = c.class_id 
                          WHERE c.subject_id = ? 
                          ORDER BY s.last_name, s.first_name");
    $stmt->execute([$subject_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get attendance summary
    $stmt = $pdo->prepare("SELECT 
                          COUNT(DISTINCT a.date) as total_classes,
                          ROUND(SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as average_attendance
                          FROM attendance a 
                          JOIN classes c ON a.class_id = c.class_id 
                          WHERE c.subject_id = ?");
    $stmt->execute([$subject_id]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'subject' => $subject,
        'students' => $students,
        'attendance' => $attendance
    ]);
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
