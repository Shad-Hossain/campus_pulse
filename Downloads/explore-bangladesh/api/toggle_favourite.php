<?php
require_once __DIR__ . '/_bootstrap.php';

$userId = require_login();

$input = json_input();
$destinationId = (int) ($input['destination_id'] ?? 0);

if (!$destinationId) {
    json_out(['error' => 'destination_id is required'], 422);
}

$check = $pdo->prepare("SELECT favourite_id FROM favourites WHERE user_id = ? AND destination_id = ?");
$check->execute([$userId, $destinationId]);
$existing = $check->fetch();

if ($existing) {
    $del = $pdo->prepare("DELETE FROM favourites WHERE favourite_id = ?");
    $del->execute([$existing['favourite_id']]);
    json_out(['status' => 'ok', 'is_favourite' => false]);
} else {
    $ins = $pdo->prepare("INSERT INTO favourites (user_id, destination_id) VALUES (?, ?)");
    $ins->execute([$userId, $destinationId]);
    json_out(['status' => 'ok', 'is_favourite' => true]);
}
