<?php
// GET                                          (any logged-in user)
// POST title, description, achieved_on         (faculty / admin)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        api_user();
        $rows = $pdo->query(
            'SELECT a.id, a.title, a.description, a.achieved_on, u.full_name AS person
               FROM achievements a LEFT JOIN users u ON u.id = a.related_user_id
              ORDER BY a.achieved_on DESC, a.id DESC LIMIT 50'
        )->fetchAll();
        json_out(['items' => $rows]);
    }

    $user  = api_user(['faculty', 'admin']);
    $in    = api_post_body();
    $title = clean_str($in['title'] ?? '', 200);
    $date  = clean_str($in['achieved_on'] ?? '', 10);
    if ($title === '') {
        json_out(['error' => 'Title is required.'], 422);
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    $date = ($d && $d->format('Y-m-d') === $date) ? $date : null;
    $pdo->prepare('INSERT INTO achievements (title, description, achieved_on, posted_by) VALUES (?,?,?,?)')
        ->execute([$title, clean_str($in['description'] ?? '', 2000), $date, $user['id']]);
    json_out(['ok' => true], 201);
});
