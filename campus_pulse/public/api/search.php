<?php
// GET ?q=&cat=all|Academic|Admin|Club|Competition  -> mixed news / events / achievements
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    api_user();
    $pdo  = db();
    $q    = trim($_GET['q'] ?? '');
    $cat  = $_GET['cat'] ?? 'all';
    $cats = ['Academic', 'Admin', 'Club', 'Competition'];
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $out  = [];

    // news
    $sql  = 'SELECT title, tag, category FROM news WHERE (title LIKE ? OR IFNULL(body,"") LIKE ? OR IFNULL(tag,"") LIKE ?)';
    $args = [$like, $like, $like];
    if (in_array($cat, $cats, true)) {
        $sql .= ' AND category = ?';
        $args[] = $cat;
    }
    $st = $pdo->prepare($sql . ' ORDER BY id DESC LIMIT 20');
    $st->execute($args);
    foreach ($st->fetchAll() as $r) {
        $out[] = ['title' => $r['title'], 'meta' => 'News · ' . ($r['tag'] ?: $r['category'])];
    }

    // events
    $sql  = 'SELECT title, category, venue FROM events WHERE (title LIKE ? OR IFNULL(description,"") LIKE ? OR IFNULL(venue,"") LIKE ?)';
    $args = [$like, $like, $like];
    if (in_array($cat, $cats, true)) {
        $sql .= ' AND category = ?';
        $args[] = $cat;
    }
    $st = $pdo->prepare($sql . ' ORDER BY event_date DESC LIMIT 20');
    $st->execute($args);
    foreach ($st->fetchAll() as $r) {
        $out[] = ['title' => $r['title'], 'meta' => 'Event · ' . $r['category'] . ($r['venue'] ? ' · ' . $r['venue'] : '')];
    }

    // achievements have no category, so only when "all"
    if (!in_array($cat, $cats, true)) {
        $st = $pdo->prepare('SELECT title FROM achievements WHERE title LIKE ? OR IFNULL(description,"") LIKE ? ORDER BY id DESC LIMIT 20');
        $st->execute([$like, $like]);
        foreach ($st->fetchAll() as $r) {
            $out[] = ['title' => $r['title'], 'meta' => 'Achievement'];
        }
    }

    json_out(['items' => $out]);
});
