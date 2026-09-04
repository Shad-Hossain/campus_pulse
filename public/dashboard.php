<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Campus_pulse</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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
                <button class="avatar" id="topbar-avatar">S</button>
            </div>

            <div class="dash-ticker">
                <span class="dash-ticker-badge">LIVE</span>
                <span>Midterm routine published &middot; Robotics Club recruitment open &middot; Library extended hours during exam week</span>
            </div>

            <!-- ---- HOME ---- -->
            <section class="view active" id="view-home" data-title="Home feed">
                <div class="live-counter">
                    <span class="lc-dot"></span>
                    <span class="num" id="live-count">482</span>
                    <span>students active on campus right now</span>
                </div>
                <div class="section-title">Campus news</div>
                <div class="dash-grid" id="news-grid"></div>
            </section>

            <!-- ---- EVENTS ---- -->
            <section class="view" id="view-events" data-title="Events">
                <div class="section-head">
                    <div class="section-title" style="margin:0;">Browse events</div>
                    <button class="action-btn" id="event-create-toggle" style="display:none;">+ Create event</button>
                </div>
                <div class="form-card" id="event-form" style="display:none;">
                    <div class="form-row2">
                        <div class="field"><label>Event title</label><input type="text" id="ev-title" placeholder="e.g. AI Workshop"></div>
                        <div class="field"><label>Category</label>
                            <select id="ev-cat"><option>Academic</option><option>Club</option><option>Competition</option></select>
                        </div>
                    </div>
                    <div class="field"><label>Date &amp; venue</label><input type="text" id="ev-meta" placeholder="e.g. Aug 12 . Room 501"></div>
                    <button class="btn-primary" id="ev-submit">Submit event</button>
                </div>
                <div class="filter-row" id="events-filter">
                    <button class="chip active" data-cat="all">All</button>
                    <button class="chip" data-cat="Academic">Academic</button>
                    <button class="chip" data-cat="Club">Club</button>
                    <button class="chip" data-cat="Competition">Competition</button>
                </div>
                <div class="dash-grid" id="events-grid"></div>
            </section>

            <!-- ---- RESOURCES / STUDY HUB ---- -->
            <section class="view" id="view-resources" data-title="Study Hub">
                <div class="section-title">Quick links</div>
                <div class="qlink-grid" id="qlink-grid"></div>

                <div class="section-head">
                    <div class="filter-row" id="resource-tabs" style="margin:0;">
                        <button class="chip active" data-kind="notes">Notes</button>
                        <button class="chip" data-kind="qbank">Question bank</button>
                    </div>
                    <button class="action-btn" id="resource-upload-toggle">+ Upload</button>
                </div>
                <div class="form-card" id="resource-form" style="display:none;">
                    <div class="form-row2">
                        <div class="field"><label>Course code</label><input type="text" id="res-course" placeholder="e.g. CSE 4165"></div>
                        <div class="field"><label>Type</label>
                            <select id="res-kind"><option value="notes">Notes</option><option value="qbank">Question bank</option></select>
                        </div>
                    </div>
                    <div class="field"><label>Title</label><input type="text" id="res-title" placeholder="e.g. Midterm question paper"></div>
                    <div class="field"><label>Attach file</label><input type="file" id="res-file"></div>
                    <button class="btn-primary" id="res-submit">Submit</button>
                </div>
                <div class="dash-grid" id="resources-grid"></div>
            </section>

            <!-- ---- MY RESEARCH (faculty) ---- -->
            <section class="view" id="view-grants" data-title="My Research">
                <div class="section-head">
                    <div class="section-title" style="margin:0;">My submissions</div>
                    <button class="action-btn" id="grant-create-toggle">+ Submit new</button>
                </div>
                <div class="form-card" id="grant-form" style="display:none;">
                    <div class="field"><label>Title</label><input type="text" id="gr-title" placeholder="e.g. Applied ML for crop yield prediction"></div>
                    <div class="field"><label>Short description</label><textarea id="gr-desc" placeholder="1-2 lines about the research"></textarea></div>
                    <button class="btn-primary" id="gr-submit">Submit for review</button>
                </div>
                <div class="dash-grid" id="my-grants-grid"></div>
            </section>

            <!-- ---- ACHIEVEMENTS (student) ---- -->
            <section class="view" id="view-achievements" data-title="Achievements">
                <div class="section-title">Student &amp; faculty spotlight</div>
                <div class="dash-grid" id="achieve-grid"></div>
                <div class="section-title">Research grants</div>
                <div class="dash-grid" id="grant-grid"></div>
            </section>

            <!-- ---- MANAGE GRANTS (admin) ---- -->
            <section class="view" id="view-grants-admin" data-title="Manage Grants">
                <div class="section-title">Pending grant submissions</div>
                <div class="dash-grid" id="grants-pending-grid"></div>
                <div class="section-title">Approved research grants</div>
                <div class="dash-grid" id="grant-grid-admin"></div>
            </section>

            <!-- ---- MANAGE ALERTS (admin) ---- -->
            <section class="view" id="view-alerts" data-title="Manage Alerts">
                <div class="section-title">Campus status</div>
                <div class="form-card open" style="margin-bottom:22px;">
                    <div class="field"><label>Set campus-wide status</label>
                        <select id="admin-status-select">
                            <option value="normal">Normal</option>
                            <option value="alert">Alert - minor disruption</option>
                            <option value="critical">Critical - class suspended / hazard</option>
                        </select>
                    </div>
                    <button class="btn-primary" id="admin-status-btn">Update status</button>
                </div>
                <div class="section-title">Post a new alert</div>
                <div class="form-card open" style="margin-bottom:22px;">
                    <div class="form-row2">
                        <div class="field"><label>Alert title</label><input type="text" id="admin-alert-title" placeholder="e.g. Road closed near Gate 2"></div>
                        <div class="field"><label>Type</label>
                            <select id="admin-alert-type"><option>Traffic</option><option>Weather</option><option>Campus notice</option></select>
                        </div>
                    </div>
                    <button class="btn-primary" id="admin-post-btn">Post to ticker</button>
                </div>
                <div class="section-title">Active alerts</div>
                <div id="active-alerts-list"></div>
            </section>

            <!-- ---- USER DIRECTORY (admin) ---- -->
            <section class="view" id="view-directory" data-title="User Directory">
                <div class="search-bar"><input type="text" id="directory-search" placeholder="Search by name or department..."></div>
                <table class="directory">
                    <thead><tr><th>Name</th><th>Department</th><th>Role</th></tr></thead>
                    <tbody id="directory-body"></tbody>
                </table>
            </section>

            <!-- ---- SEARCH ---- -->
            <section class="view" id="view-search" data-title="Search">
                <div class="search-bar"><input type="text" id="search-input" placeholder="Search news, events, achievements..."></div>
                <div class="filter-row" id="search-filter">
                    <button class="chip active" data-cat="all">All categories</button>
                    <button class="chip" data-cat="Academic">Academic</button>
                    <button class="chip" data-cat="Admin">Admin</button>
                    <button class="chip" data-cat="Club">Club</button>
                    <button class="chip" data-cat="Competition">Competition</button>
                </div>
                <div class="dash-grid" id="search-grid"></div>
            </section>

            <!-- ---- PROFILE ---- -->
            <section class="view" id="view-profile" data-title="Profile">
                <div class="dash-grid">
                    <div class="stat-box">
                        <div class="profile-head">
                            <div class="avatar-lg" id="profile-avatar-fallback">S</div>
                            <div>
                                <b id="profile-head-name">Shad</b><br>
                                <span id="profile-head-sub">Student</span>
                            </div>
                        </div>
                        <div class="field"><label>Full name</label><input type="text" id="profile-name" value="Shad"></div>
                        <div class="field"><label>UIU email</label><input type="text" id="profile-email" value="" disabled></div>
                        <div class="field"><label>Short bio</label><textarea id="profile-bio">CSE student, building civic-tech side projects.</textarea></div>
                        <button class="btn-primary" id="profile-save-btn">Save changes</button>
                    </div>
                    <div>
                        <div class="section-title">Notification settings</div>
                        <div class="stat-box">
                            <div class="saved-item"><span>Traffic alerts</span>
                                <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                            </div>
                            <div class="saved-item"><span>Weather alerts</span>
                                <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                            </div>
                            <div class="saved-item"><span>Event reminders</span>
                                <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                            </div>
                            <div class="saved-item"><span>Research &amp; grants</span>
                                <label class="switch"><input type="checkbox"><span class="switch-slider"></span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script>
// ================= Demo data (replace with fetch() calls once PHP backend is ready) =================
const demoNews = [
    { title: "Spring 2027 registration opens Sept 15", tag: "Academic", cat: "Academic" },
    { title: "New research grant call for CSE dept", tag: "Research", cat: "Academic" },
    { title: "Campus wifi maintenance this weekend", tag: "Notice", cat: "Admin" }
];

const demoEvents = [
    { title: "Tech Fest 2026", meta: "Sept 20 . Auditorium", cat: "Competition" },
    { title: "Career Fair", meta: "Oct 2 . Main Hall", cat: "Academic" },
    { title: "Robotics Club Meetup", meta: "Sept 10 . Room 305", cat: "Club" }
];

const demoQuickLinks = [
    { title: "UCAM (Student Portal)", url: "https://ucam.uiu.ac.bd/Security/Login.aspx" },
    { title: "ELMS", url: "https://elms.uiu.ac.bd/login/index.php" },
    { title: "UIU Notice Board", url: "https://www.uiu.ac.bd/notice/" },
    { title: "Examcon", url: "https://examcon.uiu.ac.bd/" },
    { title: "CGPA Calculator", url: "https://naiimur.me/UIU-CGPA-Calculator/" }
];

const demoResources = [
    { title: "CSE 4165 Midterm Notes", meta: "Notes", kind: "notes" },
    { title: "CSE 3521 Previous Year Question", meta: "Question bank", kind: "qbank" }
];

const demoMyGrants = [
    { title: "Applied ML for crop yield prediction", meta: "Under review" }
];

const demoAchievements = [
    { title: "UIU team wins national hackathon", meta: "Aug 2026" }
];

const demoGrants = [
    { title: "Low-cost water sensor network", meta: "Approved - Aug 2026" }
];

const demoGrantsPending = [
    { title: "Applied ML for crop yield prediction", meta: "Submitted by Dr. Farhana" }
];

const demoAlerts = [
    { title: "Heavy traffic near Gate 2", meta: "Traffic . 10m ago" },
    { title: "Light rain expected this evening", meta: "Weather . 1h ago" }
];

const demoDirectory = [
    { name: "Shad Hossain", dept: "CSE", role: "Student" },
    { name: "Dr. Farhana", dept: "CSE", role: "Faculty" },
    { name: "Admin User", dept: "-", role: "Admin" }
];

// ================= Render helpers =================
function renderCards(containerId, items, keyMain, keySub) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = items.map(item => `
        <div class="dash-card">
            <h3>${item[keyMain]}</h3>
            <p>${item[keySub] || ''}</p>
        </div>
    `).join('');
}

function renderQuickLinks() {
    const container = document.getElementById('qlink-grid');
    container.innerHTML = demoQuickLinks.map(link => `
        <a class="qlink-card" href="${link.url}" target="_blank" rel="noopener noreferrer">${link.title}</a>
    `).join('');
}

function renderAlertsList() {
    const container = document.getElementById('active-alerts-list');
    container.innerHTML = demoAlerts.map(a => `
        <div class="dash-card"><h3>${a.title}</h3><p>${a.meta}</p></div>
    `).join('');
}

function renderDirectory() {
    const body = document.getElementById('directory-body');
    body.innerHTML = demoDirectory.map(u => `
        <tr><td>${u.name}</td><td>${u.dept}</td><td>${u.role}</td></tr>
    `).join('');
}

function filterResources(kind) {
    const filtered = kind === 'all' ? demoResources : demoResources.filter(r => r.kind === kind);
    renderCards('resources-grid', filtered, 'title', 'meta');
}

function filterEvents(cat) {
    const filtered = cat === 'all' ? demoEvents : demoEvents.filter(e => e.cat === cat);
    renderCards('events-grid', filtered, 'title', 'meta');
}

function filterSearch(cat) {
    const filtered = cat === 'all' ? demoNews : demoNews.filter(n => n.cat === cat);
    renderCards('search-grid', filtered, 'title', 'tag');
}

function switchView(viewId) {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    const target = document.getElementById('view-' + viewId);
    if (target) {
        target.classList.add('active');
        document.getElementById('view-title').innerText = target.getAttribute('data-title');
    }
}

// ================= Sidebar + dashboard boot =================
function loadDashboard(user) {
    document.getElementById('side-name').innerText = user.name;
    document.getElementById('topbar-avatar').innerText = user.name.charAt(0).toUpperCase();
    document.getElementById('role-tag').innerText = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('profile-head-name').innerText = user.name;
    document.getElementById('profile-head-sub').innerText = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('profile-name').value = user.name;
    document.getElementById('profile-email').value = user.username;

    // common nav for every role
    let menuItems = [
        { id: 'home', label: 'Home feed' },
        { id: 'events', label: 'Events' },
        { id: 'resources', label: 'Study Hub' }
    ];

    if (user.role === 'student') {
        menuItems.push({ id: 'achievements', label: 'Achievements' });
    } else if (user.role === 'faculty') {
        menuItems.push({ id: 'grants', label: 'My Research' });
        document.getElementById('event-create-toggle').style.display = 'inline-block';
    } else if (user.role === 'admin') {
        menuItems.push({ id: 'grants-admin', label: 'Manage Grants' });
        menuItems.push({ id: 'alerts', label: 'Manage Alerts' });
        menuItems.push({ id: 'directory', label: 'User Directory' });
        document.getElementById('event-create-toggle').style.display = 'inline-block';
    }

    menuItems.push({ id: 'search', label: 'Search' });
    menuItems.push({ id: 'profile', label: 'Profile' });

    const sideNav = document.getElementById('side-nav');
    sideNav.innerHTML = '';
    menuItems.forEach((item, index) => {
        const btn = document.createElement('button');
        btn.className = 'nav-btn' + (index === 0 ? ' active' : '');
        btn.innerHTML = item.label;
        btn.onclick = () => {
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            switchView(item.id);
        };
        sideNav.appendChild(btn);
    });

    // fill every section with its demo data (only relevant ones will ever be viewed,
    // but rendering all is harmless and keeps this simple)
    renderCards('news-grid', demoNews, 'title', 'tag');
    filterEvents('all');
    filterResources('notes');
    renderCards('my-grants-grid', demoMyGrants, 'title', 'meta');
    renderCards('achieve-grid', demoAchievements, 'title', 'meta');
    renderCards('grant-grid', demoGrants, 'title', 'meta');
    renderCards('grants-pending-grid', demoGrantsPending, 'title', 'meta');
    renderCards('grant-grid-admin', demoGrants, 'title', 'meta');
    filterSearch('all');
    renderQuickLinks();
    renderAlertsList();
    renderDirectory();

    switchView('home');
}

//  Simple form toggles (create-event / upload / grant-submit)     
document.getElementById('event-create-toggle')?.addEventListener('click', () => {
    const form = document.getElementById('event-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
document.getElementById('resource-upload-toggle')?.addEventListener('click', () => {
    const form = document.getElementById('resource-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
document.getElementById('grant-create-toggle')?.addEventListener('click', () => {
    const form = document.getElementById('grant-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

// filter chips (events / resources / search)
document.querySelectorAll('.filter-row').forEach(row => {
    row.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            row.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');

            if (row.id === 'resource-tabs') {
                filterResources(chip.dataset.kind);
            } else if (row.id === 'search-filter') {
                filterSearch(chip.dataset.cat);
            } else if (row.id === 'events-filter') {
                filterEvents(chip.dataset.cat);
            }
        });
    });
});

// Auth check on page load 
const userData = localStorage.getItem('campus_pulse_user');
if (!userData) {
    window.location.href = "login.php";
} else {
    loadDashboard(JSON.parse(userData));
}

document.getElementById('logout-btn').addEventListener('click', function () {
    localStorage.removeItem('campus_pulse_user');
    window.location.href = "login.php";
});

    </script>
</body>
</html>