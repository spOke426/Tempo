<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Reuse one database connection for each page request.
function db(): PDO
{
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    static $connection = null;

    if ($connection === null) {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $connection = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $connection;
}

// Escape any text printed inside HTML to avoid script injection.
function h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function go(string $page): void
{
    header('Location: ' . $page);
    exit;
}

function logged_in(): bool
{
    return isset($_SESSION['userName'], $_SESSION['role']);
}

function require_login(): void
{
    if (!logged_in()) {
        go('login.php');
    }
}

function role_is(...$roles): bool
{
    return logged_in() && in_array($_SESSION['role'], $roles, true);
}

function require_role(...$roles): void
{
    require_login();
    if (!role_is(...$roles)) {
        http_response_code(403);
        exit('Access denied. <a href="index.php">Back to Menu</a>');
    }
}

// A small CSRF check for forms that update or delete data.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function check_csrf(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Invalid form request. Please reload the page.');
    }
}

function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        echo '<div class="notice">' . h($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function departments(): array
{
    return db()->query('SELECT * FROM Department ORDER BY depName')->fetchAll();
}

function people_in_role(string $role): array
{
    $stmt = db()->prepare('SELECT userName FROM Registration WHERE `role` = ? ORDER BY userName');
    $stmt->execute([$role]);
    return $stmt->fetchAll();
}

function staff_table(string $role): ?string
{
    return [
        'tournament_manager' => 'TournamentManager',
        'coach' => 'Coach',
        'dean' => 'Dean',
    ][$role] ?? null;
}
