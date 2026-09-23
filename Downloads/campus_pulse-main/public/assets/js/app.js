// public/assets/js/app.js — dashboard logic (talks to public/api/*.php)
// window.CP = { user, csrf } is printed by dashboard.php from the PHP session.
(() => {
    'use strict';
    const CP = window.CP;

    /* ================= helpers ================= */
    const $   = id => document.getElementById(id);
    const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    async function api(path, { method = 'GET', json, form } = {}) {
        const opts = { method, headers: {}, credentials: 'same-origin' };
        if (method !== 'GET') opts.headers['X-CSRF-Token'] = CP.csrf;
        if (json) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(json); }
        if (form) opts.body = form;               // FormData: browser sets the multipart header
        const res = await fetch(path, opts);
        let data = {};
        try { data = await res.json(); } catch (_) { /* non-JSON response */ }
        if (res.status === 401) { location.href = 'login.php'; throw new Error('auth'); }
        if (!res.ok) throw new Error(data.error || 'Request failed (' + res.status + ')');
        return data;
    }
    const get  = path => api(path);
    const post = (path, json) => api(path, { method: 'POST', json });
    const fail = err => { if (err.message !== 'auth') alert(err.message); };

    function ago(mins) {
        mins = Number(mins) || 0;
        if (mins < 1) return 'just now';
        if (mins < 60) return mins + 'm ago';
        if (mins < 1440) return Math.floor(mins / 60) + 'h ago';
        return Math.floor(mins / 1440) + 'd ago';
    }
    function fmtDate(d, opts) {
        if (!d) return '';
        return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', opts || { day: 'numeric', month: 'short' });
    }
    function debounce(fn, ms = 300) {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    }
    // run an async click handler with the button disabled (stops double submits)
    async function busy(btn, fn) {
        btn.disabled = true;
        try { await fn(); } catch (err) { fail(err); } finally { btn.disabled = false; }
    }

    // items: [{ title, meta, extra }]  — extra is trusted HTML built with esc() below
    function cards(id, items, empty = 'Nothing here yet.') {
        const el = $(id);
        if (!el) return;
        el.innerHTML = items.length
            ? items.map(i => `<div class="dash-card"><h3>${esc(i.title)}</h3><p>${esc(i.meta || '')}</p>${i.extra || ''}</div>`).join('')
            : `<p style="opacity:.7">${esc(empty)}</p>`;
    }

    /* ================= state ================= */
    const state = { eventCat: 'all', resKind: 'notes', searchCat: 'all', alerts: [], status: 'normal' };

    /* ================= loaders ================= */
    async function loadNews() {
        const { items } = await get('api/news.php');
        cards('news-grid', items.map(n => ({ title: n.title, meta: n.tag || n.category })), 'No news yet.');
    }

    async function loadEvents() {
        const { items } = await get('api/events.php?cat=' + encodeURIComponent(state.eventCat));
        cards('events-grid', items.map(e => ({
            title: e.title,
            meta: [fmtDate(e.event_date), e.venue].filter(Boolean).join(' · ') + ' · ' + e.category
        })), 'No events found.');
    }

    async function loadLinks() {
        const { items } = await get('api/links.php');
        $('qlink-grid').innerHTML = items.map(l =>
            `<a class="qlink-card" href="${esc(l.url)}" target="_blank" rel="noopener noreferrer">${esc(l.title)}</a>`).join('');
    }

    async function loadResources() {
        const { items } = await get('api/resources.php?kind=' + encodeURIComponent(state.resKind));
        cards('resources-grid', items.map(r => ({
            title: r.title,
            meta: [r.course_code, r.uploader].filter(Boolean).join(' · '),
            extra: r.file_path ? `<a class="dl" href="${esc(r.file_path)}" target="_blank" rel="noopener">Download</a>` : ''
        })), 'No resources uploaded yet.');
    }

    const grantLabel = { pending: 'Under review', approved: 'Approved', rejected: 'Rejected' };

    async function loadMyGrants() {
        const { items } = await get('api/grants.php?scope=mine');
        cards('my-grants-grid', items.map(g => ({ title: g.title, meta: grantLabel[g.status] || g.status })), 'No submissions yet.');
    }

    async function loadAchievements() {
        const [a, g] = await Promise.all([get('api/achievements.php'), get('api/grants.php?scope=approved')]);
        cards('achieve-grid', a.items.map(x => ({
            title: x.title,
            meta: [x.person, fmtDate(x.achieved_on, { month: 'short', year: 'numeric' })].filter(Boolean).join(' · ')
        })), 'No achievements yet.');
        cards('grant-grid', g.items.map(x => ({ title: x.title, meta: 'Approved · ' + x.submitter })), 'No approved grants yet.');
    }

    async function loadGrantsAdmin() {
        const [p, a] = await Promise.all([get('api/grants.php?scope=pending'), get('api/grants.php?scope=approved')]);
        cards('grants-pending-grid', p.items.map(g => ({
            title: g.title,
            meta: 'Submitted by ' + g.submitter,
            extra: `<div class="card-actions">
                        <button data-act="grant-review" data-id="${g.id}" data-decision="approved">Approve</button>
                        <button data-act="grant-review" data-id="${g.id}" data-decision="rejected">Reject</button>
                    </div>`
        })), 'No pending submissions.');
        cards('grant-grid-admin', a.items.map(x => ({ title: x.title, meta: 'Approved · ' + x.submitter })), 'No approved grants yet.');
    }

    // ticker + status pill (everyone) and active-alerts list (admin)
    async function loadAlerts() {
        const data = await get('api/alerts.php');
        state.alerts = data.alerts;
        state.status = data.status;

        const pill = $('status-pill');
        pill.className = 'status-pill ' + data.status;
        pill.textContent = data.status;

        $('ticker-text').textContent = data.alerts.length
            ? data.alerts.map(a => a.title).join(' · ')
            : 'No active alerts. Campus is running normally.';

        if (CP.user.role === 'admin') {
            $('admin-status-select').value = data.status;
            const list = $('active-alerts-list');
            list.innerHTML = data.alerts.length ? data.alerts.map(a => `
                <div class="dash-card">
                    <h3>${esc(a.title)}</h3>
                    <p>${esc(a.type)} · ${esc(ago(a.mins_ago))}</p>
                    <div class="card-actions"><button data-act="alert-resolve" data-id="${a.id}">Resolve</button></div>
                </div>`).join('') : '<p style="opacity:.7">No active alerts.</p>';
        }
    }

    async function loadDirectory() {
        const q = $('directory-search').value.trim();
        const { items } = await get('api/users.php?q=' + encodeURIComponent(q));
        $('directory-body').innerHTML = items.map(u =>
            `<tr><td>${esc(u.full_name)}</td><td>${esc(u.department || '-')}</td><td>${esc(cap(u.role))}</td></tr>`).join('');
    }

    async function loadSearch() {
        const q = $('search-input').value.trim();
        const { items } = await get('api/search.php?q=' + encodeURIComponent(q) + '&cat=' + encodeURIComponent(state.searchCat));
        cards('search-grid', items, 'No results.');
    }

    async function loadProfile() {
        const { profile, settings } = await get('api/profile.php');
        $('profile-head-name').textContent = profile.full_name;
        $('profile-name').value  = profile.full_name;
        $('profile-email').value = profile.email;
        $('profile-bio').value   = profile.bio || '';
        document.querySelectorAll('input[data-key]').forEach(cb => { cb.checked = !!settings[cb.dataset.key]; });
    }

    // which loader runs when a sidebar view opens
    const viewLoaders = {
        'home': loadNews,
        'events': loadEvents,
        'resources': () => Promise.all([loadLinks(), loadResources()]),
        'grants': loadMyGrants,
        'achievements': loadAchievements,
        'grants-admin': loadGrantsAdmin,
        'alerts': loadAlerts,
        'directory': loadDirectory,
        'search': loadSearch,
        'profile': loadProfile
    };

    /* ================= views + sidebar ================= */
    function switchView(viewId) {
        document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
        const target = $('view-' + viewId);
        if (!target) return;
        target.classList.add('active');
        $('view-title').innerText = target.getAttribute('data-title');
        if (viewLoaders[viewId]) viewLoaders[viewId]().catch(fail);
    }

    function loadDashboard(user) {
        const roleName = cap(user.role);
        $('side-name').innerText = user.name;
        $('topbar-avatar').innerText = user.name.charAt(0).toUpperCase();
        $('role-tag').innerText = roleName;
        $('profile-head-sub').innerText = roleName;

        const menu = [
            { id: 'home', label: 'Home feed' },
            { id: 'events', label: 'Events' },
            { id: 'resources', label: 'Study Hub' }
        ];
        if (user.role === 'student') {
            menu.push({ id: 'achievements', label: 'Achievements' });
        } else if (user.role === 'faculty') {
            menu.push({ id: 'grants', label: 'My Research' });
            $('event-create-toggle').style.display = 'inline-block';
        } else if (user.role === 'admin') {
            menu.push({ id: 'grants-admin', label: 'Manage Grants' });
            menu.push({ id: 'alerts', label: 'Manage Alerts' });
            menu.push({ id: 'directory', label: 'User Directory' });
            $('event-create-toggle').style.display = 'inline-block';
        }
        menu.push({ id: 'search', label: 'Search' });
        menu.push({ id: 'profile', label: 'Profile' });

        const nav = $('side-nav');
        nav.innerHTML = '';
        menu.forEach((item, i) => {
            const btn = document.createElement('button');
            btn.className = 'nav-btn' + (i === 0 ? ' active' : '');
            btn.textContent = item.label;
            btn.onclick = () => {
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                switchView(item.id);
            };
            nav.appendChild(btn);
        });

        switchView('home');
        loadAlerts().catch(fail);
        setInterval(() => loadAlerts().catch(() => {}), 60000);   // keep ticker + status pill fresh
    }

    /* ================= UI wiring ================= */
    const toggle = (btnId, formId) => $(btnId)?.addEventListener('click', () => {
        const f = $(formId);
        f.style.display = f.style.display === 'none' ? 'block' : 'none';
    });
    toggle('event-create-toggle', 'event-form');
    toggle('resource-upload-toggle', 'resource-form');
    toggle('grant-create-toggle', 'grant-form');

    // filter chips
    document.querySelectorAll('.filter-row').forEach(row => {
        row.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', () => {
                row.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                if (row.id === 'resource-tabs')      { state.resKind = chip.dataset.kind;  loadResources().catch(fail); }
                else if (row.id === 'search-filter') { state.searchCat = chip.dataset.cat; loadSearch().catch(fail); }
                else if (row.id === 'events-filter') { state.eventCat = chip.dataset.cat;  loadEvents().catch(fail); }
            });
        });
    });

    $('search-input').addEventListener('input', debounce(() => loadSearch().catch(fail)));
    $('directory-search').addEventListener('input', debounce(() => loadDirectory().catch(fail)));

    // create event (faculty / admin)
    $('ev-submit').addEventListener('click', e => busy(e.target, async () => {
        await post('api/events.php', {
            title: $('ev-title').value, category: $('ev-cat').value,
            event_date: $('ev-date').value, venue: $('ev-venue').value
        });
        ['ev-title', 'ev-date', 'ev-venue'].forEach(id => $(id).value = '');
        $('event-form').style.display = 'none';
        await loadEvents();
    }));

    // upload resource
    $('res-submit').addEventListener('click', e => busy(e.target, async () => {
        const fd = new FormData();
        fd.append('course_code', $('res-course').value);
        fd.append('kind', $('res-kind').value);
        fd.append('title', $('res-title').value);
        if ($('res-file').files[0]) fd.append('file', $('res-file').files[0]);
        await api('api/resources.php', { method: 'POST', form: fd });
        ['res-course', 'res-title', 'res-file'].forEach(id => $(id).value = '');
        $('resource-form').style.display = 'none';
        state.resKind = $('res-kind').value;
        await loadResources();
    }));

    // faculty: submit grant
    $('gr-submit').addEventListener('click', e => busy(e.target, async () => {
        await post('api/grants.php', { action: 'submit', title: $('gr-title').value, description: $('gr-desc').value });
        $('gr-title').value = ''; $('gr-desc').value = '';
        $('grant-form').style.display = 'none';
        await loadMyGrants();
    }));

    // admin: campus status + post alert
    $('admin-status-btn').addEventListener('click', e => busy(e.target, async () => {
        await post('api/alerts.php', { action: 'status', status: $('admin-status-select').value });
        await loadAlerts();
    }));
    $('admin-post-btn').addEventListener('click', e => busy(e.target, async () => {
        await post('api/alerts.php', { action: 'post', title: $('admin-alert-title').value, type: $('admin-alert-type').value });
        $('admin-alert-title').value = '';
        await loadAlerts();
    }));

    // buttons rendered inside cards (approve / reject / resolve)
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        if (btn.dataset.act === 'grant-review') {
            busy(btn, async () => {
                await post('api/grants.php', { action: 'review', id: Number(btn.dataset.id), decision: btn.dataset.decision });
                await loadGrantsAdmin();
            });
        } else if (btn.dataset.act === 'alert-resolve') {
            busy(btn, async () => {
                await post('api/alerts.php', { action: 'resolve', id: Number(btn.dataset.id) });
                await loadAlerts();
            });
        }
    });

    // profile
    $('profile-save-btn').addEventListener('click', e => busy(e.target, async () => {
        const r = await post('api/profile.php', { full_name: $('profile-name').value, bio: $('profile-bio').value });
        $('side-name').innerText = r.name;
        $('profile-head-name').textContent = r.name;
        $('topbar-avatar').innerText = r.name.charAt(0).toUpperCase();
    }));
    document.querySelectorAll('input[data-key]').forEach(cb => {
        cb.addEventListener('change', () => {
            const settings = {};
            document.querySelectorAll('input[data-key]').forEach(x => { settings[x.dataset.key] = x.checked ? 1 : 0; });
            post('api/profile.php', { settings }).catch(err => { cb.checked = !cb.checked; fail(err); });
        });
    });

    // logout
    $('logout-btn').addEventListener('click', async () => {
        try { await api('auth/logout.php', { method: 'POST', json: {} }); } catch (_) { /* go to login anyway */ }
        location.href = 'login.php';
    });

    /* ================= boot ================= */
    loadDashboard(CP.user);
})();
