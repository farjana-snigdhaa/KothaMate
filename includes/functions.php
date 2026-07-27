<?php
require_once __DIR__ . '/../config/db.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = BASE_URL;
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $db = getDb();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);

    return $stmt->fetch() ?: null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool
{
    return !empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $path = ''): void
{
    header('Location: ' . url($path));
    exit;
}

function formatCurrency(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function formatDate(string $date): string
{
    return date('M j, Y', strtotime($date));
}
