<?php
require_once __DIR__ . '/functions.php';
require_role('admin');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['deptID'] ?? 0);
    $name = trim($_POST['depName'] ?? '');
    try {
        if ($action === 'add' && $name !== '') {
            $stmt = db()->prepare('INSERT INTO Department (depName) VALUES (?)');
            $stmt->execute([$name]);
            flash('Department added.');
        } elseif ($action === 'edit' && $id > 0 && $name !== '') {
            $stmt = db()->prepare('UPDATE Department SET depName = ? WHERE deptID = ?');
            $stmt->execute([$name, $id]);
            flash('Department updated.');
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = db()->prepare('DELETE FROM Department WHERE deptID = ?');
            $stmt->execute([$id]);
            flash('Department deleted.');
        } else {
            $error = 'Enter a department name and select a valid action.';
        }
        if ($error === '') go('departments.php');
    } catch (PDOException $e) {
        $error = 'Cannot save or delete this department. It may already exist or be used by another record.';
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM Department WHERE deptID = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = departments();
$pageTitle = 'Department Management';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h2><?= $edit ? 'Edit Department' : 'Add Department' ?></h2>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
        <?php if ($edit): ?><input type="hidden" name="deptID" value="<?= h($edit['deptID']) ?>"><?php endif; ?>
        <div class="field"><label>Department Name</label><input type="text" name="depName" required maxlength="100" placeholder="e.g., College of Computer Studies" value="<?= h($edit['depName'] ?? '') ?>"></div>
        <button class="btn"><?= $edit ? 'Save Changes' : 'Add Department' ?></button>
        <?php if ($edit): ?><a class="btn btn-muted" href="departments.php">Cancel Edit</a><?php endif; ?>
    </form>
</div>
<div class="card">
    <h2>Department List</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Department ID</th><th>Department Name</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $d): ?>
            <tr><td><?= h($d['deptID']) ?></td><td><?= h($d['depName']) ?></td><td>
                <div class="actions">
                    <a href="departments.php?edit=<?= urlencode($d['deptID']) ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this department?');">
                        <?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="deptID" value="<?= h($d['deptID']) ?>"><button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </div>
            </td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="3">No departments yet.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
