<?php
require_once __DIR__ . '/_bootstrap.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id")->fetchAll();

$categoryCounts = [];
foreach ($pdo->query("SELECT category_id, COUNT(*) c FROM destinations GROUP BY category_id") as $row) {
    $categoryCounts[$row['category_id']] = (int) $row['c'];
}
foreach ($categories as &$cat) {
    $cat['count'] = $categoryCounts[$cat['category_id']] ?? 0;
}
unset($cat);

$featured = $pdo->query(
    "SELECT d.*, c.category_name, c.icon, dist.district_name
     FROM destinations d
     JOIN categories c ON c.category_id = d.category_id
     JOIN districts dist ON dist.district_id = d.district_id
     ORDER BY d.destination_id ASC LIMIT 6"
)->fetchAll();

foreach ($featured as &$d) {
    $d['description'] = truncateText($d['description'], 100);
}
unset($d);

json_out([
    'categories'         => $categories,
    'featured'           => $featured,
    'total_destinations' => (int) $pdo->query("SELECT COUNT(*) c FROM destinations")->fetch()['c'],
    'total_districts'    => (int) $pdo->query("SELECT COUNT(DISTINCT district_id) c FROM destinations")->fetch()['c'],
]);
