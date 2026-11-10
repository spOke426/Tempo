<?php
// 1. DATABASE CONNECTION
// XAMPP defaults: username = root, password = empty.
// If your MySQL password is different, change it here.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli('localhost', 'root', '');
    $conn->set_charset('utf8mb4');

    // Creates the practice database/table automatically the first time.
    // In a skills test, use the database/table names from the printed paper.
    $conn->query("CREATE DATABASE IF NOT EXISTS skills_practice
                  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db('skills_practice');

    $conn->query("CREATE TABLE IF NOT EXISTS products (
        productID INT AUTO_INCREMENT PRIMARY KEY,
        productName VARCHAR(100) NOT NULL,
        category VARCHAR(30) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        quantity INT NOT NULL DEFAULT 0
    )");
} catch (mysqli_sql_exception $error) {
    die('Database connection error. Check XAMPP MySQL and db.php settings. Details: '
        . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Display user/database values safely in HTML.
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
