<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header('Location: index.php');
    exit();
}

require_once '../php/db.php';

// Fetch professor data using session values
$professor_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM professors WHERE professor_id = ?");
$stmt->execute([$professor_id]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

// Populate session fields only if values are available in DB
if ($professor) {
    $_SESSION['first_name'] = $professor['first_name'];
    $_SESSION['last_name'] = $professor['last_name'];
    $_SESSION['email'] = $professor['email'];
    $_SESSION['mobile'] = $professor['mobile'];
}

// Get professor's subjects (from attendance_reports.php)
$query = "SELECT s.*, c.class_id, c.class_code, c.schedule, c.room 
          FROM subjects s 
          JOIN classes c ON s.subject_id = c.subject_id 
          WHERE c.professor_id = ? 
          ORDER BY s.subject_name";
$stmt = $pdo->prepare($query);
$stmt->execute([$professor_id]);
$subjects = $stmt->fetchAll();

// Get attendance statistics (from attendance_reports.php)
$attendance_stats = [];
foreach ($subjects as $subject) {
    $query = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused
              FROM attendance 
              WHERE class_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$subject['class_id']]);
    $stats = $stmt->fetch();
    
    $attendance_stats[$subject['class_id']] = $stats;
}

// Get recent attendance dates (from attendance_reports.php)
$recent_dates = [];
foreach ($subjects as $subject) {
    $query = "SELECT DISTINCT date 
              FROM attendance 
              WHERE class_id = ? 
              ORDER BY date DESC 
              LIMIT 2";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$subject['class_id']]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $recent_dates[$subject['class_id']] = $dates;
}

// Get detailed attendance statistics (from attendance_analytics.php)
$detailed_stats = [];
foreach ($subjects as $subject) {
    // Overall statistics
    $query = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused,
                MIN(date) as first_date,
                MAX(date) as last_date
              FROM attendance 
              WHERE class_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$subject['class_id']]);
    $overall = $stmt->fetch();
    
    // Monthly breakdown
    $query = "SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused
              FROM attendance 
              WHERE class_id = ?
              GROUP BY DATE_FORMAT(date, '%Y-%m')
              ORDER BY month DESC
              LIMIT 6";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$subject['class_id']]);
    $monthly = $stmt->fetchAll();
    
    // Student-wise statistics
    $query = "SELECT 
                s.student_id,
                s.first_name,
                s.last_name,
                COUNT(a.student_id) as total_classes,
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN a.status = 'Excused' THEN 1 ELSE 0 END) as excused
              FROM students s
              JOIN student_classes sc ON s.student_id = sc.student_id
              LEFT JOIN attendance a ON s.student_id = a.student_id AND a.class_id = ?
              WHERE sc.class_id = ?
              GROUP BY s.student_id, s.first_name, s.last_name
              ORDER BY s.last_name, s.first_name";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$subject['class_id'], $subject['class_id']]);
    $students = $stmt->fetchAll();
    
    $detailed_stats[$subject['class_id']] = [
        'overall' => $overall,
        'monthly' => $monthly,
        'students' => $students
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard - Global Reciprocal College</title>
    <link rel="stylesheet" href="../css/styles_fixed.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .stat-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .stat-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-present { background: #d4edda; color: #155724; }
        .badge-absent { background: #f8d7da; color: #721c24; }
        .badge-late { background: #fff3cd; color: #856404; }
        .badge-excused { background: #d1ecf1; color: #0c5460; }

        .recent-dates {
            margin-top: 1rem;
        }

        .date-list {
            list-style: none;
            padding: 0;
        }

        .date-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .date-item:last-child {
            border-bottom: none;
        }

        .view-report-btn {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .view-report-btn:hover {
            background: var(--primary-dark);
        }

        /* Attendance Modal Styles */
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

        .attendance-record {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .record-header {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 1rem;
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .record-item {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 1rem;
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            align-items: center;
            font-size: 0.9rem;
        }

        .attendance-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
        }

        .attendance-status.Present {
            background-color: #d4edda;
            color: #155724;
        }

        .attendance-status.Absent {
            background-color: #f8d7da;
            color: #721c24;
        }

        .attendance-status.Late {
            background-color: #fff3cd;
            color: #856404;
        }

        .attendance-status.Excused {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .attendance-status.null {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .students-table th,
        .students-table td {
            padding: 0.75rem;
            border: 1px solid var(--light-gray);
            text-align: left;
        }

        .students-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .students-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .attendance-rate {
            font-weight: 600;
        }

        .rate-high { color: #28a745; }
        .rate-medium { color: #ffc107; }
        .rate-low { color: #dc3545; }

        .no-data {
            text-align: center;
            color: var(--gray);
            padding: 2rem;
            font-style: italic;
        }

        .tab-container {
            margin-bottom: 2rem;
        }

        .tab-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            background: #f8f9fa;
            cursor: pointer;
            font-weight: 500;
        }

        .tab-btn.active {
            background: var(--primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
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

        /* Enhanced Stat Card Styles */
        .stat-card-enhanced {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
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
        }

        .table-title-enhanced {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
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
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar_professor.php'; ?>

    <?php include '../includes/sidebar_professor.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header-enhanced">
            <h2 class="table-title-enhanced"><i class="fas fa-chart-line" style="margin-right: 10px;"></i>Professor Dashboard - Attendance Reports & Analytics</h2>
        </div>

        <div class="dashboard-container">
            <?php if (empty($subjects)): ?>
                <div class="no-data">
                    <p>No classes found. Please create a class first.</p>
                </div>
            <?php else: ?>
                <!-- Attendance Reports -->
                <div class="reports-container">
                        <div class="stats-grid">
                            <?php foreach ($subjects as $subject): 
                                $stats = $attendance_stats[$subject['class_id']] ?? null;
                                $recent = $recent_dates[$subject['class_id']] ?? [];
                            ?>
                            <div class="stat-card-enhanced">
                                <div class="stat-header-enhanced">
                                    <div class="stat-icon-enhanced">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <div class="stat-info-enhanced">
                                        <h3 class="stat-title-enhanced"><?php echo $subject['subject_name']; ?></h3>
                                        <span class="stat-subtitle-enhanced"><?php echo $subject['class_code']; ?></span>
                                    </div>
                                </div>

                                <?php if ($stats && $stats['total_records'] > 0): ?>
                                    <div class="stat-metrics-enhanced">
                                        <div class="stat-main-metric">
                                            <div class="stat-value-enhanced">
                                                <?php
                                                $attendance_rate = $stats['total_records'] > 0 ?
                                                    (($stats['present'] + $stats['late'] + $stats['excused']) / $stats['total_records']) * 100 : 0;
                                                echo number_format($attendance_rate, 1) . '%';
                                                ?>
                                            </div>
                                            <div class="stat-label-enhanced">Attendance Rate</div>
                                        </div>

                                        <div class="stat-breakdown-enhanced">
                                            <div class="stat-breakdown-item">
                                                <div class="stat-breakdown-icon" style="color: #28a745;">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="stat-breakdown-value"><?php echo $stats['present']; ?></div>
                                                <div class="stat-breakdown-label">Present</div>
                                            </div>
                                            <div class="stat-breakdown-item">
                                                <div class="stat-breakdown-icon" style="color: #dc3545;">
                                                    <i class="fas fa-times-circle"></i>
                                                </div>
                                                <div class="stat-breakdown-value"><?php echo $stats['absent']; ?></div>
                                                <div class="stat-breakdown-label">Absent</div>
                                            </div>
                                            <div class="stat-breakdown-item">
                                                <div class="stat-breakdown-icon" style="color: #ffc107;">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="stat-breakdown-value"><?php echo $stats['late']; ?></div>
                                                <div class="stat-breakdown-label">Late</div>
                                            </div>
                                            <div class="stat-breakdown-item">
                                                <div class="stat-breakdown-icon" style="color: #17a2b8;">
                                                    <i class="fas fa-user-check"></i>
                                                </div>
                                                <div class="stat-breakdown-value"><?php echo $stats['excused']; ?></div>
                                                <div class="stat-breakdown-label">Excused</div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($recent)): ?>
                                    <div class="stat-actions-enhanced">
                                        <h4 class="stat-section-title">Recent Sessions</h4>
                                        <div class="stat-recent-list">
                                            <?php foreach ($recent as $date): ?>
                                                <div class="stat-recent-item">
                                                    <div class="stat-recent-date">
                                                        <i class="fas fa-calendar-day"></i>
                                                        <?php echo date('M j, Y', strtotime($date)); ?>
                                                    </div>
                                                    <button class="stat-action-btn" data-class-id="<?php echo $subject['class_id']; ?>" data-date="<?php echo $date; ?>">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <div class="stat-empty-enhanced">
                                        <div class="stat-empty-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="stat-empty-text">No attendance records yet</div>
                                        <button class="stat-primary-btn" onclick="location.href='professor_manage_schedule.php'">
                                            <i class="fas fa-plus"></i> Take Attendance
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Analytics Tab -->
                    <div id="analytics-tab" class="tab-content">
                        <div class="subject-selector">
                            <select class="subject-select" id="subjectSelect" onchange="updateAnalytics()">
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['class_id']; ?>">
                                        <?php echo $subject['subject_name']; ?> (<?php echo $subject['class_code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php foreach ($subjects as $subject): 
                            $stats = $detailed_stats[$subject['class_id']] ?? null;
                            $show = $subject === reset($subjects) ? '' : 'style="display: none;"';
                        ?>
                        <div id="analytics-<?php echo $subject['class_id']; ?>" class="analytics-content" <?php echo $show; ?>>
                            <?php if ($stats && $stats['overall']['total_records'] > 0): ?>
                                <!-- Overall Statistics -->
                                <div class="stat-card">
                                    <h3 class="chart-title">Overall Attendance Statistics</h3>
                                    <div class="stats-grid">
                                        <div class="stat-item stat-present">
                                            <div class="stat-number"><?php echo $stats['overall']['present']; ?></div>
                                            <div>Present</div>
                                        </div>
                                        <div class="stat-item stat-absent">
                                            <div class="stat-number"><?php echo $stats['overall']['absent']; ?></div>
                                            <div>Absent</div>
                                        </div>
                                        <div class="stat-item stat-late">
                                            <div class="stat-number"><?php echo $stats['overall']['late']; ?></div>
                                            <div>Late</div>
                                        </div>
                                        <div class="stat-item stat-excused">
                                            <div class="stat-number"><?php echo $stats['overall']['excused']; ?></div>
                                            <div>Excused</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Charts -->
                                <div class="analytics-grid">
                                    <div class="chart-container">
                                        <h3 class="chart-title">Attendance Distribution</h3>
                                        <canvas id="pieChart-<?php echo $subject['class_id']; ?>" width="400" height="400"></canvas>
                                    </div>
                                    
                                    <div class="chart-container">
                                        <h3 class="chart-title">Monthly Trend</h3>
                                        <canvas id="barChart-<?php echo $subject['class_id']; ?>" width="400" height="400"></canvas>
                                    </div>
                                </div>

                                <!-- Student-wise Statistics -->
                                <div class="stat-card">
                                    <h3 class="chart-title">Student Attendance Summary</h3>
                                    <table class="students-table">
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Excused</th>
                                                <th>Attendance Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['students'] as $student): 
                                                $total = $student['total_classes'];
                                                $present = $student['present'] + $student['late'] + $student['excused'];
                                                $rate = $total > 0 ? ($present / $total) * 100 : 0;
                                                $rateClass = $rate >= 80 ? 'rate-high' : ($rate >= 60 ? 'rate-medium' : 'rate-low');
                                            ?>
                                            <tr>
                                                <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                                <td><?php echo $student['present']; ?></td>
                                                <td><?php echo $student['absent']; ?></td>
                                                <td><?php echo $student['late']; ?></td>
                                                <td><?php echo $student['excused']; ?></td>
                                                <td class="attendance-rate <?php echo $rateClass; ?>">
                                                    <?php echo number_format($rate, 1); ?>%
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php else: ?>
                                <div class="no-data">
                                    <p>No attendance records found for this class</p>
                                    <button class="btn btn-primary" onclick="location.href='professor_manage_schedule.php'">
                                        Take Attendance
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Attendance Modal -->
    <div id="attendanceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="attendanceModalTitle">Attendance Details</h3>
                <button class="modal-close" onclick="closeAttendanceModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="attendanceContent">
                    <!-- Attendance records will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global function for modal close button
        function closeAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            modal.classList.remove('show');
        }

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            function showTab(tabName) {
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });

                // Show selected tab
                document.getElementById(tabName + '-tab').classList.add('active');

                // Update active button
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`.tab-btn[onclick="showTab('${tabName}')"]`).classList.add('active');
            }

            function viewDateReport(classId, date) {
                // Load attendance data for the specific date and class
                loadAttendanceData(classId, date);
            }

            function loadAttendanceData(classId, date) {
                const modal = document.getElementById('attendanceModal');
                const modalTitle = document.getElementById('attendanceModalTitle');
                const content = document.getElementById('attendanceContent');

                // Set modal title
                modalTitle.textContent = `Attendance for ${new Date(date).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                })}`;

                // Show loading state
                content.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="loading-spinner">Loading attendance data...</div></div>';

                // Open modal
                modal.classList.add('show');

                // Fetch attendance data
                fetch(`../php/get_attendance_for_date.php?class_id=${classId}&date=${date}`, { credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(attendanceRecords => {
                        if (attendanceRecords.length === 0) {
                            content.innerHTML = '<div style="text-align: center; color: var(--gray); padding: 2rem;">No attendance records found for this date.</div>';
                            return;
                        }

                        // Create attendance records display
                        const recordsHTML = `
                            <div class="attendance-record">
                                <div class="record-header">
                                    <div>Student Name</div>
                                    <div>Status</div>
                                    <div>Remarks</div>
                                </div>
                                ${attendanceRecords.map(record => {
                                    const status = record.status || 'Not Marked';
                                    const statusClass = record.status || 'null';
                                    return `
                                        <div class="record-item">
                                            <div>${record.first_name} ${record.last_name}</div>
                                            <div><span class="attendance-status ${statusClass}">${status}</span></div>
                                            <div>${record.remarks || 'No remarks'}</div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        `;

                        content.innerHTML = recordsHTML;
                    })
                    .catch(error => {
                        console.error('Error loading attendance data:', error);
                        content.innerHTML = '<div style="text-align: center; color: var(--danger); padding: 2rem;">Error loading attendance data. Please try again.</div>';
                    });
            }

            // Attach event listeners to view report buttons
            document.querySelectorAll('.stat-action-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const classId = this.getAttribute('data-class-id');
                    const date = this.getAttribute('data-date');
                    if (classId && date) {
                        viewDateReport(classId, date);
                    }
                });
            });

            // Close modal when clicking outside
            document.addEventListener('click', function(event) {
                const modal = document.getElementById('attendanceModal');
                if (event.target === modal) {
                    closeAttendanceModal();
                }
            });

            function updateAnalytics() {
                const classId = document.getElementById('subjectSelect').value;
                document.querySelectorAll('.analytics-content').forEach(el => {
                    el.style.display = 'none';
                });
                document.getElementById('analytics-' + classId).style.display = 'block';
            }

            // Initialize charts for each subject
            <?php foreach ($subjects as $subject): 
                $stats = $detailed_stats[$subject['class_id']] ?? null;
                if ($stats && $stats['overall']['total_records'] > 0):
            ?>
            // Pie Chart
            const pieCtx<?php echo $subject['class_id']; ?> = document.getElementById('pieChart-<?php echo $subject['class_id']; ?>').getContext('2d');
            new Chart(pieCtx<?php echo $subject['class_id']; ?>, {
                type: 'pie',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'Excused'],
                    datasets: [{
                        data: [
                            <?php echo $stats['overall']['present']; ?>,
                            <?php echo $stats['overall']['absent']; ?>,
                            <?php echo $stats['overall']['late']; ?>,
                            <?php echo $stats['overall']['excused']; ?>
                        ],
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

            // Bar Chart
            const barCtx<?php echo $subject['class_id']; ?> = document.getElementById('barChart-<?php echo $subject['class_id']; ?>').getContext('2d');
            const monthlyData<?php echo $subject['class_id']; ?> = <?php echo json_encode($stats['monthly']); ?>;
            const months<?php echo $subject['class_id']; ?> = monthlyData<?php echo $subject['class_id']; ?>.map(item => item.month);
            const presentData<?php echo $subject['class_id']; ?> = monthlyData<?php echo $subject['class_id']; ?>.map(item => item.present);
            const absentData<?php echo $subject['class_id']; ?> = monthlyData<?php echo $subject['class_id']; ?>.map(item => item.absent);
            
            new Chart(barCtx<?php echo $subject['class_id']; ?>, {
                type: 'bar',
                data: {
                    labels: months<?php echo $subject['class_id']; ?>,
                    datasets: [
                        {
                            label: 'Present',
                            data: presentData<?php echo $subject['class_id']; ?>,
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'Absent',
                            data: absentData<?php echo $subject['class_id']; ?>,
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
            <?php endif; endforeach; ?>

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

            // Dropdown behaviour is handled in the included navbar script
        });
    </script>
</body>
</html>
