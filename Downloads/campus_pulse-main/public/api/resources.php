<?php
// GET  ?kind=notes|qbank                                  (any logged-in user)
// POST multipart: course_code, kind, title, file          (any logged-in user)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo   = db();
    $kinds = ['notes', 'qbank'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        api_user();
        $kind = $_GET['kind'] ?? 'all';
        $sql  = 'SELECT r.id, r.course_code, r.kind, r.title, r.file_path, u.full_name AS uploader
                   FROM resources r LEFT JOIN users u ON u.id = r.uploaded_by';
        $args = [];
        if (in_array($kind, $kinds, true)) {
            $sql .= ' WHERE r.kind = ?';
            $args[] = $kind;
        }
        $st = $pdo->prepare($sql . ' ORDER BY r.id DESC LIMIT 100');
        $st->execute($args);
        json_out(['items' => $st->fetchAll()]);
    }

    $user   = api_user();
    $in     = api_post_body();
    $course = strtoupper(clean_str($in['course_code'] ?? '', 20));
    $kind   = $in['kind'] ?? '';
    $title  = clean_str($in['title'] ?? '', 200);
    if ($course === '' || $title === '' || !in_array($kind, $kinds, true)) {
        json_out(['error' => 'Course code, type and title are required.'], 422);
    }

    $filePath = null;
    if (!empty($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            json_out(['error' => 'Upload failed (file too large? check upload_max_filesize in php.ini).'], 422);
        }
        if ($f['size'] > 10 * 1024 * 1024) {
            json_out(['error' => 'File must be 10 MB or smaller.'], 422);
        }
        $ext     = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'png', 'jpg', 'jpeg'];
        if (!in_array($ext, $allowed, true)) {
            json_out(['error' => 'Allowed types: ' . implode(', ', $allowed)], 422);
        }
        $dir = __DIR__ . '/../uploads/resources';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = bin2hex(random_bytes(12)) . '.' . $ext;   // random name: never trust the original one
        if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) {
            json_out(['error' => 'Could not save the file.'], 500);
        }
        $filePath = 'uploads/resources/' . $name;
    }

    $pdo->prepare('INSERT INTO resources (course_code, kind, title, file_path, uploaded_by) VALUES (?,?,?,?,?)')
        ->execute([$course, $kind, $title, $filePath, $user['id']]);
    json_out(['ok' => true], 201);
});
