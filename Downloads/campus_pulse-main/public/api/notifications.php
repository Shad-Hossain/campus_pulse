<?php
// GET -> { items:[{title, meta, kind}] }
// Built from: active Traffic/Weather alerts, events in the next 7 days, and Research-tagged news —
// each category only appears if the user has that toggle switched on in Profile > Notification settings.
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo  = db();
    $user = api_user();

    $st = $pdo->prepare('SELECT traffic_alerts, weather_alerts, event_reminders, research_alerts FROM notification_settings WHERE user_id = ?');
    $st->execute([$user['id']]);
    $settings = $st->fetch() ?: [];

    $items = [];

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

    json_out(['items' => $items]);
});
