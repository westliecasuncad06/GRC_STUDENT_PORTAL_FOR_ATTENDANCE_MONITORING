<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Global Reciprocal College</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        :root {
            --primary: #C62828;
            --primary-dark: #B71C1C;
            --primary-light: #EF5350;
            --secondary: #2E7D32;
            --accent: #1565C0;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .school-logo {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .school-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .tagline {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .role-tabs {
            display: flex;
            background: var(--light);
            border-bottom: 2px solid var(--light-gray);
        }

        .tab {
            flex: 1;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            color: var(--gray);
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .tab:hover::before {
            left: 100%;
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: white;
        }

        .form-container {
            padding: 40px;
        }

        .form-section {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section.active {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light);
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1);
        }

        .form-group input::placeholder {
            color: var(--gray);
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(198, 40, 40, 0.3);
        }

        .register-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(198, 40, 40, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c8e6c9;
        }

        .error-message {
            background: #fee;
            color: #c62828;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffcdd2;
        }

        @media (max-width: 768px) {
            .register-container {
                max-width: 500px;
            }
            
            .header {
                padding: 30px;
            }
            
            .form-container {
                padding: 30px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .role-tabs {
                flex-direction: column;
            }
            
            .tab {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .register-container {
                border-radius: 15px;
            }
            
            .header {
                padding: 25px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .school-name {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="header">
            <div class="school-logo">🎓</div>
            <h1 class="school-name">Global Reciprocal College</h1>
            <p class="tagline">Join our academic community</p>
        </div>

        <div class="role-tabs">
            <div class="tab active" onclick="showForm('student')">
                📚 Student Registration
            </div>
            <div class="tab" onclick="showForm('professor')">
                👨‍🏫 Professor Registration
            </div>
        </div>

        <div class="form-container">
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    Registration successful! You can now <a href="index.php">login here</a>.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php 
                    $error = $_GET['error'];
                    if ($error === 'email_exists') {
                        echo 'Email already exists. Please use a different email.';
                    } elseif ($error === 'database_error') {
                        echo 'System error. Please try again later.';
                    } else {
                        echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Student Registration Form -->
            <form id="studentForm" class="form-section active" action="php/register.php" method="POST">
                <input type="hidden" name="role" value="student">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="student_id">Student ID *</label>
                        <input type="text" id="student_id" name="student_id" required placeholder="Enter student ID">
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" placeholder="Enter middle name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="Enter email address">
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required placeholder="Create a password">
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile Number *</label>
                        <input type="tel" id="mobile" name="mobile" required placeholder="Enter mobile number">
                    </div>
                    <div class="form-group">
                        <label for="address">Address *</label>
                        <input type="text" id="address" name="address" required placeholder="Enter complete address">
                    </div>
                </div>
                <button type="submit" class="register-btn">Create Student Account</button>
            </form>

            <!-- Professor Registration Form -->
            <form id="professorForm" class="form-section" action="php/register.php" method="POST">
                <input type="hidden" name="role" value="professor">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="professor_id">Professor ID *</label>
                        <input type="text" id="professor_id" name="professor_id" required placeholder="Enter professor ID">
                    </div>
                    <div class="form-group">
                        <label for="employee_id">Employee ID *</label>
                        <input type="text" id="employee_id" name="employee_id" required placeholder="Enter employee ID">
                    </div>
                    <div class="form-group">
                        <label for="prof_first_name">First Name *</label>
                        <input type="text" id="prof_first_name" name="first_name" required placeholder="Enter first name">
                    </div>
                    <div class="form-group">
                        <label for="prof_last_name">Last Name *</label>
                        <input type="text" id="prof_last_name" name="last_name" required placeholder="Enter last name">
                    </div>
                    <div class="form-group">
                        <label for="prof_email">Email Address *</label>
                        <input type="email" id="prof_email" name="email" required placeholder="Enter email address">
                    </div>
                    <div class="form-group">
                        <label for="prof_password">Password *</label>
                        <input type="password" id="prof_password" name="password" required placeholder="Create a password">
                    </div>
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <input type="text" id="department" name="department" required placeholder="Enter department">
                    </div>
                    <div class="form-group">
                        <label for="prof_mobile">Mobile Number *</label>
                        <input type="tel" id="prof_mobile" name="mobile" required placeholder="Enter mobile number">
                    </div>
                </div>
                <button type="submit" class="register-btn">Create Professor Account</button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="index.php">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script>
        function showForm(role) {
            // Hide all forms
            document.querySelectorAll('.form-section').forEach(form => {
                form.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected form and activate tab
            document.getElementById(role + 'Form').classList.add('active');
            document.querySelectorAll('.tab')[role === 'student' ? 0 : 1].classList.add('active');
        }

        // Add form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const inputs = this.querySelectorAll('input[required]');
                    let valid = true;
                    
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            valid = false;
                            input.style.borderColor = '#c62828';
                        } else {
                            input.style.borderColor = '';
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
        });
    </script>
</body>
</html>
