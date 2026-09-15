<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in — COMPASS</title>
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

<link rel="stylesheet" href="/explore-bangladesh-main/css/style.css?v=2">
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
    <p style="margin-top:16px; font-size:.9rem;">No account? <a href="/explore-bangladesh-main/register.php" style="color:var(--river-dark); font-weight:600;">Sign up</a></p>
  </div>
</section>

<div id="site-footer"></div>

<script src="/explore-bangladesh-main/js/util.js"></script>
<script src="/explore-bangladesh-main/js/api.js"></script>
<script src="/explore-bangladesh-main/js/layout.js"></script>
<script src="/explore-bangladesh-main/js/script.js"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiPost('/explore-bangladesh-main/api/login.php', {
    email: fd.get('email'),
    password: fd.get('password'),
  });
  if (res.success) {
    window.location.href = '/explore-bangladesh-main/index.php';
  } else {
    document.getElementById('formMsg').innerHTML =
      `<div class="advice-box warn">${escapeHtml(res.error || 'Invalid email or password.')}</div>`;
  }
});
</script>
</body>
</html>
