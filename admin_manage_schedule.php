<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

// Handle schedule actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add_subject':
                $subject_code = $_POST['subject_code'];
                $subject_name = $_POST['subject_name'];
                $professor_id = $_POST['professor_id'];
                $class_code = $_POST['class_code'];
                $schedule = $_POST['schedule'];
                $room = $_POST['room'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, professor_id, class_code, schedule, room, created_at, updated_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$subject_code, $subject_name, $professor_id, $class_code, $schedule, $room]);
                    $success = "Subject added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding subject: " . $e->getMessage();
                }
                break;
                
            case 'edit_subject':
                $subject_id = $_POST['subject_id'];
                $subject_code = $_POST['subject_code'];
                $subject_name = $_POST['subject_name'];
                $professor_id = $_POST['professor_id'];
                $class_code = $_POST['class_code'];
                $schedule = $_POST['schedule'];
                $room = $_POST['room'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, professor_id = ?, class_code = ?, schedule = ?, room = ?, updated_at = NOW() 
                                          WHERE subject_id = ?");
                    $stmt->execute([$subject_code, $subject_name, $professor_id, $class_code, $schedule, $room, $subject_id]);
                    $success = "Subject updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating subject: " . $e->getMessage();
                }
                break;
                
            case 'delete_subject':
                $subject_id = $_POST['subject_id'];
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
                    $stmt->execute([$subject_id]);
                    $success = "Subject deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting subject: " . $e->getMessage();
                }
                break;
                
            case 'assign_professor':
                $subject_id = $_POST['subject_id'];
                $professor_id = $_POST['professor_id'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE subjects SET professor_id = ?, updated_at = NOW() WHERE subject_id = ?");
                    $stmt->execute([$professor_id, $subject_id]);
                    $success = "Professor assigned successfully!";
                } catch (PDOException $e) {
                    $error = "Error assigning professor: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all subjects with professor information
$query = "SELECT s.*, p.first_name, p.last_name 
          FROM subjects s 
          LEFT JOIN professors p ON s.professor_id = p.professor_id 
          ORDER BY s.created_at DESC";
$subjects = $pdo->query($query)->fetchAll();

// Get all professors for dropdown
$professors = $pdo->query("SELECT * FROM professors ORDER BY first_name, last_name")->fetchAll();

// Get enrolled students count for each subject
$enrollment_counts = [];
foreach ($subjects as $subject) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_subjects WHERE subject_id = ?");
    $stmt->execute([$subject['subject_id']]);
    $enrollment_counts[$subject['subject_id']] = $stmt->fetch()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - Global Reciprocal College</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            Global Reciprocal College
        </div>
        <div class="navbar-user">
            <span>Welcome, <?php echo $_SESSION['name']; ?></span>
            <div class="user-dropdown">
                <button class="dropdown-toggle">⚙️</button>
                <div class="dropdown-menu">
                    <a href="settings.php" class="dropdown-item">Settings</a>
                    <a href="php/logout.php" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
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
                <a href="admin_manage_schedule.php" class="sidebar-link active">Manage Schedule</a>
            </li>
            <li class="sidebar-item">
                <a href="settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header">
            <h2 class="table-title">Manage Schedule & Subjects</h2>
            <div class="table-actions">
                <button class="btn btn-primary" onclick="openModal('addSubjectModal')">Add Subject</button>
                <button class="btn btn-secondary" onclick="openModal('generateQRModal')">Generate QR Code</button>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Total Subjects</h3>
                <div class="number"><?php echo count($subjects); ?></div>
            </div>
            <div class="dashboard-card">
                <h3>Active Professors</h3>
                <div class="number"><?php echo count($professors); ?></div>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Class Code</th>
                        <th>Professor</th>
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
                        <td><?php echo $subject['subject_name']; ?></td>
                        <td><?php echo $subject['class_code']; ?></td>
                        <td>
                            <?php if ($subject['first_name']): ?>
                                <?php echo $subject['first_name'] . ' ' . $subject['last_name']; ?>
                                <button class="btn btn-sm btn-warning" onclick="assignProfessor(<?php echo $subject['subject_id']; ?>, '<?php echo $subject['first_name'] . ' ' . $subject['last_name']; ?>')">Change</button>
                            <?php else: ?>
                                <span class="badge badge-warning">Not Assigned</span>
                                <button class="btn btn-sm btn-primary" onclick="assignProfessor(<?php echo $subject['subject_id']; ?>, '')">Assign</button>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $subject['schedule']; ?></td>
                        <td><?php echo $subject['room']; ?></td>
                        <td><?php echo $enrollment_counts[$subject['subject_id']] ?? 0; ?> students</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editSubject(<?php echo htmlspecialchars(json_encode($subject)); ?>)">Edit</button>
                            <button class="btn btn-sm btn-info" onclick="viewEnrolledStudents(<?php echo $subject['subject_id']; ?>)">View Students</button>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete_subject">
                                <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this subject?')">Delete</button>
                            </form>
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
                            <input type="text" name="subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" name="subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>Professor</label>
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
                            <label>Class Code</label>
                            <input type="text" name="class_code" required>
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
                            <input type="text" name="subject_code" id="edit_subject_code" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" name="subject_name" id="edit_subject_name" required>
                        </div>
                        <div class="form-group">
                            <label>Professor</label>
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
                            <input type="text" name="class_code" id="edit_class_code" required>
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
                    <div id="qrCodeContainer" class="text-center mt-3" style="display: none;">
                        <div id="qrcode"></div>
                        <p class="mt-2">Scan this QR code to enroll in the class</p>
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
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
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

        function viewEnrolledStudents(subjectId) {
            // This would typically make an AJAX call to get enrolled students
            // For now, we'll just show a placeholder
            document.getElementById('enrolledStudentsList').innerHTML = '<p>Loading enrolled students...</p>';
            openModal('viewStudentsModal');
            
            // Simulate loading (in real implementation, use fetch API)
            setTimeout(() => {
                document.getElementById('enrolledStudentsList').innerHTML = `
                    <p>Enrollment data would be loaded here for subject ID: ${subjectId}</p>
                    <p>This would show a list of students enrolled in this class.</p>
                `;
            }, 1000);
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
    </script>
</body>
</html>
