<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Destinations — COMPASS</title>
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
  <div class="container">
    <span class="eyebrow">Browse</span>
    <h2>All destinations</h2>
    <p style="color:var(--ink-soft); max-width:60ch;">Filter by terrain type or region, then open a destination to see live weather, transport routes, and nearby stays.</p>

    <form id="filterForm" class="filter-bar" style="margin-top:24px;">
      <input type="text" name="q" id="qInput" placeholder="Search destination…">
      <select name="category" id="categorySelect">
        <option value="0">All categories</option>
      </select>
      <select name="division" id="divisionSelect">
        <option value="0">All divisions</option>
      </select>
      <button type="submit" class="btn btn-forest btn-sm">Filter</button>
    </form>

    <div id="resultsBox" style="margin-top:30px;">
      <p>Loading destinations…</p>
    </div>
  </div>
</section>

<div id="site-footer"></div>

<script src="/explore-bangladesh-main/js/util.js"></script>
<script src="/explore-bangladesh-main/js/api.js"></script>
<script src="/explore-bangladesh-main/js/layout.js"></script>
<script src="/explore-bangladesh-main/js/script.js"></script>
<script>
function destCardHtml(d) {
  const entry = d.entry_fee > 0 ? money(d.entry_fee) + ' entry' : 'Free entry';
  const weatherChip = d.weather_score !== null
    ? `<span class="weather-chip ${d.weather_score_class}">● ${d.weather_score}/100</span>`
    : `<span class="weather-chip score-good">Forecast pending</span>`;
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
          ${weatherChip}
        </div>
        <a href="/explore-bangladesh-main/destination_details.php?id=${d.destination_id}" class="btn btn-forest btn-block btn-sm">View details</a>
      </div>
    </div>`;
}

function currentFilters() {
  return {
    q: qs('q'),
    category: qs('category', '0'),
    division: qs('division', '0'),
  };
}

async function populateFilterOptions() {
  const { categories, divisions } = await apiGet('/explore-bangladesh-main/api/filters.php');
  const f = currentFilters();

  const catSel = document.getElementById('categorySelect');
  categories.forEach(c => {
    const opt = document.createElement('option');
    opt.value = c.category_id;
    opt.textContent = `${c.icon} ${c.category_name}`;
    if (String(c.category_id) === f.category) opt.selected = true;
    catSel.appendChild(opt);
  });

  const divSel = document.getElementById('divisionSelect');
  divisions.forEach(d => {
    const opt = document.createElement('option');
    opt.value = d.division_id;
    opt.textContent = d.division_name;
    if (String(d.division_id) === f.division) opt.selected = true;
    divSel.appendChild(opt);
  });

  document.getElementById('qInput').value = f.q;
  catSel.addEventListener('change', () => document.getElementById('filterForm').requestSubmit());
  divSel.addEventListener('change', () => document.getElementById('filterForm').requestSubmit());
}

async function loadResults() {
  const box = document.getElementById('resultsBox');
  box.innerHTML = '<p>Loading destinations…</p>';

  const f = currentFilters();
  const params = new URLSearchParams({ q: f.q, category: f.category, division: f.division });
  const data = await apiGet('/explore-bangladesh-main/api/destinations_list.php?' + params.toString());

  if (!data.destinations.length) {
    box.innerHTML = '<div class="info-note">No destinations match those filters yet. Try clearing a filter.</div>';
  } else {
    box.innerHTML = `<div class="destination-grid">${data.destinations.map(destCardHtml).join('')}</div>`;
  }
  document.dispatchEvent(new CustomEvent('content:rendered'));
}

document.getElementById('filterForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const f = new FormData(e.target);
  const params = new URLSearchParams();
  for (const [k, v] of f.entries()) if (v) params.set(k, v);
  history.replaceState(null, '', '?' + params.toString());
  loadResults();
});

populateFilterOptions();
loadResults();
</script>
</body>
</html>
