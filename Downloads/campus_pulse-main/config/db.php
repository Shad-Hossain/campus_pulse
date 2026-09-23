<?php
// config/db.php — PDO connection (XAMPP/MAMP default: root, no password)
// Apnar machine e password/port alada hole config/db.local.php banan (gitignored):
//   <?php return ['user' => 'root', 'pass' => 'root', 'port' => 8889];

$DB_CONFIG = [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'name'    => 'campus_pulse',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];

if (file_exists(__DIR__ . '/db.local.php')) {
    $DB_CONFIG = array_merge($DB_CONFIG, require __DIR__ . '/db.local.php');
}

function db(): PDO
{
    static $pdo = null;
    global $DB_CONFIG;

    if ($pdo === null) {
        $c   = $DB_CONFIG;
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
        try {
            $pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('DB connection failed: ' . $e->getMessage());
            exit('Database connection failed. config/db.php (ba db.local.php) check korun.');
        }
    }
    return $pdo;
}
