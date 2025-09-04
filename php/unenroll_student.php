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
$class_id = trim($_POST['class_id'] ?? '');

if (empty($class_id)) {
    echo json_encode(['success' => false, 'message' => 'Class ID is required']);
    exit();
}

// Check if student is enrolled in this class
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_classes WHERE student_id = ? AND class_id = ?");
$stmt->execute([$student_id, $class_id]);
$enrollment_count = $stmt->fetch()['count'];

if ($enrollment_count == 0) {
    echo json_encode(['success' => false, 'message' => 'You are not enrolled in this class.']);
    exit();
}

try {
    // Unenroll the student
    $stmt = $pdo->prepare("DELETE FROM student_classes WHERE student_id = ? AND class_id = ?");
    $stmt->execute([$student_id, $class_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Successfully unenrolled from the class!'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to unenroll: ' . $e->getMessage()]);
}
?>
