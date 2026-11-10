<?php
session_start();
require_once __DIR__ . '/config.php';

// Escapes user text so it is safe to display in HTML.
function h($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function peso($value): string {
    return '₱' . number_format((float) $value, 2);
}

// Show a message once on the next page.
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function show_flash(): void {
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="notice ' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}

function redirect(string $page): void {
    header('Location: ' . $page);
    exit;
}

// Basic anti-CSRF protection for add, edit, and delete actions.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_input(): void {
    echo '<input type="hidden" name="csrf_token" value="' . h($_SESSION['csrf_token']) . '">';
}
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid form token. Refresh the page and try again.');
    }
}

function valid_date(string $date): bool {
    $d = DateTime::createFromFormat('!Y-m-d', $date);
    return $d !== false && $d->format('Y-m-d') === $date;
}

function valid_amount($value): bool {
    return is_scalar($value) && preg_match('/^\d+(\.\d{1,2})?$/', (string) $value) === 1;
}

function get_id($value): int {
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
}
