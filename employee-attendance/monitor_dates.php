<?php
require 'functions.php';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$error = '';
$records = [];
$totalHours = 0;
$totalPay = 0;

// Check for real calendar dates, not just strings.
function isValidDate($value) {
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

if ($from !== '' || $to !== '') {
    if (!isValidDate($from) || !isValidDate($to) || $from > $to) {
        $error = 'Enter a valid From date and To date. From must not be after To.';
    } else {
        // An exclusive upper limit includes ALL times on the To date.
        $toExclusive = date('Y-m-d', strtotime($to . ' +1 day'));
        $stmt = $pdo->prepare('SELECT a.*, e.empFName, e.empLName, e.empRPH FROM attendance a JOIN employees e ON e.empID = a.empID WHERE a.attTimeIn >= ? AND a.attTimeIn < ? AND a.attStat = ? ORDER BY a.attTimeIn, a.attRN');
        $stmt->execute([$from . ' 00:00:00', $toExclusive . ' 00:00:00', 'Not Cancelled']);
        $records = $stmt->fetchAll();
    }
}
startPage('Attendance Monitoring (Date Range)');
if ($error) echo '<div class="error">' . h($error) . '</div>';
?>
<section class="panel">
    <form method="get" class="filter-form">
        <label>Date From
            <input type="date" name="from" value="<?= h($from) ?>" required>
        </label>
        <label>Date To
            <input type="date" name="to" value="<?= h($to) ?>" required>
        </label>
        <button type="submit">View Records</button>
    </form>
</section>
<?php if ($from !== '' && $to !== '' && !$error): ?>
<section class="panel">
    <h3>Attendance Records: <?= h($from) ?> to <?= h($to) ?></h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Record #</th><th>Emp. ID</th><th>Employee</th><th>Date/Time In</th><th>Date/Time Out</th><th>Total Hours</th></tr></thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <?php
                    $hours = hoursWorked($r['attTimeIn'], $r['attTimeOut']);
                    $totalHours += $hours;
                    $totalPay += $hours * (float) $r['empRPH'];
                ?>
                <tr>
                    <td><?= h($r['attRN']) ?></td><td><?= h($r['empID']) ?></td>
                    <td><?= h($r['empLName'] . ', ' . $r['empFName']) ?></td>
                    <td><?= h(displayDateTime($r['attTimeIn'])) ?></td><td><?= h(displayDateTime($r['attTimeOut'])) ?></td>
                    <td><?= $r['attTimeOut'] ? number_format($hours, 2) : 'Pending' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$records): ?><tr><td colspan="6">No attendance records found for this date range.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="totals">
        <div><span>Attendance Records</span><strong><?= count($records) ?></strong></div>
        <div><span>Total Hours</span><strong><?= number_format($totalHours, 2) ?></strong></div>
    </div>
    <p class="hint">Date Generated: <?= h(date('m/d/Y')) ?></p>
</section>
<?php else: ?>
<?php endif; ?>
<?php endPage(); ?>
