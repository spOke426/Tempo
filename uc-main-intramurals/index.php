<?php
require_once __DIR__ . '/functions.php';
require_login();
$departmentCount = db()->query('SELECT COUNT(*) FROM Department')->fetchColumn();
$eventCount = db()->query('SELECT COUNT(*) FROM `Event`')->fetchColumn();
$athleteCount = db()->query('SELECT COUNT(*) FROM AthletesProfile')->fetchColumn();
$pageTitle = 'Main Menu';
require __DIR__ . '/header.php';
?>
<p>Choose your transaction:</p>
<div class="card-grid">
    <?php if (role_is('admin')): ?>
        <a class="card menu-card" href="departments.php"><h2>Department Management</h2><p>Add, edit, and delete departments.</p><span class="small"><?= h($departmentCount) ?> departments</span></a>
    <?php endif; ?>
    <a class="card menu-card" href="events.php"><h2>Event Management</h2><p>View intramurals events. Tournament managers can add or update events.</p><span class="small"><?= h($eventCount) ?> events</span></a>
    <a class="card menu-card" href="people.php"><h2>Coaches, Deans & Managers</h2><p>View staff profiles. Staff may update their own information.</p></a>
    <a class="card menu-card" href="athletes.php"><h2>Athletes Profile</h2><p>View athletes and their assigned events, coaches, and deans.</p><span class="small"><?= h($athleteCount) ?> athlete profiles</span></a>
</div>
<div class="card"><p><strong>Welcome, <?= h($_SESSION['userName']) ?>!</strong> You are signed in as <span class="pill"><?= h(str_replace('_', ' ', $_SESSION['role'])) ?></span>.</p>
<p class="small">This practice system follows the main database tables in the supplied school skills-test sheet.</p></div>