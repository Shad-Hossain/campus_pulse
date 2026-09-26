<?php
require_once __DIR__ . '/../includes/helpers.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$error = flash_get('signup_error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGN UP</title>
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
            <h2>Create Account</h2>

            <div class="roletabs">
                <input type="radio" name="roletab" id="tabstudent" checked>
                <label for="tabstudent">Student</label>

                <input type="radio" name="roletab" id="tabfaculty">
                <label for="tabfaculty">Faculty</label>
            </div>

            <form class="loginform" action="auth/signup.php" method="POST">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="role" id="roleinput" value="student">
                <?php if ($error): ?><p class="form-error" style="color:#8b1e1e;margin:0 0 10px;font-size:14px;"><?= e($error) ?></p><?php endif; ?>
                <input type="text" name="fullname" placeholder="Full Name" required>
                <input type="email" name="email" id="emailinput" placeholder="Email (e.g. yourname@uiu.ac.bd)" required>
                <small id="emailhint" style="display:block;margin:-8px 0 10px;color:#6b6b6b;font-size:12px;">Students must use their official UIU email (must end with @uiu.ac.bd)</small>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password (min 8 characters)" required minlength="8">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" class="loginbtn">Sign Up</button>
            </form>

            <p class="signuplink">Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>

   <script>
    const roleInput = document.getElementById('roleinput');
    const emailInput = document.getElementById('emailinput');
    const emailHint = document.getElementById('emailhint');

    document.getElementById('tabstudent').addEventListener('change', () => {
        roleInput.value = 'student';
        emailHint.style.display = 'block';
    });
    document.getElementById('tabfaculty').addEventListener('change', () => {
        roleInput.value = 'faculty';
        emailHint.style.display = 'none';
    });

    // quick client-side checks (PHP verifies again in auth/signup.php, which is the real gate)
    document.querySelector('.loginform').addEventListener('submit', function (e) {
        if (this.password.value !== this.confirm_password.value) {
            e.preventDefault();
            alert('Passwords do not match.');
            return;
        }
        if (roleInput.value === 'student' && !/@([a-z0-9-]+\.)*uiu\.ac\.bd$/i.test(emailInput.value.trim())) {
            e.preventDefault();
            alert('Students must sign up with a valid UIU email (must end with @uiu.ac.bd).');
        }
    });
</script>
</body>
</html>