<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: index.php');
    exit();
}
// $_SESSION['user_id'] = 'professor_test_id';
// $_SESSION['role'] = 'professor';
// $_SESSION['first_name'] = 'TestProfessor';

require_once '../php/db.php';

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

// Handle schedule actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $professor_id = $_SESSION['user_id'];
        
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
                
            case 'mark_attendance':
                $class_id = $_POST['class_id'];
                $date = $_POST['date'];
                $attendance_data = json_decode($_POST['attendance'], true);
                
                try {
                    // Delete existing attendance for this class and date
                    $stmt = $pdo->prepare("DELETE FROM attendance WHERE class_id = ? AND date = ?");
                    $stmt->execute([$class_id, $date]);
                    
                    // Insert new attendance records
                    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, class_id, date, status, remarks, created_at) 
                                          VALUES (?, ?, ?, ?, ?, NOW())");
                    
                    foreach ($attendance_data as $student_id => $data) {
                        $stmt->execute([$student_id, $class_id, $date, $data['status'], $data['remarks']]);
                    }
                    
                    $success = "Attendance recorded successfully!";
                } catch (PDOException $e) {
                    $error = "Error recording attendance: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get professor's subjects
$professor_id = $_SESSION['user_id'];
$query = "SELECT s.*, c.class_id, c.class_code, c.schedule, c.room 
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
    <title>Manage Classes - Global Reciprocal College</title>
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
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
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
            cursor: pointer;
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

        .stat-details-enhanced {
            margin-bottom: 1.5rem;
        }

        .stat-detail-enhanced {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-detail-enhanced:last-child {
            border-bottom: none;
        }

        .stat-detail-icon {
            font-size: 1.1rem;
            color: var(--primary);
            width: 20px;
        }

        .stat-detail-text {
            font-size: 0.9rem;
            color: var(--dark);
            font-weight: 500;
        }

        .stat-actions-enhanced {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 1.5rem;
        }

        .stat-action-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: background-color 0.2s;
            margin-right: 0.5rem;
        }

        .stat-action-btn:hover {
            background: var(--primary-dark);
        }

        .stat-action-btn.secondary {
            background: var(--secondary);
        }

        .stat-action-btn.secondary:hover {
            background: var(--secondary-dark);
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
            margin-top: 1rem;
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

        .no-data {
            text-align: center;
            color: var(--gray);
            padding: 3rem 1rem;
            font-style: italic;
        }

        .success-message, .error-message {
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

        @media (max-width: 768px) {
            .stat-card-enhanced {
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            padding: 0;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .modal-close:hover {
            background-color: #f8f9fa;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        /* Attendance Modal Specific */
        .modal-attendance {
            max-width: 800px;
        }

        .enrolled-students-section {
            margin-bottom: 2rem;
        }

        .enrolled-students-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .enrolled-students-table-container {
            overflow-x: auto;
        }

        .enrolled-students-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .enrolled-students-table th,
        .enrolled-students-table td {
            padding: 0.75rem;
            border: 1px solid var(--light-gray);
            text-align: left;
        }

        .enrolled-students-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .enrolled-students-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .date-input-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .date-input-group input {
            flex: 1;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 0.75rem;
            border: 1px solid var(--light-gray);
            text-align: left;
        }

        .attendance-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .attendance-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .status-select {
            padding: 0.25rem 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-late { background-color: #fff3cd; color: #856404; }
        .status-excused { background-color: #d1ecf1; color: #0c5460; }

        .date-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .date-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            cursor: pointer;
        }

        .date-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .collapse-icon {
            font-size: 1.2rem;
            transition: transform 0.2s;
        }

        .date-content {
            padding: 1.5rem;
            display: none;
        }

        .attendance-actions {
            margin-top: 1rem;
            text-align: center;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <a href="manage_subjects.php" class="sidebar-link">Manage Subjects</a>
            </li>
            <li class="sidebar-item">
                <a href="professor_manage_schedule.php" class="sidebar-link active">Manage Class</a>
            </li>
            <li class="sidebar-item">
                <a href="../admin/settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header-enhanced">
            <h2 class="table-title-enhanced"><i class="fas fa-calendar-alt" style="margin-right: 10px;"></i>Manage My Classes</h2>
            <div class="table-actions-enhanced">
                <button class="stat-primary-btn" onclick="openModal('addSubjectModal')">
                    <i class="fas fa-plus"></i> Add New Class
                </button>
            </div>
        </div>

        <div class="dashboard-container">
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($subjects)): ?>
                <div class="no-data">
                    <p>No classes found. Create your first class to get started.</p>
                    <button class="stat-primary-btn" onclick="openModal('addSubjectModal')">
                        <i class="fas fa-plus"></i> Add New Class
                    </button>
                </div>
            <?php else: ?>
                <div class="stats-grid">
                    <?php foreach ($subjects as $subject): ?>
                    <div class="stat-card-enhanced" onclick="viewClassDetails('<?php echo $subject['class_id']; ?>', '<?php echo $subject['subject_name']; ?>')">
                        <div class="stat-header-enhanced">
                            <div class="stat-icon-enhanced">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div class="stat-info-enhanced">
                                <h3 class="stat-title-enhanced"><?php echo $subject['subject_name']; ?></h3>
                                <span class="stat-subtitle-enhanced"><?php echo $subject['class_code']; ?> • <?php echo $subject['subject_code']; ?></span>
                            </div>
                        </div>

                        <div class="stat-details-enhanced">
                            <div class="stat-detail-enhanced">
                                <i class="fas fa-calendar stat-detail-icon"></i>
                                <span class="stat-detail-text"><?php echo $subject['schedule']; ?></span>
                            </div>
                            <div class="stat-detail-enhanced">
                                <i class="fas fa-map-marker-alt stat-detail-icon"></i>
                                <span class="stat-detail-text"><?php echo $subject['room']; ?></span>
                            </div>
                            <div class="stat-detail-enhanced">
                                <i class="fas fa-users stat-detail-icon"></i>
                                <span class="stat-detail-text"><?php echo $enrollment_counts[$subject['subject_id']] ?? 0; ?> students enrolled</span>
                            </div>
                        </div>

                        <div class="stat-actions-enhanced">
                            <button class="stat-action-btn" onclick="event.stopPropagation(); openAttendanceModal('<?php echo $subject['class_id']; ?>', '<?php echo $subject['subject_name']; ?>')">
                                <i class="fas fa-clipboard-check"></i> Take Attendance
                            </button>
                            <button class="stat-action-btn secondary" onclick="event.stopPropagation(); regenerateCode('<?php echo $subject['subject_id']; ?>')">
                                <i class="fas fa-sync-alt"></i> Regenerate Code
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Subject Modal -->
        <div id="addSubjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Class</h3>
                    <button class="modal-close" onclick="closeModal('addSubjectModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="add_subject">
                        <div class="form-group">
                            <label>Subject Code</label>
                            <input type="text" name="subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" name="subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>Schedule</label>
                            <input type="text" name="schedule" placeholder="e.g., MWF 9:00-10:30" required>
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <input type="text" name="room" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addSubjectModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Subject Modal -->
        <div id="editSubjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Class</h3>
                    <button class="modal-close" onclick="closeModal('editSubjectModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="edit_subject">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        <div class="form-group">
                            <label>Subject Code</label>
                            <input type="text" name="subject_code" id="edit_subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" name="subject_name" id="edit_subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>Schedule</label>
                            <input type="text" name="schedule" id="edit_schedule" required>
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <input type="text" name="room" id="edit_room" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('editSubjectModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attendance Modal -->
        <div id="attendanceModal" class="modal">
            <div class="modal-content modal-attendance">
                <div class="modal-header">
                    <h3 class="modal-title" id="attendanceModalTitle">Student Attendance</h3>
                    <button class="modal-close" onclick="closeModal('attendanceModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="class_id" id="attendance_class_id">

                    <!-- Enrolled Students Section -->
                    <div id="enrolledStudentsSection">
                        <!-- Enrolled students will be loaded here -->
                    </div>

                    <!-- Add New Date Section -->
                    <div class="form-group">
                        <label>Add New Attendance Date</label>
                        <div class="date-input-group">
                            <input type="date" id="new_attendance_date">
                            <button class="btn btn-primary" onclick="addNewAttendanceDate()">
                                <span>+</span> Add Date
                            </button>
                        </div>
                    </div>

                    <!-- Attendance Dates Accordion -->
                    <div id="attendanceDatesAccordion">
                        <!-- Attendance dates with collapsible sections will be loaded here -->
                    </div>
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
        function openModal(modalId) {
            console.log('Opening modal:', modalId);
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function viewClassDetails(classId, className) {
            document.getElementById('attendance_class_id').value = classId;
            document.getElementById('attendanceModalTitle').textContent = 'Class Details - ' + className;
            
            // Clear previous content
            document.getElementById('enrolledStudentsSection').innerHTML = '<div style="text-align: center; padding: 1rem;"><span class="loading-spinner">Loading students...</span></div>';
            document.getElementById('attendanceDatesAccordion').innerHTML = '';
            
            // Load enrolled students and attendance dates
            Promise.all([
                loadEnrolledStudents(classId),
                loadAttendanceDates(classId)
            ]).then(() => {
                openModal('attendanceModal');
            }).catch(error => {
                console.error('Error loading class details:', error);
                alert('Error loading class details. Please try again.');
            });
        }

        function openAttendanceModal(classId, className) {
            document.getElementById('attendance_class_id').value = classId;
            document.getElementById('attendanceModalTitle').textContent = 'Attendance Management - ' + className;
            
            // Clear previous content
            document.getElementById('enrolledStudentsSection').innerHTML = '<div style="text-align: center; padding: 1rem;"><span class="loading-spinner">Loading students...</span></div>';
            document.getElementById('attendanceDatesAccordion').innerHTML = '';
            
            // Load enrolled students and attendance dates
            Promise.all([
                loadEnrolledStudents(classId),
                loadAttendanceDates(classId)
            ]).then(() => {
                openModal('attendanceModal');
            }).catch(error => {
                console.error('Error loading attendance data:', error);
                alert('Error loading attendance data. Please try again.');
            });
        }

        function loadEnrolledStudents(classId) {
            return new Promise((resolve, reject) => {
                fetch('../php/get_class_students.php?class_id=' + classId, { credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(students => {
                        const container = document.getElementById('enrolledStudentsSection');
                        
                        if (students.length === 0) {
                            container.innerHTML = '<p style="text-align: center; color: var(--gray); padding: 1rem;">No students enrolled in this class.</p>';
                            resolve();
                            return;
                        }
                        
                        container.innerHTML = `
                            <div class="enrolled-students-section">
                                <h4 class="enrolled-students-title">
                                    <span>👥</span> Enrolled Students (${students.length})
                                </h4>
                                <div class="enrolled-students-table-container">
                                    <table class="enrolled-students-table">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${students.map(student => `
                                                <tr>
                                                    <td>${student.student_id}</td>
                                                    <td>${student.first_name} ${student.last_name}</td>
                                                    <td>${student.email}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                        resolve();
                    })
                    .catch(error => {
                        console.error('Error loading enrolled students:', error);
                        document.getElementById('enrolledStudentsSection').innerHTML = '<p style="color: var(--danger); text-align: center;">Error loading students</p>';
                        reject(error);
                    });
            });
        }

        function loadAttendanceDates(classId) {
            return new Promise((resolve, reject) => {
                fetch('../php/get_attendance_dates.php?class_id=' + classId, { credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(dates => {
                        const container = document.getElementById('attendanceDatesAccordion');
                        container.innerHTML = '';
                        
                        if (dates.length === 0) {
                            container.innerHTML = '<p style="text-align: center; color: var(--gray); padding: 2rem;">No attendance records found.</p>';
                            resolve();
                            return;
                        }
                        
                        // Sort dates descending so latest is first
                        dates.sort((a, b) => new Date(b) - new Date(a));
                        
                        dates.forEach(date => {
                            const dateCard = document.createElement('div');
                            dateCard.className = 'date-card';

                            // Format date to MM-DD-YYYY
                            const formattedDate = new Date(date).toLocaleDateString('en-US');

                            dateCard.innerHTML = `
                                <div class="date-header" onclick="toggleDateCollapse(this)">
                                    <h4>${formattedDate}</h4>
                                    <span class="collapse-icon">▼</span>
                                </div>
                                <div class="date-content">
                                    <div class="students-list" data-date="${date}">
                                        <!-- Students will be loaded here when expanded -->
                                    </div>
                                    <div class="attendance-actions">
                                        <button class="btn btn-primary" onclick="saveAttendance('${date}')">Save</button>
                                    </div>
                                </div>
                            `;

                            container.appendChild(dateCard);
                        });
                        resolve();
                    })
                    .catch(error => {
                        console.error('Error loading dates:', error);
                        alert('Error loading attendance dates. Please try again.');
                        reject(error);
                    });
            });
        }

        function toggleDateCollapse(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.collapse-icon');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.textContent = '▲';
                
                // Load students for this date if not already loaded
                const date = content.querySelector('.students-list').dataset.date;
                const classId = document.getElementById('attendance_class_id').value;
                loadStudentsForDate(classId, date, content.querySelector('.students-list'));
            } else {
                content.style.display = 'none';
                icon.textContent = '▼';
            }
        }

        function loadStudentsForDate(classId, date, container) {
            if (container.querySelector('table')) return; // Already loaded
            
            fetch(`../php/get_attendance_for_date.php?class_id=${classId}&date=${date}`, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(attendanceRecords => {
                    container.innerHTML = `
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${attendanceRecords.map(record => `
                                    <tr>
                                        <td>${record.student_id}</td>
                                        <td>${record.first_name} ${record.last_name}</td>
                                        <td>
                                            <select name="attendance[${record.student_id}][status]" required class="status-select ${record.status ? 'status-' + record.status.toLowerCase() : ''}">
                                                <option value="">Select</option>
                                                <option value="Present" ${record.status === 'Present' ? 'selected' : ''}>Present</option>
                                                <option value="Absent" ${record.status === 'Absent' ? 'selected' : ''}>Absent</option>
                                                <option value="Late" ${record.status === 'Late' ? 'selected' : ''}>Late</option>
                                                <option value="Excused" ${record.status === 'Excused' ? 'selected' : ''}>Excused</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[${record.student_id}][remarks]" placeholder="Remarks" value="${record.remarks || ''}">
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;

                    // Add event listener to update color on status change
                    container.querySelectorAll('.status-select').forEach(select => {
                        select.addEventListener('change', function() {
                            this.className = 'status-select status-' + this.value.toLowerCase();
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading attendance records:', error);
                    container.innerHTML = '<p style="color: var(--danger);">Error loading attendance records</p>';
                });
        }

        function addNewAttendanceDate() {
            const dateInput = document.getElementById('new_attendance_date');
            const date = dateInput.value;
            const classId = document.getElementById('attendance_class_id').value;
            
            if (!date) {
                alert('Please select a date');
                return;
            }
            
            // Show loading state
            const addButton = document.querySelector('.btn-primary[onclick="addNewAttendanceDate()"]');
            const originalText = addButton.innerHTML;
            addButton.innerHTML = '<span>Creating...</span>';
            addButton.disabled = true;
            
            // Call backend to create attendance date
            fetch('../php/create_attendance_date.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ class_id: classId, date: date })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.exists) {
                        alert('This date already exists. You can edit the existing attendance records.');
                    } else {
                        // Successfully created new date - reload and auto-expand it
                        loadAttendanceDates(classId).then(() => {
                            // Find and expand the newly created date by checking the data-date attribute
                            const dateCards = document.querySelectorAll('.date-card');
                            for (const dateCard of dateCards) {
                                const studentsList = dateCard.querySelector('.students-list');
                                if (studentsList && studentsList.dataset.date === date) {
                                    const dateHeader = dateCard.querySelector('.date-header');
                                    toggleDateCollapse(dateHeader);
                                    break;
                                }
                            }
                        });
                    }
                } else {
                    alert('Failed to create attendance date: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error creating attendance date:', error);
                alert('Error creating attendance date. Please try again.');
            })
            .finally(() => {
                // Restore button state
                addButton.innerHTML = originalText;
                addButton.disabled = false;
                dateInput.value = '';
            });
        }

function saveAttendance(date) {
            const classId = document.getElementById('attendance_class_id').value;
            const studentsList = document.querySelector(`.students-list[data-date="${date}"]`);
            
            if (!studentsList) {
                alert('Please expand the date first to load students');
                return;
            }
            
            const attendanceData = [];
            // Only iterate over tbody > tr to avoid header rows
            const rows = studentsList.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const studentIdCell = row.querySelector('td:first-child');
                if (!studentIdCell) return; // skip if no td found
                
                const studentId = studentIdCell.textContent;
                const statusSelect = row.querySelector('select');
                const remarksInput = row.querySelector('input[type="text"]');
                const status = statusSelect ? statusSelect.value : '';
                const remarks = remarksInput ? remarksInput.value : '';
                
                attendanceData.push({ student_id: studentId, status, remarks });
            });
            
            fetch('../php/save_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    class_id: classId,
                    date: date,
                    attendance: attendanceData
                }),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Attendance saved successfully!');
                    // Reload attendance dates and expand the saved date
                    loadAttendanceDates(classId).then(() => {
                        const dateHeaders = document.querySelectorAll('.date-header h4');
                        for (const header of dateHeaders) {
                            // Compare the date in the data-date attribute instead of formatted text
                            const dateCard = header.closest('.date-card');
                            const studentsList = dateCard.querySelector('.students-list');
                            if (studentsList && studentsList.dataset.date === date) {
                                const dateHeader = dateCard.querySelector('.date-header');
                                toggleDateCollapse(dateHeader);
                                break;
                            }
                        }
                    });
                } else {
                    alert('Failed to save attendance: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error saving attendance:', error);
                alert('Error saving attendance. Please try again.');
            });
        }

        function editAttendance(date) {
            // For now, just focus on the date and allow editing
            const studentsList = document.querySelector(`.students-list[data-date="${date}"]`);
            if (studentsList) {
                studentsList.scrollIntoView({ behavior: 'smooth' });
            }
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

        // Removed event listeners for non-existent elements to fix errors
    </script>
</body>
</html>
