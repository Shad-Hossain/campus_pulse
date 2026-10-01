<?php
// includes/moderation.php — probation / suspension / auto-block rules (used by api/users.php)
//
// Rules:
//  * Admin can suspend for N days (N >= 10). Login is refused until the suspension ends.
//  * The 3rd suspension automatically BLOCKS the account. Admin can never block directly,
//    and a block never expires — only an admin can revoke it.
//  * Admin can cancel / extend an active suspension at any time.
//  * "Probation" is not a status: it is just a notice ("You are under watch.") the admin sends.
//    The 3rd probation notice automatically suspends the user for 10 days (which counts as a suspension).
require_once __DIR__ . '/helpers.php';

const MOD_MIN_SUSPEND_DAYS      = 10;
const MOD_MAX_SUSPEND_DAYS      = 365;
const MOD_SUSPENSIONS_TO_BLOCK  = 3;
const MOD_PROBATIONS_TO_SUSPEND = 3;
const MOD_AUTO_SUSPEND_DAYS     = 10;

class ModError extends Exception
{
    public $status;

    public function __construct(string $msg, int $status = 422)
    {
        parent::__construct($msg);
        $this->status = $status;
    }
}

// Loads + row-locks the target user (call inside a transaction)
function mod_lock_target(PDO $pdo, int $id): array
{
    $st = $pdo->prepare(
        'SELECT id, full_name, role, is_active, suspension_count, probation_count, is_blocked, suspended_until,
                (suspended_until IS NOT NULL AND suspended_until > NOW()) AS is_suspended
           FROM users WHERE id = ? FOR UPDATE'
    );
    $st->execute([$id]);
    $u = $st->fetch();
    if (!$u || !(int)$u['is_active']) {
        throw new ModError('User not found.', 404);
    }
    if ($u['role'] === 'admin') {
        throw new ModError('Admin accounts cannot be moderated.', 403);
    }
    return $u;
}

function mod_notice(PDO $pdo, int $userId, string $kind, string $message): void
{
    $pdo->prepare('INSERT INTO user_notices (user_id, kind, message) VALUES (?,?,?)')
        ->execute([$userId, $kind, clean_str($message, 500)]);
}

function mod_log(PDO $pdo, int $userId, ?int $adminId, string $action, ?int $days = null, ?string $note = null): void
{
    $pdo->prepare('INSERT INTO moderation_log (user_id, admin_id, action, days, note) VALUES (?,?,?,?,?)')
        ->execute([$userId, $adminId, $action, $days, $note !== null && $note !== '' ? clean_str($note, 255) : null]);
}

// Counts one suspension. Returns 'suspended' or 'blocked' (when this was the 3rd one).
function mod_apply_suspension(PDO $pdo, array $u, int $days, ?int $adminId, string $reason, bool $auto): string
{
    $uid   = (int)$u['id'];
    $count = (int)$u['suspension_count'] + 1;

    mod_log($pdo, $uid, $adminId, $auto ? 'auto_suspend' : 'suspend', $days, $reason);

    if ($count >= MOD_SUSPENSIONS_TO_BLOCK) {
        $pdo->prepare('UPDATE users SET suspension_count = ?, probation_count = 0, suspended_until = NULL, is_blocked = 1 WHERE id = ?')
            ->execute([$count, $uid]);
        mod_log($pdo, $uid, null, 'auto_block', null, 'Reached ' . MOD_SUSPENSIONS_TO_BLOCK . ' suspensions');
        return 'blocked';
    }

    $pdo->prepare('UPDATE users SET suspension_count = ?, probation_count = 0, suspended_until = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?')
        ->execute([$count, $days, $uid]);

    $until = $pdo->prepare("SELECT DATE_FORMAT(suspended_until, '%d %b %Y, %h:%i %p') FROM users WHERE id = ?");
    $until->execute([$uid]);
    $msg = "Your account has been suspended for {$days} days (until " . $until->fetchColumn() . ').';
    if ($reason !== '') {
        $msg .= ' Reason: ' . $reason;
    }
    mod_notice($pdo, $uid, 'Suspension', $msg);
    return 'suspended';
}
