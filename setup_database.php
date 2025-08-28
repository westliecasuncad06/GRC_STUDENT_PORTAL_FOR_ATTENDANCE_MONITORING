<?php
require_once 'db.php';

try {
    // Create tables
    $queries = [
        // Students table
        "CREATE TABLE IF NOT EXISTS students (
            student_id VARCHAR(20) PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            middle_name VARCHAR(50),
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            mobile VARCHAR(15) NOT NULL,
            address TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )",

        // Professors table
        "CREATE TABLE IF NOT EXISTS professors (
            professor_id VARCHAR(20) PRIMARY KEY,
            employee_id VARCHAR(20) UNIQUE NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            department VARCHAR(100) NOT NULL,
            mobile VARCHAR(15) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )",

        // Administrators table
        "CREATE TABLE IF NOT EXISTS administrators (
            admin_id VARCHAR(20) PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )",

        // Subjects table
        "CREATE TABLE IF NOT EXISTS subjects (
            subject_id VARCHAR(20) PRIMARY KEY,
            subject_name VARCHAR(100) NOT NULL,
            subject_code VARCHAR(20) UNIQUE NOT NULL,
            description TEXT,
            credits INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )",

        // Classes table
        "CREATE TABLE IF NOT EXISTS classes (
            class_id VARCHAR(20) PRIMARY KEY,
            class_name VARCHAR(100) NOT NULL,
            class_code VARCHAR(20) UNIQUE NOT NULL,
            subject_id VARCHAR(20),
            professor_id VARCHAR(20),
            schedule VARCHAR(100),
            room VARCHAR(50),
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
            FOREIGN KEY (professor_id) REFERENCES professors(professor_id)
        )",

        // Attendance table
        "CREATE TABLE IF NOT EXISTS attendance (
            attendance_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(20),
            class_id VARCHAR(20),
            date DATE NOT NULL,
            status ENUM('Present', 'Absent', 'Late') NOT NULL,
            remarks TEXT,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (student_id) REFERENCES students(student_id),
            FOREIGN KEY (class_id) REFERENCES classes(class_id)
        )",

        // Student-Class enrollment table
        "CREATE TABLE IF NOT EXISTS student_classes (
            enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(20),
            class_id VARCHAR(20),
            enrolled_at DATETIME NOT NULL,
            FOREIGN KEY (student_id) REFERENCES students(student_id),
            FOREIGN KEY (class_id) REFERENCES classes(class_id),
            UNIQUE KEY unique_enrollment (student_id, class_id)
        )",

        // Professor-Subject assignment table
        "CREATE TABLE IF NOT EXISTS professor_subjects (
            assignment_id INT AUTO_INCREMENT PRIMARY KEY,
            professor_id VARCHAR(20),
            subject_id VARCHAR(20),
            assigned_at DATETIME NOT NULL,
            FOREIGN KEY (professor_id) REFERENCES professors(professor_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
            UNIQUE KEY unique_assignment (professor_id, subject_id)
        )"
    ];

    foreach ($queries as $query) {
        $pdo->exec($query);
    }

    // Create default admin account
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $adminQuery = "INSERT IGNORE INTO administrators (admin_id, first_name, last_name, email, password, created_at, updated_at) 
                   VALUES ('ADM001', 'System', 'Administrator', 'admin@grc.edu', ?, NOW(), NOW())";
    $stmt = $pdo->prepare($adminQuery);
    $stmt->execute([$adminPassword]);

    echo "Database setup completed successfully!<br>";
    echo "Default admin account created:<br>";
    echo "Email: admin@grc.edu<br>";
    echo "Password: admin123<br>";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage();
}
?>
