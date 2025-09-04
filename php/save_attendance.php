<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['class_id']) || !isset($data['date']) || !isset($data['attendance'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$class_id = $data['class_id'];
$date = $data['date'];
$attendance_records = $data['attendance'];

try {
    $pdo->beginTransaction();
    
    foreach ($attendance_records as $record) {
        if (!isset($record['student_id']) || !isset($record['status'])) {
            continue;
        }
        
        $student_id = $record['student_id'];
        $status = $record['status'];
        $remarks = isset($record['remarks']) ? $record['remarks'] : '';
        
        // Check if attendance record already exists
        $check_stmt = $pdo->prepare("SELECT attendance_id FROM attendance WHERE student_id = ? AND class_id = ? AND date = ?");
        $check_stmt->execute([$student_id, $class_id, $date]);
        $existing_record = $check_stmt->fetch();
        
        if ($existing_record) {
            // Update existing record
            $update_stmt = $pdo->prepare("UPDATE attendance SET status = ?, remarks = ?, created_at = NOW() WHERE attendance_id = ?");
            $update_stmt->execute([$status, $remarks, $existing_record['attendance_id']]);
        } else {
            // Insert new record
            $insert_stmt = $pdo->prepare("INSERT INTO attendance (student_id, class_id, date, status, remarks, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $insert_stmt->execute([$student_id, $class_id, $date, $status, $remarks]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Attendance saved successfully']);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
