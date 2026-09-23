<?php
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}
if (!csrf_valid($_POST['csrf'] ?? null)) {
    flash_set('login_error', 'Session expired. Try again.');
    header('Location: ../login.php');
    exit;
}

$identity = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'student';

$stmt = db()->prepare('SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
$stmt->execute([$identity, $identity]);
$user = $stmt->fetch();

// same message for every failure (don't leak which part was wrong)
if (!$user || !password_verify($password, $user['password_hash']) || $user['role'] !== $role) {
    flash_set('login_error', 'Invalid username, password, or role selected.');
    header('Location: ../login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id'         => (int)$user['id'],
    'name'       => $user['full_name'],
    'username'   => $user['username'],
    'email'      => $user['email'],
    'role'       => $user['role'],
    'department' => $user['department'],
];
header('Location: ../dashboard.php');
exit;
