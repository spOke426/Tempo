<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'UC Main Intramurals';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | UC Main Intramurals</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div>
            <div class="school">University of Cebu – Main Campus</div>
            <div class="brand">Intramurals System</div>
        </div>
        <?php if (logged_in()): ?>
            <div class="logged-user"><?= h($_SESSION['userName']) ?> <span>(<?= h(str_replace('_', ' ', $_SESSION['role'])) ?>)</span></div>
        <?php endif; ?>
    </div>
</header>
<nav class="nav">
    <div class="container nav-inner">
        <?php if (logged_in()): ?>
            <a href="index.php">Menu</a>
            <?php if (role_is('admin')): ?><a href="departments.php">Departments</a><?php endif; ?>
            <a href="events.php">Events</a>
            <a href="people.php">Staff Profiles</a>
            <a href="athletes.php">Athletes</a>
            <a href="logout.php">Log Out</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container page">
    <h1><?= h($pageTitle) ?></h1>
    <?php show_flash(); ?>
