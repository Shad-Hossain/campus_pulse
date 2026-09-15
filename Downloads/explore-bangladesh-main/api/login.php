<?php
require_once __DIR__ . '/_bootstrap.php';

$input = json_input();
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    json_out(['success' => false, 'error' => 'Email and password are required.'], 422);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    json_out(['success' => false, 'error' => 'Invalid email or password.'], 401);
}

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_name'] = $user['full_name'];

json_out(['success' => true, 'user_name' => $user['full_name']]);
