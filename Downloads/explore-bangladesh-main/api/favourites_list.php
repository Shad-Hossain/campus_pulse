<?php
require_once __DIR__ . '/_bootstrap.php';

$userId = require_login();

$stmt = $pdo->prepare(
    "SELECT d.*, c.category_name, c.icon, dist.district_name
     FROM favourites f
     JOIN destinations d ON d.destination_id = f.destination_id
     JOIN categories c ON c.category_id = d.category_id
     JOIN districts dist ON dist.district_id = d.district_id
     WHERE f.user_id = ?
     ORDER BY f.saved_at DESC"
);
$stmt->execute([$userId]);

json_out(['favourites' => $stmt->fetchAll()]);
