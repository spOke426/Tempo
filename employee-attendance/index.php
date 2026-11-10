<?php
require 'functions.php';
startPage('Main Menu');
?>
<div class="panel">
    <h3>Choose your transaction</h3>
    <div class="menu-grid">
        <a href="departments.php" class="menu-card">
            <strong>Departments Management</strong>
            <span>Add, edit, view, and delete departments.</span>
        </a>
        <a href="employees.php" class="menu-card">
            <strong>Employees Management</strong>
            <span>Manage employee information and hourly rate.</span>
        </a>
        <a href="attendance.php" class="menu-card">
            <strong>Attendance Recording</strong>
            <span>Record time in, time out, and cancellations.</span>
        </a>
        <a href="monitor_employee.php" class="menu-card">
            <strong>Attendance Monitoring (By Employee)</strong>
            <span>View total hours and estimated salary.</span>
        </a>
        <a href="monitor_dates.php" class="menu-card">
            <strong>Attendance Monitoring (Date Range)</strong>
            <span>View attendance records between two dates.</span>
        </a>
    </div>
</div>
<?php endPage(); ?>
