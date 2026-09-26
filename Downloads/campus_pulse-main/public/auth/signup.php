<?php
require_once __DIR__ . '/../../includes/helpers.php';

function back(string $msg)
{
    flash_set('signup_error', $msg);
    header('Location: ../signup.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit;
}
if (!csrf_valid($_POST['csrf'] ?? null)) {
    back('Session expired. Try again.');
}

$fullName = clean_str($_POST['fullname'] ?? '', 120);
$email    = clean_str($_POST['email'] ?? '', 150);
$username = clean_str($_POST['username'] ?? '', 50);
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role     = $_POST['role'] ?? 'student';

// SECURITY: public signup can never create an admin. Admin accounts are created in the DB only.
if (!in_array($role, ['student', 'faculty'], true)) {
    back('Only student and faculty accounts can be created here.');
}
if ($fullName === '' || $username === '' || $email === '') {
    back('All fields are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back('Enter a valid email address.');
}
// Students must sign up with their official UIU email (e.g. name@uiu.ac.bd)
if ($role === 'student' && !preg_match('/@([a-z0-9-]+\.)*uiu\.ac\.bd$/i', $email)) {
    back('Students must sign up with a valid UIU email (must end with @uiu.ac.bd).');
}
if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
    back('Username: 3-30 characters, letters/numbers/_/. only.');
}
if (strlen($password) < 8) {
    back('Password must be at least 8 characters.');
}
if ($password !== $confirm) {
    back('Passwords do not match.');
}

$pdo = db();
try {
    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO users (full_name, username, email, password_hash, role) VALUES (?,?,?,?,?)')
        ->execute([$fullName, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    $id = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO notification_settings (user_id) VALUES (?)')->execute([$id]);
    $pdo->commit();
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($ex->getCode() === '23000') {
        back('Username or email already registered.');
    }
    error_log($ex);
    // --- TEMP DEBUG ---
    // Real message ta ekhane dekhabo (production e r r safe na, kaj hoye gele
    // ei line ta abar 'Something went wrong. Try again.' diye replace kore dio):
    back('DB error: ' . $ex->getMessage());
}

// auto login
session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => $id, 'name' => $fullName, 'username' => $username,
    'email' => $email, 'role' => $role, 'department' => null,
];
header('Location: ../dashboard.php');
exit;