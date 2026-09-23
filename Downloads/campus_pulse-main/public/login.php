<?php
require_once __DIR__ . '/../includes/helpers.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$error = flash_get('login_error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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
            <h2>Welcome Back!</h2>

            <div class="roletabs">
                <input type="radio" name="roletab" id="tabstudent" checked>
                <label for="tabstudent">Student</label>

                <input type="radio" name="roletab" id="tabfaculty">
                <label for="tabfaculty">Faculty</label>

                <input type="radio" name="roletab" id="tabadmin">
                <label for="tabadmin">Admin</label>
            </div>

            <form class="loginform" action="auth/login.php" method="POST">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="role" id="roleinput" value="student">
                <?php if ($error): ?><p class="form-error" style="color:#8b1e1e;margin:0 0 10px;font-size:14px;"><?= e($error) ?></p><?php endif; ?>
                <input type="text" name="username" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="loginbtn">Login</button>
            </form>

            <p class="signuplink">Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>

    <script>
    // role tabs only set the hidden "role" input; login itself is verified by PHP (auth/login.php)
    const roleInput = document.getElementById('roleinput');
    document.getElementById('tabstudent').addEventListener('change', () => roleInput.value = 'student');
    document.getElementById('tabfaculty').addEventListener('change', () => roleInput.value = 'faculty');
    document.getElementById('tabadmin').addEventListener('change', () => roleInput.value = 'admin');
</script>
</body>
</html>