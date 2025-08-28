<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

require_once '../db.php';

if (!isset($_GET['student_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Student ID required']);
    exit();
}

$student_id = $_GET['student_id'];
$professor_id = $_SESSION['user_id'];

try {
    // Get student basic info
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit();
    }
    
    // Get classes where this student is enrolled and taught by this professor
    $stmt = $pdo->prepare("SELECT c.*, s.subject_name 
                          FROM classes c 
                          JOIN subjects s ON c.subject_id = s.subject_id
                          JOIN student_classes sc ON c.class_id = sc.class_id
                          WHERE sc.student_id = ? AND c.professor_id = ?");
    $stmt->execute([$student_id, $professor_id]);
    $classes = $stmt->fetchAll();
    
    // Get attendance summary for this student in professor's classes
    $stmt = $pdo->prepare("SELECT 
                          SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                          SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                          SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late
                          FROM attendance a
                          JOIN classes c ON a.class_id = c.class_id
                          WHERE a.student_id = ? AND c.professor_id = ?");
    $stmt->execute([$student_id, $professor_id]);
    $attendance = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'student' => $student,
        'classes' => $classes,
        'attendance' => $attendance
    ]);
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
