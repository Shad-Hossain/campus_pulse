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

                <input type="radio" name="roletab" id="tabadmin">
                <label for="tabadmin">Admin</label>
            </div>

            <form class="loginform" action="includes/signup_handler.php" method="POST">
                <input type="hidden" name="role" id="roleinput" value="student">
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

    <script>
        const roleInput = document.getElementById('roleinput');
        document.getElementById('tabstudent').addEventListener('change', () => roleInput.value = 'student');
        document.getElementById('tabfaculty').addEventListener('change', () => roleInput.value = 'faculty');
        document.getElementById('tabadmin').addEventListener('change', () => roleInput.value = 'admin');
    </script>
</body>
</html>