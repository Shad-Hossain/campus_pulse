/**
 * Loads includes/header.html + includes/footer.html into every page
 * (the old header.php / footer.php PHP includes), then fills in the
 * login/logout area by asking the API who — if anyone — is logged in.
 *
 * Every page just needs:
 *   <div id="site-header"></div>  ...content...  <div id="site-footer"></div>
 *   <script src="/js/api.js"></script>
 *   <script src="/js/layout.js"></script>
 */

async function loadLayout() {
  const headerSlot = document.getElementById('site-header');
  const footerSlot = document.getElementById('site-footer');

  const [headerHtml, footerHtml] = await Promise.all([
    fetch('/includes/header.html').then(r => r.text()),
    fetch('/includes/footer.html').then(r => r.text()),
  ]);

  if (headerSlot) headerSlot.innerHTML = headerHtml;
  if (footerSlot) footerSlot.innerHTML = footerHtml;

  highlightActiveNav();
  wireNavToggle();
  wireThemeToggle();
  setFooterYear();
  await paintAuthArea();
  document.dispatchEvent(new CustomEvent('layout:ready'));
}

function highlightActiveNav() {
  const current = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('#mainNav a[data-nav]').forEach(a => {
    if (a.dataset.nav === current) a.classList.add('active');
  });
}

function wireNavToggle() {
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mainNav');
  if (toggle && nav) toggle.addEventListener('click', () => nav.classList.toggle('open'));
}

function wireThemeToggle() {
  const themeToggle = document.getElementById('themeToggle');
  const root = document.documentElement;

  function sync() {
    if (!themeToggle) return;
    const isDark = root.getAttribute('data-theme') === 'dark';
    const icon = themeToggle.querySelector('.theme-toggle-icon');
    if (icon) icon.textContent = isDark ? '☀️' : '🌙';
    themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
  }
  sync();

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const isDark = root.getAttribute('data-theme') === 'dark';
      if (isDark) {
        root.removeAttribute('data-theme');
        try { localStorage.setItem('theme', 'light'); } catch (e) {}
      } else {
        root.setAttribute('data-theme', 'dark');
        try { localStorage.setItem('theme', 'dark'); } catch (e) {}
      }
      sync();
    });
  }
}

function setFooterYear() {
  const el = document.getElementById('footerBottom');
  if (el) {
    const year = new Date().getFullYear();
    el.innerHTML = `© ${year} Explore Bangladesh. Built with PHP, MySQL &amp; ❤ for Bangladesh tourism.`;
  }
}

async function paintAuthArea() {
  const slot = document.getElementById('authSlot');
  if (!slot) return;

  let session = { logged_in: false };
  try {
    session = await apiGet('/api/session.php');
  } catch (e) { /* treat as logged out */ }

  document.querySelectorAll('[data-auth-only]').forEach(el => {
    el.style.display = session.logged_in ? '' : 'none';
  });

  if (session.logged_in) {
    slot.innerHTML = `
      <span class="user-pill">👋 ${escapeHtml(session.user_name || 'Traveler')}</span>
      <a href="#" id="logoutLink" class="btn btn-ghost">Log out</a>`;
    document.getElementById('logoutLink').addEventListener('click', async (e) => {
      e.preventDefault();
      await apiPost('/api/logout.php', {});
      window.location.href = '/index.php';
    });
  } else {
    slot.innerHTML = `
      <a href="/login.php" class="btn btn-ghost">Log in</a>
      <a href="/register.php" class="btn btn-primary">Sign up</a>`;
  }
}

document.addEventListener('DOMContentLoaded', loadLayout);
