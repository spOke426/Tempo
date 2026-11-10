<?php
require_once __DIR__ . '/functions.php';
if (logged_in()) go('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $userName = trim($_POST['userName'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM Registration WHERE userName = ?');
    $stmt->execute([$userName]);
    $account = $stmt->fetch();
    if ($account && password_verify($password, $account['password'])) {
        session_regenerate_id(true);
        $_SESSION['userName'] = $account['userName'];
        $_SESSION['role'] = $account['role'];
        go('index.php');
    }
    $error = 'Incorrect ID number or password.';
}
$pageTitle = 'Login';
require __DIR__ . '/header.php';
?>
<div class="card">
    <p>Login using your student or employee ID number.</p>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <div class="field"><label for="userName">Student / Employee ID</label><input id="userName" type="text" name="userName" required autofocus></div>
        <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required></div>
        <button class="btn">Login</button>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </form>
</div>
