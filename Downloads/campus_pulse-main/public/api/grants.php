<?php
// GET  ?scope=mine (faculty) | pending (admin) | approved (any)
// POST action=submit {title, description}        (faculty)
// POST action=review {id, decision}              (admin; decision = approved|rejected)
require_once __DIR__ . '/../../includes/helpers.php';

api_run(function () {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $user  = api_user();
        $scope = $_GET['scope'] ?? 'approved';
        $base  = 'SELECT g.id, g.title, g.description, g.status, g.created_at, u.full_name AS submitter
                    FROM research_grants g JOIN users u ON u.id = g.submitted_by';

        if ($scope === 'mine') {
            api_user(['faculty']);
            $st = $pdo->prepare("$base WHERE g.submitted_by = ? ORDER BY g.id DESC");
            $st->execute([$user['id']]);
        } elseif ($scope === 'pending') {
            api_user(['admin']);
            $st = $pdo->query("$base WHERE g.status = 'pending' ORDER BY g.id ASC");
        } else {
            $st = $pdo->query("$base WHERE g.status = 'approved' ORDER BY g.updated_at DESC LIMIT 50");
        }
        json_out(['items' => $st->fetchAll()]);
    }

    $in = api_post_body();

    if (($in['action'] ?? '') === 'submit') {
        $user  = api_user(['faculty']);
        $title = clean_str($in['title'] ?? '', 200);
        if ($title === '') {
            json_out(['error' => 'Title is required.'], 422);
        }
        $pdo->prepare('INSERT INTO research_grants (title, description, submitted_by) VALUES (?,?,?)')
            ->execute([$title, clean_str($in['description'] ?? '', 2000), $user['id']]);
        json_out(['ok' => true], 201);
    }

    if (($in['action'] ?? '') === 'review') {
        $user     = api_user(['admin']);
        $decision = $in['decision'] ?? '';
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            json_out(['error' => 'Invalid decision.'], 422);
        }
        $pdo->prepare("UPDATE research_grants SET status = ?, reviewed_by = ? WHERE id = ? AND status = 'pending'")
            ->execute([$decision, $user['id'], (int)($in['id'] ?? 0)]);
        json_out(['ok' => true]);
    }

    json_out(['error' => 'Unknown action.'], 400);
});
