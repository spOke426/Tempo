<?php
require_once 'functions.php';
require_once 'config.php';

if (logged_in()) {
    go('dashboard.php');
}

// Login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        flash('Please enter your email and password.', 'error');
        go('index.php');
    }

    $stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['email'] = $user['email'];
        flash('Welcome! You are now logged in.');
        go('dashboard.php');
    }

    flash('Incorrect email or password.', 'error');
    go('index.php');
}

$page_title = 'Login - Student Management System';
require 'header.php';
?>
<div class="login-card card">
    <h1>Login</h1>
    <p class="muted">Sign in to manage student records.</p>

    <form method="POST" action="index.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" class="btn btn-primary btn-wide">Login</button>
    </form>
    <p class="hint">Sample account: <strong>alpha@uc.com</strong> / <strong>hello123</strong></p>
</div>
<?php require 'footer.php'; ?>
