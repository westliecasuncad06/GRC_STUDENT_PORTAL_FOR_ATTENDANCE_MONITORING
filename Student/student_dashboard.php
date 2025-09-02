     <?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit();
}

require_once '../php/db.php';

// Fetch student data
$student_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Store student data in session for form population
$_SESSION['first_name'] = $student['first_name'];
$_SESSION['last_name'] = $student['last_name'];
$_SESSION['email'] = $student['email'];
$_SESSION['mobile'] = $student['mobile'];
$_SESSION['address'] = $student['address'];
// Attendance analytics
$overall_stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE student_id = ? GROUP BY status");
$overall_stmt->execute([$student_id]);
$overall_analytics = $overall_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
// Overall statistics
$overall_stats_stmt = $pdo->prepare("SELECT
    COUNT(*) as total_records,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
    SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused,
    MIN(date) as first_date,
    MAX(date) as last_date
FROM attendance WHERE student_id = ?");
$overall_stats_stmt->execute([$student_id]);
$overall_stats = $overall_stats_stmt->fetch();
// Monthly data
$monthly_stmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%Y-%m') as month, status, COUNT(*) as count FROM attendance WHERE student_id = ? GROUP BY month, status ORDER BY month");
$monthly_stmt->execute([$student_id]);
$monthly_raw = $monthly_stmt->fetchAll();
// Process monthly data
$monthly_data = [];
$months = [];
foreach ($monthly_raw as $row) {
    $month = $row['month'];
    if (!in_array($month, $months)) {
        $months[] = $month;
    }
    if (!isset($monthly_data[$month])) {
        $monthly_data[$month] = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0];
    }
    $monthly_data[$month][$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Global Reciprocal College</title>
    <link rel="stylesheet" href="../css/styles_fixed.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Enhanced Dashboard Container */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Enhanced Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card-enhanced {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .stat-card-enhanced:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
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

        .stat-recent-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stat-recent-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-recent-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--dark);
            font-weight: 500;
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
        }

        .stat-action-btn:hover {
            background: var(--primary-dark);
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

        /* Enhanced Table Header */
        .table-header-enhanced {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            gap: 1rem;
        }

        /* Enhanced Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 3rem 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .page-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0.5rem 0 0 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 16px;
            padding: 0;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            padding: 0.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1.5rem 2rem;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 0 0 16px 16px;
        }

        /* Enhanced Attendance Record Styles */
        .attendance-record {
            width: 100%;
        }

        .record-header {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .record-item {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            align-items: center;
        }

        .record-item:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .record-item span {
            font-size: 0.9rem;
            color: var(--dark);
        }

        .record-item span:first-child {
            font-weight: 600;
        }

        .record-item .attendance-status {
            justify-self: center;
        }

        /* Enhanced Attendance Table */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .attendance-table th {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
        }

        .attendance-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .attendance-table tr:hover {
            background-color: #e9ecef;
        }

        .attendance-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }

        .attendance-status.Present {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .attendance-status.Absent {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .attendance-status.Late {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .attendance-status.Excused {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .attendance-status.null {
            background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        /* Enhanced Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        /* Enhanced Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        /* Loading States */
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-state-text {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        /* Dropdown Menu Styles */
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

        /* Charts Section */
        .charts-section {
            margin: 2rem 0;
        }

        .charts-container {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .chart-item {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-item h3 {
            text-align: center;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stat-card-enhanced {
                padding: 1.5rem;
            }

            .stat-breakdown-enhanced {
                grid-template-columns: 1fr;
            }

            .stat-recent-item {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start;
            }

            .stat-action-btn {
                width: 100%;
                justify-content: center;
            }

            .table-header-enhanced {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .table-actions-enhanced {
                width: 100%;
                justify-content: center;
            }

            .charts-container {
                flex-direction: column;
            }

            .chart-item {
                min-width: auto;
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
            <span>Welcome, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></span>
            <div class="user-dropdown">
                <button class="dropdown-toggle">⚙️</button>
                <div class="dropdown-menu">
                    <a href="../admin/settings.php" class="dropdown-item">Settings</a>
                    <a href="../php/logout.php" class="dropdown-item">Logout</a>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="student_dashboard.php" class="sidebar-link active">Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="student_manage_schedule.php" class="sidebar-link">My Subjects</a>
            </li>
            <li class="sidebar-item">
                <a href="../admin/settings.php" class="sidebar-link">Settings</a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header-enhanced">
            <h2 class="table-title-enhanced"><i class="fas fa-tachometer-alt" style="margin-right: 10px;"></i>Student Dashboard</h2>
            <div class="table-actions-enhanced">
                <button class="btn btn-primary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </div>

        <div class="dashboard-container">
        <!-- Per Subject Attendance Tiles -->
        <?php
        // Fetch student's enrolled subjects/classes
        $stmt = $pdo->prepare("SELECT c.class_id, c.class_name, s.subject_name
                               FROM student_classes sc
                               JOIN classes c ON sc.class_id = c.class_id
                               JOIN subjects s ON c.subject_id = s.subject_id
                               WHERE sc.student_id = ?");
        $stmt->execute([$student_id]);
        $student_subjects = $stmt->fetchAll();

        // Prepare attendance stats per subject for the student
        $attendance_stats = [];
        $recent_dates = [];
        foreach ($student_subjects as $subject) {
            // Attendance stats
            $query = "SELECT 
                        COUNT(*) as total_records,
                        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                        SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused
                      FROM attendance
                      WHERE student_id = ? AND class_id = ?";
            $stmt2 = $pdo->prepare($query);
            $stmt2->execute([$student_id, $subject['class_id']]);
            $stats = $stmt2->fetch();

            $attendance_stats[$subject['class_id']] = $stats;

            // Recent attendance dates
            $query = "SELECT DISTINCT date
                      FROM attendance
                      WHERE student_id = ? AND class_id = ?
                      ORDER BY date DESC
                      LIMIT 2";
            $stmt3 = $pdo->prepare($query);
            $stmt3->execute([$student_id, $subject['class_id']]);
            $dates = $stmt3->fetchAll(PDO::FETCH_COLUMN);
            $recent_dates[$subject['class_id']] = $dates;
        }
        ?>

        <div class="stats-grid">
            <?php foreach ($student_subjects as $subject):
                $stats = $attendance_stats[$subject['class_id']] ?? null;
            ?>
            <div class="stat-card-enhanced">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info-enhanced">
                        <h3 class="stat-title-enhanced"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                        <p class="stat-subtitle-enhanced"><?php echo htmlspecialchars($subject['class_name']); ?></p>
                    </div>
                </div>

                <?php if ($stats && $stats['total_records'] > 0):
                    $attendance_rate = ($stats['present'] + $stats['late'] + $stats['excused']) / $stats['total_records'] * 100;
                ?>
                <div class="stat-metrics-enhanced">
                    <div class="stat-main-metric">
                        <div class="stat-value-enhanced"><?php echo number_format($attendance_rate, 1); ?>%</div>
                        <div class="stat-label-enhanced">Attendance Rate</div>
                    </div>

                    <div class="stat-breakdown-enhanced">
                        <div class="stat-breakdown-item">
                            <i class="fas fa-check-circle stat-breakdown-icon" style="color: #28a745;"></i>
                            <div>
                                <div class="stat-breakdown-value"><?php echo $stats['present']; ?></div>
                                <div class="stat-breakdown-label">Present</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-times-circle stat-breakdown-icon" style="color: #dc3545;"></i>
                            <div>
                                <div class="stat-breakdown-value"><?php echo $stats['absent']; ?></div>
                                <div class="stat-breakdown-label">Absent</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-clock stat-breakdown-icon" style="color: #ffc107;"></i>
                            <div>
                                <div class="stat-breakdown-value"><?php echo $stats['late']; ?></div>
                                <div class="stat-breakdown-label">Late</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-exclamation-circle stat-breakdown-icon" style="color: #17a2b8;"></i>
                            <div>
                                <div class="stat-breakdown-value"><?php echo $stats['excused']; ?></div>
                                <div class="stat-breakdown-label">Excused</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($recent_dates[$subject['class_id']])): ?>
                <div class="stat-actions-enhanced">
                    <h4 class="stat-section-title">Recent Attendance</h4>
                    <div class="stat-recent-list">
                        <?php foreach ($recent_dates[$subject['class_id']] as $date): ?>
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: var(--primary);"></i>
                                <?php echo date('M j, Y', strtotime($date)); ?>
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('<?php echo $subject['class_id']; ?>', '<?php echo $date; ?>')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="stat-empty-enhanced">
                    <i class="fas fa-chart-line stat-empty-icon"></i>
                    <div class="stat-empty-text">No attendance records yet</div>
                    <button class="stat-primary-btn">
                        <i class="fas fa-plus"></i>
                        Start Tracking
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Overall Analytics Charts -->
        <div class="charts-section">
            <h2>Overall Attendance Analytics</h2>
            <div class="charts-container">
                <div class="chart-item">
                    <h3>Attendance Distribution</h3>
                    <canvas id="pieChart" width="400" height="400"></canvas>
                </div>
                <div class="chart-item">
                    <h3>Monthly Attendance</h3>
                    <canvas id="barChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
        </div>

        <!-- Recent Attendance -->
        <div class="table-container">
            <div class="table-header-enhanced">
                <h2 class="table-title-enhanced">Recent Attendance</h2>
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
            <div class="table-header-enhanced">
                <h2 class="table-title-enhanced">My Enrolled Classes</h2>
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


    <script>
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
        

        <!-- Attendance Modal -->
        <div id="attendanceModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="attendanceModalTitle" aria-hidden="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="attendanceModalTitle" class="modal-title">Attendance and Remarks</h3>
                    <button class="modal-close" aria-label="Close modal" onclick="closeAttendanceModal()">&times;</button>
                </div>
                <div class="modal-body" id="attendanceModalBody">
                    <p>Loading attendance data...</p>
                </div>
            </div>
        </div>

        

        <script>
            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const attendanceData = <?php echo json_encode($attendance_analytics); ?>;
            const labels = Object.keys(attendanceData);
            const data = Object.values(attendanceData);
            if (data.length > 0) {
                new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#28a745',
                                '#dc3545',
                                '#ffc107',
                                '#17a2b8'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            } else {
                pieCtx.font = '20px Arial';
                pieCtx.fillText('No attendance data available', 10, 50);
            }

            // Bar Chart
            const barCtx = document.getElementById('barChart').getContext('2d');
            const monthlyData = <?php echo json_encode($monthly_data); ?>;
            const months = Object.keys(monthlyData).sort();
            const presentData = months.map(month => monthlyData[month]['Present'] || 0);
            const absentData = months.map(month => monthlyData[month]['Absent'] || 0);

            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Present',
                            data: presentData,
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'Absent',
                            data: absentData,
                            backgroundColor: '#dc3545'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Number of Records'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>

        <script>
            // Attendance Modal Functions
            function openAttendanceModal(classId, date) {
                const modal = document.getElementById('attendanceModal');
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                loadAttendanceData(classId, date);
            }

            function closeAttendanceModal() {
                const modal = document.getElementById('attendanceModal');
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                document.getElementById('attendanceModalBody').innerHTML = '<p>Loading attendance data...</p>';
            }

            async function loadAttendanceData(classId, date) {
                const modalBody = document.getElementById('attendanceModalBody');
                modalBody.innerHTML = '<p>Loading attendance data...</p>';
                try {
                    const response = await fetch(`../php/get_attendance_for_date.php?class_id=${classId}&date=${date}`);
                    if (!response.ok) throw new Error('Failed to fetch attendance data');
                    const attendanceRecords = await response.json();

                    if (attendanceRecords.length === 0) {
                        modalBody.innerHTML = '<p>No attendance records found for this date.</p>';
                        return;
                    }

                    let html = '<div class="attendance-record">';
                    html += '<div class="record-header"><strong>Student</strong><strong>Status</strong><strong>Remarks</strong></div>';
                    attendanceRecords.forEach(record => {
                        const statusClass = record.status === 'Present' ? 'Present' : record.status === 'Absent' ? 'Absent' : record.status === 'Late' ? 'Late' : 'Excused';
                        html += `<div class="record-item">
                            <span>${record.first_name} ${record.last_name}</span>
                            <span class="attendance-status ${statusClass}">${record.status || 'No status'}</span>
                            <span>${record.remarks || ''}</span>
                        </div>`;
                    });
                    html += '</div>';
                    modalBody.innerHTML = html;
                } catch (error) {
                    modalBody.innerHTML = '<p>Error loading attendance data.</p>';
                    console.error(error);
                }
            }

        // Close attendance modal when clicking outside
        document.getElementById('attendanceModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeAttendanceModal();
            }
        });

        // Refresh dashboard function
        function refreshDashboard() {
            const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;

            // Reload the page to refresh all data
            setTimeout(() => {
                location.reload();
            }, 500);
        }
    </script>
</body>
</html>
