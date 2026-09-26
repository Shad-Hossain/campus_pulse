<?php
// GET  -> show the "enter your username or email" form
// POST -> generate a reset token and show the reset link
// NOTE: this demo has no mail server wired up, so the reset link is shown directly
//       on screen instead of being emailed. Wire up a real mailer before deploying.
require_once __DIR__ . '/../../includes/helpers.php';

$resetLink = null;
$error     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Session expired. Try again.';
    } else {
        $identity = trim($_POST['identity'] ?? '');
        $stmt = db()->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1');
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            db()->prepare(
                'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
                 ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)'
            )->execute([$user['id'], $token]);

            $resetLink = 'reset-password.php?token=' . $token;
        } else {
            // same message whether the account exists or not — don't leak which accounts are real
            $error = 'If that account exists, a reset link has been generated below.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password — Campus_pulse</title>
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
            <h2>Reset password</h2>

            <?php if ($resetLink): ?>
                <div style="width:300px;text-align:center;">
                    <p style="font-size:15px;color:#2E7D32;font-weight:600;">Reset link generated.</p>
                    <p style="font-size:13px;color:#444;margin-top:8px;">This demo has no email server configured, so here is your link directly (valid 1 hour):</p>
                    <p style="margin-top:12px;word-break:break-all;"><a href="<?= e($resetLink) ?>" style="color:#2E7D32;font-weight:600;"><?= e($resetLink) ?></a></p>
                </div>
            <?php else: ?>
                <form class="loginform" method="POST">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <?php if ($error): ?><p class="form-error" style="color:#8b1e1e;margin:0 0 10px;font-size:14px;"><?= e($error) ?></p><?php endif; ?>
                    <input type="text" name="identity" placeholder="Username or Email" required>
                    <button type="submit" class="loginbtn">Send reset link</button>
                </form>
            <?php endif; ?>

            <p class="signuplink"><a href="../login.php">Back to login</a></p>
        </div>
    </div>
</body>
</html>
