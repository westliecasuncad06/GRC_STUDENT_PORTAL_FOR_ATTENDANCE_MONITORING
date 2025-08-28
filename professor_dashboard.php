<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard - Global Reciprocal College</title>
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
            <span>Welcome, Prof. <?php echo $_SESSION['name']; ?></span>
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
                <a href="professor_dashboard.php" class="sidebar-link active">Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="manage_students.php" class="sidebar-link">Students</a>
            </li>
            <li class="sidebar-item">
                <a href="my_classes.php" class="sidebar-link">My Classes</a>
            </li>
            <li class="sidebar-item">
                <a href="my_subjects.php" class="sidebar-link">My Subjects</a>
            </li>
            <li class="sidebar-item">
                <a href="attendance.php" class="sidebar-link">Attendance</a>
            </li>
            <li class="sidebar-item">
                <a href="settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Professor Dashboard</h1>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <?php
            // Get professor statistics
            $professor_id = $_SESSION['user_id'];
            
            // Total Classes
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM classes WHERE professor_id = ?");
            $stmt->execute([$professor_id]);
            $stats['classes'] = $stmt->fetch()['count'];
            
            // Total Students
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT sc.student_id) as count 
                                 FROM student_classes sc 
                                 JOIN classes c ON sc.class_id = c.class_id 
                                 WHERE c.professor_id = ?");
            $stmt->execute([$professor_id]);
            $stats['students'] = $stmt->fetch()['count'];
            
            // Total Subjects
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT subject_id) as count FROM classes WHERE professor_id = ?");
            $stmt->execute([$professor_id]);
            $stats['subjects'] = $stmt->fetch()['count'];
            ?>
            
            <div class="dashboard-card">
                <h3>My Classes</h3>
                <div class="number"><?php echo $stats['classes']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Students</h3>
                <div class="number"><?php echo $stats['students']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>My Subjects</h3>
                <div class="number"><?php echo $stats['subjects']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>Today's Schedule</h3>
                <div class="number"><?php echo date('M d'); ?></div>
            </div>
        </div>

        <!-- Recent Classes -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">My Recent Classes</h2>
                <div class="table-actions">
                    <button class="btn btn-primary">Create New Class</button>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Subject</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT c.*, s.subject_name 
                                         FROM classes c 
                                         JOIN subjects s ON c.subject_id = s.subject_id 
                                         WHERE c.professor_id = ? 
                                         ORDER BY c.created_at DESC 
                                         LIMIT 5");
                    $stmt->execute([$professor_id]);
                    $classes = $stmt->fetchAll();
                    
                    foreach ($classes as $class) {
                        // Count students in this class
                        $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM student_classes WHERE class_id = ?");
                        $stmt2->execute([$class['class_id']]);
                        $student_count = $stmt2->fetch()['count'];
                        
                        echo "<tr>
                            <td>{$class['class_name']}</td>
                            <td>{$class['subject_name']}</td>
                            <td>{$class['schedule']}</td>
                            <td>{$class['room']}</td>
                            <td>{$student_count}</td>
                            <td>
                                <button class='btn btn-sm btn-primary'>View</button>
                                <button class='btn btn-sm btn-success'>Attendance</button>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
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
