<?php
require_once 'functions.php';
require_login();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    go('dashboard.php');
}
check_csrf();

$id = trim((string) ($_POST['id'] ?? '')); // Empty when adding; populated when editing.
$idno = trim((string) ($_POST['idno'] ?? ''));
$lastname = trim((string) ($_POST['lastname'] ?? ''));
$firstname = trim((string) ($_POST['firstname'] ?? ''));
$course = trim((string) ($_POST['course'] ?? ''));
$level = trim((string) ($_POST['level'] ?? ''));

if ($idno === '' || !ctype_digit($idno) || strlen($idno) > 10 ||
    $lastname === '' || strlen($lastname) > 50 ||
    $firstname === '' || strlen($firstname) > 50 ||
    $course === '' || strlen($course) > 10 ||
    $level === '' || strlen($level) > 5) {
    flash('Enter a numeric ID number and complete all fields using the allowed lengths.', 'error');
    go('dashboard.php');
}

try {
    if ($id !== '') {
        if (!ctype_digit($id) || (int) $id < 1) {
            flash('Invalid student ID.', 'error');
            go('dashboard.php');
        }
        $stmt = $pdo->prepare('UPDATE students
            SET idno = ?, lastname = ?, firstname = ?, course = ?, level = ?
            WHERE id = ?');
        $stmt->execute([$idno, $lastname, $firstname, $course, $level, (int) $id]);
        flash('Student updated successfully.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO students (idno, lastname, firstname, course, level)
            VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$idno, $lastname, $firstname, $course, $level]);
        flash('New student added successfully.');
    }
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        flash('That ID Number already exists. Use a unique ID Number.', 'error');
    } else {
        error_log($error->getMessage());
        flash('Could not save student. Please try again.', 'error');
    }
}
go('dashboard.php');
