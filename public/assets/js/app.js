// ============================================================
// Campus Pulse — dashboard frontend logic.
// Talks to the PHP JSON APIs under /api/*.php. The logged-in
// user comes from window.CURRENT_USER, set server-side in
// dashboard.php from the PHP session (see includes/helpers.php).
// ============================================================

const user = window.CURRENT_USER;
if (!user) {
    window.location.href = 'login.php';
}

// ---------------- fetch helpers ----------------
async function apiGet(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    if (res.status === 401) { window.location.href = 'login.php'; return null; }
    return res.json();
}

async function apiSend(url, method, body, isForm = false) {
    const opts = { method, credentials: 'same-origin' };
    if (isForm) {
        opts.body = body; // FormData - browser sets content-type
    } else {
        opts.headers = { 'Content-Type': 'application/json' };
        opts.body = JSON.stringify(body);
    }
    const res = await fetch(url, opts);
    if (res.status === 401) { window.location.href = 'login.php'; return null; }
    return res.json();
}

// ---------------- render helpers ----------------
function renderCards(containerId, items, keyMain, keySub) {
    const container = document.getElementById(containerId);
    if (!container) return;
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="dash-card"><p>Nothing here yet.</p></div>';
        return;
    }
    container.innerHTML = items.map(item => `
        <div class="dash-card">
            <h3>${escapeHtml(item[keyMain] ?? '')}</h3>
            <p>${escapeHtml(item[keySub] ?? '')}</p>
        </div>
    `).join('');
}

function renderPendingGrants(items) {
    const container = document.getElementById('grants-pending-grid');
    if (!container) return;
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="dash-card"><p>No pending submissions.</p></div>';
        return;
    }
    container.innerHTML = items.map(g => `
        <div class="dash-card">
            <h3>${escapeHtml(g.title)}</h3>
            <p>${escapeHtml(g.description || '')}</p>
            <p style="margin-top:6px;color:#999;">Submitted by ${escapeHtml(g.submitted_by_name || 'Unknown')}</p>
            <div style="margin-top:10px;display:flex;gap:8px;">
                <button class="btn-primary" style="padding:6px 14px;font-size:12px;" data-grant-approve="${g.id}">Approve</button>
                <button class="action-btn" style="padding:6px 14px;font-size:12px;" data-grant-reject="${g.id}">Reject</button>
            </div>
        </div>
    `).join('');

    container.querySelectorAll('[data-grant-approve]').forEach(btn => {
        btn.addEventListener('click', () => reviewGrant(btn.dataset.grantApprove, 'approved'));
    });
    container.querySelectorAll('[data-grant-reject]').forEach(btn => {
        btn.addEventListener('click', () => reviewGrant(btn.dataset.grantReject, 'rejected'));
    });
}

async function reviewGrant(id, status) {
    await apiSend('api/grants.php', 'PATCH', { id, status });
    loadGrantsAdmin();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

const quickLinks = [
    { title: 'UCAM (Student Portal)', url: 'https://ucam.uiu.ac.bd/Security/Login.aspx' },
    { title: 'ELMS', url: 'https://elms.uiu.ac.bd/login/index.php' },
    { title: 'UIU Notice Board', url: 'https://www.uiu.ac.bd/notice/' },
    { title: 'Examcon', url: 'https://examcon.uiu.ac.bd/' },
    { title: 'CGPA Calculator', url: 'https://naiimur.me/UIU-CGPA-Calculator/' },
];

function renderQuickLinks() {
    const container = document.getElementById('qlink-grid');
    if (!container) return;
    container.innerHTML = quickLinks.map(link => `
        <a class="qlink-card" href="${link.url}" target="_blank" rel="noopener noreferrer">${escapeHtml(link.title)}</a>
    `).join('');
}

function switchView(viewId) {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    const target = document.getElementById('view-' + viewId);
    if (target) {
        target.classList.add('active');
        document.getElementById('view-title').innerText = target.getAttribute('data-title');
    }
    // lazy-load data for the view being opened
    viewLoaders[viewId]?.();
}

// ---------------- section loaders (fetch on demand) ----------------
async function loadNews() {
    const data = await apiGet('api/news.php?category=all');
    if (data?.ok) renderCards('news-grid', data.items, 'title', 'tag');
}

async function loadEvents(cat = 'all') {
    const data = await apiGet('api/events.php?category=' + encodeURIComponent(cat));
    if (data?.ok) renderCards('events-grid', data.items, 'title', 'meta');
}

async function loadResources(kind = 'notes') {
    const data = await apiGet('api/resources.php?kind=' + encodeURIComponent(kind));
    if (data?.ok) renderCards('resources-grid', data.items, 'title', 'course_code');
}

async function loadAchievementsView() {
    const [ach, gr] = await Promise.all([
        apiGet('api/achievements.php'),
        apiGet('api/grants.php?scope=approved'),
    ]);
    if (ach?.ok) renderCards('achieve-grid', ach.items, 'title', 'meta');
    if (gr?.ok) renderCards('grant-grid', gr.items, 'title', 'description');
}

async function loadMyGrants() {
    const data = await apiGet('api/grants.php?scope=mine');
    if (data?.ok) renderCards('my-grants-grid', data.items, 'title', 'status');
}

async function loadGrantsAdmin() {
    const [pending, approved] = await Promise.all([
        apiGet('api/grants.php?scope=pending'),
        apiGet('api/grants.php?scope=approved'),
    ]);
    if (pending?.ok) renderPendingGrants(pending.items);
    if (approved?.ok) renderCards('grant-grid-admin', approved.items, 'title', 'description');
}

async function loadAlerts() {
    const data = await apiGet('api/alerts.php');
    if (!data?.ok) return;
    const container = document.getElementById('active-alerts-list');
    if (container) {
        container.innerHTML = data.items.map(a => `
            <div class="dash-card"><h3>${escapeHtml(a.title)}</h3><p>${escapeHtml(a.type)}</p></div>
        `).join('') || '<div class="dash-card"><p>No active alerts.</p></div>';
    }
    const select = document.getElementById('admin-status-select');
    if (select && data.status) select.value = data.status.status;
}

async function loadDirectory(q = '') {
    const data = await apiGet('api/directory.php?q=' + encodeURIComponent(q));
    if (data?.ok) {
        document.getElementById('directory-body').innerHTML = data.items.map(u => `
            <tr><td>${escapeHtml(u.name)}</td><td>${escapeHtml(u.dept || '-')}</td><td>${escapeHtml(u.role)}</td></tr>
        `).join('');
    }
}

async function runSearch(q = '', cat = 'all') {
    if (!q) { renderCards('search-grid', [], 'title', 'tag'); return; }
    const data = await apiGet(`api/search.php?q=${encodeURIComponent(q)}&category=${encodeURIComponent(cat)}`);
    if (data?.ok) renderCards('search-grid', data.items, 'title', 'tag');
}

async function loadProfile() {
    const data = await apiGet('api/profile.php');
    if (!data?.ok) return;
    document.getElementById('profile-name').value = data.profile.full_name;
    document.getElementById('profile-email').value = data.profile.email;
    document.getElementById('profile-bio').value = data.profile.bio || '';
    document.getElementById('profile-head-name').innerText = data.profile.full_name;

    const n = data.notifications || {};
    if (document.getElementById('notif-traffic')) document.getElementById('notif-traffic').checked = !!n.traffic_alerts;
    if (document.getElementById('notif-weather')) document.getElementById('notif-weather').checked = !!n.weather_alerts;
    if (document.getElementById('notif-events')) document.getElementById('notif-events').checked = !!n.event_reminders;
    if (document.getElementById('notif-grants')) document.getElementById('notif-grants').checked = !!n.research_grants;
}

async function loadLiveCount() {
    const data = await apiGet('api/stats.php');
    if (data?.ok) document.getElementById('live-count').innerText = data.live_count;
}

const viewLoaders = {
    home: loadNews,
    events: () => loadEvents('all'),
    resources: () => loadResources('notes'),
    achievements: loadAchievementsView,
    grants: loadMyGrants,
    'grants-admin': loadGrantsAdmin,
    alerts: loadAlerts,
    directory: () => loadDirectory(''),
    search: () => {},
    profile: loadProfile,
};

// ---------------- sidebar + dashboard boot ----------------
function loadDashboard() {
    document.getElementById('side-name').innerText = user.full_name;
    document.getElementById('topbar-avatar').innerText = user.full_name.charAt(0).toUpperCase();
    document.getElementById('role-tag').innerText = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('profile-head-name').innerText = user.full_name;
    document.getElementById('profile-head-sub').innerText = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('profile-avatar-fallback').innerText = user.full_name.charAt(0).toUpperCase();
    document.getElementById('profile-name').value = user.full_name;
    document.getElementById('profile-email').value = user.email;

    let menuItems = [
        { id: 'home', label: 'Home feed' },
        { id: 'events', label: 'Events' },
        { id: 'resources', label: 'Study Hub' },
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

    renderQuickLinks();
    loadLiveCount();
    setInterval(loadLiveCount, 30000);
    switchView('home');
}

// ---------------- form toggles ----------------
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

// ---------------- filter chips ----------------
document.querySelectorAll('.filter-row').forEach(row => {
    row.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            row.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');

            if (row.id === 'resource-tabs') {
                loadResources(chip.dataset.kind);
            } else if (row.id === 'search-filter') {
                runSearch(document.getElementById('search-input').value.trim(), chip.dataset.cat);
            } else if (row.id === 'events-filter') {
                loadEvents(chip.dataset.cat);
            }
        });
    });
});

// ---------------- create/submit actions ----------------
document.getElementById('ev-submit')?.addEventListener('click', async () => {
    const title = document.getElementById('ev-title').value.trim();
    const category = document.getElementById('ev-cat').value;
    const meta = document.getElementById('ev-meta').value.trim();
    if (!title) { alert('Event title is required.'); return; }
    const res = await apiSend('api/events.php', 'POST', { title, category, meta });
    if (res?.ok) {
        document.getElementById('ev-title').value = '';
        document.getElementById('ev-meta').value = '';
        document.getElementById('event-form').style.display = 'none';
        loadEvents('all');
    } else {
        alert(res?.error || 'Could not create event.');
    }
});

document.getElementById('res-submit')?.addEventListener('click', async () => {
    const course = document.getElementById('res-course').value.trim();
    const title = document.getElementById('res-title').value.trim();
    const kind = document.getElementById('res-kind').value;
    const fileInput = document.getElementById('res-file');
    if (!course || !title) { alert('Course code and title are required.'); return; }

    const fd = new FormData();
    fd.append('course_code', course);
    fd.append('title', title);
    fd.append('kind', kind);
    if (fileInput.files[0]) fd.append('file', fileInput.files[0]);

    const res = await apiSend('api/resources.php', 'POST', fd, true);
    if (res?.ok) {
        document.getElementById('res-course').value = '';
        document.getElementById('res-title').value = '';
        fileInput.value = '';
        document.getElementById('resource-form').style.display = 'none';
        loadResources(kind);
    } else {
        alert(res?.error || 'Could not upload resource.');
    }
});

document.getElementById('gr-submit')?.addEventListener('click', async () => {
    const title = document.getElementById('gr-title').value.trim();
    const description = document.getElementById('gr-desc').value.trim();
    if (!title) { alert('Title is required.'); return; }
    const res = await apiSend('api/grants.php', 'POST', { title, description });
    if (res?.ok) {
        document.getElementById('gr-title').value = '';
        document.getElementById('gr-desc').value = '';
        document.getElementById('grant-form').style.display = 'none';
        loadMyGrants();
    } else {
        alert(res?.error || 'Could not submit grant.');
    }
});

document.getElementById('admin-status-btn')?.addEventListener('click', async () => {
    const status = document.getElementById('admin-status-select').value;
    const res = await apiSend('api/alerts.php', 'PATCH', { status });
    if (res?.ok) alert('Campus status updated.');
});

document.getElementById('admin-post-btn')?.addEventListener('click', async () => {
    const title = document.getElementById('admin-alert-title').value.trim();
    const type = document.getElementById('admin-alert-type').value;
    if (!title) { alert('Alert title is required.'); return; }
    const res = await apiSend('api/alerts.php', 'POST', { title, type });
    if (res?.ok) {
        document.getElementById('admin-alert-title').value = '';
        loadAlerts();
    } else {
        alert(res?.error || 'Could not post alert.');
    }
});

document.getElementById('directory-search')?.addEventListener('input', (e) => {
    loadDirectory(e.target.value.trim());
});

document.getElementById('search-input')?.addEventListener('input', (e) => {
    const activeCat = document.querySelector('#search-filter .chip.active')?.dataset.cat || 'all';
    runSearch(e.target.value.trim(), activeCat);
});

document.getElementById('profile-save-btn')?.addEventListener('click', async () => {
    const full_name = document.getElementById('profile-name').value.trim();
    const bio = document.getElementById('profile-bio').value.trim();
    const notifications = {
        traffic_alerts: document.getElementById('notif-traffic')?.checked,
        weather_alerts: document.getElementById('notif-weather')?.checked,
        event_reminders: document.getElementById('notif-events')?.checked,
        research_grants: document.getElementById('notif-grants')?.checked,
    };
    const res = await apiSend('api/profile.php', 'PATCH', { full_name, bio, notifications });
    if (res?.ok) {
        alert('Profile saved.');
        document.getElementById('side-name').innerText = full_name;
        document.getElementById('profile-head-name').innerText = full_name;
    } else {
        alert(res?.error || 'Could not save profile.');
    }
});

// ---------------- logout ----------------
document.getElementById('logout-btn').addEventListener('click', function () {
    window.location.href = 'logout.php';
});

// ---------------- boot ----------------
loadDashboard();
