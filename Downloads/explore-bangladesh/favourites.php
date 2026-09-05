<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Favourites — Explore Bangladesh</title>
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
  <div class="container">
    <span class="eyebrow">Saved</span>
    <h2>Your favourite destinations</h2>
    <div id="favBox" style="margin-top:20px;"><p>Loading…</p></div>
  </div>
</section>

<div id="site-footer"></div>

<script src="/js/util.js"></script>
<script src="/js/api.js"></script>
<script src="/js/layout.js"></script>
<script src="/js/script.js"></script>
<script>
async function loadFavourites() {
  const box = document.getElementById('favBox');
  const data = await apiGet('/api/favourites_list.php');

  if (data.status === 'login_required') {
    window.location.href = '/login.php';
    return;
  }

  if (!data.favourites.length) {
    box.innerHTML = '<div class="info-note">You haven\'t saved anything yet. Browse <a href="/destinations.php">destinations</a> and tap the heart icon.</div>';
    return;
  }

  box.innerHTML = `<div class="destination-grid" style="margin-top:10px;">${data.favourites.map(d => `
    <div class="dest-card">
      <div class="dest-media cat-${d.category_id}">
        ${d.icon}
        <span class="dest-tag">${escapeHtml(d.category_name)}</span>
        <button class="fav-btn" data-dest-id="${d.destination_id}">❤️</button>
      </div>
      <div class="dest-body">
        <h3>${escapeHtml(d.name)}</h3>
        <div class="dest-loc">📍 ${escapeHtml(d.district_name)}</div>
        <a href="/destination_details.php?id=${d.destination_id}" class="btn btn-forest btn-block btn-sm">View details</a>
      </div>
    </div>`).join('')}</div>`;

  document.dispatchEvent(new CustomEvent('content:rendered'));
}

loadFavourites();
</script>
</body>
</html>
