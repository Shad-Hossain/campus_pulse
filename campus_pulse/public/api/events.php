<?php
// GET  ?cat=Academic|Club|Competition          (any logged-in user)
// POST title, category, event_date, venue      (faculty / admin)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo  = db();
    $cats = ['Academic', 'Club', 'Competition'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        api_user();
        $cat  = $_GET['cat'] ?? 'all';
        $sql  = 'SELECT id, title, category, event_date, venue, description FROM events';
        $args = [];
        if (in_array($cat, $cats, true)) {
            $sql .= ' WHERE category = ?';
            $args[] = $cat;
        }
        // upcoming first (soonest), then past events (newest)
        $sql .= ' ORDER BY (event_date >= CURDATE()) DESC, event_date ASC LIMIT 50';
        $st = $pdo->prepare($sql);
        $st->execute($args);
        json_out(['items' => $st->fetchAll()]);
    }

    $user  = api_user(['faculty', 'admin']);
    $in    = api_post_body();
    $title = clean_str($in['title'] ?? '', 200);
    $cat   = $in['category'] ?? '';
    $date  = clean_str($in['event_date'] ?? '', 10);

    if ($title === '' || !in_array($cat, $cats, true)) {
        json_out(['error' => 'Title and category are required.'], 422);
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        json_out(['error' => 'Pick a valid date.'], 422);
    }
    $pdo->prepare('INSERT INTO events (title, category, event_date, venue, description, created_by) VALUES (?,?,?,?,?,?)')
        ->execute([$title, $cat, $date, clean_str($in['venue'] ?? '', 150), clean_str($in['description'] ?? '', 2000), $user['id']]);
    json_out(['ok' => true], 201);
});
