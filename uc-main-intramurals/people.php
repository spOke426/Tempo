<?php
require_once __DIR__ . '/functions.php';
require_login();
$error = '';
$myTable = staff_table($_SESSION['role']);

// Employees update only their own staff record.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $myTable !== null) {
    check_csrf();
    $fName = trim($_POST['fName'] ?? '');
    $lName = trim($_POST['lName'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $deptID = (int) ($_POST['deptID'] ?? 0);
    if ($fName === '' || $lName === '' || $mobile === '' || $deptID < 1) {
        $error = 'Complete every staff field.';
    } else {
        try {
            $stmt = db()->prepare("UPDATE $myTable SET fName = ?, lName = ?, mobile = ?, deptID = ? WHERE userName = ?");
            $stmt->execute([$fName, $lName, $mobile, $deptID, $_SESSION['userName']]);
            flash('Staff profile updated.');
            go('people.php');
        } catch (PDOException $e) {
            $error = 'Could not update profile. Check the selected department.';
        }
    }
}

$myProfile = null;
if ($myTable !== null) {
    $stmt = db()->prepare("SELECT * FROM $myTable WHERE userName = ?");
    $stmt->execute([$_SESSION['userName']]);
    $myProfile = $stmt->fetch();
}

// Staff directories are read-only except for each user's own profile form.
$staff = db()->query("
    SELECT 'Tournament Manager' AS staffRole, userName, fName, lName, mobile, depName
      FROM TournamentManager t JOIN Department d ON t.deptID = d.deptID
    UNION ALL
    SELECT 'Coach', userName, fName, lName, mobile, depName
      FROM Coach c JOIN Department d ON c.deptID = d.deptID
    UNION ALL
    SELECT 'Dean', userName, fName, lName, mobile, depName
      FROM Dean n JOIN Department d ON n.deptID = d.deptID
    ORDER BY staffRole, lName
")->fetchAll();
$pageTitle = 'Staff Profiles';
require __DIR__ . '/header.php';
?>
<?php if ($myProfile): ?>
<div class="card">
    <h2>Update My Profile</h2>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <p><strong>ID:</strong> <?= h($_SESSION['userName']) ?> <span class="pill"><?= h(str_replace('_', ' ', $_SESSION['role'])) ?></span></p>
        <div class="fields-two">
            <div class="field"><label>First Name</label><input type="text" name="fName" required value="<?= h($myProfile['fName']) ?>"></div>
            <div class="field"><label>Last Name</label><input type="text" name="lName" required value="<?= h($myProfile['lName']) ?>"></div>
            <div class="field"><label>Mobile Number</label><input type="text" name="mobile" required value="<?= h($myProfile['mobile']) ?>"></div>
            <div class="field"><label>Department</label><select name="deptID" required>
                <?php foreach (departments() as $d): ?><option value="<?= h($d['deptID']) ?>" <?= (int) $d['deptID'] === (int) $myProfile['deptID'] ? 'selected' : '' ?>><?= h($d['depName']) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <button class="btn">Save Profile</button>
    </form>
</div>
<?php endif; ?>
<div class="card">
    <h2>Tournament Managers, Coaches & Deans</h2>
    <p class="small">Staff accounts are created through the Registration page.</p>
    <div class="table-wrap"><table><thead><tr><th>Role</th><th>ID</th><th>Name</th><th>Department</th><th>Mobile</th></tr></thead><tbody>
        <?php foreach ($staff as $person): ?>
            <tr><td><?= h($person['staffRole']) ?></td><td><?= h($person['userName']) ?></td><td><?= h($person['lName'] . ', ' . $person['fName']) ?></td><td><?= h($person['depName']) ?></td><td><?= h($person['mobile']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$staff): ?><tr><td colspan="5">No staff profiles yet. Register a coach, dean, or tournament manager.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
