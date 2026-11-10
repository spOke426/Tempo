<?php
require_once 'functions.php';
$pageTitle = 'Main Menu';
$eventCount = $pdo->query('SELECT COUNT(*) FROM Events')->fetchColumn();
$participantCount = $pdo->query('SELECT COUNT(*) FROM Participants')->fetchColumn();
$regCount = $pdo->query('SELECT COUNT(*) FROM Registration')->fetchColumn();
require 'header.php';
?>
<div class="card">
    <p>Welcome, <strong>Local Admin</strong>! Choose your transaction:</p>
    <div class="menu-grid">
        <a class="menu-card" href="events.php"><strong>Events Management</strong><span>Add, search, edit, delete and view events.</span></a>
        <a class="menu-card" href="participants.php"><strong>Participants Management</strong><span>Add, search, edit, delete and view participants.</span></a>
        <a class="menu-card" href="registration.php"><strong>Participant Registration</strong><span>Register a participant and calculate the fee paid.</span></a>
        <a class="menu-card" href="monitoring.php"><strong>Registration Monitoring</strong><span>Filter by event name and view totals.</span></a>
    </div>
</div>
<div class="stat-grid">
    <div class="stat"><div class="value"><?= (int) $eventCount ?></div><div class="label">Events</div></div>
    <div class="stat"><div class="value"><?= (int) $participantCount ?></div><div class="label">Participants</div></div>
    <div class="stat"><div class="value"><?= (int) $regCount ?></div><div class="label">Registrations</div></div>
</div>
