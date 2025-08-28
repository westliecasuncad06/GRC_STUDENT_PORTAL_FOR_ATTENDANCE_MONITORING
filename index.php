<?php
session_start(); // Start the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Reciprocal College - Student Portal</title>
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

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .school-logo {
            font-size: 3rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .school-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .tagline {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.6;
        }

        .features {
            margin-top: 30px;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .feature-icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .right-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1);
        }

        .form-group input::placeholder {
            color: var(--gray);
        }

        .login-btn {
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

        .login-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(198, 40, 40, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .error-message {
            background: #fee;
            color: #c62828;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffcdd2;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                height: auto;
                max-width: 400px;
            }
            
            .left-section, .right-section {
                padding: 30px;
            }
            
            .left-section {
                order: 2;
            }
            
            .right-section {
                order: 1;
            }
            
            .school-name {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: 15px;
            }
            
            .left-section, .right-section {
                padding: 25px;
            }
            
            .school-name {
                font-size: 1.8rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-section">
            <div class="school-logo">🎓</div>
            <h1 class="school-name">Global Reciprocal College</h1>
            <p class="tagline">Excellence in Education Through Innovation and Collaboration</p>
            
            <div class="features">
                <div class="feature">
                    <span class="feature-icon">📚</span>
                    <span>Comprehensive Course Management</span>
                </div>
                <div class="feature">
                    <span class="feature-icon">👥</span>
                    <span>Role-Based Access Control</span>
                </div>
                <div class="feature">
                    <span class="feature-icon">📊</span>
                    <span>Real-time Attendance Tracking</span>
                </div>
                <div class="feature">
                    <span class="feature-icon">🔒</span>
                    <span>Secure Student Portal</span>
                </div>
            </div>
        </div>
        
        <div class="right-section">
            <div class="login-header">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Sign in to your GRC Student Portal account</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php 
                    $error = $_GET['error'];
                    if ($error === 'invalid_credentials') {
                        echo 'Invalid email or password. Please try again.';
                    } elseif ($error === 'database_error') {
                        echo 'System error. Please try again later.';
                    } else {
                        echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form class="login-form" id="loginForm" action="php/login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
                
                <div class="forgot-password"> 
                    <a href="#">Forgot your password?</a>
                </div>
                
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Create account</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
