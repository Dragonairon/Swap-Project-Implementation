<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once PROJECT_ROOT . '/config/database.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/auth.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/helpers.php';

require_role(['admin','hr']);

$username   = trim($_GET['username'] ?? '');
$onlyLocked = isset($_GET['locked']) ? (int)$_GET['locked'] : 0;
$result     = $_GET['result'] ?? 'all';
$limit      = 200;


// ==================== ADD-ON: CSRF token for admin actions ====================
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$purgeMsg = '';
$purgeErr = '';
$purgeMinutes = 2; // DEMO retention window (minutes)


// ==================== ADD-ON: Admin purge (archive -> delete) ====================
// Demo policy: purge logs older than N minutes (so you can test quickly).
// NOTE: Using MySQL's NOW() - INTERVAL avoids PHP/MySQL timezone mismatch issues.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purge_logs'])) {
  $roleNow = $_SESSION['role'] ?? '';
  if ($roleNow !== 'admin') {
    $purgeErr = 'Only Admin can purge logs.';
  } elseif (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $purgeErr = 'Invalid CSRF token.';
  } else {
    $minutes = (int)$purgeMinutes; // uses the demo retention window above
    $minutes = max(1, (int)$minutes);

    try {
      $pdo->beginTransaction();

      // 1) Archive first (recommended for audit trail)
      $archiveStmt = $pdo->prepare("
        INSERT INTO login_attempts_archive
        (attempt_id, user_id, username_entered, success_flag, fail_count, lock_until, attempt_time,
         archived_at, archived_reason, archived_by_user_id)
        SELECT
          attempt_id, user_id, username_entered, success_flag, fail_count, lock_until, attempt_time,
          NOW(), 'Beyond one year policy', :admin_id
        FROM login_attempts
        WHERE attempt_time < (NOW() - INTERVAL {$minutes} MINUTE)
      ");
      $archiveStmt->execute([
        ':admin_id' => (int)($_SESSION['user_id'] ?? 0),
      ]);
      $archivedCount = $archiveStmt->rowCount();

      // 2) Delete from live table
      $deleteStmt = $pdo->prepare("
        DELETE FROM login_attempts
        WHERE attempt_time < (NOW() - INTERVAL {$minutes} MINUTE)
      ");
      $deleteStmt->execute();
      $deletedCount = $deleteStmt->rowCount();

      $pdo->commit();

      $purgeMsg = "Purged logs older than {$minutes} minutes. Archived: {$archivedCount}, Deleted: {$deletedCount}.";
    } catch (Throwable $ex) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $purgeErr = "Purge failed. Please try again.";
      // Optional debugging:
      // error_log($ex->getMessage());
    }
  }
}
// ================================================================================


// BEFORE (VULNERABLE)
// $sql = "SELECT attempt_id, user_id, username_entered, success_flag, fail_count, lock_until, attempt_time
//         FROM login_attempts
//         WHERE username_entered LIKE '%$username%'
//         ORDER BY attempt_time DESC";
// $rows = $pdo->query($sql)->fetchAll();

/* ======================= SAFE QUERY (PREPARED) ======================= */
$sql = "
  SELECT attempt_id, user_id, username_entered, success_flag, fail_count, lock_until, attempt_time
  FROM login_attempts
  WHERE 1=1
";

$params = [];

/* Username filter */
if ($username !== '') {
  $sql .= " AND username_entered LIKE :u ";
  $params[':u'] = '%' . $username . '%';
}

/* Result filter */
if ($result === 'success') {
  $sql .= " AND success_flag = 1 ";
} elseif ($result === 'fail') {
  $sql .= " AND success_flag = 0 ";
}

/* Locked-only filter */
if ($onlyLocked === 1) {
  $sql .= " AND lock_until IS NOT NULL AND lock_until > NOW() ";
}

/* Fixed ordering (safe) */
$sql .= " ORDER BY attempt_time DESC LIMIT :lim ";

$stmt = $pdo->prepare($sql);

/* Bind parameters */
foreach ($params as $k => $v) {
  $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll();

/* ==================================================== */

$title = "TP AMC — Login Logs";
require_once PROJECT_ROOT . '/pages/Irfan/includes/header.php';

$role = $_SESSION['role'] ?? '';
?>

<section class="hero">
  <h1>Security Logs: Login Attempts</h1>
  <p>Available to Admin + HR for monitoring and incident review.</p>
  <div class="hero-actions">
    <?php if ($role === 'admin'): ?>
      <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/locked_accounts/locked_accounts_admin_secure.php">View Locked Accounts (Admin)</a>
      <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/logs/archive_logs.php" style="margin-left:8px;">View Archived Logs</a>
    <?php else: ?>
      <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/locked_accounts/locked_accounts_hr_secure.php">View Locked Accounts (HR)</a>
    <?php endif; ?>
  </div>
</section>

<?php if ($role === 'admin'): ?>
  <div class="card" style="margin-top:16px;">
    <h3 style="margin:0 0 8px 0;">Admin: Purge Old Logs</h3>
    <p class="muted" style="margin-top:0;">
      Server Logs Retention Policy: deletes logs older than <strong><?= (int)$purgeMinutes ?> minutes</strong> (demo purpose only)
    </p>

    <?php if ($purgeMsg): ?><div class="alert success"><?= e($purgeMsg) ?></div><?php endif; ?>
    <?php if ($purgeErr): ?><div class="alert error"><?= e($purgeErr) ?></div><?php endif; ?>

    <form method="post" onsubmit="return confirm('Purge logs older than <?= (int)$purgeMinutes ?> minutes? This will archive them first, then delete from live logs.');">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <button class="btn btn-outline" type="submit" name="purge_logs" value="1">
        Purge Logs Older Than <?= (int)$purgeMinutes ?> Minutes
      </button>
    </form>
  </div>
<?php endif; ?>

<div class="card" style="margin-top:16px;">
  <form method="get" class="row">
    <div>
      <label>Username contains</label>
      <input type="text" name="username" value="<?= e($username) ?>">
    </div>

    <div>
      <label>Result</label>
      <select name="result">
        <option value="all" <?= $result==='all'?'selected':'' ?>>All</option>
        <option value="success" <?= $result==='success'?'selected':'' ?>>Success only</option>
        <option value="fail" <?= $result==='fail'?'selected':'' ?>>Failures only</option>
      </select>
    </div>

    <div style="grid-column:span 2;">
      <div class="checkline">
        <input type="checkbox" id="lockedOnly" name="locked" value="1" <?= $onlyLocked ? 'checked' : '' ?>>
        <label for="lockedOnly" style="margin:0; font-weight:700;">Show only currently locked</label>
      </div>

      <div style="height:10px"></div>
      <button class="btn" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/logs/logs.php" style="margin-left:8px;">Reset</a>
    </div>
  </form>
</div>

<div class="card" style="margin-top:16px;">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>User ID</th>
        <th>Username Entered</th>
        <th>Success</th>
        <th>Fail Count</th>
        <th>Locked?</th>
        <th>Lock Until</th>
        <th>Attempt Time</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr>
          <td colspan="8" class="muted">
            No logs found in live table.
            <?php if ($role === 'admin'): ?>
              You may have purged older logs — check <a href="<?= BASE_URL ?>pages/Irfan/logs/archive_logs.php">Archived Logs</a>.
            <?php endif; ?>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <?php $lockedNow = (!empty($r['lock_until']) && strtotime($r['lock_until']) > time()); ?>
          <tr>
            <td><?= (int)$r['attempt_id'] ?></td>
            <td><?= $r['user_id'] !== null ? (int)$r['user_id'] : '-' ?></td>
            <td><?= e($r['username_entered']) ?></td>
            <td><?= ((int)$r['success_flag'] === 1) ? 'Yes' : 'No' ?></td>
            <td><?= (int)$r['fail_count'] ?></td>
            <td><?= $lockedNow ? 'Yes' : 'No' ?></td>
            <td><?= $r['lock_until'] ? e($r['lock_until']) : '-' ?></td>
            <td><?= e($r['attempt_time']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <p class="muted" style="margin-top:10px;">Showing latest <?= (int)$limit ?> records.</p>
</div>

<?php require_once PROJECT_ROOT . '/pages/Irfan/includes/footer.php'; ?>
