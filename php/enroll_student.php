<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$student_id = $_SESSION['user_id'];
$class_code = trim($_POST['class_code'] ?? '');

if (empty($class_code)) {
    echo json_encode(['success' => false, 'message' => 'Class code is required']);
    exit();
}

// Check if class exists with the given code
$stmt = $pdo->prepare("SELECT c.*, s.subject_name, p.first_name, p.last_name
                      FROM classes c
                      JOIN subjects s ON c.subject_id = s.subject_id
                      JOIN professors p ON c.professor_id = p.professor_id
                      WHERE c.class_code = ?");
$stmt->execute([$class_code]);
$class = $stmt->fetch();

if (!$class) {
    echo json_encode(['success' => false, 'message' => 'Invalid class code. Please check the code and try again.']);
    exit();
}

// Check if student is already enrolled in this class
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_classes WHERE student_id = ? AND class_id = ?");
$stmt->execute([$student_id, $class['class_id']]);
$enrollment_count = $stmt->fetch()['count'];

if ($enrollment_count > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already enrolled in this class.']);
    exit();
}

try {
    // Enroll the student
    $stmt = $pdo->prepare("INSERT INTO student_classes (student_id, class_id, enrolled_at) VALUES (?, ?, NOW())");
    $stmt->execute([$student_id, $class['class_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Successfully enrolled in ' . $class['subject_name'] . '!',
        'class_info' => [
            'class_name' => $class['class_name'],
            'subject_name' => $class['subject_name'],
            'professor' => $class['first_name'] . ' ' . $class['last_name'],
            'schedule' => $class['schedule'],
            'room' => $class['room']
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to enroll: ' . $e->getMessage()]);
}
?>
