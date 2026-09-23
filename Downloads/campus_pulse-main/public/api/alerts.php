<?php
// GET  -> { status, alerts:[...active] }            (any logged-in user; ticker + status pill)
// POST -> action = post | resolve | status           (admin only)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        api_user();
        $status = $pdo->query('SELECT status FROM campus_status ORDER BY id DESC LIMIT 1')->fetchColumn() ?: 'normal';
        $alerts = $pdo->query(
            'SELECT id, title, type, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS mins_ago
               FROM alerts WHERE is_active = 1 ORDER BY id DESC LIMIT 30'
        )->fetchAll();
        json_out(['status' => $status, 'alerts' => $alerts]);
    }

    $user = api_user(['admin']);
    $in   = api_post_body();

    switch ($in['action'] ?? '') {
        case 'post':
            $title = clean_str($in['title'] ?? '', 200);
            $type  = $in['type'] ?? '';
            if ($title === '' || !in_array($type, ['Traffic', 'Weather', 'Campus notice'], true)) {
                json_out(['error' => 'Title and a valid type are required.'], 422);
            }
            $pdo->prepare('INSERT INTO alerts (title, type, created_by) VALUES (?,?,?)')
                ->execute([$title, $type, $user['id']]);
            json_out(['ok' => true], 201);

        case 'resolve':
            $pdo->prepare('UPDATE alerts SET is_active = 0 WHERE id = ?')->execute([(int)($in['id'] ?? 0)]);
            json_out(['ok' => true]);

        case 'status':
            $status = $in['status'] ?? '';
            if (!in_array($status, ['normal', 'alert', 'critical'], true)) {
                json_out(['error' => 'Invalid status.'], 422);
            }
            $pdo->prepare('INSERT INTO campus_status (status, updated_by) VALUES (?,?)')
                ->execute([$status, $user['id']]);
            json_out(['ok' => true]);

        default:
            json_out(['error' => 'Unknown action.'], 400);
    }
});
