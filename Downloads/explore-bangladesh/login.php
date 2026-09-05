<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in — Explore Bangladesh</title>
<script>
  (function () {
    try {
      var saved = localStorage.getItem('theme');
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (saved === 'dark' || (!saved && prefersDark)) {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    } catch (e) {}
  })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div id="site-header"></div>

<section class="section-tight">
  <div class="container" style="max-width:440px;">
    <span class="eyebrow">Welcome back</span>
    <h2>Log in</h2>
    <div id="formMsg"></div>
    <form id="loginForm" class="weather-form" style="margin-top:20px;">
      <div class="field"><label>Email</label><input type="email" name="email" required></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>
    <p style="margin-top:16px; font-size:.9rem;">No account? <a href="/register.php" style="color:var(--river-dark); font-weight:600;">Sign up</a></p>
  </div>
</section>

<div id="site-footer"></div>

<script src="/js/util.js"></script>
<script src="/js/api.js"></script>
<script src="/js/layout.js"></script>
<script src="/js/script.js"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiPost('/api/login.php', {
    email: fd.get('email'),
    password: fd.get('password'),
  });
  if (res.success) {
    window.location.href = '/index.php';
  } else {
    document.getElementById('formMsg').innerHTML =
      `<div class="advice-box warn">${escapeHtml(res.error || 'Invalid email or password.')}</div>`;
  }
});
</script>
</body>
</html>
