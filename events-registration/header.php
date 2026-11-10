<?php
$pageTitle = $pageTitle ?? 'Events Registration System';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | Events Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container topbar">
        <div>
            <a class="brand" href="index.php">Events Registration System</a>
            <div class="subtitle">Local Admin · PHP + MySQL · XAMPP</div>
        </div>
        <span class="header-pill">UC Main · Skills Test</span>
    </div>
</header>
<nav class="navbar" aria-label="Main menu">
    <div class="container navlinks">
        <a class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Menu</a>
        <a class="<?= $currentPage === 'events.php' ? 'active' : '' ?>" href="events.php">Events Management</a>
        <a class="<?= $currentPage === 'participants.php' ? 'active' : '' ?>" href="participants.php">Participants Management</a>
        <a class="<?= $currentPage === 'registration.php' ? 'active' : '' ?>" href="registration.php">Participant Registration</a>
        <a class="<?= $currentPage === 'monitoring.php' ? 'active' : '' ?>" href="monitoring.php">Registration Monitoring</a>
    </div>
</nav>
<main class="container main-content">
    <div class="page-heading"><h1><?= h($pageTitle) ?></h1><a href="index.php" class="back-link">Back to Menu</a></div>
    <?php show_flash(); ?>
