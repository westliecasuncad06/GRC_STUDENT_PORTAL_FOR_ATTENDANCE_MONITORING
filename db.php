<?php
// Database connection settings
$host = 'localhost';
$db = 'GRC_STUDENT_PORTAL_FOR_ATTENDANCE_MONITORING';
$user = 'root'; // Change as necessary
$pass = ''; // Change as necessary

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
