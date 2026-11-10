<?php
$page_title = $page_title ?? 'Student Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($page_title) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="<?= logged_in() ? 'dashboard.php' : 'index.php' ?>">SCHOOL v1.0</a>
            <?php if (logged_in()): ?>
                <div class="nav-actions">
                    <span class="email"><?= h($_SESSION['email'] ?? '') ?></span>
                    <form action="logout.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <button class="btn btn-outline" type="submit">Logout</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?php $notice = take_flash(); ?>
        <?php if ($notice): ?>
            <div class="notice <?= $notice['type'] === 'error' ? 'notice-error' : 'notice-success' ?>" role="status">
                <?= h($notice['text']) ?>
            </div>
        <?php endif; ?>
