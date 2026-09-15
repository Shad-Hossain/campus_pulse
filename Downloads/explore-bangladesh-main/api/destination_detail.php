<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../config/weather_api.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
    "SELECT d.*, c.category_name, c.icon, dist.district_name, dv.division_name
     FROM destinations d
     JOIN categories c ON c.category_id = d.category_id
     JOIN districts dist ON dist.district_id = d.district_id
     JOIN divisions dv ON dv.division_id = dist.division_id
     WHERE d.destination_id = ?"
);
$stmt->execute([$id]);
$dest = $stmt->fetch();

if (!$dest) {
    json_out(['error' => 'Destination not found'], 404);
}

$forecast = [];
if ($dest['latitude'] && $dest['longitude']) {
    $forecast = getForecastForDestination($pdo, $id, (float) $dest['latitude'], (float) $dest['longitude']);
    foreach ($forecast as &$day) {
        $day['score_class'] = weatherScoreLabel((int) $day['weather_score'])['class'];
    }
    unset($day);
}

$attractions = $pdo->prepare("SELECT * FROM attractions WHERE destination_id = ?");
$attractions->execute([$id]);
$attractions = $attractions->fetchAll();

$routes = $pdo->prepare(
    "SELECT r.*, t.transport_type, t.operator_name FROM transport_routes r
     JOIN transport t ON t.transport_id = r.transport_id
     WHERE r.destination_id = ?"
);
$routes->execute([$id]);
$routes = $routes->fetchAll();

$hotels = $pdo->prepare("SELECT * FROM hotels WHERE destination_id = ? ORDER BY rating DESC");
$hotels->execute([$id]);
$hotels = $hotels->fetchAll();

$services = $pdo->prepare("SELECT * FROM nearby_services WHERE destination_id = ?");
$services->execute([$id]);
$services = $services->fetchAll();

$avgRating = $pdo->prepare("SELECT ROUND(AVG(stars),1) avg_stars, COUNT(*) total FROM ratings WHERE destination_id = ?");
$avgRating->execute([$id]);
$avgRating = $avgRating->fetch();

$reviews = $pdo->prepare(
    "SELECT rv.review_text, rv.created_at, rv.is_verified, u.full_name FROM reviews rv
     JOIN users u ON u.user_id = rv.user_id
     WHERE rv.destination_id = ? ORDER BY rv.created_at DESC LIMIT 10"
);
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

json_out([
    'destination'  => $dest,
    'forecast'     => $forecast,
    'attractions'  => $attractions,
    'routes'       => $routes,
    'hotels'       => $hotels,
    'services'     => $services,
    'avg_rating'   => $avgRating,
    'reviews'      => $reviews,
    'logged_in'    => !empty($_SESSION['user_id']),
]);
