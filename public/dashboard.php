<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Campus_pulse</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="dash-body">

    <div class="app-shell">
        <aside class="sidebar">
            <div class="side-brand">
                <span class="dash-logo">CP</span>
                <span>Campus_pulse</span>
            </div>
            <span class="role-tag" id="role-tag">Student</span>

            <nav class="side-nav" id="side-nav"></nav>

            <div class="side-foot">
                Signed in as <b id="side-name">User</b>
                <button id="logout-btn">Sign out</button>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <h1 id="view-title">Home feed</h1>
            </div>

            <div class="dash-ticker">
                <span class="dash-ticker-badge">LIVE</span>
                <span>Midterm routine published · Robotics Club recruitment open · Library extended hours during exam week</span>
            </div>

            <!-- HOME -->
            <section class="view active" id="view-home" data-title="Home feed">
                <div class="section-title">Campus news</div>
                <div class="dash-grid" id="news-grid"></div>
            </section>

            <!-- EVENTS -->
            <section class="view" id="view-events" data-title="Events">
                <div class="section-title">Upcoming events</div>
                <div class="dash-grid" id="events-grid"></div>
            </section>

            <!-- RESOURCES -->
            <section class="view" id="view-resources" data-title="Study Hub">
                <div class="section-title">Quick links</div>
                <div class="dash-resource-links">
                    <a href="#">Class Routine</a>
                    <a href="#">Notes Bank</a>
                    <a href="#">Previous Year Questions</a>
                </div>
            </section>

            <!-- STUDENT ONLY -->
            <section class="view" id="view-achievements" data-title="Achievements">
                <div class="section-title">Recent achievements</div>
                <div class="dash-grid" id="achievements-grid"></div>
            </section>

            <!-- FACULTY ONLY -->
            <section class="view" id="view-grants" data-title="My Research">
                <div class="section-title">Grant submissions</div>
                <div class="dash-grid" id="grants-grid"></div>
            </section>

            <!-- ADMIN ONLY -->
            <section class="view" id="view-admin" data-title="Manage Platform">
                <div class="section-title">Admin overview</div>
                <div class="dash-grid" id="admin-grid"></div>
            </section>
        </main>
    </div>

    <button class="dash-sos" title="Emergency SOS">SOS</button>

    <script>
        // ---- Demo data (later this becomes fetch('api/get_news.php') etc.) ----
        const demoNews = [
            { title: "Spring 2027 registration opens Sept 15", tag: "Academic" },
            { title: "New research grant call for CSE dept", tag: "Research" },
            { title: "Campus wifi maintenance this weekend", tag: "Notice" }
        ];
        const demoEvents = [
            { title: "Tech Fest 2026", meta: "Sept 20 · Auditorium" },
            { title: "Career Fair", meta: "Oct 2 · Main Hall" }
        ];
        const demoAchievements = [
            { title: "UIU team wins national hackathon", meta: "Aug 2026" }
        ];
        const demoGrants = [
            { title: "Applied ML for crop yield prediction", status: "Under review" }
        ];
        const demoAdmin = [
            { title: "128 students registered", meta: "" },
            { title: "5 posts awaiting approval", meta: "" }
        ];

        function renderCards(containerId, items, keyMain, keySub) {
            const container = document.getElementById(containerId);
            container.innerHTML = items.map(item => `
                <div class="dash-card">
                    <h3>${item[keyMain]}</h3>
                    <p>${item[keySub] || ''}</p>
                </div>
            `).join('');
        }

        function switchView(viewId) {
            document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
            const target = document.getElementById('view-' + viewId);
            if (target) {
                target.classList.add('active');
                document.getElementById('view-title').innerText = target.getAttribute('data-title');
            }
        }

        function loadDashboard(user) {
            document.getElementById('side-name').innerText = user.name;
            document.getElementById('role-tag').innerText = user.role.charAt(0).toUpperCase() + user.role.slice(1);

            // build sidebar menu based on role
            let menuItems = [
                { id: 'home', label: 'Home feed', icon: '🏠' },
                { id: 'events', label: 'Events', icon: '📅' },
                { id: 'resources', label: 'Study Hub', icon: '📚' }
            ];

            if (user.role === 'student') {
                menuItems.push({ id: 'achievements', label: 'Achievements', icon: '🏆' });
            } else if (user.role === 'faculty') {
                menuItems.push({ id: 'grants', label: 'My Research', icon: '🔬' });
            } else if (user.role === 'admin') {
                menuItems.push({ id: 'admin', label: 'Manage Platform', icon: '🛡️' });
            }

            const sideNav = document.getElementById('side-nav');
            sideNav.innerHTML = '';
            menuItems.forEach((item, index) => {
                const btn = document.createElement('button');
                btn.className = 'nav-btn' + (index === 0 ? ' active' : '');
                btn.innerHTML = `<span>${item.icon}</span> ${item.label}`;
                btn.onclick = () => {
                    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    switchView(item.id);
                };
                sideNav.appendChild(btn);
            });

            // fill demo data into cards
            renderCards('news-grid', demoNews, 'title', 'tag');
            renderCards('events-grid', demoEvents, 'title', 'meta');
            renderCards('achievements-grid', demoAchievements, 'title', 'meta');
            renderCards('grants-grid', demoGrants, 'title', 'status');
            renderCards('admin-grid', demoAdmin, 'title', 'meta');

            switchView('home');
        }

        // ---- Auth check on page load ----
        const userData = localStorage.getItem('campus_pulse_user');
        if (!userData) {
            window.location.href = "login.php"; // no fake session, kick back to login
        } else {
            loadDashboard(JSON.parse(userData));
        }

        // ---- Logout ----
        document.getElementById('logout-btn').addEventListener('click', function(){
            localStorage.removeItem('campus_pulse_user');
            window.location.href = "login.php";
        });
    </script>

</body>
</html>