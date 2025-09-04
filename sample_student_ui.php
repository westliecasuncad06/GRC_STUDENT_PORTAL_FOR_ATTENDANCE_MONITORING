<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Student Portal - Attendance Monitoring</title>
    <link rel="stylesheet" href="css/styles.css">
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
            background: linear-gradient(90deg, #C62828 0%, #B71C1C 100%);
        }

        .stat-card-enhanced:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border-color: #C62828;
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            color: #343a40;
            margin: 0 0 0.25rem 0;
        }

        .stat-subtitle-enhanced {
            font-size: 0.9rem;
            color: #6c757d;
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
            color: #C62828;
            margin-bottom: 0.25rem;
        }

        .stat-label-enhanced {
            font-size: 1rem;
            color: #6c757d;
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
            color: #343a40;
        }

        .stat-breakdown-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .stat-actions-enhanced {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 1.5rem;
        }

        .stat-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #343a40;
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
            color: #343a40;
            font-weight: 500;
        }

        .stat-action-btn {
            background: #C62828;
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
            background: #B71C1C;
        }

        .stat-empty-enhanced {
            text-align: center;
            padding: 3rem 1rem;
        }

        .stat-empty-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .stat-empty-text {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .stat-primary-btn {
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            color: #343a40;
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
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
            border-top: 3px solid #C62828;
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
            color: #6c757d;
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
            color: #343a40;
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
            <button type="button" class="hamburger-menu" id="sidebarToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <span class="navbar-title">Global Reciprocal College</span>
            <span class="navbar-title-mobile">GRC</span>
        </div>
        <div class="navbar-user">
            <span>Welcome, Sample Student</span>
            <div class="user-dropdown">
                <button type="button" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">Settings</a>
                    <a href="#" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link"><i class="fas fa-calendar-alt"></i> My Schedule</a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a>
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
        <div class="stats-grid">
            <!-- Sample Subject 1 -->
            <div class="stat-card-enhanced">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info-enhanced">
                        <h3 class="stat-title-enhanced">Mathematics</h3>
                        <p class="stat-subtitle-enhanced">MATH-101</p>
                    </div>
                </div>

                <div class="stat-metrics-enhanced">
                    <div class="stat-main-metric">
                        <div class="stat-value-enhanced">85%</div>
                        <div class="stat-label-enhanced">Attendance Rate</div>
                    </div>

                    <div class="stat-breakdown-enhanced">
                        <div class="stat-breakdown-item">
                            <i class="fas fa-check-circle stat-breakdown-icon" style="color: #28a745;"></i>
                            <div>
                                <div class="stat-breakdown-value">17</div>
                                <div class="stat-breakdown-label">Present</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-times-circle stat-breakdown-icon" style="color: #dc3545;"></i>
                            <div>
                                <div class="stat-breakdown-value">2</div>
                                <div class="stat-breakdown-label">Absent</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-clock stat-breakdown-icon" style="color: #ffc107;"></i>
                            <div>
                                <div class="stat-breakdown-value">1</div>
                                <div class="stat-breakdown-label">Late</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-exclamation-circle stat-breakdown-icon" style="color: #17a2b8;"></i>
                            <div>
                                <div class="stat-breakdown-value">0</div>
                                <div class="stat-breakdown-label">Excused</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stat-actions-enhanced">
                    <h4 class="stat-section-title">Recent Attendance</h4>
                    <div class="stat-recent-list">
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: #C62828;"></i>
                                Oct 15, 2023
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('MATH-101', '2023-10-15')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: #C62828;"></i>
                                Oct 12, 2023
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('MATH-101', '2023-10-12')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Subject 2 -->
            <div class="stat-card-enhanced">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info-enhanced">
                        <h3 class="stat-title-enhanced">Computer Science</h3>
                        <p class="stat-subtitle-enhanced">CS-201</p>
                    </div>
                </div>

                <div class="stat-metrics-enhanced">
                    <div class="stat-main-metric">
                        <div class="stat-value-enhanced">92%</div>
                        <div class="stat-label-enhanced">Attendance Rate</div>
                    </div>

                    <div class="stat-breakdown-enhanced">
                        <div class="stat-breakdown-item">
                            <i class="fas fa-check-circle stat-breakdown-icon" style="color: #28a745;"></i>
                            <div>
                                <div class="stat-breakdown-value">23</div>
                                <div class="stat-breakdown-label">Present</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-times-circle stat-breakdown-icon" style="color: #dc3545;"></i>
                            <div>
                                <div class="stat-breakdown-value">1</div>
                                <div class="stat-breakdown-label">Absent</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-clock stat-breakdown-icon" style="color: #ffc107;"></i>
                            <div>
                                <div class="stat-breakdown-value">1</div>
                                <div class="stat-breakdown-label">Late</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-exclamation-circle stat-breakdown-icon" style="color: #17a2b8;"></i>
                            <div>
                                <div class="stat-breakdown-value">0</div>
                                <div class="stat-breakdown-label">Excused</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stat-actions-enhanced">
                    <h4 class="stat-section-title">Recent Attendance</h4>
                    <div class="stat-recent-list">
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: #C62828;"></i>
                                Oct 14, 2023
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('CS-201', '2023-10-14')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: #C62828;"></i>
                                Oct 11, 2023
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('CS-201', '2023-10-11')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Subject 3 -->
            <div class="stat-card-enhanced">
                <div class="stat-header-enhanced">
                    <div class="stat-icon-enhanced">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info-enhanced">
                        <h3 class="stat-title-enhanced">Physics</h3>
                        <p class="stat-subtitle-enhanced">PHYS-101</p>
                    </div>
                </div>

                <div class="stat-metrics-enhanced">
                    <div class="stat-main-metric">
                        <div class="stat-value-enhanced">78%</div>
                        <div class="stat-label-enhanced">Attendance Rate</div>
                    </div>

                    <div class="stat-breakdown-enhanced">
                        <div class="stat-breakdown-item">
                            <i class="fas fa-check-circle stat-breakdown-icon" style="color: #28a745;"></i>
                            <div>
                                <div class="stat-breakdown-value">14</div>
                                <div class="stat-breakdown-label">Present</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-times-circle stat-breakdown-icon" style="color: #dc3545;"></i>
                            <div>
                                <div class="stat-breakdown-value">3</div>
                                <div class="stat-breakdown-label">Absent</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-clock stat-breakdown-icon" style="color: #ffc107;"></i>
                            <div>
                                <div class="stat-breakdown-value">1</div>
                                <div class="stat-breakdown-label">Late</div>
                            </div>
                        </div>
                        <div class="stat-breakdown-item">
                            <i class="fas fa-exclamation-circle stat-breakdown-icon" style="color: #17a2b8;"></i>
                            <div>
                                <div class="stat-breakdown-value">1</div>
                                <div class="stat-breakdown-label">Excused</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stat-actions-enhanced">
                    <h4 class="stat-section-title">Recent Attendance</h4>
                    <div class="stat-recent-list">
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: #C62828;"></i>
                                Oct 13, 2023
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('PHYS-101', '2023-10-13')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                        <div class="stat-recent-item">
                            <div class="stat-recent-date">
                                <i class="fas fa-calendar-alt" style="color: #C62828;"></i>
                                Oct 10, 2023
                            </div>
                            <button class="stat-action-btn" onclick="openAttendanceModal('PHYS-101', '2023-10-10')">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
                    <tr>
                        <td>2023-10-15</td>
                        <td>MATH-101</td>
                        <td>Mathematics</td>
                        <td><span class="badge badge-success">Present</span></td>
                        <td>On time</td>
                    </tr>
                    <tr>
                        <td>2023-10-14</td>
                        <td>CS-201</td>
                        <td>Computer Science</td>
                        <td><span class="badge badge-success">Present</span></td>
                        <td>On time</td>
                    </tr>
                    <tr>
                        <td>2023-10-13</td>
                        <td>PHYS-101</td>
                        <td>Physics</td>
                        <td><span class="badge badge-warning">Late</span></td>
                        <td>5 minutes late</td>
                    </tr>
                    <tr>
                        <td>2023-10-12</td>
                        <td>MATH-101</td>
                        <td>Mathematics</td>
                        <td><span class="badge badge-success">Present</span></td>
                        <td>On time</td>
                    </tr>
                    <tr>
                        <td>2023-10-11</td>
                        <td>CS-201</td>
                        <td>Computer Science</td>
                        <td><span class="badge badge-danger">Absent</span></td>
                        <td>Sick leave</td>
                    </tr>
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
                    <tr>
                        <td>MATH-101</td>
                        <td>Mathematics</td>
                        <td>Prof. John Smith</td>
                        <td>MWF 9:00-10:30 AM</td>
                        <td>Room 101</td>
                    </tr>
                    <tr>
                        <td>CS-201</td>
                        <td>Computer Science</td>
                        <td>Prof. Jane Doe</td>
                        <td>TTh 2:00-3:30 PM</td>
                        <td>Room 205</td>
                    </tr>
                    <tr>
                        <td>PHYS-101</td>
                        <td>Physics</td>
                        <td>Prof. Bob Johnson</td>
                        <td>MWF 11:00-12:30 PM</td>
                        <td>Room 150</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Attendance Modal -->
        <div id
