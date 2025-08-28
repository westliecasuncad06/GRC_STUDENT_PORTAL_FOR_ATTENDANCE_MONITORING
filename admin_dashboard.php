<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    <title>Admin Dashboard - Global Reciprocal College</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="admin_dashboard.php" class="sidebar-link active">Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="admin_manage_professors.php" class="sidebar-link">Manage Professors</a>
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
        <h1>Admin Dashboard</h1>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <?php
            // Get statistics
            $stats = [];
            
            // Total Students
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
            $stats['students'] = $stmt->fetch()['count'];
            
            // Total Professors
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM professors");
            $stats['professors'] = $stmt->fetch()['count'];
            
            // Total Classes
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM classes");
            $stats['classes'] = $stmt->fetch()['count'];
            
            // Total Subjects
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM subjects");
            $stats['subjects'] = $stmt->fetch()['count'];
            ?>
            
            <div class="dashboard-card">
                <h3>Total Students</h3>
                <div class="number"><?php echo $stats['students']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Professors</h3>
                <div class="number"><?php echo $stats['professors']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Classes</h3>
                <div class="number"><?php echo $stats['classes']; ?></div>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Subjects</h3>
                <div class="number"><?php echo $stats['subjects']; ?></div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Recent Activities</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>New student registered</td>
                        <td>2024-01-15 14:30</td>
                        <td>John Doe</td>
                    </tr>
                    <tr>
                        <td>Class created</td>
                        <td>2024-01-15 13:45</td>
                        <td>Prof. Smith</td>
                    </tr>
                    <tr>
                        <td>Attendance marked</td>
                        <td>2024-01-15 12:00</td>
                        <td>Prof. Johnson</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Hamburger menu toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
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
