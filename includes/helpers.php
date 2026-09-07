<?php
/**
 * includes/helpers.php
 *
 * Shared helpers for every endpoint under public/api/*.php.
 * Include this once, after session_start() and after config/db.php.
 *
 *   require_once __DIR__ . '/../../config/db.php';   // gives you $pdo
 *   require_once __DIR__ . '/../../includes/helpers.php';
 */

declare(strict_types=1);

/**
 * Send a JSON response with the given HTTP status and stop execution.
 */
function respond(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

/**
 * The logged-in user for this request, or null if there isn't one.
 * Expects login_handler.php to set $_SESSION['user'] = ['id' => .., 'role' => .., 'name' => ..].
 */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Require any logged-in user. Halts the request with 401 if there isn't one.
 */
function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        respond(401, ['error' => 'You must be logged in.']);
    }
    return $user;
}

/**
 * Require a logged-in user with one of the given roles.
 * Example: requireRole('admin'); or requireRole('admin', 'faculty');
 */
function requireRole(string ...$roles): array
{
    $user = requireLogin();
    if (!in_array($user['role'] ?? '', $roles, true)) {
        respond(403, ['error' => 'You do not have permission to do that.']);
    }
    return $user;
}

/** Convenience wrapper for the common case of admin-only actions. */
function requireAdmin(): array
{
    return requireRole('admin');
}

/**
 * Decode a JSON request body (application/json), falling back to $_POST
 * so the same endpoint also accepts a normal HTML form submit.
 */
function requestBody(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

/**
 * Read an integer id from the query string or a decoded request body.
 * Returns 0 if missing/invalid so callers can validate with a simple check.
 */
function requiredId(array $input = []): int
{
    return (int) ($_GET['id'] ?? $input['id'] ?? 0);
}

/**
 * Trim a string field out of an input array, defaulting to ''.
 */
function stringField(array $input, string $key): string
{
    return trim((string) ($input[$key] ?? ''));
}

/**
 * Restrict a value to an allowed set, falling back to a default.
 * Handy for ENUM-backed columns like alerts.type / events.category.
 */
function enumField(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

/**
 * Human-friendly "10m ago" style string, used by the alerts ticker and
 * anywhere else a created_at timestamp is shown as relative time.
 */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
