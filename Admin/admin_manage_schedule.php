<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'add_subject':
                $subject_code = trim($_POST['subject_code']);
                $subject_name = trim($_POST['subject_name']);
                $professor_id = $_POST['professor_id'];
                $schedule = trim($_POST['schedule']);
                $room = trim($_POST['room']);
                
                $pdo->beginTransaction();
                
                $subject_id = 'SUB' . time();
                $stmt = $pdo->prepare("INSERT INTO subjects (subject_id, subject_name, subject_code, credits, created_at, updated_at) 
                                      VALUES (?, ?, ?, 3, NOW(), NOW())");
                $stmt->execute([$subject_id, $subject_name, $subject_code]);
                
                $class_code = generateUniqueClassCode($pdo);
                $class_id = 'CLASS' . time();
                $stmt = $pdo->prepare("INSERT INTO classes (class_id, class_name, class_code, subject_id, professor_id, schedule, room, created_at, updated_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$class_id, $subject_name . ' Class', $class_code, $subject_id, $professor_id, $schedule, $room]);
                
                $pdo->commit();
                $success = "Subject and class added successfully!";
                break;
                
            case 'edit_subject':
                $subject_id = $_POST['subject_id'];
                $subject_code = trim($_POST['subject_code']);
                $subject_name = trim($_POST['subject_name']);
                $professor_id = $_POST['professor_id'];
                $class_code = trim($_POST['class_code']);
                $schedule = trim($_POST['schedule']);
                $room = trim($_POST['room']);
                
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, updated_at = NOW() WHERE subject_id = ?");
                $stmt->execute([$subject_code, $subject_name, $subject_id]);
                
                $stmt = $pdo->prepare("UPDATE classes SET class_code = ?, professor_id = ?, schedule = ?, room = ?, updated_at = NOW() WHERE subject_id = ?");
                $stmt->execute([$class_code, $professor_id, $schedule, $room, $subject_id]);
                
                $pdo->commit();
                $success = "Subject updated successfully!";
                break;
                
            case 'delete_subject':
                $subject_id = $_POST['subject_id'];
                
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM classes WHERE subject_id = ?");
                $stmt->execute([$subject_id]);
                
                $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
                $stmt->execute([$subject_id]);
                
                $pdo->commit();
                $success = "Subject deleted successfully!";
                break;
                
            case 'assign_professor':
                $subject_id = $_POST['subject_id'];
                $professor_id = $_POST['professor_id'];
                
                $stmt = $pdo->prepare("UPDATE classes SET professor_id = ?, updated_at = NOW() WHERE subject_id = ?");
                $stmt->execute([$professor_id, $subject_id]);
                $success = "Professor assigned successfully!";
                break;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Error processing request: " . $e->getMessage();
    }
}

// Fetch data for display
$subjects = $pdo->query("SELECT s.*, p.first_name, p.last_name, c.class_id, c.class_code, c.schedule, c.room
                        FROM subjects s
                        JOIN classes c ON s.subject_id = c.subject_id
                        LEFT JOIN professors p ON c.professor_id = p.professor_id
                        ORDER BY s.created_at DESC")->fetchAll();

$professors = $pdo->query("SELECT * FROM professors ORDER BY first_name, last_name")->fetchAll();

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Schedule - Global Reciprocal College</title>
    <link rel="stylesheet" href="../css/styles_fixed.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .enhanced-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }
        .enhanced-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .table tbody tr:hover {
            background-color: #e3f2fd;
            transition: background-color 0.3s ease;
        }
        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-assigned {
            background: #d4edda;
            color: #155724;
        }
        .badge-unassigned {
            background: #fff3cd;
            color: #856404;
        }
        .search-container {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 1rem;
        }
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1);
        }
        .filter-select {
            padding: 12px 16px;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .modal-form .form-group {
            position: relative;
        }
        .modal-form input, .modal-form select {
            padding-left: 40px;
        }
        .form-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1rem;
        }
        .qr-container {
            text-align: center;
            padding: 20px;
            background: var(--light);
            border-radius: 8px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-top: 4px solid var(--primary);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .stats-label {
            color: var(--gray);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .table-header-enhanced {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        .table-title-enhanced {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .fade-in {
            animation: fadeInUp 0.5s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .fade-out {
            animation: fadeOut 0.5s ease-out;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .table-header-enhanced {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .search-container {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons {
                justify-content: center;
            }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.4s ease-out;
            backdrop-filter: blur(5px);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            width: 90%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.4s ease-out;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transform: scale(0.9);
            transition: transform 0.3s ease-out;
        }
        .modal.show .modal-content {
            transform: scale(1);
        }
        .modal-header {
            padding: 2rem 2.5rem;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }
        .modal-title {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            padding: 8px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        .modal-body {
            padding: 2.5rem;
        }
        .modal-footer {
            padding: 2rem 2.5rem;
            border-top: 2px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            background: #f8f9fa;
            border-radius: 0 0 16px 16px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <button class="hamburger-menu" id="sidebarToggle" aria-label="Toggle sidebar">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <span class="navbar-title">Global Reciprocal College</span>
            <span class="navbar-title-mobile">GRC</span>
        </div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <div class="user-dropdown">
                <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">⚙️</button>
                <div class="dropdown-menu" role="menu" aria-label="User menu">
                    <a href="settings.php" class="dropdown-item" role="menuitem">Settings</a>
                    <a href="../php/logout.php" class="dropdown-item" role="menuitem">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" role="navigation" aria-label="Main sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="admin_dashboard.php" class="sidebar-link">Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_professors.php" class="sidebar-link">Manage Professors</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_students.php" class="sidebar-link">Manage Students</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_schedule.php" class="sidebar-link active" aria-current="page">Manage Schedule</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content" role="main" tabindex="-1">
        <div class="table-header-enhanced">
            <h2 class="table-title-enhanced"><i class="fas fa-calendar-alt" style="margin-right: 10px;"></i>Manage Schedule & Subjects</h2>
            <div class="btn-group">
                <input type="search" id="searchInput" class="search-input" placeholder="Search subjects..." aria-label="Search subjects" onkeyup="filterSubjects()" />
                <button class="btn btn-primary btn-icon" type="button" onclick="openModal('addSubjectModal')" aria-haspopup="dialog">
                    <i class="fas fa-plus"></i> Add Subject
                </button>
                <button class="btn btn-secondary btn-icon" type="button" onclick="openModal('generateQRModal')" aria-haspopup="dialog">
                    <i class="fas fa-qrcode"></i> Generate QR
                </button>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success fade-in" role="alert" tabindex="0">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger fade-in" role="alert" tabindex="0">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stats-card fade-in" tabindex="0">
                <i class="fas fa-book" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                <div class="stats-number"><?php echo count($subjects); ?></div>
                <div class="stats-label">Total Subjects</div>
            </div>
            <div class="stats-card fade-in" tabindex="0">
                <i class="fas fa-chalkboard-teacher" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                <div class="stats-number"><?php echo count($professors); ?></div>
                <div class="stats-label">Active Professors</div>
            </div>
            <div class="stats-card fade-in" tabindex="0">
                <i class="fas fa-users" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                <div class="stats-number"><?php echo array_sum($enrollment_counts); ?></div>
                <div class="stats-label">Total Enrollments</div>
            </div>
        </div>

        <div class="table-container" role="region" aria-label="Subjects table">
            <table class="table" aria-describedby="subjectsTableCaption">
                <caption id="subjectsTableCaption" class="sr-only">List of subjects with schedule and professor information</caption>
                <thead>
                    <tr>
                        <th scope="col">Subject Code</th>
                        <th scope="col">Subject Name</th>
                        <th scope="col">Class Code</th>
                        <th scope="col">Professor</th>
                        <th scope="col">Schedule</th>
                        <th scope="col">Room</th>
                        <th scope="col">Enrolled</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="subjectsTableBody">
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo $subject['subject_code']; ?></td>
                        <td><?php echo $subject['subject_name']; ?></td>
                        <td><?php echo $subject['class_code']; ?></td>
                        <td>
                            <?php if ($subject['first_name']): ?>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-user-tie" style="color: var(--primary);"></i>
                                    <?php echo $subject['first_name'] . ' ' . $subject['last_name']; ?>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="status-badge badge-unassigned">Not Assigned</span>
                                    <button class="btn btn-sm btn-primary btn-icon" onclick="assignProfessor(<?php echo $subject['subject_id']; ?>, '')">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </button>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $subject['schedule']; ?></td>
                        <td><?php echo $subject['room']; ?></td>
                        <td><?php echo $enrollment_counts[$subject['subject_id']] ?? 0; ?> students</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary btn-icon" onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-info btn-icon" onclick="viewEnrolledStudents('<?php echo $subject['class_id']; ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_subject">
                                    <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" onclick="return confirm('Are you sure you want to delete this subject?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Subject Modal -->
        <div id="addSubjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Subject</h3>
                    <button class="modal-close" onclick="closeModal('addSubjectModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="add_subject">
                        <div class="form-group">
                            <label>Subject Code</label>
                            <i class="fas fa-hashtag form-icon"></i>
                            <input type="text" name="subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <i class="fas fa-book form-icon"></i>
                            <input type="text" name="subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>Professor</label>
                            <i class="fas fa-user-tie form-icon"></i>
                            <select name="professor_id" required>
                                <option value="">Select Professor</option>
                                <?php foreach ($professors as $professor): ?>
                                <option value="<?php echo $professor['professor_id']; ?>">
                                    <?php echo $professor['first_name'] . ' ' . $professor['last_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Schedule</label>
                            <i class="fas fa-calendar-alt form-icon"></i>
                            <input type="text" name="schedule" placeholder="e.g., MWF 9:00-10:30" required>
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <i class="fas fa-map-marker-alt form-icon"></i>
                            <input type="text" name="room" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addSubjectModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Subject Modal -->
        <div id="editSubjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Subject</h3>
                    <button class="modal-close" onclick="closeModal('editSubjectModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="edit_subject">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        <div class="form-group">
                            <label>Subject Code</label>
                            <i class="fas fa-hashtag form-icon"></i>
                            <input type="text" name="subject_code" id="edit_subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <i class="fas fa-book form-icon"></i>
                            <input type="text" name="subject_name" id="edit_subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>Professor</label>
                            <i class="fas fa-user-tie form-icon"></i>
                            <select name="professor_id" id="edit_professor_id" required>
                                <option value="">Select Professor</option>
                                <?php foreach ($professors as $professor): ?>
                                <option value="<?php echo $professor['professor_id']; ?>">
                                    <?php echo $professor['first_name'] . ' ' . $professor['last_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Class Code</label>
                            <i class="fas fa-qrcode form-icon"></i>
                            <input type="text" name="class_code" id="edit_class_code" required>
                        </div>
                        <div class="form-group">
                            <label>Schedule</label>
                            <i class="fas fa-calendar-alt form-icon"></i>
                            <input type="text" name="schedule" id="edit_schedule" required>
                        </div>
                        <div class="form-group">
                            <label>Room</label>
                            <i class="fas fa-map-marker-alt form-icon"></i>
                            <input type="text" name="room" id="edit_room" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('editSubjectModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assign Professor Modal -->
        <div id="assignProfessorModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Assign Professor</h3>
                    <button class="modal-close" onclick="closeModal('assignProfessorModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="assign_professor">
                        <input type="hidden" name="subject_id" id="assign_subject_id">
                        <div class="form-group">
                            <label>Current Professor: <span id="current_professor">Not assigned</span></label>
                        </div>
                        <div class="form-group">
                            <label>Select New Professor</label>
                            <select name="professor_id" required>
                                <option value="">Select Professor</option>
                                <?php foreach ($professors as $professor): ?>
                                <option value="<?php echo $professor['professor_id']; ?>">
                                    <?php echo $professor['first_name'] . ' ' . $professor['last_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('assignProfessorModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Assign Professor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generate QR Code Modal -->
        <div id="generateQRModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Generate QR Code</h3>
                    <button class="modal-close" onclick="closeModal('generateQRModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Subject</label>
                        <select id="qr_subject" onchange="generateQRCode()">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['class_code']; ?>">
                                <?php echo $subject['subject_name'] . ' (' . $subject['class_code'] . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="qrCodeContainer" class="qr-container" style="display: none;">
                        <div id="qrcode"></div>
                        <p style="margin-top: 15px; color: var(--gray);">Scan this QR code to enroll in the class</p>
                        <button class="btn btn-primary btn-icon" onclick="downloadQRCode()" style="margin-top: 10px;">
                            <i class="fas fa-download"></i> Download QR
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Enrolled Students Modal -->
        <div id="viewStudentsModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Enrolled Students</h3>
                    <button class="modal-close" onclick="closeModal('viewStudentsModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="enrolledStudentsList"></div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        function filterSubjects() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const tbody = document.getElementById('subjectsTableBody');
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



        function editSubject(subject) {
            document.getElementById('edit_subject_id').value = subject.subject_id;
            document.getElementById('edit_subject_code').value = subject.subject_code;
            document.getElementById('edit_subject_name').value = subject.subject_name;
            document.getElementById('edit_professor_id').value = subject.professor_id || '';
            document.getElementById('edit_class_code').value = subject.class_code;
            document.getElementById('edit_schedule').value = subject.schedule;
            document.getElementById('edit_room').value = subject.room;
            openModal('editSubjectModal');
        }

        function assignProfessor(subjectId, currentProfessor) {
            document.getElementById('assign_subject_id').value = subjectId;
            document.getElementById('current_professor').textContent = currentProfessor || 'Not assigned';
            openModal('assignProfessorModal');
        }

        function viewEnrolledStudents(classId) {
            document.getElementById('enrolledStudentsList').innerHTML = '<p>Loading enrolled students...</p>';
            openModal('viewStudentsModal');

            fetch('../php/get_class_students.php?class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('enrolledStudentsList').innerHTML = '<p>Error: ' + data.error + '</p>';
                        return;
                    }
                    if (data.length === 0) {
                        document.getElementById('enrolledStudentsList').innerHTML = '<p>No students enrolled in this class.</p>';
                        return;
                    }
                    let html = '<table class="table"><thead><tr><th>Student Name</th><th>Email</th></tr></thead><tbody>';
                    data.forEach(student => {
                        html += '<tr><td>' + student.first_name + ' ' + student.last_name + '</td><td>' + student.email + '</td></tr>';
                    });
                    html += '</tbody></table>';
                    document.getElementById('enrolledStudentsList').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('enrolledStudentsList').innerHTML = '<p>Error loading students: ' + error.message + '</p>';
                });
        }

        function generateQRCode() {
            const classCode = document.getElementById('qr_subject').value;
            const container = document.getElementById('qrCodeContainer');
            const qrDiv = document.getElementById('qrcode');

            if (classCode) {
                container.style.display = 'block';
                qrDiv.innerHTML = '';

                // Generate QR code
                QRCode.toCanvas(qrDiv, classCode, {
                    width: 200,
                    height: 200,
                    margin: 1
                }, function (error) {
                    if (error) console.error(error);
                });
            } else {
                container.style.display = 'none';
            }
        }

        function downloadQRCode() {
            const qrCanvas = document.querySelector('#qrcode canvas');
            if (qrCanvas) {
                const link = document.createElement('a');
                link.download = 'class_qr_code.png';
                link.href = qrCanvas.toDataURL();
                link.click();
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
            // Optionally add overlay for mobile
            if (window.innerWidth <= 900) {
                document.body.classList.toggle('sidebar-open');
            }
        });

        // Optional: Close sidebar when clicking outside on mobile
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

        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000); // Auto-dismiss after 5 seconds
            });
        });

        // Enhanced modal animations
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('show');
            modal.style.animation = 'fadeIn 0.3s ease-out';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.animation = 'fadeOut 0.3s ease-in';
            setTimeout(() => {
                modal.classList.remove('show');
            }, 300);
        }

        // Add loading state to buttons
        document.addEventListener('submit', function(e) {
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            }
        });
    </script>
</body>
</html>
