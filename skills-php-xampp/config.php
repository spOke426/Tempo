<?php
// Change these values only if your XAMPP MySQL settings are different.
$host = 'localhost';
$dbname = 'skills_php';
$username = 'root';
$password = ''; // XAMPP default is an empty password.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $error) {
    http_response_code(500);
    // Keep connection details private; display instructions instead.
    exit('Cannot connect to MySQL. Start MySQL in XAMPP, import database.sql in phpMyAdmin, and check config.php.');
}
