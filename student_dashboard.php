<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
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
    <title>Student Dashboard - Global Reciprocal College</title>
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
                <a href="student_dashboard.php" class="sidebar-link active">Dashboard</a>
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
                <a href="enroll.php" class="sidebar-link">Enroll in Class</a>
            </li>
            <li class="sidebar-item">
                <a href="settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Student Dashboard</h1>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <?php
            // Get student statistics
            $student_id = $_SESSION['user_id'];
            
            // Total Classes
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_classes WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $stats['classes'] = $stmt->fetch()['count'];
            
            // Total Subjects
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT s.subject_id) as count 
                                 FROM student_classes sc 
                                 JOIN classes c ON sc.class_id = c.class_id 
                                 JOIN subjects s ON c.subject_id = s.subject_id 
                                 WHERE sc.student_id = ?");
            $stmt->execute([$student_id]);
            $stats['subjects'] = $stmt->fetch()['count'];
            
            // Attendance Rate
            $stmt = $pdo->prepare("SELECT 
                                 COUNT(*) as total,
                                 SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
                                 FROM attendance 
                                 WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $attendance = $stmt->fetch();
            $attendance_rate = $attendance['total'] > 0 ? round(($attendance['present'] / $attendance['total']) * 100) : 0;
            ?>
            
            <div class="dashboard-card">
                <h3>My Classes</h3>
                <div class="number"><?php echo $stats['classes']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>My Subjects</h3>
                <div class="number"><?php echo $stats['subjects']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>Attendance Rate</h3>
                <div class="number"><?php echo $attendance_rate; ?>%</div>
            </div>
            
            <div class="dashboard-card">
                <h3>Today's Schedule</h3>
                <div class="number"><?php echo date('M d'); ?></div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Recent Attendance</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT a.*, c.class_name, s.subject_name 
                                         FROM attendance a 
                                         JOIN classes c ON a.class_id = c.class_id 
                                         JOIN subjects s ON c.subject_id = s.subject_id 
                                         WHERE a.student_id = ? 
                                         ORDER BY a.date DESC, a.created_at DESC 
                                         LIMIT 10");
                    $stmt->execute([$student_id]);
                    $attendance_records = $stmt->fetchAll();
                    
                    foreach ($attendance_records as $record) {
                        $status_class = '';
                        switch ($record['status']) {
                            case 'Present':
                                $status_class = 'badge-success';
                                break;
                            case 'Late':
                                $status_class = 'badge-warning';
                                break;
                            case 'Absent':
                                $status_class = 'badge-danger';
                                break;
                        }
                        
                        echo "<tr>
                            <td>{$record['date']}</td>
                            <td>{$record['class_name']}</td>
                            <td>{$record['subject_name']}</td>
                            <td><span class='badge {$status_class}'>{$record['status']}</span></td>
                            <td>{$record['remarks']}</td>
                        </tr>";
                    }
                    
                    if (empty($attendance_records)) {
                        echo "<tr><td colspan='5' style='text-align: center;'>No attendance records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- My Classes -->
        <div class="table-container" style="margin-top: 2rem;">
            <div class="table-header">
                <h2 class="table-title">My Enrolled Classes</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Subject</th>
                        <th>Professor</th>
                        <th>Schedule</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT c.*, s.subject_name, p.first_name, p.last_name 
                                         FROM student_classes sc 
                                         JOIN classes c ON sc.class_id = c.class_id 
                                         JOIN subjects s ON c.subject_id = s.subject_id 
                                         JOIN professors p ON c.professor_id = p.professor_id 
                                         WHERE sc.student_id = ?");
                    $stmt->execute([$student_id]);
                    $enrolled_classes = $stmt->fetchAll();
                    
                    foreach ($enrolled_classes as $class) {
                        echo "<tr>
                            <td>{$class['class_name']}</td>
                            <td>{$class['subject_name']}</td>
                            <td>Prof. {$class['first_name']} {$class['last_name']}</td>
                            <td>{$class['schedule']}</td>
                            <td>{$class['room']}</td>
                        </tr>";
                    }
                    
                    if (empty($enrolled_classes)) {
                        echo "<tr><td colspan='5' style='text-align: center;'>No classes enrolled yet</td></tr>";
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
