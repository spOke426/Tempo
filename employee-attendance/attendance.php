<?php
require 'functions.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $empID = filter_var($_POST['empID'] ?? '', FILTER_VALIDATE_INT);
            $timeIn = mysqlDateTime($_POST['timeIn'] ?? '');
            $timeOutText = trim($_POST['timeOut'] ?? '');
            $timeOut = $timeOutText !== '' ? mysqlDateTime($timeOutText) : null;

            if (!$empID || !$timeIn || ($timeOutText !== '' && !$timeOut)) {
                $error = 'Please choose an employee and enter valid date/time values.';
            } elseif ($timeOut && strtotime($timeOut) <= strtotime($timeIn)) {
                $error = 'Time out must be later than time in.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO attendance (empID, attDate, attTimeIn, attTimeOut, attStat) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$empID, date('Y-m-d H:i:s'), $timeIn, $timeOut, 'Not Cancelled']);
                go('attendance.php?msg=added');
            }
        } elseif ($action === 'cancel') {
            $stmt = $pdo->prepare('UPDATE attendance SET attStat = ? WHERE attRN = ? AND attStat = ?');
            $stmt->execute(['Cancelled', (int) ($_POST['attRN'] ?? 0), 'Not Cancelled']);
            go('attendance.php?msg=cancelled');
        } elseif ($action === 'timeout') {
            $record = (int) ($_POST['attRN'] ?? 0);
            $stmt = $pdo->prepare('SELECT attTimeIn FROM attendance WHERE attRN = ? AND attTimeOut IS NULL AND attStat = ?');
            $stmt->execute([$record, 'Not Cancelled']);
            $row = $stmt->fetch();
            $now = date('Y-m-d H:i:s');
            if (!$row || strtotime($now) <= strtotime($row['attTimeIn'])) {
                $error = 'Cannot time out this record yet.';
            } else {
                $stmt = $pdo->prepare('UPDATE attendance SET attTimeOut = ? WHERE attRN = ? AND attTimeOut IS NULL AND attStat = ?');
                $stmt->execute([$now, $record, 'Not Cancelled']);
                go('attendance.php?msg=timeout');
            }
        }
    } catch (PDOException $e) {
        $error = 'Could not save the attendance record. Check that the employee exists.';
    }
}

$employees = $pdo->query('SELECT empID, empFName, empLName FROM employees ORDER BY empLName, empFName')->fetchAll();
$records = $pdo->query('SELECT a.*, e.empFName, e.empLName FROM attendance a JOIN employees e ON e.empID = a.empID ORDER BY a.attRN DESC')->fetchAll();
startPage('Attendance Recording');
if ($error) echo '<div class="error">' . h($error) . '</div>';
?>
<section class="panel">
    <h3>Record Attendance Here</h3>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="add">
        <label>Employee
            <select name="empID" required>
                <option value="">Choose an employee</option>
                <?php foreach ($employees as $e): ?>
                <option value="<?= h($e['empID']) ?>"><?= h($e['empID']) ?> - <?= h($e['empLName'] . ', ' . $e['empFName']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Date / Time In
            <input type="datetime-local" name="timeIn" value="<?= h(date('Y-m-d\TH:i')) ?>" required>
        </label>
        <label>Date / Time Out (optional)
            <input type="datetime-local" name="timeOut">
        </label>
        <div class="form-actions"><button type="submit">Save Attendance</button></div>
    </form>
    <p class="hint">Leave Time Out blank if the employee is still working, then click "Time Out Now" in the table later.</p>
</section>
<section class="panel">
    <h3>Attendance Records</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Record #</th><th>Emp. ID</th><th>Employee</th><th>Date/Time In</th><th>Date/Time Out</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= h($r['attRN']) ?></td>
                    <td><?= h($r['empID']) ?></td>
                    <td><?= h($r['empLName'] . ', ' . $r['empFName']) ?></td>
                    <td><?= h(displayDateTime($r['attTimeIn'])) ?></td>
                    <td><?= h(displayDateTime($r['attTimeOut'])) ?></td>
                    <td><?= $r['attTimeOut'] ? number_format(hoursWorked($r['attTimeIn'], $r['attTimeOut']), 2) . ' h' : '—' ?></td>
                    <td><?= h($r['attStat']) ?></td>
                    <td class="actions">
                        <?php if ($r['attStat'] !== 'Cancelled'): ?>
                            <?php if ($r['attTimeOut'] === null): ?>
                            <form method="post">
                                <input type="hidden" name="action" value="timeout">
                                <input type="hidden" name="attRN" value="<?= h($r['attRN']) ?>">
                                <button class="link-button" type="submit">Time Out Now</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" onsubmit="return confirm('Cancel this attendance record?')">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="attRN" value="<?= h($r['attRN']) ?>">
                                <button class="link-button danger" type="submit">Cancel</button>
                            </form>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="8">No attendance recorded yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endPage(); ?>
