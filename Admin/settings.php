<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../php/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'update_profile') {
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];
            $mobile = $_POST['mobile'];

            try {
                if ($role == 'professor') {
                    $stmt = $pdo->prepare("UPDATE professors SET first_name = ?, last_name = ?, email = ?, mobile = ?, updated_at = NOW() WHERE professor_id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $mobile, $user_id]);

                    // Update session data
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['email'] = $email;
                    $_SESSION['mobile'] = $mobile;
                } elseif ($role == 'student') {
                    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, mobile = ?, updated_at = NOW() WHERE student_id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $mobile, $user_id]);

                    // Update session data
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['email'] = $email;
                    $_SESSION['mobile'] = $mobile;
                }

                $success = "Profile updated successfully!";
            } catch (PDOException $e) {
                $error = "Error updating profile: " . $e->getMessage();
            }
        } elseif ($action == 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $error = "New passwords do not match!";
            } else {
                try {
                    // Verify current password
                    if ($role == 'professor') {
                        $stmt = $pdo->prepare("SELECT password FROM professors WHERE professor_id = ?");
                    } elseif ($role == 'student') {
                        $stmt = $pdo->prepare("SELECT password FROM students WHERE student_id = ?");
                    }
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();

                    if (password_verify($current_password, $user['password'])) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                        if ($role == 'professor') {
                            $stmt = $pdo->prepare("UPDATE professors SET password = ?, updated_at = NOW() WHERE professor_id = ?");
                        } elseif ($role == 'student') {
                            $stmt = $pdo->prepare("UPDATE students SET password = ?, updated_at = NOW() WHERE student_id = ?");
                        }
                        $stmt->execute([$hashed_password, $user_id]);

                        $success = "Password changed successfully!";
                    } else {
                        $error = "Current password is incorrect!";
                    }
                } catch (PDOException $e) {
                    $error = "Error changing password: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch current user data
if ($role == 'professor') {
    $stmt = $pdo->prepare("SELECT * FROM professors WHERE professor_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($role == 'student') {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Global Reciprocal College</title>
    <link rel="stylesheet" href="../css/styles_fixed.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

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

        .current-info-enhanced {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item-enhanced {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label-enhanced {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value-enhanced {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-section-enhanced {
            margin-top: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group-enhanced {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .form-label-enhanced {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input-enhanced {
            padding: 1rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-input-enhanced:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-input-enhanced::placeholder {
            color: var(--gray);
        }

        .stat-actions-enhanced {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 2rem;
            margin-top: 2rem;
        }

        .stat-primary-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            justify-content: center;
        }

        .stat-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

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

        .alert-enhanced {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success-enhanced {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error-enhanced {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-icon {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .stat-card-enhanced {
                padding: 1.5rem;
            }

            .dashboard-container {
                padding: 1rem;
            }
        }

        /* Password Section Specific */
        .password-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .password-form-grid .form-grid {
            grid-template-columns: 1fr 1fr;
        }

        @media (max-width: 768px) {
            .password-form-grid .form-grid {
                grid-template-columns: 1fr;
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
            <span>Welcome, <?php echo $_SESSION['first_name']; ?></span>
            <div class="user-dropdown">
                <button class="dropdown-toggle">⚙️</button>
                <div class="dropdown-menu">
                    <a href="settings.php" class="dropdown-item">Settings</a>
                    <a href="../php/logout.php" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <?php if ($role == 'professor'): ?>
                <li class="sidebar-item">
                    <a href="../Professor/professor_dashboard.php" class="sidebar-link">Dashboard</a>
                </li>
                <li class="sidebar-item">
                    <a href="../Professor/manage_subjects.php" class="sidebar-link">Manage Subjects</a>
                </li>
                <li class="sidebar-item">
                    <a href="../Professor/professor_manage_schedule.php" class="sidebar-link">Manage Class</a>
                </li>
                <li class="sidebar-item">
                    <a href="settings.php" class="sidebar-link active">Settings</a>
                </li>
            <?php elseif ($role == 'student'): ?>
                <li class="sidebar-item">
                    <a href="../Student/student_dashboard.php" class="sidebar-link">Dashboard</a>
                </li>
                <li class="sidebar-item">
                    <a href="../Student/student_manage_schedule.php" class="sidebar-link">My Subjects</a>
                </li>
                <li class="sidebar-item">
                    <a href="settings.php" class="sidebar-link active">Settings</a>
                </li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-header-enhanced">
            <h2 class="table-title-enhanced"><i class="fas fa-cog" style="margin-right: 10px;"></i>Account Settings</h2>
        </div>

        <div class="dashboard-container">
            <?php if (isset($success)): ?>
                <div class="alert-enhanced alert-success-enhanced">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert-enhanced alert-error-enhanced">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="settings-grid">
                <!-- Profile Information Section -->
                <div class="stat-card-enhanced">
                    <div class="stat-header-enhanced">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-info-enhanced">
                            <h3 class="stat-title-enhanced">Profile Information</h3>
                            <span class="stat-subtitle-enhanced">Update your personal details</span>
                        </div>
                    </div>

                    <div class="current-info-enhanced">
                        <div class="info-grid">
                            <div class="info-item-enhanced">
                                <span class="info-label-enhanced">User ID</span>
                                <span class="info-value-enhanced"><?php echo $user_id; ?></span>
                            </div>
                            <?php if ($role == 'professor' && isset($user['employee_id'])): ?>
                                <div class="info-item-enhanced">
                                    <span class="info-label-enhanced">Employee ID</span>
                                    <span class="info-value-enhanced"><?php echo $user['employee_id']; ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($role == 'professor' && isset($user['department'])): ?>
                                <div class="info-item-enhanced">
                                    <span class="info-label-enhanced">Department</span>
                                    <span class="info-value-enhanced"><?php echo $user['department']; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form action="" method="POST" class="form-section-enhanced">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-grid">
                            <div class="form-group-enhanced">
                                <label class="form-label-enhanced">First Name</label>
                                <input type="text" name="first_name" class="form-input-enhanced" value="<?php echo htmlspecialchars($user['first_name']); ?>" required placeholder="Enter your first name">
                            </div>
                            <div class="form-group-enhanced">
                                <label class="form-label-enhanced">Last Name</label>
                                <input type="text" name="last_name" class="form-input-enhanced" value="<?php echo htmlspecialchars($user['last_name']); ?>" required placeholder="Enter your last name">
                            </div>
                            <div class="form-group-enhanced">
                                <label class="form-label-enhanced">Email</label>
                                <input type="email" name="email" class="form-input-enhanced" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Enter your email">
                            </div>
                            <div class="form-group-enhanced">
                                <label class="form-label-enhanced">Mobile</label>
                                <input type="text" name="mobile" class="form-input-enhanced" value="<?php echo htmlspecialchars($user['mobile']); ?>" required placeholder="Enter your mobile number">
                            </div>
                        </div>

                        <div class="stat-actions-enhanced">
                            <button type="submit" class="stat-primary-btn">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="stat-card-enhanced">
                    <div class="stat-header-enhanced">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="stat-info-enhanced">
                            <h3 class="stat-title-enhanced">Change Password</h3>
                            <span class="stat-subtitle-enhanced">Secure your account</span>
                        </div>
                    </div>

                    <form action="" method="POST" class="form-section-enhanced">
                        <input type="hidden" name="action" value="change_password">

                        <div class="password-form-grid">
                            <div class="form-group-enhanced">
                                <label class="form-label-enhanced">Current Password</label>
                                <input type="password" name="current_password" class="form-input-enhanced" required placeholder="Enter current password">
                            </div>

                            <div class="form-grid">
                                <div class="form-group-enhanced">
                                    <label class="form-label-enhanced">New Password</label>
                                    <input type="password" name="new_password" class="form-input-enhanced" required placeholder="Enter new password">
                                </div>
                                <div class="form-group-enhanced">
                                    <label class="form-label-enhanced">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-input-enhanced" required placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <div class="stat-actions-enhanced">
                            <button type="submit" class="stat-primary-btn">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
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

        // Dropdown functionality
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        if (dropdownToggle && dropdownMenu) {
            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.user-dropdown')) {
                    dropdownMenu.classList.remove('show');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    </script>
</body>
</html>
