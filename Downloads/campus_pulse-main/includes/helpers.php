<?php
// includes/helpers.php — session, auth guards, CSRF, JSON helpers
// Every page/endpoint starts with: require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/* ---------- output ---------- */
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ---------- auth ---------- */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

// For normal pages (dashboard.php): not logged in => redirect to login
function require_login_page(string $loginUrl = 'login.php'): array
{
    $u = current_user();
    if (!$u) {
        header('Location: ' . $loginUrl);
        exit;
    }
    return $u;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---------- flash messages (login/signup pages) ---------- */
function flash_set(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

function flash_get(string $key): ?string
{
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

/* ---------- JSON API helpers ---------- */
function json_out($data, int $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Logged-in check for API. $roles = allowed roles (null = any logged-in user)
function api_user(?array $roles = null): array
{
    $u = current_user();
    if (!$u) {
        json_out(['error' => 'Please log in first.'], 401);
    }
    if ($roles !== null && !in_array($u['role'], $roles, true)) {
        json_out(['error' => 'You are not allowed to do this.'], 403);
    }
    return $u;
}

// POST-only + CSRF check. Returns the JSON body (or $_POST for form/multipart)
function api_post_body(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_out(['error' => 'POST required.'], 405);
    }
    if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        json_out(['error' => 'Session expired. Reload the page.'], 419);
    }
    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ctype, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }
    return $_POST;
}

// Wrap endpoint logic so a DB error returns clean JSON instead of a stack trace
function api_run(callable $fn)
{
    try {
        $fn();
    } catch (Throwable $t) {
        error_log($t);
        json_out(['error' => 'Server error.'], 500);
    }
}

function clean_str($v, int $max = 255): string
{
    $v = trim((string)$v);
    return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}
