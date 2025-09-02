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
            max-width: 1100px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        .header {
            width: 100%;
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

        .steps-section {
            flex: 1 1 300px;
            background: var(--light-gray);
            padding: 40px 30px;
            border-right: 2px solid var(--light);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .steps-section h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 1.8rem;
        }

        .steps-list {
            list-style: disc inside;
            color: var(--dark);
            font-size: 1rem;
            line-height: 1.6;
        }

        .form-section {
            flex: 2 1 600px;
            padding: 40px 50px;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .progress-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            transform: translateY(-50%);
            z-index: 1;
        }

        .progress-step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #999;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            transform: scale(1.1);
        }

        .progress-step.completed {
            background: var(--secondary);
            border-color: var(--secondary);
            color: #fff;
        }

        .form-step {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .form-step.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1);
        }

        .form-input.error, .form-select.error {
            border-color: #fc544b;
            background-color: #fff5f5;
        }

        .form-input::placeholder {
            color: var(--gray);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(198, 40, 40, 0.3);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-submit {
            background: var(--secondary);
            color: #fff;
        }

        .btn-submit:hover {
            background: #13855c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
        }

        .field-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .error-message {
            color: #fc544b;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .required-field::after {
            content: '*';
            color: #fc544b;
            margin-left: 4px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        @media (max-width: 900px) {
            .register-container {
                flex-direction: column;
                max-width: 500px;
            }

            .steps-section {
                border-right: none;
                border-bottom: 2px solid var(--light);
                padding: 30px 25px;
            }

            .form-section {
                padding: 30px 25px;
            }

            .btn-group {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .header {
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

        <div class="steps-section" aria-label="Registration Steps">
            <h2>Registration Steps</h2>
            <ul class="steps-list">
                <li>Select your account type</li>
                <li>Provide personal information</li>
                <li>Enter additional details</li>
                <li>Submit your registration</li>
                <li>Verify your email and login</li>
            </ul>
        </div>

        <div class="form-section">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" role="alert">
                    Registration successful! You can now <a href="index.php">login here</a>.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert">
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

            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-step active" data-step="1">1</div>
                <div class="progress-step" data-step="2">2</div>
                <div class="progress-step" data-step="3">3</div>
            </div>

            <form id="registrationForm" action="php/register.php" method="POST">
                <!-- Step 1: Account Type -->
                <div class="form-step active" data-step="1">
                    <div class="form-group">
                        <label for="role" class="form-label required-field">Register as:</label>
                        <select name="role" id="role" required class="form-select">
                            <option value="">Select account type</option>
                            <option value="professor">Professor</option>
                            <option value="student">Student</option>
                        </select>
                        <div class="field-hint">Choose whether you're registering as a professor or student</div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" onclick="validateStep1()">Next</button>
                    </div>
                </div>

                <!-- Step 2: Personal Information -->
                <div class="form-step" data-step="2">
                    <div class="form-group">
                        <label for="first_name" class="form-label required-field">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-input" placeholder="Enter your first name" required autocomplete="given-name">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label required-field">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-input" placeholder="Enter your last name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required-field">Email Address</label>
                        <input type="email" name="email" id="email" class="form-input" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label required-field">Password</label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="Create a strong password" required>
                        <div class="field-hint">Use at least 8 characters with letters and numbers</div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Back</button>
                        <button type="button" class="btn btn-primary" onclick="validateStep2()">Next</button>
                    </div>
                </div>

                <!-- Step 3: Additional Information -->
                <div class="form-step" data-step="3">
                    <!-- Student Specific Fields -->
                    <div id="studentFields" style="display: none;">
                        <div class="form-group">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input type="text" name="student_id" id="student_id" class="form-input" placeholder="Enter student ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="course" class="form-label">Course</label>
                            <input type="text" name="course" id="course" class="form-input" placeholder="e.g., Computer Science">
                        </div>
                        
                        <div class="form-group">
                            <label for="year_level" class="form-label">Year Level</label>
                            <input type="text" name="year_level" id="year_level" class="form-input" placeholder="e.g., 1st Year">
                        </div>
                        
                        <div class="form-group">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" name="section" id="section" class="form-input" placeholder="e.g., A">
                        </div>
                        
                        <div class="form-group">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="tel" name="mobile" id="mobile" class="form-input" placeholder="Enter mobile number">
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" id="address" class="form-input" placeholder="Enter complete address">
                        </div>
                    </div>

                    <!-- Professor Specific Fields -->
                    <div id="professorFields" style="display: none;">
                        <div class="form-group">
                            <label for="professor_id" class="form-label">Professor ID</label>
                            <input type="text" name="professor_id" id="professor_id" class="form-input" placeholder="Enter professor ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="employee_id" class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" class="form-input" placeholder="Enter employee ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" name="department" id="department" class="form-input" placeholder="e.g., Computer Science Department">
                        </div>
                        
                        <div class="form-group">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="tel" name="mobile" id="mobile" class="form-input" placeholder="Enter mobile number">
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Back</button>
                        <button type="submit" class="btn btn-submit">Create Account</button>
                    </div>
                </div>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="index.php">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function updateProgressIndicator() {
            document.querySelectorAll('.progress-step').forEach((step, index) => {
                const stepNumber = parseInt(step.getAttribute('data-step'));
                step.classList.remove('active', 'completed');
                
                if (stepNumber < currentStep) {
                    step.classList.add('completed');
                } else if (stepNumber === currentStep) {
                    step.classList.add('active');
                }
            });
        }

        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(section => {
                section.classList.remove('active');
            });
            
            const targetSection = document.querySelector(`.form-step[data-step="${step}"]`);
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            currentStep = step;
            updateProgressIndicator();
        }

        function validateStep1() {
            const role = document.getElementById('role');
            if (!role.value) {
                role.classList.add('error');
                alert('Please select a user type');
                return;
            }
            role.classList.remove('error');
            nextStep(2);
        }

        function validateStep2() {
            const fields = ['first_name', 'last_name', 'email', 'password'];
            let isValid = true;
            
            fields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });
            
            // Validate email format
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.value && !emailRegex.test(email.value)) {
                email.classList.add('error');
                alert('Please enter a valid email address');
                isValid = false;
            }
            
            // Validate password length
            const password = document.getElementById('password');
            if (password.value && password.value.length < 8) {
                password.classList.add('error');
                alert('Password must be at least 8 characters long');
                isValid = false;
            }
            
            if (isValid) {
                nextStep(3);
            }
        }

        function nextStep(next) {
            if (next <= totalSteps) {
                showStep(next);
                
                // Show/hide fields based on user type when moving to step 3
                if (next === 3) {
                    const role = document.getElementById('role').value;
                    if (role === 'student') {
                        document.getElementById('studentFields').style.display = 'block';
                        document.getElementById('professorFields').style.display = 'none';
                    } else if (role === 'professor') {
                        document.getElementById('studentFields').style.display = 'none';
                        document.getElementById('professorFields').style.display = 'block';
                    }
                }
            }
        }

        function prevStep(prev) {
            if (prev >= 1) {
                showStep(prev);
            }
        }

        // Update fields when user type changes
        document.getElementById('role').addEventListener('change', function() {
            if (currentStep >= 3) {
                if (this.value === 'student') {
                    document.getElementById('studentFields').style.display = 'block';
                    document.getElementById('professorFields').style.display = 'none';
                } else if (this.value === 'professor') {
                    document.getElementById('studentFields').style.display = 'none';
                    document.getElementById('professorFields').style.display = 'block';
                }
            }
        });

        // Initialize progress indicator
        updateProgressIndicator();
    </script>
</body>
</html>
