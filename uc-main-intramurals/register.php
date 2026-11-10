<?php
require_once __DIR__ . '/functions.php';
if (logged_in()) go('index.php');
$error = '';
$roles = ['admin', 'athlete', 'tournament_manager', 'coach', 'dean'];
$departmentList = departments();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $userName = trim($_POST['userName'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirmPassword'] ?? '');
    $role = (string) ($_POST['role'] ?? '');
    $first = trim($_POST['fName'] ?? '');
    $last = trim($_POST['lName'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $deptID = (int) ($_POST['deptID'] ?? 0);
    $table = staff_table($role);

    if (!preg_match('/^[A-Za-z0-9_-]{3,30}$/', $userName)) {
        $error = 'ID must be 3–30 characters (letters, numbers, _ or -).';
    } elseif (!in_array($role, $roles, true)) {
        $error = 'Please select a valid role.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must have 8+ characters and include a letter, number and special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($table !== null && ($first === '' || $last === '' || $mobile === '' || $deptID < 1)) {
        $error = 'Coach, dean, and manager roles require full name, mobile, and department.';
    } else {
        try {
            db()->beginTransaction();
            $stmt = db()->prepare('INSERT INTO Registration (userName, password, `role`) VALUES (?, ?, ?)');
            $stmt->execute([$userName, password_hash($password, PASSWORD_DEFAULT), $role]);
            if ($table !== null) {
                // $table is chosen ONLY from a fixed list in staff_table().
                $stmt = db()->prepare("INSERT INTO $table (userName, fName, lName, mobile, deptID) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userName, $first, $last, $mobile, $deptID]);
            }
            db()->commit();
            flash('Registration successful! Please log in.');
            go('login.php');
        } catch (PDOException $e) {
            if (db()->inTransaction()) db()->rollBack();
            $error = $e->getCode() === '23000'
                ? 'ID number already registered, or invalid department. Try again.'
                : 'Could not save account. Check your MySQL setup.';
        }
    }
}
$pageTitle = 'Registration';
require __DIR__ . '/header.php';
?>
<div class="card">
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <div class="field"><label for="userName">Student / Employee ID Number</label><input name="userName" id="userName" type="text" required maxlength="30" value="<?= h($_POST['userName'] ?? '') ?>"></div>
        <div class="fields-two">
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required minlength="8"></div>
            <div class="field"><label for="confirmPassword">Confirm Password</label><input id="confirmPassword" name="confirmPassword" type="password" required minlength="8"></div>
        </div>
        <div class="field"><label for="role">Role</label><select name="role" id="role" required>
            <option value="">-- Select role --</option>
            <?php foreach ($roles as $role): ?><option value="<?= h($role) ?>" <?= ($_POST['role'] ?? '') === $role ? 'selected' : '' ?>><?= h(ucwords(str_replace('_', ' ', $role))) ?></option><?php endforeach; ?>
        </select></div>
        <h2>Additional details (required for Manager, Coach, or Dean)</h2>
        <div class="fields-two">
            <div class="field"><label>First Name</label><input name="fName" type="text" value="<?= h($_POST['fName'] ?? '') ?>"></div>
            <div class="field"><label>Last Name</label><input name="lName" type="text" value="<?= h($_POST['lName'] ?? '') ?>"></div>
            <div class="field"><label>Mobile</label><input name="mobile" type="text" value="<?= h($_POST['mobile'] ?? '') ?>"></div>
            <div class="field"><label>Department</label><select name="deptID"><option value="">-- Select department --</option>
                <?php foreach ($departmentList as $d): ?><option value="<?= h($d['deptID']) ?>" <?= (int) ($_POST['deptID'] ?? 0) === (int) $d['deptID'] ? 'selected' : '' ?>><?= h($d['depName']) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <button class="btn" type="submit">Create Account</button>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </form>
</div>
