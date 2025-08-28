<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add_student':
                $student_id = $_POST['student_id'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $middle_name = $_POST['middle_name'];
                $email = $_POST['email'];
                $password = md5($_POST['password']);
                $mobile = $_POST['mobile'];
                $address = $_POST['address'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, middle_name, email, password, mobile, address, created_at, updated_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$student_id, $first_name, $last_name, $middle_name, $email, $password, $mobile, $address]);
                    $success = "Student added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding student: " . $e->getMessage();
                }
                break;
                
            case 'edit_student':
                $student_id = $_POST['student_id'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $middle_name = $_POST['middle_name'];
                $email = $_POST['email'];
                $mobile = $_POST['mobile'];
                $address = $_POST['address'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, middle_name = ?, email = ?, mobile = ?, address = ?, updated_at = NOW() 
                                          WHERE student_id = ?");
                    $stmt->execute([$first_name, $last_name, $middle_name, $email, $mobile, $address, $student_id]);
                    $success = "Student updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating student: " . $e->getMessage();
                }
                break;
                
            case 'delete_student':
                $student_id = $_POST['student_id'];
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
                    $stmt->execute([$student_id]);
                    $success = "Student deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting student: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all students
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM students";
if (!empty($search)) {
    $query .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_id LIKE ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query($query);
}
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Global Reciprocal College</title>
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
                <a href="admin_manage_students.php" class="sidebar-link active">Manage Students</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_schedule.php" class="sidebar-link">Manage Schedule</a>
            </li>
            <li class="sidebar-item">
                <a href="settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header">
            <h2 class="table-title">Manage Students</h2>
            <div class="table-actions">
                <input type="text" class="search-input" placeholder="Search students..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       onkeyup="if(event.key === 'Enter') searchStudents(this.value)">
                <button class="btn btn-primary" onclick="openModal('addStudentModal')">Add Student</button>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><?php echo $student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']; ?></td>
                        <td><?php echo $student['email']; ?></td>
                        <td><?php echo $student['mobile']; ?></td>
                        <td><?php echo $student['address']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">Edit</button>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete_student">
                                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Student</h3>
                    <button class="modal-close" onclick="closeModal('addStudentModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="add_student">
                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" name="student_id" required>
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="tel" name="mobile" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="editStudentModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Student</h3>
                    <button class="modal-close" onclick="closeModal('editStudentModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="edit_student">
                        <input type="hidden" name="student_id" id="edit_student_id">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" id="edit_middle_name">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="tel" name="mobile" id="edit_mobile" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" id="edit_address" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('editStudentModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function searchStudents(query) {
            window.location.href = 'admin_manage_students.php?search=' + encodeURIComponent(query);
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function editStudent(student) {
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_middle_name').value = student.middle_name || '';
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_mobile').value = student.mobile;
            document.getElementById('edit_address').value = student.address;
            openModal('editStudentModal');
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
