<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Explore Bangladesh</title>
<script>
  // Apply saved theme before first paint, to avoid a light-mode flash.
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

<section class="hero">
  <div class="hero-inner">
    <span class="hero-eyebrow">🌤️ Weather-smart trip planning for Bangladesh</span>
    <h1>Plan your Bangladesh trip <span>around the sky</span>, not against it.</h1>
    <p class="lede">Explore Bangladesh brings destinations, transport, hotels, and live weather forecasts into one platform — so you know exactly when and where to go.</p>
    <div class="hero-actions">
      <a href="/destinations.php" class="btn btn-primary">Explore destinations</a>
      <a href="/weather_suggestion.php" class="btn btn-outline-light">Check weather planner →</a>
    </div>
    <div class="hero-stats" id="heroStats">
      <div class="hero-stat"><b>…</b><span>Curated destinations</span></div>
      <div class="hero-stat"><b>…</b><span>Districts covered</span></div>
      <div class="hero-stat"><b>3</b><span>Terrain categories</span></div>
    </div>
  </div>
  <div class="hero-waves">
    <svg viewBox="0 0 1440 110" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%; height:90px;">
      <path fill="#FAF6EC" d="M0,64L48,58.7C96,53,192,43,288,48C384,53,480,75,576,80C672,85,768,75,864,58.7C960,43,1056,21,1152,21.3C1248,21,1344,43,1392,53.3L1440,64L1440,120L0,120Z"></path>
    </svg>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Categories</span>
        <h2>Three ways to see Bangladesh</h2>
        <p>From misty hill valleys to the world's longest sea beach and UNESCO heritage ruins.</p>
      </div>
      <a href="/destinations.php" class="btn btn-ghost">View all destinations →</a>
    </div>

    <div class="category-grid" id="categoryGrid">
      <!-- filled by JS -->
    </div>
  </div>
</section>

<section class="section bg-alt">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Featured</span>
        <h2>Popular right now</h2>
      </div>
    </div>

    <div class="destination-grid" id="featuredGrid">
      <!-- filled by JS -->
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="weather-tool">
      <div>
        <span class="eyebrow">Weather Planner</span>
        <h2>Should you book that ticket today?</h2>
        <p>We pull live forecasts for every destination and score each day for travel — so you can avoid getting caught in monsoon rain or a rough sea crossing.</p>
        <a href="/weather_suggestion.php" class="btn btn-primary">Open the weather planner →</a>
      </div>
      <div>
        <div class="info-note">
          🌦️ <strong>How it works:</strong> every destination has GPS coordinates linked to OpenWeatherMap. We fetch a 5-day forecast, calculate a 0–100 "travel score" from rain probability, storms, and wind — then warn you before you book a ticket into bad weather.
        </div>
      </div>
    </div>
  </div>
</section>

<div id="site-footer"></div>

<script src="/js/util.js"></script>
<script src="/js/api.js"></script>
<script src="/js/layout.js"></script>
<script src="/js/script.js"></script>
<script>
const SLUG_MAP = { 1: 'mountain', 2: 'sea', 3: 'heritage' };
const DESC_MAP = {
  1: 'Cloud-covered valleys, tea gardens, and tribal hill trails.',
  2: 'Endless beaches, coral islands, and coastal sunsets.',
  3: 'Ancient mosques, temples, and UNESCO ruins across the delta.',
};

function destCardHtml(d) {
  const entry = d.entry_fee > 0 ? money(d.entry_fee) + ' entry' : 'Free entry';
  return `
    <div class="dest-card">
      <div class="dest-media cat-${d.category_id}">
        ${d.icon}
        <span class="dest-tag">${escapeHtml(d.category_name)}</span>
        <button class="fav-btn" data-dest-id="${d.destination_id}">🤍</button>
      </div>
      <div class="dest-body">
        <h3>${escapeHtml(d.name)}</h3>
        <div class="dest-loc">📍 ${escapeHtml(d.district_name)}</div>
        <p class="dest-desc">${escapeHtml(d.description)}</p>
        <div class="dest-meta">
          <span>${entry}</span>
          <span>🕒 ${escapeHtml(d.best_time_to_visit || '')}</span>
        </div>
        <a href="/destination_details.php?id=${d.destination_id}" class="btn btn-forest btn-block btn-sm">View details</a>
      </div>
    </div>`;
}

async function renderHome() {
  const data = await apiGet('/api/home_data.php');

  document.getElementById('heroStats').innerHTML = `
    <div class="hero-stat"><b>${data.total_destinations}+</b><span>Curated destinations</span></div>
    <div class="hero-stat"><b>${data.total_districts}+</b><span>Districts covered</span></div>
    <div class="hero-stat"><b>3</b><span>Terrain categories</span></div>`;

  document.getElementById('categoryGrid').innerHTML = data.categories.map(cat => {
    const slug = SLUG_MAP[cat.category_id] || 'mountain';
    return `
      <a href="/destinations.php?category=${cat.category_id}" class="category-card ${slug}">
        <span class="category-icon">${cat.icon}</span>
        <span class="category-count">${cat.count} spots</span>
        <h3>${escapeHtml(cat.category_name)}</h3>
        <p>${DESC_MAP[cat.category_id] || ''}</p>
      </a>`;
  }).join('');

  document.getElementById('featuredGrid').innerHTML = data.featured.map(destCardHtml).join('');

  document.dispatchEvent(new CustomEvent('content:rendered'));
}

renderHome();
</script>
</body>
</html>
