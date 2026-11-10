<?php
// Shared database connection and small helper functions.
// For a default LOCAL XAMPP installation, MySQL uses root with no password.
$host = 'localhost';
$dbname = 'attendance_db';
$username = 'root';
$password = '';

date_default_timezone_set('Asia/Manila');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    exit('Database connection failed. Start MySQL in XAMPP, import attendance.sql in phpMyAdmin, and check the settings in functions.php.');
}

// Always escape database text before displaying it in HTML.
function h($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function go($url) {
    header('Location: ' . $url);
    exit;
}

// Convert the HTML datetime-local value into MySQL DATETIME format.
function mysqlDateTime($value) {
    $date = DateTime::createFromFormat('!Y-m-d\TH:i', trim((string) $value));
    if (!$date || $date->format('Y-m-d\TH:i') !== $value) {
        return null;
    }
    return $date->format('Y-m-d H:i:s');
}

function displayDateTime($value) {
    return $value ? date('m/d/Y h:i A', strtotime($value)) : '—';
}

function hoursWorked($timeIn, $timeOut) {
    if (!$timeIn || !$timeOut) return 0;
    return max(0, (strtotime($timeOut) - strtotime($timeIn)) / 3600);
}

function messageBox() {
    $messages = [
        'added' => 'Record added successfully.',
        'updated' => 'Record updated successfully.',
        'deleted' => 'Record deleted successfully.',
        'cancelled' => 'Attendance record cancelled.',
        'timeout' => 'Time out recorded successfully.'
    ];
    $key = $_GET['msg'] ?? '';
    if (isset($messages[$key])) {
        echo '<div class="notice">' . h($messages[$key]) . '</div>';
    }
}

function startPage($title) {
    $current = basename($_SERVER['SCRIPT_NAME']);
    $links = [
        'index.php' => 'Menu',
        'departments.php' => 'Departments',
        'employees.php' => 'Employees',
        'attendance.php' => 'Record Attendance',
        'monitor_employee.php' => 'By Employee',
        'monitor_dates.php' => 'By Date Range'
    ];
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> - Employee Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="wrapper">
            <h1>Employee Attendance Monitoring System</h1>
        </div>
    </header>
    <nav class="nav">
        <div class="wrapper nav-links">
            <?php foreach ($links as $url => $name): ?>
                <a class="<?= $current === $url ? 'active' : '' ?>" href="<?= h($url) ?>"><?= h($name) ?></a>
            <?php endforeach; ?>
        </div>
    </nav>
    <main class="wrapper main">
        <h2><?= h($title) ?></h2>
        <?php messageBox(); ?>
    <?php
}

function endPage() {
    ?>
    </main>
</body>
</html>
    <?php
}
