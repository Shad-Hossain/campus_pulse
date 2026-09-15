<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../config/weather_api.php';

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$divisionId = isset($_GET['division']) ? (int) $_GET['division'] : 0;
$search     = trim($_GET['q'] ?? '');

$sql = "SELECT d.*, c.category_name, c.icon, dist.district_name, dist.division_id
        FROM destinations d
        JOIN categories c ON c.category_id = d.category_id
        JOIN districts dist ON dist.district_id = d.district_id
        WHERE 1=1";
$params = [];

if ($categoryId) { $sql .= " AND d.category_id = :cat"; $params['cat'] = $categoryId; }
if ($divisionId) { $sql .= " AND dist.division_id = :div"; $params['div'] = $divisionId; }
if ($search !== '') { $sql .= " AND d.name LIKE :q"; $params['q'] = "%$search%"; }

$sql .= " ORDER BY d.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();

foreach ($destinations as &$d) {
    $d['description'] = truncateText($d['description'], 90);
    $d['weather_score'] = null;
    if ($d['latitude'] && $d['longitude']) {
        $cached = getCachedForecast($pdo, (int) $d['destination_id']);
        if (!empty($cached)) {
            $d['weather_score'] = (int) $cached[0]['weather_score'];
            $d['weather_score_class'] = weatherScoreLabel($d['weather_score'])['class'];
        }
    }
}
unset($d);

json_out(['destinations' => $destinations]);
