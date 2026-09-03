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

            <form class="loginform" action="" method="POST">
                <input type="text" name="fullname" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" class="loginbtn">Sign Up</button>
            </form>

            <p class="signuplink">Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>
</body>
</html>