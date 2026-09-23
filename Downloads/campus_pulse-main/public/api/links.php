<?php
// GET -> quick links for Study Hub
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    api_user();
    $rows = db()->query('SELECT id, title, url FROM quick_links ORDER BY sort_order, id')->fetchAll();
    json_out(['items' => $rows]);
});
