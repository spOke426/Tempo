<?php
require 'functions.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['empID'] ?? '', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        $error = 'Enter a valid employee ID.';
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM employees WHERE empID = ?');
                $stmt->execute([$id]);
                go('employees.php?msg=deleted');
            }

            $dept = filter_var($_POST['depCode'] ?? '', FILTER_VALIDATE_INT);
            $first = trim($_POST['empFName'] ?? '');
            $last = trim($_POST['empLName'] ?? '');
            $rate = $_POST['empRPH'] ?? '';

            if (!$dept || $first === '' || $last === '' || !is_numeric($rate) || (float) $rate < 0) {
                $error = 'Please enter a department, first name, last name, and valid hourly rate.';
            } elseif ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO employees (empID, depCode, empFName, empLName, empRPH) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$id, $dept, $first, $last, $rate]);
                go('employees.php?msg=added');
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE employees SET depCode = ?, empFName = ?, empLName = ?, empRPH = ? WHERE empID = ?');
                $stmt->execute([$dept, $first, $last, $rate, $id]);
                go('employees.php?msg=updated');
            }
        } catch (PDOException $e) {
            $error = 'Could not save the change. The employee ID may already exist, or this employee has attendance records.';
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE empID = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
}
$departments = $pdo->query('SELECT depCode, depName FROM departments ORDER BY depName')->fetchAll();
$employees = $pdo->query('SELECT e.*, d.depName FROM employees e JOIN departments d ON d.depCode = e.depCode ORDER BY e.empID')->fetchAll();
startPage('Employees Management');
if ($error) echo '<div class="error">' . h($error) . '</div>';
?>
<section class="panel">
    <h3><?= $editing ? 'Edit Employee' : 'Add an Employee' ?></h3>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
        <label>Employee ID
            <input type="number" name="empID" min="1" required value="<?= h($editing['empID'] ?? '') ?>" <?= $editing ? 'readonly' : '' ?>>
        </label>
        <label>Department
            <select name="depCode" required>
                <option value="">Choose a department</option>
                <?php foreach ($departments as $d): ?>
                <option value="<?= h($d['depCode']) ?>" <?= (string) ($editing['depCode'] ?? '') === (string) $d['depCode'] ? 'selected' : '' ?>><?= h($d['depName']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>First Name
            <input type="text" name="empFName" maxlength="100" required value="<?= h($editing['empFName'] ?? '') ?>">
        </label>
        <label>Last Name
            <input type="text" name="empLName" maxlength="100" required value="<?= h($editing['empLName'] ?? '') ?>">
        </label>
        <label>Rate per Hour (₱)
            <input type="number" name="empRPH" min="0" step="0.01" required value="<?= h($editing['empRPH'] ?? '') ?>">
        </label>
        <div class="form-actions">
            <button type="submit"><?= $editing ? 'Save Changes' : 'Add Employee' ?></button>
            <?php if ($editing): ?><a class="button secondary" href="employees.php">Cancel Edit</a><?php endif; ?>
        </div>
    </form>
    <?php if (!$departments): ?><p class="hint">Add a department first before adding employees.</p><?php endif; ?>
</section>
<section class="panel">
    <h3>Employees List</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Dept.</th><th>Last Name</th><th>First Name</th><th>Rate/Hour</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($employees as $e): ?>
                <tr>
                    <td><?= h($e['empID']) ?></td><td><?= h($e['depCode']) ?> - <?= h($e['depName']) ?></td>
                    <td><?= h($e['empLName']) ?></td><td><?= h($e['empFName']) ?></td>
                    <td>₱<?= number_format((float) $e['empRPH'], 2) ?></td>
                    <td class="actions">
                        <a href="employees.php?edit=<?= h($e['empID']) ?>">Edit</a>
                        <form method="post" onsubmit="return confirm('Delete this employee?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="empID" value="<?= h($e['empID']) ?>">
                            <button class="link-button danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$employees): ?><tr><td colspan="6">No employees found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="hint">Employees with attendance records cannot be deleted, to protect the history.</p>
</section>
<?php endPage(); ?>
