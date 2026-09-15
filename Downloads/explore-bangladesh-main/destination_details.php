<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title id="pageTitleTag">COMPASS</title>
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

<div id="pageContent">
  <div class="container section"><p>Loading…</p></div>
</div>

<div id="site-footer"></div>

<script src="/explore-bangladesh-main/js/util.js"></script>
<script src="/explore-bangladesh-main/js/api.js"></script>
<script src="/explore-bangladesh-main/js/layout.js"></script>
<script src="/explore-bangladesh-main/js/script.js"></script>
<script>
const destId = qs('id');

function weatherLabelClass(day) {
  return day.score_class || 'score-good';
}

function renderNotFound() {
  document.getElementById('pageContent').innerHTML =
    '<div class="container section"><div class="info-note">Destination not found.</div></div>';
}

function renderPage(data) {
  const d = data.destination;
  document.title = d.name + ' — COMPASS';

  const forecastHtml = data.forecast.length
    ? `<div class="forecast-strip">${data.forecast.map(day => `
        <div class="forecast-day">
          <div class="d">${new Date(day.forecast_date).toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' })}</div>
          <div class="icon">🌤️</div>
          <div class="t">${day.temp_max}° / ${day.temp_min}°</div>
          <span class="weather-chip ${weatherLabelClass(day)}" style="margin-top:6px;">${day.weather_score}</span>
        </div>`).join('')}</div>`
    : `<div class="info-note">No forecast cached yet. Add your OpenWeatherMap API key in <code>config/weather_api.php</code> to fetch live data for this destination.</div>`;

  const attractionsHtml = data.attractions.length ? `
    <h3 style="margin-top:30px;">Nearby attractions</h3>
    <ul>${data.attractions.map(a => `<li><strong>${escapeHtml(a.attraction_name)}</strong> — ${a.distance_km} km · ${escapeHtml(a.description)}</li>`).join('')}</ul>` : '';

  const routesHtml = data.routes.length ? `
    <table class="data-table">
      <thead><tr><th>Mode</th><th>From</th><th>Time</th><th>Cost</th><th></th></tr></thead>
      <tbody>${data.routes.map(r => `
        <tr>
          <td>${escapeHtml(r.transport_type)} (${escapeHtml(r.operator_name)})</td>
          <td>${escapeHtml(r.origin)}${r.stop_over ? ' → ' + escapeHtml(r.stop_over) : ''}</td>
          <td>${escapeHtml(r.estimated_time)}</td>
          <td>${money(r.estimated_cost)}</td>
          <td><a href="/explore-bangladesh-main/ticket_booking.php?route=${r.route_id}" class="btn btn-primary btn-sm">Book</a></td>
        </tr>`).join('')}</tbody>
    </table>` : `<div class="info-note">Transport data coming soon for this destination.</div>`;

  const hotelsSection = data.hotels.length ? `
    <section class="section bg-alt">
      <div class="container">
        <h3>Where to stay</h3>
        <div class="destination-grid">
          ${data.hotels.map(h => `
            <div class="dest-card">
              <div class="dest-body">
                <h3>${escapeHtml(h.hotel_name)}</h3>
                <div class="dest-loc">${escapeHtml(h.hotel_type)} · ⭐ ${Number(h.rating).toFixed(1)}</div>
                <p class="dest-desc">${money(h.price_range_min)} – ${money(h.price_range_max)} / night
                  ${h.free_breakfast ? ' · 🍳 Free breakfast' : ''}${h.swimming_pool ? ' · 🏊 Pool' : ''}</p>
                <div class="dest-meta"><span>📍 ${escapeHtml(h.address)}</span></div>
                <a href="/explore-bangladesh-main/hotel_booking.php?hotel=${h.hotel_id}" class="btn btn-primary btn-block btn-sm">Book this hotel</a>
              </div>
            </div>`).join('')}
        </div>
      </div>
    </section>` : '';

  const servicesSection = data.services.length ? `
    <section class="section">
      <div class="container">
        <h3>Nearby essentials</h3>
        <table class="data-table">
          <thead><tr><th>Type</th><th>Name</th><th>Address</th><th>Rating</th><th>Contact</th></tr></thead>
          <tbody>${data.services.map(s => `
            <tr>
              <td>${escapeHtml(s.service_type)}</td>
              <td>${s.map_link ? `<a href="${escapeHtml(s.map_link)}" target="_blank">${escapeHtml(s.service_name)}</a>` : escapeHtml(s.service_name)}</td>
              <td>${escapeHtml(s.address)}</td>
              <td>⭐ ${Number(s.rating).toFixed(1)}</td>
              <td>${escapeHtml(s.contact_no)}</td>
            </tr>`).join('')}</tbody>
        </table>
      </div>
    </section>` : '';

  const ratingSummary = data.avg_rating.total > 0
    ? `⭐ <strong>${data.avg_rating.avg_stars}</strong> average from ${data.avg_rating.total} rating(s)`
    : 'No ratings yet — be the first!';

  const reviewsHtml = data.reviews.length
    ? data.reviews.map(rv => `
        <div class="info-note" style="margin-bottom:12px;">
          <strong>${escapeHtml(rv.full_name)}</strong>${rv.is_verified ? ' ✅' : ''}
          <span style="color:var(--ink-soft); font-size:.8rem;"> · ${new Date(rv.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
          <p style="margin:6px 0 0;">${escapeHtml(rv.review_text)}</p>
        </div>`).join('')
    : '<div class="info-note">No written reviews yet.</div>';

  const reviewFormHtml = data.logged_in ? `
    <form id="reviewForm" class="weather-form" style="background:#fff; padding:22px; border-radius:var(--radius-lg); box-shadow:var(--shadow-card);">
      <div class="field">
        <label>Your rating</label>
        <select name="stars" required>
          <option value="">Select stars…</option>
          ${[5,4,3,2,1].map(s => `<option value="${s}">${'⭐'.repeat(s)}</option>`).join('')}
        </select>
      </div>
      <div class="field">
        <label>Your review (optional)</label>
        <textarea name="review_text" rows="4" placeholder="Share your experience…" style="width:100%; padding:.8em 1em; border-radius:var(--radius-sm); border:1px solid rgba(31,92,74,.25); font-family:var(--font-body);"></textarea>
      </div>
      <div id="reviewMsg"></div>
      <button type="submit" class="btn btn-primary btn-block">Submit review</button>
    </form>` : `<div class="info-note">Please <a href="/explore-bangladesh-main/login.php">log in</a> to leave a review or rating.</div>`;

  document.getElementById('pageContent').innerHTML = `
    <section class="hero" style="padding-bottom:0;">
      <div class="hero-inner" style="padding-bottom:50px;">
        <span class="hero-eyebrow">${d.icon} ${escapeHtml(d.category_name)} · ${escapeHtml(d.district_name)}, ${escapeHtml(d.division_name)}</span>
        <h1 style="max-width:20ch;">${escapeHtml(d.name)}</h1>
        <p class="lede">${escapeHtml(d.description)}</p>
        <div class="hero-actions">
          <button class="btn btn-primary fav-btn-hero" data-dest-id="${d.destination_id}">
            <span class="fav-icon">🤍</span> Save to favourites
          </button>
          <a href="/explore-bangladesh-main/shared_rides.php?destination=${d.destination_id}" class="btn btn-outline-light">🚗 Share a ride</a>
          ${d.map_link ? `<a href="${escapeHtml(d.map_link)}" target="_blank" class="btn btn-outline-light">Open in Maps</a>` : ''}
        </div>
      </div>
      <div class="hero-waves">
        <svg viewBox="0 0 1440 110" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%; height:70px;">
          <path fill="#FAF6EC" d="M0,64L48,58.7C96,53,192,43,288,48C384,53,480,75,576,80C672,85,768,75,864,58.7C960,43,1056,21,1152,21.3C1248,21,1344,43,1392,53.3L1440,64L1440,120L0,120Z"></path>
        </svg>
      </div>
    </section>

    <section class="section-tight">
      <div class="container two-col">
        <div>
          <h3>At a glance</h3>
          <table class="data-table">
            <tr><th>Best time to visit</th><td>${escapeHtml(d.best_time_to_visit)}</td></tr>
            <tr><th>Entry fee</th><td>${d.entry_fee > 0 ? money(d.entry_fee) : 'Free'}</td></tr>
            <tr><th>Opening hours</th><td>${escapeHtml(d.opening_hours)}</td></tr>
            <tr><th>Safety tips</th><td>${escapeHtml(d.safety_tips)}</td></tr>
          </table>
          ${attractionsHtml}
        </div>
        <div>
          <h3>5-day forecast</h3>
          ${forecastHtml}
          <h3 style="margin-top:30px;">Getting there</h3>
          ${routesHtml}
        </div>
      </div>
    </section>

    ${hotelsSection}
    ${servicesSection}

    <section class="section bg-alt" id="reviews">
      <div class="container two-col">
        <div>
          <h3>Reviews &amp; ratings</h3>
          <p style="color:var(--ink-soft);">${ratingSummary}</p>
          ${reviewsHtml}
        </div>
        <div>
          <h3>Leave a review</h3>
          ${reviewFormHtml}
        </div>
      </div>
    </section>`;

  const form = document.getElementById('reviewForm');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      const res = await apiPost('/explore-bangladesh-main/api/review_add.php', {
        destination_id: destId,
        stars: fd.get('stars'),
        review_text: fd.get('review_text'),
      });
      const msg = document.getElementById('reviewMsg');
      if (res.success) {
        msg.innerHTML = '<div class="advice-box"><h4>✅ Thanks for sharing your experience!</h4></div>';
        loadDestination();
      } else {
        msg.innerHTML = `<div class="advice-box warn">${escapeHtml(res.error || 'Something went wrong.')}</div>`;
      }
    });
  }

  document.dispatchEvent(new CustomEvent('content:rendered'));
}

async function loadDestination() {
  if (!destId) { renderNotFound(); return; }
  const data = await apiGet('/explore-bangladesh-main/api/destination_detail.php?id=' + encodeURIComponent(destId));
  if (data.error) { renderNotFound(); return; }
  renderPage(data);
}

loadDestination();
</script>
</body>
</html>
