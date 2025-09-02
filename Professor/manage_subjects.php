<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: index.php');
    exit();
}

require_once '../php/db.php';

$professor_id = $_SESSION['user_id'];

// Handle subject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add_subject':
                $subject_code = $_POST['subject_code'];
                $subject_name = $_POST['subject_name'];
                $schedule = $_POST['schedule'];
                $room = $_POST['room'];
                
                try {
                    // First insert the subject
                    $stmt = $pdo->prepare("INSERT INTO subjects (subject_id, subject_name, subject_code, credits, created_at, updated_at) 
                                          VALUES (?, ?, ?, 3, NOW(), NOW())");
                    $subject_id = 'SUB' . time();
                    $stmt->execute([$subject_id, $subject_name, $subject_code]);
                    
                    // Generate unique class code
                    $class_code = generateUniqueClassCode($pdo);
                    
                    // Then create a class for this subject
                    $stmt = $pdo->prepare("INSERT INTO classes (class_id, class_name, class_code, subject_id, professor_id, schedule, room, created_at, updated_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $class_id = 'CLASS' . time();
                    $stmt->execute([$class_id, $subject_name . ' Class', $class_code, $subject_id, $professor_id, $schedule, $room]);
                    
                    $success = "Subject and class added successfully! Class Code: " . $class_code;
                } catch (PDOException $e) {
                    $error = "Error adding subject: " . $e->getMessage();
                }
                break;
                
            case 'edit_subject':
                $subject_id = $_POST['subject_id'];
                $subject_code = $_POST['subject_code'];
                $subject_name = $_POST['subject_name'];
                $schedule = $_POST['schedule'];
                $room = $_POST['room'];
                
                try {
                    // Update subject information
                    $stmt = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, updated_at = NOW() 
                                          WHERE subject_id = ?");
                    $stmt->execute([$subject_code, $subject_name, $subject_id]);
                    
                    // Update class information
                    $stmt = $pdo->prepare("UPDATE classes SET schedule = ?, room = ?, updated_at = NOW() 
                                          WHERE subject_id = ? AND professor_id = ?");
                    $stmt->execute([$schedule, $room, $subject_id, $professor_id]);
                    
                    $success = "Subject updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating subject: " . $e->getMessage();
                }
                break;
                
            case 'delete_subject':
                $subject_id = $_POST['subject_id'];
                
                try {
                    // First delete associated classes
                    $stmt = $pdo->prepare("DELETE FROM classes WHERE subject_id = ? AND professor_id = ?");
                    $stmt->execute([$subject_id, $professor_id]);
                    
                    // Then delete the subject if no other professors are using it
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM classes WHERE subject_id = ?");
                    $check_stmt->execute([$subject_id]);
                    $class_count = $check_stmt->fetch()['count'];
                    
                    if ($class_count == 0) {
                        $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
                        $stmt->execute([$subject_id]);
                    }
                    
                    $success = "Subject deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting subject: " . $e->getMessage();
                }
                break;
                
            case 'regenerate_code':
                $subject_id = $_POST['subject_id'];
                
                try {
                    $new_code = generateUniqueClassCode($pdo);
                    $stmt = $pdo->prepare("UPDATE classes SET class_code = ?, updated_at = NOW() 
                                          WHERE subject_id = ? AND professor_id = ?");
                    $stmt->execute([$new_code, $subject_id, $professor_id]);
                    
                    $success = "Class code regenerated successfully! New Code: " . $new_code;
                } catch (PDOException $e) {
                    $error = "Error regenerating code: " . $e->getMessage();
                }
                break;
        }
    }
}

// Function to generate unique class code
function generateUniqueClassCode($pdo) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $maxAttempts = 10;
    
    for ($i = 0; $i < $maxAttempts; $i++) {
        $code = '';
        for ($j = 0; $j < 8; $j++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Check if code already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM classes WHERE class_code = ?");
        $stmt->execute([$code]);
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            return $code;
        }
    }
    
    // If all attempts fail, use timestamp-based code
    return 'CLASS' . time();
}

// Get professor's subjects
$query = "SELECT s.*, c.class_code, c.schedule, c.room
          FROM subjects s
          JOIN classes c ON s.subject_id = c.subject_id
          WHERE c.professor_id = ?
          ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$professor_id]);
$subjects = $stmt->fetchAll();

// Get enrolled students count for each subject
$enrollment_counts = [];
foreach ($subjects as $subject) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_classes sc 
                          JOIN classes c ON sc.class_id = c.class_id 
                          WHERE c.subject_id = ?");
    $stmt->execute([$subject['subject_id']]);
    $enrollment_counts[$subject['subject_id']] = $stmt->fetch()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Global Reciprocal College</title>
    <link rel="stylesheet" href="../css/styles_fixed.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card-enhanced {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-header-enhanced {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-icon-enhanced {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-info-enhanced {
            flex: 1;
        }

        .stat-title-enhanced {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.25rem 0;
        }

        .stat-subtitle-enhanced {
            font-size: 0.9rem;
            color: var(--gray);
            font-weight: 500;
        }

        .stat-metrics-enhanced {
            margin-bottom: 1.5rem;
        }

        .stat-main-metric {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .stat-value-enhanced {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-label-enhanced {
            font-size: 1rem;
            color: var(--gray);
            font-weight: 600;
        }

        .stat-breakdown-enhanced {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-breakdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-breakdown-icon {
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
        }

        .stat-breakdown-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-breakdown-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 500;
        }

        .stat-actions-enhanced {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 1.5rem;
        }

        .stat-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .stat-empty-enhanced {
            text-align: center;
            padding: 3rem 1rem;
        }

        .stat-empty-icon {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .stat-empty-text {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .stat-primary-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .table-header-enhanced {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .table-title-enhanced {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .table-actions-enhanced {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .search-input-enhanced {
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            width: 300px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .search-input-enhanced:focus {
            outline: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .table-container-enhanced {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table-enhanced {
            width: 100%;
            border-collapse: collapse;
        }

        .table-enhanced th {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table-enhanced td {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            font-size: 0.9rem;
        }

        .table-enhanced tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table-enhanced tr:hover {
            background-color: #e9ecef;
        }

        .subject-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .subject-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .class-code-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .regenerate-btn {
            background: var(--warning);
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .regenerate-btn:hover {
            background: var(--warning-dark);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm-enhanced {
            padding: 0.375rem 0.75rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s;
        }

        .btn-primary-enhanced {
            background: var(--primary);
            color: white;
        }

        .btn-primary-enhanced:hover {
            background: var(--primary-dark);
        }

        .btn-warning-enhanced {
            background: var(--warning);
            color: white;
        }

        .btn-warning-enhanced:hover {
            background: var(--warning-dark);
        }

        .btn-danger-enhanced {
            background: var(--danger);
            color: white;
        }

        .btn-danger-enhanced:hover {
            background: var(--danger-dark);
        }

        /* Enhanced Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 0;
            width: 90%;
            max-width: 650px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform: scale(0.9);
            opacity: 0;
            position: relative;
        }

        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideIn {
            from {
                transform: scale(0.9) translateY(-20px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }

        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="50" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .modal-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .modal-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-size: 1.2rem;
            cursor: pointer;
            color: white;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            z-index: 2;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .modal-body {
            padding: 2.5rem;
            background: white;
        }

        .modal-footer {
            padding: 2rem 2.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            background: #f8f9fa;
            border-radius: 0 0 20px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        /* Enhanced Form Styles */
        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
            font-size: 1rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1), 0 4px 16px rgba(0, 123, 255, 0.1);
            transform: translateY(-1px);
        }

        .form-group input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        /* Enhanced Button Styles */
        .btn-enhanced {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .btn-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-enhanced:hover::before {
            left: 100%;
        }

        .btn-primary-enhanced {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(0, 123, 255, 0.2);
        }

        .btn-primary-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 123, 255, 0.3);
        }

        .btn-secondary-enhanced {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(108, 117, 125, 0.2);
        }

        .btn-secondary-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(108, 117, 125, 0.3);
        }

        /* Subject Details Modal Specific Styles */
        .subject-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .detail-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .detail-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .detail-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            word-break: break-word;
        }

        .students-list {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: 1.5rem;
        }

        .student-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }

        .student-item:hover {
            background: #f8f9fa;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .student-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .student-info {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Form Validation Styles */
        .form-group.error input {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
        }

        .form-group.success input {
            border-color: var(--success);
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
        }

        .form-error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .form-success {
            color: var(--success);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 90vh;
                margin: 1rem;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1.5rem;
            }

            .modal-title {
                font-size: 1.25rem;
            }

            .subject-details-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .modal-footer {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-enhanced {
                width: 100%;
                justify-content: center;
            }
        }

        .success-message,
        .error-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-data {
            text-align: center;
            color: var(--gray);
            padding: 3rem;
            font-style: italic;
        }

        .user-dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            min-width: 150px;
            z-index: 1000;
            margin-top: 0.5rem;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: var(--light-gray);
        }

        .dropdown-item:first-child {
            border-radius: 8px 8px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 8px 8px;
        }

        @media (max-width: 768px) {
            .stat-card-enhanced {
                padding: 1.5rem;
            }

            .stat-breakdown-enhanced {
                grid-template-columns: 1fr;
            }

            .table-actions-enhanced {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-enhanced {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <button class="hamburger-menu" id="sidebarToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <span class="navbar-title">Global Reciprocal College</span>
            <span class="navbar-title-mobile">GRC</span>
        </div>
        <div class="navbar-user">
            <span>Welcome, <?php echo $_SESSION['first_name']; ?></span>
            <div class="user-dropdown">
                <button class="dropdown-toggle">⚙️</button>
                <div class="dropdown-menu">
                    <a href="../admin/settings.php" class="dropdown-item">Settings</a>
                    <a href="../php/logout.php" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="professor_dashboard.php" class="sidebar-link">Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="manage_subjects.php" class="sidebar-link active">Manage Subjects</a>
            </li>
            <li class="sidebar-item">
                <a href="professor_manage_schedule.php" class="sidebar-link">Manage Class</a>
            </li>
             <li class="sidebar-item">
                <a href="../admin/settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>

    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="table-header-enhanced">
                <h2 class="table-title-enhanced"><i class="fas fa-book" style="margin-right: 10px;"></i>My Subjects</h2>
                <div class="table-actions-enhanced">
                    <input type="text" id="searchInput" class="search-input-enhanced" placeholder="Search subjects..." onkeyup="filterSubjects()">
                    <button class="stat-primary-btn" onclick="openModal('addSubjectModal')">
                        <i class="fas fa-plus"></i> Add Subject
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card-enhanced">
                    <div class="stat-header-enhanced">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-info-enhanced">
                            <h3 class="stat-title-enhanced">Total Subjects</h3>
                            <span class="stat-subtitle-enhanced">Active subjects</span>
                        </div>
                    </div>
                    <div class="stat-metrics-enhanced">
                        <div class="stat-main-metric">
                            <div class="stat-value-enhanced"><?php echo count($subjects); ?></div>
                            <div class="stat-label-enhanced">Subjects</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card-enhanced">
                    <div class="stat-header-enhanced">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info-enhanced">
                            <h3 class="stat-title-enhanced">Total Students</h3>
                            <span class="stat-subtitle-enhanced">Enrolled students</span>
                        </div>
                    </div>
                    <div class="stat-metrics-enhanced">
                        <div class="stat-main-metric">
                            <div class="stat-value-enhanced"><?php echo array_sum($enrollment_counts); ?></div>
                            <div class="stat-label-enhanced">Students</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container-enhanced">
                <table class="table-enhanced">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Class Code</th>
                            <th>Schedule</th>
                            <th>Room</th>
                            <th>Enrolled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?php echo $subject['subject_code']; ?></td>
                            <td>
                                <a href="javascript:void(0)" onclick="viewSubjectDetails('<?php echo $subject['subject_id']; ?>')" class="subject-link">
                                    <?php echo $subject['subject_name']; ?>
                                </a>
                            </td>
                            <td>
                                <div class="class-code-display">
                                    <span><?php echo $subject['class_code']; ?></span>
                                    <button class="btn-sm-enhanced btn-warning-enhanced" onclick="regenerateCode('<?php echo $subject['subject_id']; ?>')">
                                        <i class="fas fa-sync-alt"></i> Regenerate
                                    </button>
                                </div>
                            </td>
                            <td><?php echo $subject['schedule']; ?></td>
                            <td><?php echo $subject['room']; ?></td>
                            <td><?php echo $enrollment_counts[$subject['subject_id']] ?? 0; ?> students</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-sm-enhanced btn-primary-enhanced" onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_subject">
                                        <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                        <button type="submit" class="btn-sm-enhanced btn-danger-enhanced" onclick="return confirm('Are you sure you want to delete this subject?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <div class="stat-empty-enhanced">
                                    <div class="stat-empty-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="stat-empty-text">No subjects found. Click "Add Subject" to create your first subject.</div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subject Details Modal -->
        <div id="subjectDetailsModal" class="modal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal('subjectDetailsModal')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h3 class="modal-title">
                            <div class="modal-title-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            Subject Details
                        </h3>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="subjectDetailsContent">
                        <!-- Subject details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-enhanced btn-secondary-enhanced" onclick="closeModal('subjectDetailsModal')">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Subject Modal -->
        <div id="addSubjectModal" class="modal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal('addSubjectModal')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h3 class="modal-title">
                            <div class="modal-title-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            Add New Subject
                        </h3>
                    </div>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="add_subject">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-hashtag"></i>
                                Subject Code
                            </label>
                            <input type="text" name="subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-book"></i>
                                Subject Name
                            </label>
                            <input type="text" name="subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-alt"></i>
                                Schedule
                            </label>
                            <input type="text" name="schedule" placeholder="e.g., MWF 9:00-10:30" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                Room
                            </label>
                            <input type="text" name="room" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-enhanced btn-secondary-enhanced" onclick="closeModal('addSubjectModal')">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn-enhanced btn-primary-enhanced">
                                <i class="fas fa-plus"></i>
                                Add Subject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Subject Modal -->
        <div id="editSubjectModal" class="modal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal('editSubjectModal')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h3 class="modal-title">
                            <div class="modal-title-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            Edit Subject
                        </h3>
                    </div>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="edit_subject">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-hashtag"></i>
                                Subject Code
                            </label>
                            <input type="text" name="subject_code" id="edit_subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-book"></i>
                                Subject Name
                            </label>
                            <input type="text" name="subject_name" id="edit_subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-alt"></i>
                                Schedule
                            </label>
                            <input type="text" name="schedule" id="edit_schedule" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                Room
                            </label>
                            <input type="text" name="room" id="edit_room" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-enhanced btn-secondary-enhanced" onclick="closeModal('editSubjectModal')">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn-enhanced btn-primary-enhanced">
                                <i class="fas fa-save"></i>
                                Update Subject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Regenerate Code Form -->
        <form id="regenerateForm" action="" method="POST" style="display: none;">
            <input type="hidden" name="action" value="regenerate_code">
            <input type="hidden" name="subject_id" id="regenerate_subject_id">
        </form>
    </main>

    <script>
        function filterSubjects() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const tbody = document.querySelector('.table-enhanced tbody');
            const rows = tbody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let match = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(query)) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? '' : 'none';
            }
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function viewSubjectDetails(subjectId) {
            // Load subject details via AJAX
            fetch('../php/get_subject_details.php?subject_id=' + subjectId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const subject = data.subject;
                        const content = `
                            <div class="subject-info">
                                <h4>Subject Information</h4>
                                <p><strong>Subject Code:</strong> ${subject.subject_code}</p>
                                <p><strong>Subject Name:</strong> ${subject.subject_name}</p>
                                <p><strong>Class Code:</strong> ${subject.class_code}</p>
                                <p><strong>Schedule:</strong> ${subject.schedule}</p>
                                <p><strong>Room:</strong> ${subject.room}</p>
                            </div>
                            <div class="enrolled-students">
                                <h4>Enrolled Students (${data.students.length})</h4>
                                ${data.students.length > 0 ? 
                                    data.students.map(student => `
                                        <div class="student-item">
                                            <p><strong>${student.student_id}</strong> - ${student.first_name} ${student.last_name}</p>
                                            <p>Email: ${student.email} | Mobile: ${student.mobile}</p>
                                        </div>
                                    `).join('') : 
                                    '<p>No students enrolled yet</p>'}
                            </div>
                            <div class="attendance-summary">
                                <h4>Attendance Summary</h4>
                                <p><strong>Total Classes:</strong> ${data.attendance.total_classes}</p>
                                <p><strong>Average Attendance:</strong> ${data.attendance.average_attendance}%</p>
                            </div>
                        `;
                        document.getElementById('subjectDetailsContent').innerHTML = content;
                        openModal('subjectDetailsModal');
                    } else {
                        alert('Error loading subject details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading subject details');
                });
        }

        function editSubject(subject) {
            document.getElementById('edit_subject_id').value = subject.subject_id;
            document.getElementById('edit_subject_code').value = subject.subject_code;
            document.getElementById('edit_subject_name').value = subject.subject_name;
            document.getElementById('edit_schedule').value = subject.schedule;
            document.getElementById('edit_room').value = subject.room;
            openModal('editSubjectModal');
        }

        function regenerateCode(subjectId) {
            if (confirm('Are you sure you want to regenerate the class code? Students will need the new code to enroll.')) {
                document.getElementById('regenerate_subject_id').value = subjectId;
                document.getElementById('regenerateForm').submit();
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        });

        // Dropdown functionality
        document.querySelector('.dropdown-toggle').addEventListener('click', function() {
            document.querySelector('.dropdown-menu').classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.user-dropdown')) {
                document.querySelector('.dropdown-menu').classList.remove('show');
            }
        });

        // Hamburger menu toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
            if (window.innerWidth <= 900) {
                document.body.classList.toggle('sidebar-open');
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth <= 900 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });
    </script>
</body>
</html>
