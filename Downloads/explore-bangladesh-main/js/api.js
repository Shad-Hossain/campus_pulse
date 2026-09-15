/**
 * Every page talks to the server ONLY through these two functions.
 * All PHP lives under /api/ and always returns JSON. The pages
 * themselves (this file included) are plain HTML + JS — no PHP
 * logic is mixed into them.
 */

async function apiGet(path) {
  const res = await fetch(path, { credentials: 'same-origin' });
  return res.json();
}

async function apiPost(path, data) {
  const res = await fetch(path, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data || {}),
  });
  return res.json();
}
