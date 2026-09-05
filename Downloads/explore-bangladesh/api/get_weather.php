<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../config/weather_api.php';

$destinationId = isset($_GET['destination_id']) ? (int) $_GET['destination_id'] : 0;
$travelDate    = $_GET['travel_date'] ?? null;

if (!$destinationId) {
    json_out(['error' => 'destination_id is required'], 422);
}

$stmt = $pdo->prepare("SELECT destination_id, name, latitude, longitude FROM destinations WHERE destination_id = ?");
$stmt->execute([$destinationId]);
$dest = $stmt->fetch();

if (!$dest) {
    json_out(['error' => 'Destination not found'], 404);
}

$forecast = getForecastForDestination($pdo, $destinationId, (float) $dest['latitude'], (float) $dest['longitude']);

$advice = ['found' => false];
if ($travelDate) {
    $advice = getTravelAdvice($pdo, $destinationId, (float) $dest['latitude'], (float) $dest['longitude'], $travelDate);
}

json_out([
    'destination' => $dest['name'],
    'forecast'    => $forecast,
    'advice'      => $advice,
]);
