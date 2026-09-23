<?php
require_once __DIR__ . '/../includes/helpers.php';
$user = require_login_page('login.php');   // not logged in => back to login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Campus_pulse</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .status-pill{padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;background:#2f6b3f;color:#fff;margin-left:auto;margin-right:12px}
        .status-pill.alert{background:#b7791f}
        .status-pill.critical{background:#8b1e1e}
        .card-actions{display:flex;gap:8px;margin-top:10px}
        .card-actions button{cursor:pointer;padding:5px 12px;border-radius:6px;border:1px solid currentColor;background:transparent;color:inherit;font:inherit;font-size:13px}
        .dash-card a.dl{font-size:13px;text-decoration:underline}
    </style>
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
                <span class="status-pill" id="status-pill">Normal</span>
                <button class="avatar" id="topbar-avatar">S</button>
            </div>

            <div class="dash-ticker">
                <span class="dash-ticker-badge">LIVE</span>
                <span id="ticker-text">Loading alerts...</span>
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
                    <div class="form-row2">
                        <div class="field"><label>Date</label><input type="date" id="ev-date"></div>
                        <div class="field"><label>Venue</label><input type="text" id="ev-venue" placeholder="e.g. Room 501"></div>
                    </div>
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

            <!-- ACHIEVEMENTS (student) -->
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
                                <b id="profile-head-name"></b><br>
                                <span id="profile-head-sub">Student</span>
                            </div>
                        </div>
                        <div class="field"><label>Full name</label><input type="text" id="profile-name" value=""></div>
                        <div class="field"><label>UIU email</label><input type="text" id="profile-email" value="" disabled></div>
                        <div class="field"><label>Short bio</label><textarea id="profile-bio"></textarea></div>
                        <button class="btn-primary" id="profile-save-btn">Save changes</button>
                    </div>
                    <div>
                        <div class="section-title">Notification settings</div>
                        <div class="stat-box">
                            <div class="saved-item"><span>Traffic alerts</span>
                                <label class="switch"><input type="checkbox" data-key="traffic_alerts"><span class="switch-slider"></span></label>
                            </div>
                            <div class="saved-item"><span>Weather alerts</span>
                                <label class="switch"><input type="checkbox" data-key="weather_alerts"><span class="switch-slider"></span></label>
                            </div>
                            <div class="saved-item"><span>Event reminders</span>
                                <label class="switch"><input type="checkbox" data-key="event_reminders"><span class="switch-slider"></span></label>
                            </div>
                            <div class="saved-item"><span>Research &amp; grants</span>
                                <label class="switch"><input type="checkbox" data-key="research_alerts"><span class="switch-slider"></span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script>
        // values come from the PHP session (not localStorage)
        window.CP = <?= json_encode([
            'user' => [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'role'       => $user['role'],
            ],
            'csrf' => csrf_token(),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>