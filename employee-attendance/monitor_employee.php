<?php
require 'functions.php';
$employees = $pdo->query('SELECT empID, empFName, empLName FROM employees ORDER BY empLName, empFName')->fetchAll();
$selectedID = (int) ($_GET['empID'] ?? 0);
$employee = null;
$records = [];
$totalHours = 0;

if ($selectedID > 0) {
    $stmt = $pdo->prepare('SELECT e.*, d.depName FROM employees e JOIN departments d ON e.depCode = d.depCode WHERE e.empID = ?');
    $stmt->execute([$selectedID]);
    $employee = $stmt->fetch();

    if ($employee) {
        $stmt = $pdo->prepare('SELECT * FROM attendance WHERE empID = ? AND attStat = ? ORDER BY attTimeIn');
        $stmt->execute([$selectedID, 'Not Cancelled']);
        $records = $stmt->fetchAll();
    }
}
startPage('Attendance Monitoring (By Employee)');
?>
<section class="panel">
    <form method="get" class="filter-form">
        <label>Input Employee
            <input
                type="text"
                name="empID"
                placeholder="Enter Employee ID"
                value="<?= $selectedID > 0 ? h($selectedID) : '' ?>"
                required>
        </label>
        <button type="submit">View Attendance</button>
    </form>
</section>
<?php if ($employee): ?>
<section class="panel">
    <div class="info-row">
        <p><strong>Name:</strong> <?= h($employee['empLName'] . ', ' . $employee['empFName']) ?></p>
        <p><strong>Department:</strong> <?= h($employee['depName']) ?></p>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Record #</th><th>Emp. ID</th><th>Date/Time In</th><th>Date/Time Out</th><th>Total Hours</th></tr></thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <?php $hours = hoursWorked($r['attTimeIn'], $r['attTimeOut']); $totalHours += $hours; ?>
                <tr>
                    <td><?= h($r['attRN']) ?></td><td><?= h($r['empID']) ?></td>
                    <td><?= h(displayDateTime($r['attTimeIn'])) ?></td>
                    <td><?= h(displayDateTime($r['attTimeOut'])) ?></td>
                    <td><?= $r['attTimeOut'] ? number_format($hours, 2) : 'Pending' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$records): ?><tr><td colspan="5">No active attendance records for this employee.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="totals">
        <div><span>Rate Per Hour</span><strong>₱<?= number_format((float) $employee['empRPH'], 2) ?></strong></div>
        <div><span>Total Hours Worked</span><strong><?= number_format($totalHours, 2) ?></strong></div>
        <div><span>Estimated Salary</span><strong>₱<?= number_format($totalHours * (float) $employee['empRPH'], 2) ?></strong></div>
    </div>
    <p class="hint">Date Generated: <?= h(date('m/d/Y')) ?></p>
</section>
<?php elseif ($selectedID > 0): ?>
<p class="error">Employee not found.</p>
<?php else: ?>
<?php endif; ?>
<?php endPage(); ?>
