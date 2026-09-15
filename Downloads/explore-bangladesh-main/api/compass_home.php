<?php
/**
 * COMPASS homepage data (additive — used only by index.php).
 * Fills the 8 homepage sections from existing tables. Tolerates the
 * missing optional `deals` table (returns empty list until the user
 * creates it manually).
 */
require_once __DIR__ . '/_bootstrap.php';

$totalDests = (int) $pdo->query("SELECT COUNT(*) c FROM destinations")->fetch()['c'];
$totalDistricts = (int) $pdo->query("SELECT COUNT(DISTINCT district_id) c FROM destinations")->fetch()['c'];

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id")->fetchAll();
$catCounts = [];
foreach ($pdo->query("SELECT category_id, COUNT(*) c FROM destinations GROUP BY category_id") as $row) {
    $catCounts[$row['category_id']] = (int) $row['c'];
}
foreach ($categories as &$cat) { $cat['count'] = $catCounts[$cat['category_id']] ?? 0; }
unset($cat);

// All destinations with their metadata (used by popular/trending/heritage/weather/nearby).
$dests = $pdo->query(
    "SELECT d.destination_id, d.name, d.description, d.entry_fee, d.best_time_to_visit,
            d.latitude, d.longitude, d.category_id, c.category_name, c.icon, dist.district_name, dist.division_id
     FROM destinations d
     JOIN categories c ON c.category_id = d.category_id
     JOIN districts dist ON dist.district_id = d.district_id"
)->fetchAll();

// Per-destination "activity" = number of hotels, restaurants and nearby services.
$activity = [];
foreach ($pdo->query("SELECT destination_id, COUNT(*) c FROM hotels GROUP BY destination_id") as $r) $activity[$r['destination_id']] = ($activity[$r['destination_id']] ?? 0) + (int) $r['c'] * 2;
foreach ($pdo->query("SELECT destination_id, COUNT(*) c FROM restaurants GROUP BY destination_id") as $r) $activity[$r['destination_id']] = ($activity[$r['destination_id']] ?? 0) + (int) $r['c'];
foreach ($pdo->query("SELECT destination_id, COUNT(*) c FROM nearby_services GROUP BY destination_id") as $r) $activity[$r['destination_id']] = ($activity[$r['destination_id']] ?? 0) + (int) $r['c'];
foreach ($pdo->query("SELECT destination_id, COUNT(*) c FROM food_items fi JOIN restaurants r ON r.restaurant_id = fi.restaurant_id GROUP BY destination_id") as $r) $activity[$r['destination_id']] = ($activity[$r['destination_id']] ?? 0) + (int) $r['c'];

foreach ($dests as &$d) {
    $d['description'] = truncateText($d['description'], 90);
    $d['activity']    = $activity[$d['destination_id']] ?? 0;
    $d['weather_score'] = null;
}
unset($d);

$byActivity = function (array $list): array {
    usort($list, fn($a, $b) => $b['activity'] <=> $a['activity']);
    return $list;
};

// 1. Popular destinations — ordered by real on-site activity (stays + dining + services).
$popular = array_slice($byActivity(array_values($dests)), 0, 6);

// 2. Trending this month — in-season first, then on-site activity.
$monthsMap = ['January'=>1,'February'=>2,'March'=>3,'April'=>4,'May'=>5,'June'=>6,'July'=>7,
    'August'=>8,'September'=>9,'October'=>10,'November'=>11,'December'=>12];
$curMonth = (int) date('n');
function seasonScore(string $best, int $curMonth, array $monthsMap): array {
    $txt = strtolower($best);
    if (strpos($txt, 'year-round') !== false) return [100, 'In season now'];
    if (preg_match('/^\s*([a-z]+)\s+to\s+([a-z]+)/i', $best, $m)) {
        $start = $monthsMap[ucfirst(strtolower($m[1]))] ?? 0;
        $end   = $monthsMap[ucfirst(strtolower($m[2]))] ?? 0;
        if ($start && $end) {
            if ($start <= $end)   $inRange = ($curMonth >= $start && $curMonth <= $end);
            else                  $inRange = ($curMonth >= $start || $curMonth <= $end); // year wrap
            if ($inRange) return [100, 'In season now'];
            $next = $start;
            if ($next < $curMonth) $next += 12;
            $monthsAway = $next - $curMonth;
            if ($monthsAway <= 1)  return [70, 'Season starts next month'];
            if ($monthsAway <= 3)  return [45, 'Season starts soon'];
            return [20, 'Off-season'];
        }
    }
    return [40, 'Mixed season'];
}
$trending = array_map(function ($d) use ($curMonth, $monthsMap) {
    [$score, $label] = seasonScore((string) $d['best_time_to_visit'], $curMonth, $monthsMap);
    $d['season_score'] = $score;
    return $d;
}, array_values($dests));
usort($trending, fn($a, $b) =>
    ($b['season_score'] <=> $a['season_score']) ?: ($b['activity'] <=> $a['activity']));
$trending = array_slice($trending, 0, 6);

// 3. Highest rated — hotels + restaurants carry ratings (ratings/reviews tables are empty).
$highestRated = [];
foreach ($pdo->query("SELECT h.hotel_id id, h.hotel_name name, h.rating, h.hotel_type type, h.destination_id, d.name dest_name
                      FROM hotels h JOIN destinations d ON d.destination_id = h.destination_id
                      WHERE h.rating > 0 ORDER BY h.rating DESC LIMIT 4") as $r) {
    $highestRated[] = ['kind' => 'hotel', 'name' => $r['name'], 'rating' => (float) $r['rating'],
        'meta' => $r['type'] . ' · ' . $r['dest_name'], 'link' => '/explore-bangladesh-main/hotel_booking.php?hotel=' . (int) $r['id'],
        'price_hint' => true];
}
foreach ($pdo->query("SELECT r.restaurant_id id, r.restaurant_name name, r.rating, r.destination_id, d.name dest_name
                      FROM restaurants r JOIN destinations d ON d.destination_id = r.destination_id
                      WHERE r.rating > 0 ORDER BY r.rating DESC LIMIT 4") as $r) {
    $highestRated[] = ['kind' => 'food', 'name' => $r['name'], 'rating' => (float) $r['rating'],
        'meta' => 'Restaurant · ' . $r['dest_name'], 'link' => '/explore-bangladesh-main/destination_details.php?id=' . (int) $r['destination_id'],
        'price_hint' => false];
}
usort($highestRated, fn($a, $b) => $b['rating'] <=> $a['rating']);
$highestRated = array_slice($highestRated, 0, 6);

// 4. Recommended for current weather — in-season destinations for this month.
$weatherPicks = [];
foreach ($dests as $d) {
    [$score, $label] = seasonScore((string) $d['best_time_to_visit'], $curMonth, $monthsMap);
    $weatherPicks[] = $d + ['season_score' => $score, 'season_label' => $label];
}
usort($weatherPicks, fn($a, $b) => $b['season_score'] <=> $a['season_score']);
$weatherPicks = array_slice($weatherPicks, 0, 6);
if (file_exists(__DIR__ . '/../config/weather_api_key.php')) {
    require_once __DIR__ . '/../config/weather_api_key.php';
}
$weatherKeySet = defined('OPENWEATHER_API_KEY') && trim((string) OPENWEATHER_API_KEY) !== '';

// 5. Nearby destinations — coordinates returned so the page can sort by GPS distance.
$locations = array_map(fn($d) => [
    'destination_id' => (int) $d['destination_id'], 'name' => $d['name'],
    'latitude' => (float) $d['latitude'], 'longitude' => (float) $d['longitude'],
    'district_name' => $d['district_name'], 'icon' => $d['icon'],
], $dests);

// 6. Heritage & culture — category 3.
$heritage = array_values(array_filter($dests, fn($d) => (int) $d['category_id'] === 3));
$heritage = array_slice($heritage, 0, 6);

// 7. Featured partners — hotels, restaurants and transport operators.
$partners = [];
foreach ($pdo->query("SELECT hotel_id id, hotel_name name, hotel_type type, rating, destination_id FROM hotels WHERE rating > 0 ORDER BY rating DESC LIMIT 3") as $r) {
    $partners[] = ['kind' => '🏨', 'name' => $r['name'], 'meta' => $r['type'] . ' · ⭐ ' . number_format((float) $r['rating'], 1),
        'link' => '/explore-bangladesh-main/hotel_booking.php?hotel=' . (int) $r['id']];
}
foreach ($pdo->query("SELECT restaurant_id id, restaurant_name name, rating, destination_id FROM restaurants WHERE rating > 0 ORDER BY rating DESC LIMIT 3") as $r) {
    $partners[] = ['kind' => '🍽️', 'name' => $r['name'], 'meta' => 'Restaurant · ⭐ ' . number_format((float) $r['rating'], 1),
        'link' => '/explore-bangladesh-main/destination_details.php?id=' . (int) $r['destination_id']];
}
foreach ($pdo->query("SELECT transport_id id, transport_type type, operator_name name FROM transport ORDER BY operator_name ASC LIMIT 4") as $r) {
    $icons = ['Bus' => '🚌', 'Train' => '🚆', 'Flight' => '✈️', 'Launch' => '🚢'];
    $partners[] = ['kind' => $icons[$r['type']] ?? '🚌', 'name' => $r['name'], 'meta' => $r['type'] . ' operator',
        'link' => '/explore-bangladesh-main/ticket_booking.php'];
}
$partners = array_slice($partners, 0, 8);

// 8. Discount deals — reads optional `deals` table if the user created it.
$deals = [];
try {
    $rows = $pdo->query(
        "SELECT d.deal_id, d.title, d.description, d.discount_percent, d.code, d.valid_until,
                dst.name AS dest_name
         FROM deals d
         LEFT JOIN destinations dst ON dst.destination_id = d.destination_id
         WHERE d.is_active = 1 AND (d.valid_until IS NULL OR d.valid_until >= CURDATE())
         ORDER BY d.discount_percent DESC LIMIT 6"
    );
    if ($rows !== false) {
        foreach ($rows as $r) {
            $deals[] = [
                'title' => $r['title'],
                'description' => $r['description'],
                'discount_percent' => (int) $r['discount_percent'],
                'code' => $r['code'],
                'valid_until' => $r['valid_until'],
                'dest_name' => $r['dest_name'],
            ];
        }
    }
} catch (Exception $e) {
    $deals = []; // table not created yet — see the "database to-do" note on the homepage.
}

$divisions = $pdo->query("SELECT * FROM divisions ORDER BY division_name")->fetchAll();

json_out([
    'stats'             => ['destinations' => $totalDests, 'districts' => $totalDistricts],
    'categories'        => $categories,
    'popular'           => $popular,
    'trending'          => $trending,
    'highest_rated'     => $highestRated,
    'weather_picks'     => $weatherPicks,
    'weather_key_set'   => $weatherKeySet,
    'locations'         => $locations,
    'heritage'          => $heritage,
    'partners'          => $partners,
    'deals'             => $deals,
    'divisions'         => $divisions,
]);