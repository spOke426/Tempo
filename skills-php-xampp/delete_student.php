<?php
require_once 'functions.php';
require_login();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    go('dashboard.php');
}
check_csrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    flash('Invalid student ID.', 'error');
    go('dashboard.php');
}

$stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
$stmt->execute([$id]);
flash($stmt->rowCount() ? 'Student deleted.' : 'Student not found.');
go('dashboard.php');
