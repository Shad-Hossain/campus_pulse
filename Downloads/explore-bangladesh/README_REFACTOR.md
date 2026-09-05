# Architecture change: HTML pages are now pure HTML, PHP is API-only

## Pattern

- `/api/*.php` — the ONLY PHP that touches the database or session. Every
  endpoint returns JSON. `api/_bootstrap.php` is shared (session_start,
  DB connect, `json_out()`/`json_input()` helpers, `require_login()` /
  `require_admin()` guards).
- Top-level `*.php` files (`index.php`, `destinations.php`, ...) are now
  100% raw HTML + `<script>` — no `<?php ... ?>` anywhere in them. They
  keep the `.php` extension only because that's what you asked for; a
  plain web server can serve them as static files.
- `includes/header.html` / `includes/footer.html` — static fragments,
  injected into every page by `js/layout.js` (this replaces the old
  `include header.php / footer.php` and the session-based
  logged-in/logged-out `<?php if ?>` block — that now happens in JS
  after calling `GET /api/session.php`).
- `js/api.js` — tiny `apiGet()` / `apiPost()` fetch wrapper every page uses.
- `js/util.js` — `escapeHtml`, `money`, `truncateText`, etc. (client-side
  version of `includes/functions.php`).
- `js/layout.js` — loads header/footer, dark-mode toggle, mobile nav,
  paints the login/logout area.
- `js/script.js` — favourite-heart AJAX + weather-planner widget.

## Converted so far

- `index.php` → `api/home_data.php`
- `destinations.php` → `api/destinations_list.php`, `api/filters.php`
- `destination_details.php` → `api/destination_detail.php`, `api/review_add.php`
- `login.php` → `api/login.php`
- `register.php` → `api/register.php`
- `logout.php` → `api/logout.php`
- `favourites.php` → `api/favourites_list.php`, `api/toggle_favourite.php`
- `api/get_weather.php` — kept (was already API-only)
- `api/session.php` — new, used by the header on every page

## Still on the old PHP+HTML-mixed pattern (not converted yet)

Public: `hotels.php`, `weather_suggestion.php`, `ticket_booking.php`,
`hotel_booking.php`, `shared_rides.php`, `booking_history.php`.

Admin panel: `admin/login.php`, `admin/dashboard.php`,
`admin/destinations.php`, `admin/hotels.php`, `admin/reviews.php`,
`admin/bookings.php`, `admin/logout.php`, and `admin/includes/*`.

These still work exactly as before (untouched), but mix PHP into the
HTML the same way the original project did. Say the word and I'll
convert these in the same pattern.

## Setup — unchanged

Same as before: create the DB from `database.sql`, set `config/db.php`
credentials, optionally set `config/weather_api_key.php`, then serve
this folder with PHP (`php -S localhost:8000` or XAMPP/WAMP htdocs).
