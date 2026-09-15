<?php
require_once __DIR__ . '/_bootstrap.php';

unset($_SESSION['user_id'], $_SESSION['user_name']);

json_out(['success' => true]);
