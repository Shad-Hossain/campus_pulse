<?php
// GET  -> { items:[{title, kind, ...}], unread }
// POST -> marks the user's personal notices as read (called when the bell panel is opened)
// Built from: active Traffic/Weather alerts, events in the next 7 days, and Research-tagged news —
// each category only appears if the user has that toggle switched on in Profile > Notification settings.
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo  = db();
    $user = api_user();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        api_post_body();
        $pdo->prepare('UPDATE user_notices SET is_read = 1 WHERE user_id = ?')->execute([$user['id']]);
        json_out(['ok' => true]);
    }

    $st = $pdo->prepare('SELECT traffic_alerts, weather_alerts, event_reminders, research_alerts FROM notification_settings WHERE user_id = ?');
    $st->execute([$user['id']]);
    $settings = $st->fetch() ?: [];

    $items = [];

    // personal notices from the admin (probation / suspension) — always shown, no toggle
    $st = $pdo->prepare(
        'SELECT kind, message, is_read, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS mins_ago
           FROM user_notices WHERE user_id = ? ORDER BY id DESC LIMIT 10'
    );
    $st->execute([$user['id']]);
    $unread = 0;
    foreach ($st->fetchAll() as $r) {
        if (!$r['is_read']) {
            $unread++;
        }
        $items[] = [
            'kind' => $r['kind'], 'title' => $r['message'], 'mins_ago' => (int)$r['mins_ago'],
            'personal' => true, 'unread' => !$r['is_read'],
        ];
    }

    if (!empty($settings['traffic_alerts'])) {
        $rows = $pdo->query(
            "SELECT title, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS mins_ago
               FROM alerts WHERE is_active = 1 AND type = 'Traffic' ORDER BY id DESC LIMIT 10"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['kind' => 'Traffic', 'title' => $r['title'], 'mins_ago' => (int)$r['mins_ago']];
        }
    }

    if (!empty($settings['weather_alerts'])) {
        $rows = $pdo->query(
            "SELECT title, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS mins_ago
               FROM alerts WHERE is_active = 1 AND type = 'Weather' ORDER BY id DESC LIMIT 10"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['kind' => 'Weather', 'title' => $r['title'], 'mins_ago' => (int)$r['mins_ago']];
        }
    }

    if (!empty($settings['event_reminders'])) {
        $rows = $pdo->query(
            "SELECT title, event_date FROM events
               WHERE event_date >= CURDATE() AND event_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
               ORDER BY event_date ASC LIMIT 10"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['kind' => 'Event', 'title' => $r['title'], 'when' => $r['event_date']];
        }
    }

    if (!empty($settings['research_alerts'])) {
        $rows = $pdo->query(
            "SELECT title, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS mins_ago
               FROM news WHERE tag = 'Research' ORDER BY id DESC LIMIT 10"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['kind' => 'Research', 'title' => $r['title'], 'mins_ago' => (int)$r['mins_ago']];
        }
    }

    json_out(['items' => $items, 'unread' => $unread]);
});
