<?php
session_start();

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$name = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Campus_pulse</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="dash-body">

    <header class="dash-nav">
        <div class="dash-brand">
            <span class="dash-logo">CP</span>
            <span>Campus_pulse</span>
        </div>
        <div class="dash-profile">
            <span>Hi, <?php echo htmlspecialchars($name); ?> (<?php echo ucfirst($role); ?>)</span>
            <a href="includes/logout.php" class="dash-logout">Logout</a>
        </div>
    </header>

    <div class="dash-ticker">
        <span class="dash-ticker-badge">LIVE</span>
        <marquee behavior="scroll" scrollamount="4">Midterm routine published · Robotics Club recruitment open · Library extended hours during exam week</marquee>
    </div>

    <main class="dash-main">

        <?php if ($role === 'student'): ?>

            <section class="dash-welcome">
                <h1>Welcome back, <?php echo htmlspecialchars($name); ?></h1>
                <p>Here's what's happening around UIU today.</p>
            </section>

            <section class="dash-grid">
                <div class="dash-card">
                    <h3>📢 Latest News</h3>
                    <ul class="dash-list">
                        <li>Spring 2027 registration opens Sept 15</li>
                        <li>New research grant call for CSE dept</li>
                    </ul>
                </div>
                <div class="dash-card">
                    <h3>🎉 Upcoming Events</h3>
                    <ul class="dash-list">
                        <li>Tech Fest 2026 — Sept 20</li>
                        <li>Career Fair — Oct 2</li>
                    </ul>
                </div>
                <div class="dash-card">
                    <h3>🏆 Achievements</h3>
                    <ul class="dash-list">
                        <li>UIU team wins national hackathon</li>
                    </ul>
                </div>
                <div class="dash-card dash-card-wide">
                    <h3>📚 Resources Hub</h3>
                    <div class="dash-resource-links">
                        <a href="#">Class Routine</a>
                        <a href="#">Notes Bank</a>
                        <a href="#">Previous Year Questions</a>
                    </div>
                </div>
            </section>

        <?php elseif ($role === 'faculty'): ?>

            <section class="dash-welcome">
                <h1>Welcome, Dr. <?php echo htmlspecialchars($name); ?></h1>
                <p>Manage your courses and student activity.</p>
            </section>

            <section class="dash-grid">
                <div class="dash-card">
                    <h3>📘 My Courses</h3>
                    <ul class="dash-list">
                        <li>CSE 4165 — Web Programming</li>
                        <li>CSE 3521 — Database Systems</li>
                    </ul>
                </div>
                <div class="dash-card">
                    <h3>📝 Pending Approvals</h3>
                    <ul class="dash-list">
                        <li>3 student notes awaiting review</li>
                        <li>1 research grant submission</li>
                    </ul>
                </div>
                <div class="dash-card dash-card-wide">
                    <h3>📊 Attendance Overview</h3>
                    <p style="font-size:14px;color:#555;">Attendance summary chart / table goes here.</p>
                </div>
            </section>

        <?php elseif ($role === 'admin'): ?>

            <section class="dash-welcome">
                <h1>Admin Panel</h1>
                <p>Platform moderation and management.</p>
            </section>

            <section class="dash-grid">
                <div class="dash-card">
                    <h3>👥 User Management</h3>
                    <ul class="dash-list">
                        <li>128 students registered</li>
                        <li>14 faculty accounts</li>
                    </ul>
                </div>
                <div class="dash-card">
                    <h3>🛡️ Content Moderation</h3>
                    <ul class="dash-list">
                        <li>5 posts awaiting approval</li>
                        <li>2 reported items</li>
                    </ul>
                </div>
                <div class="dash-card dash-card-wide">
                    <h3>📈 Site Activity</h3>
                    <p style="font-size:14px;color:#555;">Traffic / usage stats go here.</p>
                </div>
            </section>

        <?php else: ?>
            <p>Unknown role. Please <a href="login.php">log in again</a>.</p>
        <?php endif; ?>

    </main>

    <button class="dash-sos" title="Emergency SOS">SOS</button>

</body>
</html>