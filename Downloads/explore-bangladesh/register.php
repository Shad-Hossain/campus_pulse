<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign up — Explore Bangladesh</title>
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
    <span class="eyebrow">Join Explore Bangladesh</span>
    <h2>Create your account</h2>
    <div id="formMsg"></div>
    <form id="registerForm" class="weather-form" style="margin-top:20px;">
      <div class="field"><label>Full name</label><input type="text" name="full_name" required></div>
      <div class="field"><label>Email</label><input type="email" name="email" required></div>
      <div class="field"><label>Phone</label><input type="text" name="phone"></div>
      <div class="field"><label>Password</label><input type="password" name="password" required minlength="6"></div>
      <button type="submit" class="btn btn-primary btn-block">Sign up</button>
    </form>
    <p style="margin-top:16px; font-size:.9rem;">Already have an account? <a href="/login.php" style="color:var(--river-dark); font-weight:600;">Log in</a></p>
  </div>
</section>

<div id="site-footer"></div>

<script src="/js/util.js"></script>
<script src="/js/api.js"></script>
<script src="/js/layout.js"></script>
<script src="/js/script.js"></script>
<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiPost('/api/register.php', {
    full_name: fd.get('full_name'),
    email: fd.get('email'),
    phone: fd.get('phone'),
    password: fd.get('password'),
  });
  if (res.success) {
    window.location.href = '/index.php';
  } else {
    document.getElementById('formMsg').innerHTML =
      `<div class="advice-box warn">${escapeHtml(res.error || 'Please fill all fields correctly.')}</div>`;
  }
});
</script>
</body>
</html>
