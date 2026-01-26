<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once PROJECT_ROOT . '/config/database.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/auth.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/helpers.php';

/*
  INTENTIONALLY VULNERABLE (OWASP A01 demo):
  This page is supposed to be Admin-only, but we mistakenly allow HR to access it.
  Result: HR can URL-guess this page and perform admin actions (unlock accounts).
*/
require_login(); // ONLY checks login, not role (vulnerable)

$msg = '';

// Unlock action (intentionally NOT restricted to admin here)
if (isset($_GET['unlock']) && $_GET['unlock'] !== '') {
  $u = trim($_GET['unlock']);

  // Still using prepared statement (SQLi is not the point here)
  $stmt = $pdo->prepare("
    UPDATE login_attempts
    SET lock_until = NULL, fail_count = 0
    WHERE username_entered = :u
  ");
  $stmt->execute([':u' => $u]);

  $msg = "Unlocked account (VULNERABLE DEMO): " . $u;
}

// Locked accounts = any username whose max(lock_until) is still in the future
$sql = "
SELECT
  username_entered,
  MAX(fail_count) AS fail_count,
  MAX(lock_until) AS lock_until,
  MAX(attempt_time) AS last_attempt
FROM login_attempts
GROUP BY username_entered
HAVING MAX(lock_until) IS NOT NULL
   AND UNIX_TIMESTAMP(MAX(lock_until)) > UNIX_TIMESTAMP(NOW())
ORDER BY UNIX_TIMESTAMP(MAX(lock_until)) ASC
";
$rows = $pdo->query($sql)->fetchAll();

$title = "TP AMC — Locked Accounts (Admin) (VULNERABLE)";
require_once PROJECT_ROOT . '/pages/Irfan/includes/header.php';
?>

<section class="hero">
  <h1>Locked Accounts (Admin) — VULNERABLE</h1>
  <p>
    OWASP A01 Demo: HR can access this admin function via URL guessing (Broken Access Control).
  </p>
  <div class="hero-actions">
    <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/logs/logs.php">Back to Logs</a>
  </div>
</section>

<?php if ($msg): ?>
  <div class="alert success"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Username</th>
        <th>Fail Count</th>
        <th>Lock Until</th>
        <th>Last Attempt</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="5" class="muted">No locked accounts right now.</td></tr>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['username_entered'] ?? '-') ?></td>
          <td><?= (int)($r['fail_count'] ?? 0) ?></td>
          <td><?= e($r['lock_until'] ?? '-') ?></td>
          <td><?= e($r['last_attempt'] ?? '-') ?></td>
          <td>
            <!-- GET unlock is intentionally weak for demo -->
            <a class="btn btn-outline"
               href="<?= BASE_URL ?>pages/Irfan/locked_accounts/locked_accounts_admin_vulnerable.php?unlock=<?= urlencode($r['username_entered']) ?>">
              Unlock
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once PROJECT_ROOT . '/pages/Irfan/includes/footer.php'; ?>
