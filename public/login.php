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

            <form class="loginform" action="includes/login_handler.php" method="POST">
                <input type="hidden" name="role" id="roleinput" value="student">
                <input type="text" name="username" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="loginbtn">Login</button>
            </form>

            <p class="signuplink">Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>

    <script>
    // ---- Mock user database (demo only) ----
    const mockUsers = [
        { username: "student1", password: "1234", role: "student", name: "Shad" },
        { username: "faculty1", password: "1234", role: "faculty", name: "Dr. Farhana" },
        { username: "admin1",   password: "1234", role: "admin",   name: "Admin" }
    ];

    const roleInput = document.getElementById('roleinput');
    document.getElementById('tabstudent').addEventListener('change', () => roleInput.value = 'student');
    document.getElementById('tabfaculty').addEventListener('change', () => roleInput.value = 'faculty');
    document.getElementById('tabadmin').addEventListener('change', () => roleInput.value = 'admin');

    // ---- Handle login form submit ----
    document.querySelector('.loginform').addEventListener('submit', function(e){
        e.preventDefault(); // stop normal form submit (no PHP backend yet)

        const username = this.username.value.trim();
        const password = this.password.value;
        const role = roleInput.value;

        // find a matching mock user
        const found = mockUsers.find(u =>
            u.username === username && u.password === password && u.role === role
        );

        if (found) {
            localStorage.setItem('campus_pulse_user', JSON.stringify(found));
            window.location.href = "dashboard.php";
        } else {
            alert("Invalid username, password, or role selected.");
        }
    });

    // ---- Already logged in? skip login page ----
    if (localStorage.getItem('campus_pulse_user')) {
        window.location.href = "dashboard.php";
    }
</script>
</body>
</html>