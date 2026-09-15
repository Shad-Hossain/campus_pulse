<?php
require_once __DIR__ . '/_bootstrap.php';

$userId = require_login();
$input = json_input();

$destinationId = (int) ($input['destination_id'] ?? 0);
$stars = max(1, min(5, (int) ($input['stars'] ?? 0)));
$text = trim($input['review_text'] ?? '');

if (!$destinationId || !$stars) {
    json_out(['success' => false, 'error' => 'A destination and a star rating are required.'], 422);
}

$ratingStmt = $pdo->prepare(
    "INSERT INTO ratings (user_id, destination_id, stars) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE stars = VALUES(stars)"
);
$ratingStmt->execute([$userId, $destinationId, $stars]);

if ($text !== '') {
    $reviewStmt = $pdo->prepare("INSERT INTO reviews (user_id, destination_id, review_text) VALUES (?, ?, ?)");
    $reviewStmt->execute([$userId, $destinationId, $text]);
}

json_out(['success' => true]);
