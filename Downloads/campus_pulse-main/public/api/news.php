<?php
// GET  ?cat=Academic|Admin|Club|Competition   (any logged-in user)
// POST title, body, tag, category             (admin only)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo  = db();
    $cats = ['Academic', 'Admin', 'Club', 'Competition'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        api_user();
        $cat = $_GET['cat'] ?? 'all';
        if (in_array($cat, $cats, true)) {
            $st = $pdo->prepare('SELECT id, title, body, tag, category, created_at FROM news WHERE category = ? ORDER BY id DESC LIMIT 50');
            $st->execute([$cat]);
        } else {
            $st = $pdo->query('SELECT id, title, body, tag, category, created_at FROM news ORDER BY id DESC LIMIT 50');
        }
        json_out(['items' => $st->fetchAll()]);
    }

    $user  = api_user(['admin']);
    $in    = api_post_body();
    $title = clean_str($in['title'] ?? '', 200);
    $cat   = $in['category'] ?? 'Academic';
    if ($title === '' || !in_array($cat, $cats, true)) {
        json_out(['error' => 'Title and valid category required.'], 422);
    }
    $pdo->prepare('INSERT INTO news (title, body, tag, category, posted_by) VALUES (?,?,?,?,?)')
        ->execute([$title, clean_str($in['body'] ?? '', 5000), clean_str($in['tag'] ?? '', 50), $cat, $user['id']]);
    json_out(['ok' => true], 201);
});
