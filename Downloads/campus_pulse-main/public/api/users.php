<?php
// GET ?q=  -> user directory (admin only)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    api_user(['admin']);
    $q    = trim($_GET['q'] ?? '');
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $st   = db()->prepare(
        'SELECT id, full_name, department, role FROM users
          WHERE is_active = 1 AND (full_name LIKE ? OR IFNULL(department, "") LIKE ?)
          ORDER BY full_name LIMIT 200'
    );
    $st->execute([$like, $like]);
    json_out(['items' => $st->fetchAll()]);
});
