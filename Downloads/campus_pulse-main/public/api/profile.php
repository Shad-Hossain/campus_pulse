<?php
// GET  -> profile + notification settings
// POST {full_name, bio}  and/or  {settings:{traffic_alerts, weather_alerts, event_reminders, research_alerts}}
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo  = db();
    $user = api_user();
    $keys = ['traffic_alerts', 'weather_alerts', 'event_reminders', 'research_alerts'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $st = $pdo->prepare('SELECT full_name, username, email, role, department, bio FROM users WHERE id = ?');
        $st->execute([$user['id']]);
        $profile = $st->fetch();

        $st = $pdo->prepare('SELECT traffic_alerts, weather_alerts, event_reminders, research_alerts FROM notification_settings WHERE user_id = ?');
        $st->execute([$user['id']]);
        $settings = $st->fetch() ?: array_fill_keys($keys, 0);
        foreach ($settings as $k => $v) {
            $settings[$k] = (int)$v;
        }
        json_out(['profile' => $profile, 'settings' => $settings]);
    }

    $in = api_post_body();

    if (isset($in['full_name'])) {
        $name = clean_str($in['full_name'], 120);
        if ($name === '') {
            json_out(['error' => 'Name cannot be empty.'], 422);
        }
        $pdo->prepare('UPDATE users SET full_name = ?, bio = ? WHERE id = ?')
            ->execute([$name, clean_str($in['bio'] ?? '', 1000), $user['id']]);
        $_SESSION['user']['name'] = $name;
    }

    if (isset($in['settings']) && is_array($in['settings'])) {
        $vals = [];
        foreach ($keys as $k) {
            $vals[$k] = empty($in['settings'][$k]) ? 0 : 1;
        }
        // upsert (old users may not have a row yet)
        $pdo->prepare(
            'INSERT INTO notification_settings (user_id, traffic_alerts, weather_alerts, event_reminders, research_alerts)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE traffic_alerts = VALUES(traffic_alerts), weather_alerts = VALUES(weather_alerts),
                                     event_reminders = VALUES(event_reminders), research_alerts = VALUES(research_alerts)'
        )->execute([$user['id'], $vals['traffic_alerts'], $vals['weather_alerts'], $vals['event_reminders'], $vals['research_alerts']]);
    }
    json_out(['ok' => true, 'name' => $_SESSION['user']['name']]);
});
