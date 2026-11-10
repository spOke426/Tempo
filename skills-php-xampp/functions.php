<?php
// Common helper functions used by all pages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function go($page): void
{
    header('Location: ' . $page);
    exit;
}

function logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!logged_in()) {
        go('index.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf(): void
{
    $given = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals(csrf_token(), $given)) {
        http_response_code(400);
        exit('Invalid form submission. Go back, refresh the page, and try again.');
    }
}

function flash($message, $type = 'success'): void
{
    $_SESSION['flash'] = ['text' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}
