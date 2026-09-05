<?php
require_once __DIR__ . '/_bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    json_out([
        'logged_in' => true,
        'user_id'   => (int) $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'] ?? 'Traveler',
    ]);
}

json_out(['logged_in' => false]);
