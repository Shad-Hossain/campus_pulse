<?php
// GET  -> profile + notification settings
// POST {full_name, bio}  and/or  {settings:{traffic_alerts, weather_alerts, event_reminders, research_alerts}}
//      and/or  {current_password, new_password}         (change password)
//      and/or  multipart avatar file                     (change profile picture)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo  = db();
    $user = api_user();
    $keys = ['traffic_alerts', 'weather_alerts', 'event_reminders', 'research_alerts'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $st = $pdo->prepare('SELECT full_name, username, email, role, department, bio, avatar FROM users WHERE id = ?');
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

    // change password
    if (isset($in['new_password']) && $in['new_password'] !== '') {
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $st->execute([$user['id']]);
        $row = $st->fetch();
        if (!$row || !password_verify($in['current_password'] ?? '', $row['password_hash'])) {
            json_out(['error' => 'Current password is incorrect.'], 422);
        }
        if (strlen($in['new_password']) < 8) {
            json_out(['error' => 'New password must be at least 8 characters.'], 422);
        }
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($in['new_password'], PASSWORD_DEFAULT), $user['id']]);
    }

    // change profile picture
    $avatarUrl = null;
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['avatar'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            json_out(['error' => 'Upload failed (file too large? check upload_max_filesize in php.ini).'], 422);
        }
        if ($f['size'] > 5 * 1024 * 1024) {
            json_out(['error' => 'Image must be 5 MB or smaller.'], 422);
        }
        $ext     = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            json_out(['error' => 'Allowed image types: ' . implode(', ', $allowed)], 422);
        }
        $dir = __DIR__ . '/../uploads/avatars';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = 'u' . $user['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;   // random name: never trust the original one
        if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) {
            json_out(['error' => 'Could not save the image.'], 500);
        }
        $avatarUrl = 'uploads/avatars/' . $name;
        $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$avatarUrl, $user['id']]);
        $_SESSION['user']['avatar'] = $avatarUrl;
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
    json_out(['ok' => true, 'name' => $_SESSION['user']['name'], 'avatar' => $avatarUrl]);
});
