<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logging out… — Explore Bangladesh</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="container section"><p>Logging you out…</p></div>
<script src="/js/api.js"></script>
<script>
apiPost('/api/logout.php', {}).finally(() => {
  window.location.href = '/index.php';
});
</script>
</body>
</html>
