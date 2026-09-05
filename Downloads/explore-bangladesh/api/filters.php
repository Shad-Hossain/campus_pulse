<?php
require_once __DIR__ . '/_bootstrap.php';

json_out([
    'categories' => $pdo->query("SELECT * FROM categories ORDER BY category_id")->fetchAll(),
    'divisions'  => $pdo->query("SELECT * FROM divisions ORDER BY division_name")->fetchAll(),
]);
