<?php
// GET  ?q=                         -> user directory incl. suspension / probation info   (admin only)
// POST action = probation | suspend | extend | cancel_suspension | unblock               (admin only)
//        probation          id                   -> sends "You are under watch." (3rd one => auto 10-day suspension)
//        suspend            id, days(>=10), reason?
//        extend             id, days(>=1)        -> adds days to an active suspension
//        cancel_suspension  id                   -> lifts an active suspension right now
//        unblock            id                   -> revokes a block (there is deliberately NO "block" action:
//                                                   blocks happen only automatically after the 3rd suspension)
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/moderation.php';

api_run(function () {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        api_user(['admin']);
        $q    = trim($_GET['q'] ?? '');
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $st   = $pdo->prepare(
            'SELECT id, full_name, department, role, suspension_count, probation_count, is_blocked, suspended_until,
                    (suspended_until IS NOT NULL AND suspended_until > NOW()) AS is_suspended,
                    GREATEST(CEIL(TIMESTAMPDIFF(MINUTE, NOW(), suspended_until) / 1440), 0) AS days_left
               FROM users
              WHERE is_active = 1 AND (full_name LIKE ? OR IFNULL(department, "") LIKE ?)
              ORDER BY full_name LIMIT 200'
        );
        $st->execute([$like, $like]);
        $items = array_map(function ($u) {
            $status = $u['is_blocked'] ? 'blocked' : ($u['is_suspended'] ? 'suspended' : 'active');
            return [
                'id'               => (int)$u['id'],
                'full_name'        => $u['full_name'],
                'department'       => $u['department'],
                'role'             => $u['role'],
                'status'           => $status,
                'suspension_count' => (int)$u['suspension_count'],
                'probation_count'  => (int)$u['probation_count'],
                'suspended_until'  => $status === 'suspended' ? $u['suspended_until'] : null,
                'days_left'        => $status === 'suspended' ? (int)$u['days_left'] : 0,
            ];
        }, $st->fetchAll());
        json_out(['items' => $items]);
    }

    $admin  = api_user(['admin']);
    $in     = api_post_body();
    $action = $in['action'] ?? '';
    $id     = (int)($in['id'] ?? 0);
    $aid    = (int)$admin['id'];

    $pdo->beginTransaction();
    try {
        $u = mod_lock_target($pdo, $id);
        $blocked   = (bool)$u['is_blocked'];
        $suspended = (bool)$u['is_suspended'];
        $name      = $u['full_name'];

        switch ($action) {
            case 'probation':
                if ($blocked || $suspended) {
                    throw new ModError('This user is already ' . ($blocked ? 'blocked.' : 'suspended.'), 409);
                }
                $n = (int)$u['probation_count'] + 1;
                if ($n >= MOD_PROBATIONS_TO_SUSPEND) {
                    // 3rd probation notice => automatic suspension (counts as a suspension)
                    mod_log($pdo, $id, $aid, 'probation', null, '3rd probation notice');
                    $res = mod_apply_suspension($pdo, $u, MOD_AUTO_SUSPEND_DAYS, null, '3 probation notices', true);
                    $msg = $res === 'blocked'
                        ? "$name had 3 probation notices — that was suspension #3, so the account is now blocked."
                        : "$name had 3 probation notices — automatically suspended for " . MOD_AUTO_SUSPEND_DAYS . ' days.';
                } else {
                    $pdo->prepare('UPDATE users SET probation_count = ? WHERE id = ?')->execute([$n, $id]);
                    mod_notice($pdo, $id, 'Probation', 'You are under watch.');
                    mod_log($pdo, $id, $aid, 'probation');
                    $msg = "Probation notice sent to $name ($n/" . MOD_PROBATIONS_TO_SUSPEND . ').';
                }
                break;

            case 'suspend':
                if ($blocked || $suspended) {
                    throw new ModError('This user is already ' . ($blocked ? 'blocked.' : 'suspended (use Extend instead).'), 409);
                }
                $days = (int)($in['days'] ?? 0);
                if ($days < MOD_MIN_SUSPEND_DAYS || $days > MOD_MAX_SUSPEND_DAYS) {
                    throw new ModError('Suspension must be between ' . MOD_MIN_SUSPEND_DAYS . ' and ' . MOD_MAX_SUSPEND_DAYS . ' days.');
                }
                $res = mod_apply_suspension($pdo, $u, $days, $aid, clean_str($in['reason'] ?? '', 200), false);
                $msg = $res === 'blocked'
                    ? "That was suspension #" . MOD_SUSPENSIONS_TO_BLOCK . " — $name is now blocked automatically. Only you can revoke the block."
                    : "$name suspended for $days days.";
                break;

            case 'extend':
                if (!$suspended) {
                    throw new ModError('This user is not currently suspended.', 409);
                }
                $days = (int)($in['days'] ?? 0);
                if ($days < 1 || $days > MOD_MAX_SUSPEND_DAYS) {
                    throw new ModError('Enter 1 to ' . MOD_MAX_SUSPEND_DAYS . ' extra days.');
                }
                $pdo->prepare('UPDATE users SET suspended_until = DATE_ADD(suspended_until, INTERVAL ? DAY) WHERE id = ?')->execute([$days, $id]);
                $until = $pdo->prepare("SELECT DATE_FORMAT(suspended_until, '%d %b %Y, %h:%i %p') FROM users WHERE id = ?");
                $until->execute([$id]);
                mod_notice($pdo, $id, 'Suspension', "Your suspension has been extended by $days days (now until " . $until->fetchColumn() . ').');
                mod_log($pdo, $id, $aid, 'extend', $days);
                $msg = "Suspension of $name extended by $days days.";
                break;

            case 'cancel_suspension':
                if (!$suspended) {
                    throw new ModError('This user is not currently suspended.', 409);
                }
                // a cancelled suspension does not count towards the 3-strike block
                $pdo->prepare('UPDATE users SET suspended_until = NULL, suspension_count = GREATEST(suspension_count - 1, 0) WHERE id = ?')->execute([$id]);
                mod_notice($pdo, $id, 'Suspension', 'Your suspension has been lifted by the admin.');
                mod_log($pdo, $id, $aid, 'cancel_suspension');
                $msg = "Suspension of $name cancelled.";
                break;

            case 'unblock':
                if (!$blocked) {
                    throw new ModError('This user is not blocked.', 409);
                }
                // fresh start: counters reset so one new suspension does not instantly re-block
                $pdo->prepare('UPDATE users SET is_blocked = 0, suspended_until = NULL, suspension_count = 0, probation_count = 0 WHERE id = ?')->execute([$id]);
                mod_log($pdo, $id, $aid, 'unblock');
                $msg = "Block on $name revoked.";
                break;

            default:
                throw new ModError('Unknown action.', 400);
        }
        $pdo->commit();
    } catch (ModError $e) {
        $pdo->rollBack();
        json_out(['error' => $e->getMessage()], $e->status);
    } catch (Throwable $t) {
        $pdo->rollBack();
        throw $t;
    }
    json_out(['ok' => true, 'message' => $msg]);
});
