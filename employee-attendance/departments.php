<?php
require 'functions.php';
$error = '';

// Add, edit, or delete departments when a form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code = filter_var($_POST['depCode'] ?? '', FILTER_VALIDATE_INT);

    if (!$code || $code < 1) {
        $error = 'Enter a valid department code.';
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM departments WHERE depCode = ?');
                $stmt->execute([$code]);
                go('departments.php?msg=deleted');
            }

            $name = trim($_POST['depName'] ?? '');
            $head = trim($_POST['depHead'] ?? '');
            $telephone = trim($_POST['depTelNo'] ?? '');

            if ($name === '' || $head === '' || $telephone === '') {
                $error = 'Please complete all fields.';
            } elseif ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO departments (depCode, depName, depHead, depTelNo) VALUES (?, ?, ?, ?)');
                $stmt->execute([$code, $name, $head, $telephone]);
                go('departments.php?msg=added');
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE departments SET depName = ?, depHead = ?, depTelNo = ? WHERE depCode = ?');
                $stmt->execute([$name, $head, $telephone, $code]);
                go('departments.php?msg=updated');
            }
        } catch (PDOException $e) {
            $error = 'Could not save the change. The code may already exist, or this department may still have employees.';
        }
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM departments WHERE depCode = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
}
$departments = $pdo->query('SELECT * FROM departments ORDER BY depCode')->fetchAll();
startPage('Departments Management');
if ($error) echo '<div class="error">' . h($error) . '</div>';
?>
<section class="panel">
    <h3><?= $editing ? 'Edit Department' : 'Add a Department' ?></h3>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
        <label>Department Code
            <input type="number" name="depCode" min="1" required value="<?= h($editing['depCode'] ?? '') ?>" <?= $editing ? 'readonly' : '' ?>>
        </label>
        <label>Department Name
            <input type="text" name="depName" maxlength="100" required value="<?= h($editing['depName'] ?? '') ?>">
        </label>
        <label>Department Head
            <input type="text" name="depHead" maxlength="100" required value="<?= h($editing['depHead'] ?? '') ?>">
        </label>
        <label>Telephone Number
            <input type="text" name="depTelNo" maxlength="30" required value="<?= h($editing['depTelNo'] ?? '') ?>">
        </label>
        <div class="form-actions">
            <button type="submit"><?= $editing ? 'Save Changes' : 'Add Department' ?></button>
            <?php if ($editing): ?><a class="button secondary" href="departments.php">Cancel Edit</a><?php endif; ?>
        </div>
    </form>
</section>
<section class="panel">
    <h3>Departments List</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Code</th><th>Name</th><th>Head</th><th>Tel. No.</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($departments as $d): ?>
                <tr>
                    <td><?= h($d['depCode']) ?></td>
                    <td><?= h($d['depName']) ?></td>
                    <td><?= h($d['depHead']) ?></td>
                    <td><?= h($d['depTelNo']) ?></td>
                    <td class="actions">
                        <a href="departments.php?edit=<?= h($d['depCode']) ?>">Edit</a>
                        <form method="post" onsubmit="return confirm('Delete this department?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="depCode" value="<?= h($d['depCode']) ?>">
                            <button class="link-button danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$departments): ?><tr><td colspan="5">No departments found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="hint">A department with employees cannot be deleted until those employees are removed.</p>
</section>
<?php endPage(); ?>
