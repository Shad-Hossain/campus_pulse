<?php
/**
 * Shared bootstrap for every API endpoint.
 * This is the ONLY place PHP is allowed to talk to the database/session —
 * the .php files outside /api and /admin are plain HTML/JS.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

/** Read a JSON request body sent via fetch() as an associative array. */
function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Send a JSON response and stop. */
function json_out($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/** 401 helper for endpoints that require a logged-in user. */
function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        json_out(['status' => 'login_required', 'error' => 'Please log in.'], 401);
    }
    return (int) $_SESSION['user_id'];
}

/** 401 helper for endpoints that require a logged-in admin. */
function require_admin(): int
{
    if (empty($_SESSION['admin_id'])) {
        json_out(['status' => 'login_required', 'error' => 'Admin login required.'], 401);
    }
    return (int) $_SESSION['admin_id'];
}
