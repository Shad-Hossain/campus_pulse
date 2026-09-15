<?php
require_once __DIR__ . '/_bootstrap.php';

$input = json_input();
$name = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

if (!$name || !$email || strlen($password) < 6) {
    json_out(['success' => false, 'error' => 'Please fill all fields — password must be at least 6 characters.'], 422);
}

$check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    json_out(['success' => false, 'error' => 'That email is already registered.'], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $pdo->prepare("INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
$ins->execute([$name, $email, $hash, $phone]);

$_SESSION['user_id'] = $pdo->lastInsertId();
$_SESSION['user_name'] = $name;

json_out(['success' => true, 'user_name' => $name]);
