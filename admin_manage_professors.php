<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

// Handle professor actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add_professor':
                $professor_id = $_POST['professor_id'];
                $employee_id = $_POST['employee_id'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $password = md5($_POST['password']);
                $department = $_POST['department'];
                $mobile = $_POST['mobile'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO professors (professor_id, employee_id, first_name, last_name, email, password, department, mobile, created_at, updated_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$professor_id, $employee_id, $first_name, $last_name, $email, $password, $department, $mobile]);
                    $success = "Professor added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding professor: " . $e->getMessage();
                }
                break;
                
            case 'edit_professor':
                $professor_id = $_POST['professor_id'];
                $employee_id = $_POST['employee_id'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $department = $_POST['department'];
                $mobile = $_POST['mobile'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE professors SET employee_id = ?, first_name = ?, last_name = ?, email = ?, department = ?, mobile = ?, updated_at = NOW() 
                                          WHERE professor_id = ?");
                    $stmt->execute([$employee_id, $first_name, $last_name, $email, $department, $mobile, $professor_id]);
                    $success = "Professor updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating professor: " . $e->getMessage();
                }
                break;
                
            case 'delete_professor':
                $professor_id = $_POST['professor_id'];
                
                try {
                    // Check if professor has classes assigned
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) as class_count FROM classes WHERE professor_id = ?");
                    $check_stmt->execute([$professor_id]);
                    $class_count = $check_stmt->fetch()['class_count'];
                    
if ($class_count > 0) {
    $error = "Cannot delete professor: There are $class_count classes assigned to this professor. Please reassign or delete these classes first.";
    // Optionally, provide a way to reassign or delete classes here
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM professors WHERE professor_id = ?");
                        $stmt->execute([$professor_id]);
                        $success = "Professor deleted successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Error deleting professor: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all professors
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM professors";
if (!empty($search)) {
    $query .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR department LIKE ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query($query);
}
$professors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Professors - Global Reciprocal College</title>
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
                <a href="admin_manage_professors.php" class="sidebar-link active">Manage Professors</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_students.php" class="sidebar-link">Manage Students</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_schedule.php" class="sidebar-link">Manage Schedule</a>
            </li>

        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header">
            <h2 class="table-title">Manage Professors</h2>
            <div class="table-actions">
                <input type="text" class="search-input" placeholder="Search professors..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       onkeyup="if(event.key === 'Enter') searchProfessors(this.value)">
                <button class="btn btn-primary" onclick="openModal('addProfessorModal')">Add Professor</button>
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
                        <th>Professor ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Mobile</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professors as $professor): ?>
                    <tr>
                        <td><?php echo $professor['professor_id']; ?></td>
                        <td><?php echo $professor['employee_id']; ?></td>
                        <td><?php echo $professor['first_name'] . ' ' . $professor['last_name']; ?></td>
                        <td><?php echo $professor['email']; ?></td>
                        <td><?php echo $professor['department']; ?></td>
                        <td><?php echo $professor['mobile']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editProfessor(<?php echo htmlspecialchars(json_encode($professor)); ?>)">Edit</button>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete_professor">
                                <input type="hidden" name="professor_id" value="<?php echo $professor['professor_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this professor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Professor Modal -->
        <div id="addProfessorModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Professor</h3>
                    <button class="modal-close" onclick="closeModal('addProfessorModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="add_professor">
                        <div class="form-group">
                            <label>Professor ID</label>
                            <input type="text" name="professor_id" required>
                        </div>
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" name="employee_id" required>
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
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" name="department" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="tel" name="mobile" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addProfessorModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Professor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Professor Modal -->
        <div id="editProfessorModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Professor</h3>
                    <button class="modal-close" onclick="closeModal('editProfessorModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="modal-form" action="" method="POST">
                        <input type="hidden" name="action" value="edit_professor">
                        <input type="hidden" name="professor_id" id="edit_professor_id">
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" name="employee_id" id="edit_employee_id" required>
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" name="department" id="edit_department" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="tel" name="mobile" id="edit_mobile" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('editProfessorModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Professor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function searchProfessors(query) {
            window.location.href = 'admin_manage_professors.php?search=' + encodeURIComponent(query);
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function editProfessor(professor) {
            document.getElementById('edit_professor_id').value = professor.professor_id;
            document.getElementById('edit_employee_id').value = professor.employee_id;
            document.getElementById('edit_first_name').value = professor.first_name;
            document.getElementById('edit_last_name').value = professor.last_name;
            document.getElementById('edit_email').value = professor.email;
            document.getElementById('edit_department').value = professor.department;
            document.getElementById('edit_mobile').value = professor.mobile;
            openModal('editProfessorModal');
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
