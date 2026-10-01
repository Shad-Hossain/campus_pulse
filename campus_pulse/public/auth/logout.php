<?php
require_once __DIR__ . '/../../includes/helpers.php';

// POST + CSRF so a random <img src="logout.php"> can't log people out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null))) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
