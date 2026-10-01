<?php
// GET  ?token=...  -> show new-password form (if token valid & not expired)
// POST             -> set the new password, delete the token, send back to login
require_once __DIR__ . '/../../includes/helpers.php';

function find_valid_reset(string $token): ?array
{
    $stmt = db()->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    return $row ?: null;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $reset = find_valid_reset($token);

    if (!csrf_valid($_POST['csrf'] ?? null) || !$reset) {
        $error = 'This reset link is invalid or has expired. Request a new one.';
    } else {
        $p1 = $_POST['password'] ?? '';
        $p2 = $_POST['password2'] ?? '';
        if (strlen($p1) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($p1 !== $p2) {
            $error = 'Passwords do not match.';
        } else {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($p1, PASSWORD_DEFAULT), $reset['user_id']]);
            db()->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$reset['user_id']]);
            flash_set('login_notice', 'Password updated. You can log in now.');
            header('Location: ../login.php');
            exit;
        }
    }
} else {
    $token = $_GET['token'] ?? '';
    if (!find_valid_reset($token)) {
        $error = 'This reset link is invalid or has expired. Request a new one.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password — Campus_pulse</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="landingscreen">
        <div class="greenside">
            <div class="greensidehp">
                <h2 class="greensideh2">CP</h2>
                <p><b>"CAMPUS PULSE . EST . UIU"</b></p>
            </div>
        </div>
        <div class="beigeside">
            <h2>Set new password</h2>

            <?php if ($error && !find_valid_reset($token)): ?>
                <p class="form-error" style="color:#8b1e1e;font-size:14px;max-width:300px;text-align:center;"><?= e($error) ?></p>
                <p class="signuplink"><a href="forgot-password.php">Request a new link</a></p>
            <?php else: ?>
                <form class="loginform" method="POST">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <?php if ($error): ?><p class="form-error" style="color:#8b1e1e;margin:0 0 10px;font-size:14px;"><?= e($error) ?></p><?php endif; ?>
                    <input type="password" name="password" placeholder="New password (min 8 chars)" required minlength="8">
                    <input type="password" name="password2" placeholder="Confirm new password" required minlength="8">
                    <button type="submit" class="loginbtn">Update password</button>
                </form>
            <?php endif; ?>

            <p class="signuplink"><a href="../login.php">Back to login</a></p>
        </div>
    </div>
</body>
</html>
