<?php
require_once '../db.php';

try {
    $stmt = $pdo->prepare("SELECT admin_id, email, password FROM administrators WHERE email = ?");
    $stmt->execute(['admin@grc.edu']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Admin ID: " . $admin['admin_id'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Password Hash: " . $admin['password'] . "<br>";
    } else {
        echo "Admin user not found.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
