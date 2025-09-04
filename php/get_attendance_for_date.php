<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'professor' && $_SESSION['role'] !== 'student')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'db.php';

if (!isset($_GET['class_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Class ID is required']);
    exit();
}

$class_id = $_GET['class_id'];
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

try {
    if ($student_id && $date) {
        // Get attendance record for this student, class, and date
        $query = "SELECT a.student_id, s.first_name, s.last_name, a.status, a.remarks, a.date
                  FROM attendance a
                  JOIN students s ON a.student_id = s.student_id
                  WHERE a.class_id = ? AND a.student_id = ? AND a.date = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$class_id, $student_id, $date]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($attendance);
    } elseif ($student_id) {
        // Get all attendance records for this student in the class
        $query = "SELECT a.student_id, s.first_name, s.last_name, a.status, a.remarks, a.date
                  FROM attendance a
                  JOIN students s ON a.student_id = s.student_id
                  WHERE a.class_id = ? AND a.student_id = ?
                  ORDER BY a.date DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$class_id, $student_id]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($attendance);
    } else {
        // Original logic for professor view
        if (!$date) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Date is required for class view']);
            exit();
        }
        
        // Get attendance records for this class and date
        $query = "SELECT a.student_id, s.first_name, s.last_name, a.status, a.remarks
                  FROM attendance a
                  JOIN students s ON a.student_id = s.student_id
                  WHERE a.class_id = ? AND a.date = ?
                  ORDER BY s.last_name, s.first_name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$class_id, $date]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all students in the class to show those without attendance records
        $query_students = "SELECT s.student_id, s.first_name, s.last_name
                           FROM students s
                           JOIN student_classes sc ON s.student_id = sc.student_id
                           WHERE sc.class_id = ?
                           ORDER BY s.last_name, s.first_name";
        
        $stmt_students = $pdo->prepare($query_students);
        $stmt_students->execute([$class_id]);
        $all_students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge attendance records with all students
        $result = [];
        foreach ($all_students as $student) {
            $found = false;
            foreach ($attendance as $record) {
                if ($record['student_id'] === $student['student_id']) {
                    $result[] = $record;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = [
                    'student_id' => $student['student_id'],
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'status' => null,
                    'remarks' => ''
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
