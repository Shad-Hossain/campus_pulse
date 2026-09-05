// Explore Bangladesh — shared front-end behaviour
// (Runs after js/layout.js has injected the header/footer.)

function wireFavButtons(root = document) {
  root.querySelectorAll('.fav-btn, .fav-btn-hero').forEach(btn => {
    if (btn.dataset.wired) return;
    btn.dataset.wired = '1';
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const destId = btn.dataset.destId;
      try {
        const data = await apiPost('/api/toggle_favourite.php', { destination_id: destId });
        if (data.status === 'login_required') {
          window.location.href = '/login.php';
          return;
        }
        const icon = data.is_favourite ? '❤️' : '🤍';
        const iconEl = btn.querySelector('.fav-icon');
        if (iconEl) {
          iconEl.textContent = icon;
        } else {
          btn.textContent = icon;
        }
      } catch (err) {
        console.error('Favourite toggle failed', err);
      }
    });
  });
}

function weatherIcon(condition) {
  const map = {
    Clear: '☀️', Clouds: '⛅', Rain: '🌧️', Thunderstorm: '⛈️',
    Drizzle: '🌦️', Mist: '🌫️', Fog: '🌫️', Snow: '❄️'
  };
  return map[condition] || '🌤️';
}

function renderWeatherResult(data, box) {
  if (!data.forecast || data.forecast.length === 0) {
    box.innerHTML = '<div class="info-note">No forecast data yet — add your OpenWeatherMap API key in <code>config/weather_api.php</code>, or check back once cached data is available.</div>';
    return;
  }

  let strip = '<div class="forecast-strip">';
  data.forecast.forEach(day => {
    const d = new Date(day.forecast_date);
    const label = d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });
    strip += `
      <div class="forecast-day">
        <div class="d">${label}</div>
        <div class="icon">${weatherIcon(day.condition_main)}</div>
        <div class="t">${day.temp_max}° / ${day.temp_min}°</div>
        <div style="font-size:.72rem;color:#8aa;margin-top:4px;">${day.rain_probability}% rain</div>
      </div>`;
  });
  strip += '</div>';

  let adviceHtml = '';
  if (data.advice && data.advice.found) {
    const cls = data.advice.suggest_alternate ? 'advice-box warn' : 'advice-box';
    adviceHtml = `
      <div class="${cls}">
        <h4>${data.advice.advice.label}</h4>
        <p style="margin-bottom:0;">
          Forecast for ${data.advice.day.forecast_date}: ${data.advice.day.condition_main},
          ${data.advice.day.temp_min}°–${data.advice.day.temp_max}°C, ${data.advice.day.rain_probability}% rain chance.
          ${data.advice.suggest_alternate
            ? ' We suggest shifting your trip a day or two, or choosing an indoor/heritage destination instead.'
            : ' Looks like a solid day to travel.'}
        </p>
      </div>`;
  } else {
    adviceHtml = `
      <div class="info-note" style="margin-top:16px;">
        We can only forecast the next 5 days (up to ${data.forecast[data.forecast.length - 1].forecast_date}).
        Your selected travel date is further out, so we can't give a go/wait recommendation yet —
        check back closer to your trip.
      </div>`;
  }

  box.innerHTML = strip + adviceHtml;
}

// Re-wire favourite buttons any time a page repaints its content after
// layout.js has finished (header/footer in place) or after this page's
// own JS re-renders a grid of destination cards.
document.addEventListener('layout:ready', () => wireFavButtons());
document.addEventListener('content:rendered', () => wireFavButtons());
