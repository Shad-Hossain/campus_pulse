<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>COMPASS</title>
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
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/explore-bangladesh-main/css/style.css?v=2">
<link rel="stylesheet" href="/explore-bangladesh-main/css/home.css">
</head>
<body>

<header class="cs-header">
  <div class="cs-header-inner">
    <a href="/explore-bangladesh-main/index.php" class="cs-brand">
      <span class="cs-brand-mark">
        <svg viewBox="0 0 24 24" class="cs-brand-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polygon points="15.5 8.5 13.6 13.6 8.5 15.5 10.4 10.4" fill="currentColor" stroke="none"/></svg>
      </span>
      <span>COMPASS<span class="brand-dot">.</span></span>
    </a>

    <nav class="cs-nav" id="mainNav">
      <a href="/explore-bangladesh-main/index.php" data-nav="index.php">Home</a>
      <a href="/explore-bangladesh-main/destinations.php" data-nav="destinations.php">Explore</a>
      <a href="/explore-bangladesh-main/hotels.php" data-nav="hotels.php">Hotels</a>
      <a href="/explore-bangladesh-main/shared_rides.php" data-nav="shared_rides.php">Transport</a>
      <a href="/explore-bangladesh-main/weather_suggestion.php" data-nav="weather_suggestion.php">Food</a>
      <a href="#" class="cs-soon" title="Coming soon">Trending</a>
      <a href="#" class="cs-soon" title="Coming soon">Heritage &amp; Culture</a>
      <a href="#" class="cs-soon" title="Coming soon">Guides &amp; Translators</a>
    </nav>

    <div class="cs-header-actions" id="headerAuthArea">
      <button type="button" class="cs-theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <span class="theme-toggle-icon" aria-hidden="true">🌙</span>
      </button>
      <span id="authSlot">
        <a href="/explore-bangladesh-main/login.php" class="cs-signin">Sign in</a>
        <a href="/explore-bangladesh-main/register.php" class="cs-nav-cta">Join COMPASS</a>
      </span>
    </div>

    <button class="cs-nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
  </div>
</header>

<section class="cs-hero">
  <div class="cs-hero-bg">
    <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1920&q=70" alt="Dense green forest with tall straight trees">
    <div class="cs-hero-overlay"></div>
  </div>
  <div class="cs-hero-content">
    <span class="cs-hero-badge">
      <svg viewBox="0 0 24 24" class="cs-spark" fill="currentColor"><path d="M12 2l2.4 4.8L19 8l-3.6 3.6.9 5.4L12 14.5l-4.3 2.5.9-5.4L5 8l4.6-1.2L12 2z"/></svg>
      Discover Bangladesh with confidence
    </span>
    <h1 class="cs-hero-title">COMPASS<span class="dot">.</span></h1>
    <p class="cs-hero-subtitle">Your Smart Travel Companion</p>
    <p class="cs-hero-desc">Decide where to go, when to go, what to experience, and who you can trust —<br>with live weather, crowd levels, verified guides and one unified booking flow.</p>

    <form class="cs-search" id="homeSearch">
      <div class="cs-search-row">
        <div class="cs-sfield cs-sfield-dest">
          <svg viewBox="0 0 24 24" class="cs-search-ic" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" name="q" id="searchQ" placeholder="Search destinations, hotels, food...">
        </div>
        <div class="cs-sfield cs-sfield-date">
          <input type="date" name="date" id="searchDate">
          <svg viewBox="0 0 24 24" class="cs-cal-ic" id="searchCalIcon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <button type="submit" class="cs-search-btn">Search</button>
      </div>
    </form>

    <div class="cs-suggest">
      <a href="/explore-bangladesh-main/destinations.php" class="cs-suggest-pill">Bronze: Bandarban trail</a>
      <a href="/explore-bangladesh-main/destinations.php" class="cs-suggest-pill">2.5hrs: Sajek sunrise</a>
    </div>
  </div>
</section>

<div class="cs-stats-wrap">
  <div class="cs-stats" id="heroStats">
    <div class="cs-stat"><span class="ic">🗺️</span><b>13</b><span>Destinations</span></div>
    <div class="cs-stat"><span class="ic">🏨</span><b>7+</b><span>Hotels &amp; stays</span></div>
    <div class="cs-stat"><span class="ic">🚌</span><b>8</b><span>Transport options</span></div>
    <div class="cs-stat"><span class="ic">🧭</span><b>5</b><span>Verified locals</span></div>
  </div>
</div>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Popular</span>
      <h2 class="cs-title">Popular destinations</h2>
      <p class="cs-sub">Most-visited stays and dining activity across the country.</p>
    </div>
    <a href="/explore-bangladesh-main/destinations.php" class="cs-viewall">View all →</a>
  </div>
  <div class="hm-grid cs-grid-4" id="popularGrid"></div>
</section>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Right now</span>
      <h2 class="cs-title">Trending this month</h2>
      <p class="cs-sub">Destinations freshest in their season right now.</p>
    </div>
  </div>
  <div class="hm-grid cs-grid-4" id="trendingGrid"></div>
</section>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Top rated</span>
      <h2 class="cs-title">Highest rated</h2>
      <p class="cs-sub">Best-reviewed stays and restaurants in the database.</p>
    </div>
  </div>
  <div class="hm-grid cs-grid-3" id="highestGrid"></div>
</section>

<section class="cs-section" id="weatherRecSection">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Weather-smart</span>
      <h2 class="cs-title">Recommended for current weather</h2>
      <p class="cs-sub">Based on each destination's best visiting season for this month.</p>
    </div>
    <a href="/explore-bangladesh-main/weather_suggestion.php" class="cs-viewall">Open weather planner →</a>
  </div>
  <div class="hm-grid cs-grid-4" id="weatherGrid"></div>
</section>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Nearby</span>
      <h2 class="cs-title">Nearby destinations</h2>
      <p class="cs-sub">Sorted by distance from your current location (browser GPS).</p>
    </div>
  </div>
  <div class="hm-grid cs-grid-4" id="nearbyGrid">
    <div class="hm-loading">📍 Loading your location…</div>
  </div>
</section>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Culture</span>
      <h2 class="cs-title">Heritage &amp; culture</h2>
      <p class="cs-sub">Ancient sites and UNESCO ruins across the delta.</p>
    </div>
  </div>
  <div class="cs-placeholder">
    <div style="font-size:2rem;">🏛️</div>
    <b>Coming soon</b>
    <p>Heritage &amp; culture is being built by another teammate — this section will appear here shortly.</p>
  </div>
</section>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Partners</span>
      <h2 class="cs-title">Featured partners</h2>
      <p class="cs-sub">Hotels, restaurants and transport operators on COMPASS.</p>
    </div>
  </div>
  <div class="hm-grid cs-grid-4" id="partnerGrid"></div>
</section>

<section class="cs-section">
  <div class="cs-sec-head">
    <div>
      <span class="cs-eyebrow">Deals</span>
      <h2 class="cs-title">Discount deals</h2>
      <p class="cs-sub">Seasonal offers from our partners.</p>
    </div>
  </div>
  <div class="hm-grid cs-grid-4" id="dealGrid">
    <div class="cs-placeholder">
      <div style="font-size:2rem;">🎁</div>
      <b>No active deals yet</b>
      <p>Deals will appear here once the offers table is set up.</p>
    </div>
  </div>
</section>

<section class="cs-section" id="about">
  <div class="cs-cta">
    <div>
      <span class="cs-eyebrow">About COMPASS</span>
      <h2>Know your weather before you go</h2>
      <p>COMPASS brings Bangladesh's destinations, hotels, transport routes and weather forecasts into one platform — so you can pick the right place at the right time.</p>
      <a href="/explore-bangladesh-main/weather_suggestion.php" class="cs-btn cs-btn-light">Open the weather planner →</a>
    </div>
    <div class="cs-note">
      ⚠️ <strong>How it works:</strong> every destination has GPS coordinates linked to a live forecast. We score each day 0–100 for travel based on rain, storms and wind — and warn you before you book a ticket into bad weather.
    </div>
  </div>
</section>

<footer class="cs-footer">
  <div class="cs-footer-inner">
    <div>
      <span class="cs-footer-brand"><span class="cs-brand-mark">🧭</span> COMPASS</span>
      <p style="margin:12px 0 0; font-size:.85rem; line-height:1.6;">Your smart companion for exploring Bangladesh — destinations, stays, rides and weather in one place.</p>
    </div>
    <div>
      <h5>Explore</h5>
      <a href="/explore-bangladesh-main/destinations.php">Destinations</a>
      <a href="/explore-bangladesh-main/hotels.php">Hotels</a>
      <a href="/explore-bangladesh-main/shared_rides.php">Shared rides</a>
      <a href="/explore-bangladesh-main/ticket_booking.php">Book tickets</a>
    </div>
    <div>
      <h5>Plan</h5>
      <a href="/explore-bangladesh-main/weather_suggestion.php">Weather planner</a>
      <a href="/explore-bangladesh-main/favourites.php">Favourites</a>
      <a href="/explore-bangladesh-main/booking_history.php">My bookings</a>
    </div>
    <div>
      <h5>Account</h5>
      <a href="/explore-bangladesh-main/login.php">Log in</a>
      <a href="/explore-bangladesh-main/register.php">Sign up</a>
      <a href="/explore-bangladesh-main/index.php#about">About</a>
    </div>
  </div>
  <div class="cs-footer-bottom" id="footerBottom">© COMPASS.</div>
</footer>

<script src="/explore-bangladesh-main/js/util.js"></script>
<script src="/explore-bangladesh-main/js/api.js"></script>
<script src="/explore-bangladesh-main/js/layout.js"></script>
<script src="/explore-bangladesh-main/js/script.js"></script>
<script>
const BASE = '/explore-bangladesh-main/';
const IMG_MAP = {
  1: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4',
  2: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
  3: 'https://images.unsplash.com/photo-1577083288073-40892c6f211e',
};
const imgUrl = (base, w = 800) => base + '?auto=format&fit=crop&w=' + w + '&q=60';

function destCardHtml(d) {
  const entry = d.entry_fee > 0 ? money(d.entry_fee) + ' entry' : 'Free entry';
  return `
    <article class="cs-card">
      <div class="cs-card-media">
        <img src="${imgUrl(IMG_MAP[d.category_id] || IMG_MAP[1])}" alt="${escapeHtml(d.name)}" loading="lazy">
        <span class="cs-chip">${escapeHtml(d.category_name)}</span>
        <button class="fav-btn cs-fav" data-dest-id="${d.destination_id}" aria-label="Favourite">🤍</button>
      </div>
      <div class="cs-card-body">
        <a class="cs-card-title" href="${BASE}destination_details.php?id=${d.destination_id}">${escapeHtml(d.name)}</a>
        <div class="cs-card-loc">📍 ${escapeHtml(d.district_name)}</div>
        <p class="cs-card-desc">${escapeHtml(d.description)}</p>
        <div class="cs-card-meta"><span class="fee">${entry}</span><span>🕒 ${escapeHtml(d.best_time_to_visit || '')}</span></div>
        <a href="${BASE}destination_details.php?id=${d.destination_id}" class="cs-btn cs-btn-primary">View details</a>
      </div>
    </article>`;
}

let ALL_LOCS = [];

function renderStats(data) {
  const stats = [
    ['\u{1F5FA}\u{FE0F}', '13', 'Destinations'],
    ['\u{1F3E8}', '7+', 'Hotels & stays'],
    ['\u{1F68C}', '8', 'Transport options'],
    ['\u{1F9ED}', '5', 'Verified locals'],
  ];
  document.getElementById('heroStats').innerHTML = stats.map(([ic, v, l]) => `
    <div class="cs-stat">
      <span class="ic">${ic}</span>
      <b>${v}</b>
      <span>${l}</span>
    </div>`).join('');
}

function renderHighest(data) {
  const icons = { hotel: '\u{1F3E8}', food: '\u{1F37D}\u{FE0F}' };
  document.getElementById('highestGrid').innerHTML = data.highest_rated.map(item => `
    <a class="cs-card cs-icon-card" href="${item.link}">
      <span class="cs-icon-badge">${icons[item.kind]}</span>
      <div class="cs-icon-card-body">
        <h4>${escapeHtml(item.name)}</h4>
        <div class="meta">${escapeHtml(item.meta)}</div>
        <div class="stars">${starString(item.rating)} ${item.rating.toFixed(1)}</div>
      </div>
    </a>`).join('') || '<div class="hm-loading">No ratings yet.</div>';
}

function renderPartners(data) {
  document.getElementById('partnerGrid').innerHTML = data.partners.map(p => `
    <a class="cs-card cs-icon-card" href="${p.link}">
      <span class="cs-icon-badge">${p.kind}</span>
      <div class="cs-icon-card-body">
        <h4>${escapeHtml(p.name)}</h4>
        <div class="meta">${escapeHtml(p.meta)}</div>
      </div>
    </a>`).join('') || '<div class="hm-loading">No partners yet.</div>';
}

function renderDeals(data) {
  if (!data.deals.length) return;
  document.getElementById('dealGrid').innerHTML = data.deals.map(d => `
    <div class="cs-card cs-deal-card">
      <div class="cs-deal-pct">${d.discount_percent}% off</div>
      <h4>${escapeHtml(d.title)}</h4>
      <p>${escapeHtml(d.description || '')}</p>
      ${d.code ? `<span class="cs-deal-code">${escapeHtml(d.code)}</span>` : ''}
      ${d.dest_name ? `<div class="meta" style="font-size:.72rem;color:#64748b;margin-top:6px;">@ ${escapeHtml(d.dest_name)}</div>` : ''}
    </div>`).join('');
}

function renderWeather(data) {
  document.getElementById('weatherGrid').innerHTML = data.weather_picks.map(d => `
    <article class="cs-card">
      <div class="cs-card-media">
        <img src="${imgUrl(IMG_MAP[d.category_id] || IMG_MAP[1])}" alt="${escapeHtml(d.name)}" loading="lazy">
        <span class="cs-chip" style="background:#dcfce7;color:#166534;">${escapeHtml(d.season_label)}</span>
      </div>
      <div class="cs-card-body">
        <a class="cs-card-title" href="${BASE}destination_details.php?id=${d.destination_id}">${escapeHtml(d.name)}</a>
        <div class="cs-card-loc">📍 ${escapeHtml(d.district_name)} · ${escapeHtml(d.best_time_to_visit || '')}</div>
        <p class="cs-card-desc">${escapeHtml(d.description)}</p>
        <div class="cs-card-meta"><span class="fee">${escapeHtml(d.category_name)}</span></div>
        <a href="${BASE}destination_details.php?id=${d.destination_id}" class="cs-btn cs-btn-primary">View details</a>
      </div>
    </article>`).join('');
}

function haversine(lat1, lon1, lat2, lon2) {
  const R = 6371, toRad = x => x * Math.PI / 180;
  const dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1);
  const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function renderNearby(loc) {
  const grid = document.getElementById('nearbyGrid');
  if (!ALL_LOCS || !ALL_LOCS.length) {
    grid.innerHTML = '<div class="hm-loading">Location data unavailable.</div>';
    return;
  }
  const sorted = ALL_LOCS.slice().sort((a, b) => {
    const da = haversine(loc.lat, loc.lon, a.latitude, a.longitude);
    const db = haversine(loc.lat, loc.lon, b.latitude, b.longitude);
    a._km = da; b._km = db; return da - db;
  }).slice(0, 8);
  grid.innerHTML = sorted.map(d => `
    <article class="cs-card">
      <div class="cs-card-media">
        <img src="${imgUrl(IMG_MAP[d.category_id] || IMG_MAP[1])}" alt="${escapeHtml(d.name)}" loading="lazy">
        <span class="cs-chip">${escapeHtml(d.category_name)}</span>
        <button class="fav-btn cs-fav" data-dest-id="${d.destination_id}" aria-label="Favourite">🤍</button>
      </div>
      <div class="cs-card-body">
        <a class="cs-card-title" href="${BASE}destination_details.php?id=${d.destination_id}">${escapeHtml(d.name)}</a>
        <div class="cs-card-loc">📍 ${escapeHtml(d.district_name)}</div>
        <p class="cs-card-desc">${escapeHtml(d.description)}</p>
        <div class="cs-card-meta"><span class="fee">${d._km ? d._km.toFixed(0) + ' km away' : ''}</span><span>🕒 ${escapeHtml(d.best_time_to_visit || '')}</span></div>
        <a href="${BASE}destination_details.php?id=${d.destination_id}" class="cs-btn cs-btn-primary">View details</a>
      </div>
    </article>`).join('');
}

async function renderHome() {
  const data = await apiGet(BASE + 'api/compass_home.php');
  ALL_LOCS = data.locations;

  renderStats(data);

  document.getElementById('popularGrid').innerHTML = data.popular.map(destCardHtml).join('');
  document.getElementById('trendingGrid').innerHTML = data.trending.map(destCardHtml).join('');
  renderHighest(data);
  renderWeather(data);
  renderPartners(data);
  renderDeals(data);

  // Nearby destinations via GPS (progressive enhancement)
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => renderNearby({ lat: pos.coords.latitude, lon: pos.coords.longitude }),
      () => { document.getElementById('nearbyGrid').innerHTML = '<div class="hm-loading">📍 Enable location to see what\u2019s nearest to you.</div>'; },
      { timeout: 6000 }
    );
  } else {
    document.getElementById('nearbyGrid').innerHTML = '<div class="hm-loading">📍 Location not supported.</div>';
  }

  document.dispatchEvent(new CustomEvent('content:rendered'));
}

(function initHeroSearch() {
  const d = document.getElementById('searchDate');
  if (d) { d.value = new Date().toISOString().slice(0, 10); d.min = d.value; }

  // Calendar icon opens the native date picker when clicked.
  const cal = document.getElementById('searchCalIcon');
  if (cal && d) {
    cal.addEventListener('click', (e) => {
      e.preventDefault();
      try { d.showPicker && d.showPicker(); } catch (err) { d.focus(); d.click(); }
    });
    // The whole date field is also clickable.
    const wrap = cal.closest('.cs-sfield');
    if (wrap) wrap.style.cursor = 'pointer';
    d.addEventListener('click', () => { try { d.showPicker && d.showPicker(); } catch (err) {} });
  }

  // Search form submits to the live destinations results page.
  const form = document.getElementById('homeSearch');
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const q = document.getElementById('searchQ').value.trim();
      const date = document.getElementById('searchDate').value;
      const params = {};
      if (q) params.q = q;
      if (date) params.date = date;
      const qs = new URLSearchParams(params).toString();
      window.location.href = BASE + 'destinations.php' + (qs ? '?' + qs : '');
    });
  }
})();

renderHome();
</script>
</body>
</html>