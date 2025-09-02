<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

require_once '../php/db.php';

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
                    // Check if student has attendance records or class enrollments
                    $check_attendance = $pdo->prepare("SELECT COUNT(*) as attendance_count FROM attendance WHERE student_id = ?");
                    $check_attendance->execute([$student_id]);
                    $attendance_count = $check_attendance->fetch()['attendance_count'];
                    
                    $check_enrollment = $pdo->prepare("SELECT COUNT(*) as enrollment_count FROM student_classes WHERE student_id = ?");
                    $check_enrollment->execute([$student_id]);
                    $enrollment_count = $check_enrollment->fetch()['enrollment_count'];
                    
                    if ($attendance_count > 0 || $enrollment_count > 0) {
                        $error = "Cannot delete student: Student has $attendance_count attendance records and $enrollment_count class enrollments. Please remove these records first.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
                        $stmt->execute([$student_id]);
                        $success = "Student deleted successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Error deleting student: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all students
$query = "SELECT * FROM students";
$students = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Global Reciprocal College</title>
    <link rel="stylesheet" href="../css/styles_fixed.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        .badge-inactive {
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
            <button class="hamburger-menu" id="sidebarToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <span class="navbar-title">Global Reciprocal College</span>
            <span class="navbar-title-mobile">GRC</span>
        </div>
        <div class="navbar-user">
            <span>Welcome, <?php echo $_SESSION['name']; ?></span>
            <div class="user-dropdown">
                <button class="dropdown-toggle">⚙️</button>
                <div class="dropdown-menu">

                    <a href="../php/logout.php" class="dropdown-item">Logout</a>
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

        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content" role="main" tabindex="-1">
        <div class="table-header-enhanced">
            <h2 class="table-title-enhanced"><i class="fas fa-users" style="margin-right: 10px;"></i>Manage Students</h2>
            <div class="btn-group">
                <input type="search" id="searchInput" class="search-input" placeholder="Search students..." aria-label="Search students" onkeyup="filterStudents()" />
                <button class="btn btn-primary btn-icon" type="button" onclick="openModal('addStudentModal')" aria-haspopup="dialog">
                    <i class="fas fa-plus"></i> Add Student
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
                <i class="fas fa-user-graduate" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                <div class="stats-number"><?php echo count($students); ?></div>
                <div class="stats-label">Total Students</div>
            </div>
            <div class="stats-card fade-in" tabindex="0">
                <i class="fas fa-user-check" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                <div class="stats-number"><?php echo count(array_filter($students, function($s) { return strtotime($s['created_at']) > strtotime('-30 days'); })); ?></div>
                <div class="stats-label">New This Month</div>
            </div>
            <div class="stats-card fade-in" tabindex="0">
                <i class="fas fa-envelope" style="font-size: 2rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                <div class="stats-number"><?php echo count(array_unique(array_column($students, 'email'))); ?></div>
                <div class="stats-label">Unique Emails</div>
            </div>
        </div>

        <div class="table-container" role="region" aria-label="Students table">
            <table class="table" aria-describedby="studentsTableCaption">
                <caption id="studentsTableCaption" class="sr-only">List of students with their information and actions</caption>
                <thead>
                    <tr>
                        <th scope="col">Student ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Address</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                        <td><?php echo htmlspecialchars($student['address']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary btn-icon" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_student">
                                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" onclick="return confirm('Are you sure you want to delete this student?')">
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
                            <label for="add_student_id">Student ID</label>
                            <i class="fas fa-id-card form-icon"></i>
                            <input type="text" id="add_student_id" name="student_id" required>
                        </div>
                        <div class="form-group">
                            <label for="add_first_name">First Name</label>
                            <i class="fas fa-user form-icon"></i>
                            <input type="text" id="add_first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="add_last_name">Last Name</label>
                            <i class="fas fa-user form-icon"></i>
                            <input type="text" id="add_last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="add_middle_name">Middle Name</label>
                            <i class="fas fa-user form-icon"></i>
                            <input type="text" id="add_middle_name" name="middle_name">
                        </div>
                        <div class="form-group">
                            <label for="add_email">Email</label>
                            <i class="fas fa-envelope form-icon"></i>
                            <input type="email" id="add_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="add_password">Password</label>
                            <i class="fas fa-lock form-icon"></i>
                            <input type="password" id="add_password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="add_mobile">Mobile Number</label>
                            <i class="fas fa-phone form-icon"></i>
                            <input type="tel" id="add_mobile" name="mobile" required>
                        </div>
                        <div class="form-group">
                            <label for="add_address">Address</label>
                            <i class="fas fa-map-marker-alt form-icon"></i>
                            <input type="text" id="add_address" name="address" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Student
                            </button>
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
                            <label for="edit_first_name">First Name</label>
                            <i class="fas fa-user form-icon"></i>
                            <input type="text" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name</label>
                            <i class="fas fa-user form-icon"></i>
                            <input type="text" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_middle_name">Middle Name</label>
                            <i class="fas fa-user form-icon"></i>
                            <input type="text" name="middle_name" id="edit_middle_name">
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <i class="fas fa-envelope form-icon"></i>
                            <input type="email" name="email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_mobile">Mobile Number</label>
                            <i class="fas fa-phone form-icon"></i>
                            <input type="tel" name="mobile" id="edit_mobile" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <i class="fas fa-map-marker-alt form-icon"></i>
                            <input type="text" name="address" id="edit_address" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('editStudentModal')">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function filterStudents() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const tbody = document.getElementById('studentsTableBody');
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
    </script>
</body>
</html>
