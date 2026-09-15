<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logging out… — COMPASS</title>

<link rel="stylesheet" href="/explore-bangladesh-main/css/style.css?v=2">
</head>
<body>
<div class="container section"><p>Logging you out…</p></div>
<script src="/explore-bangladesh-main/js/api.js"></script>
<script>
apiPost('/explore-bangladesh-main/api/logout.php', {}).finally(() => {
  window.location.href = '/explore-bangladesh-main/index.php';
});
</script>
</body>
</html>
