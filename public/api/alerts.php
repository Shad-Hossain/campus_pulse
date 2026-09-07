<?php
/**
 * public/api/alerts.php
 *
 * Alerts API for Campus Pulse.
 * Backs the "Manage Alerts" admin view and the home-page ticker.
 *
 * Expects:
 *   - config/db.php to expose a connected PDO instance in $pdo
 *   - a login flow that sets $_SESSION['user'] = ['id' => .., 'role' => ..]
 *
 * Endpoints:
 *   GET    /api/alerts.php            -> active alerts (public, for the ticker)
 *   GET    /api/alerts.php?all=1      -> all alerts, active + inactive (admin only)
 *   POST   /api/alerts.php            -> create a new alert (admin only)
 *   PATCH  /api/alerts.php?id=5       -> toggle/set is_active on an alert (admin only)
 *   DELETE /api/alerts.php?id=5       -> permanently remove an alert (admin only)
 */

declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/db.php'; // must provide a PDO instance as $pdo

/** Send a JSON response and stop execution. */
function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

/** Currently logged-in user, or null if no session. */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Guard for admin-only actions. */
function requireAdmin(): array
{
    $user = currentUser();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        respond(403, ['error' => 'Admin access required.']);
    }
    return $user;
}

/** Decode a JSON request body, falling back to $_POST for form submits. */
function requestBody(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}

/** Human-friendly "10m ago" style string for the ticker. */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

function formatAlert(array $row): array
{
    return [
        'id'         => (int) $row['id'],
        'title'      => $row['title'],
        'type'       => $row['type'],
        'is_active'  => (bool) $row['is_active'],
        'created_at' => $row['created_at'],
        'created_by' => $row['created_by_name'] ?? null,
        'meta'       => $row['type'] . ' · ' . timeAgo($row['created_at']),
    ];
}

function handleGet(PDO $pdo): void
{
    $showAll = isset($_GET['all']) && $_GET['all'] === '1';

    if ($showAll) {
        requireAdmin();
    }

    $sql = "SELECT a.id, a.title, a.type, a.is_active, a.created_at, u.full_name AS created_by_name
            FROM alerts a
            LEFT JOIN users u ON u.id = a.created_by";
    if (!$showAll) {
        $sql .= " WHERE a.is_active = 1";
    }
    $sql .= " ORDER BY a.created_at DESC";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    respond(200, ['alerts' => array_map('formatAlert', $rows)]);
}

function handlePost(PDO $pdo): void
{
    $user = requireAdmin();
    $input = requestBody();

    $title = trim((string) ($input['title'] ?? ''));
    $type  = trim((string) ($input['type'] ?? ''));
    $allowedTypes = ['Traffic', 'Weather', 'Campus notice'];

    if ($title === '') {
        respond(422, ['error' => 'Alert title is required.']);
    }
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'Campus notice';
    }

    $stmt = $pdo->prepare(
        "INSERT INTO alerts (title, type, is_active, created_by, created_at)
         VALUES (:title, :type, 1, :created_by, NOW())"
    );
    $stmt->execute([
        ':title'      => $title,
        ':type'       => $type,
        ':created_by' => $user['id'],
    ]);

    $id = (int) $pdo->lastInsertId();
    $row = $pdo->prepare(
        "SELECT a.id, a.title, a.type, a.is_active, a.created_at, u.full_name AS created_by_name
         FROM alerts a LEFT JOIN users u ON u.id = a.created_by WHERE a.id = :id"
    );
    $row->execute([':id' => $id]);

    respond(201, [
        'message' => 'Alert posted.',
        'alert'   => formatAlert($row->fetch(PDO::FETCH_ASSOC)),
    ]);
}

function handlePatch(PDO $pdo): void
{
    requireAdmin();
    $input = requestBody();

    $id = (int) ($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        respond(422, ['error' => 'A valid alert id is required.']);
    }

    if (array_key_exists('is_active', $input)) {
        $stmt = $pdo->prepare("UPDATE alerts SET is_active = :is_active WHERE id = :id");
        $stmt->execute([':is_active' => (int) (bool) $input['is_active'], ':id' => $id]);
    } else {
        // No explicit value given -> flip current state (handy for a single "Deactivate" button)
        $stmt = $pdo->prepare("UPDATE alerts SET is_active = NOT is_active WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    if ($stmt->rowCount() === 0) {
        respond(404, ['error' => 'Alert not found.']);
    }

    respond(200, ['message' => 'Alert updated.']);
}

function handleDelete(PDO $pdo): void
{
    requireAdmin();

    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        $input = requestBody();
        $id = (int) ($input['id'] ?? 0);
    }
    if ($id <= 0) {
        respond(422, ['error' => 'A valid alert id is required.']);
    }

    $stmt = $pdo->prepare("DELETE FROM alerts WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        respond(404, ['error' => 'Alert not found.']);
    }

    respond(200, ['message' => 'Alert deleted.']);
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PATCH':
            handlePatch($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            respond(405, ['error' => 'Method not allowed.']);
    }
} catch (Throwable $e) {
    respond(500, ['error' => 'Server error.', 'detail' => $e->getMessage()]);
}
