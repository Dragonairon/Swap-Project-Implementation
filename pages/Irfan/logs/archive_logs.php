<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once PROJECT_ROOT . '/config/database.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/auth.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/helpers.php';

require_role(['admin']);

$username = trim($_GET['username'] ?? '');
$result   = $_GET['result'] ?? 'all';
$limit    = 200;


// ============================================================================
// BEFORE (VULNERABLE) — for demo reference only (DO NOT USE)
// This is what allowed payloads like:
// %' UNION SELECT 1, user_id, email, 0, 0, password_hash, NOW() FROM users #

// $sql = "
//   SELECT
//     attempt_id, user_id, username_entered, success_flag, fail_count, lock_until, attempt_time,
//     archived_at, archived_reason, archived_by_user_id
//   FROM login_attempts_archive
//   WHERE username_entered LIKE '%$username%'
//   ORDER BY archived_at DESC
//   LIMIT $limit
// ";
// $rows = $pdo->query($sql)->fetchAll();
// ============================================================================


/* ======================= AFTER (SAFE QUERY - PREPARED) ======================= */
$sql = "
  SELECT
    attempt_id, user_id, username_entered, success_flag, fail_count, lock_until, attempt_time,
    archived_at, archived_reason, archived_by_user_id
  FROM login_attempts_archive
  WHERE 1=1
";

$params = [];

/* Username filter (safe) */
if ($username !== '') {
  $sql .= " AND username_entered LIKE :u ";
  $params[':u'] = '%' . $username . '%';
}

/* Result filter (safe) */
if ($result === 'success') {
  $sql .= " AND success_flag = 1 ";
} elseif ($result === 'fail') {
  $sql .= " AND success_flag = 0 ";
}

/* Fixed ordering + safe limit */
$sql .= " ORDER BY archived_at DESC LIMIT :lim ";

$stmt = $pdo->prepare($sql);

/* Bind parameters */
foreach ($params as $k => $v) {
  $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll();
/* ============================================================================ */

$title = "TP AMC — Archived Logs";
require_once PROJECT_ROOT . '/pages/Irfan/includes/header.php';
?>

<section class="hero">
  <h1>Archived Login Logs</h1>
  <p>Admin-only view. These logs were archived during retention purge.</p>
  <div class="hero-actions">
    <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/logs/logs.php">Back to Live Logs</a>
  </div>
</section>

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
      <button class="btn" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/logs/archive_logs.php" style="margin-left:8px;">Reset</a>
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
        <th>Lock Until</th>
        <th>Attempt Time</th>
        <th>Archived At</th>
        <th>Reason</th>
        <th>Archived By (User ID)</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="10" class="muted">No archived logs found.</td></tr>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['attempt_id'] ?></td>
          <td><?= $r['user_id'] !== null ? (int)$r['user_id'] : '-' ?></td>
          <td><?= e($r['username_entered']) ?></td>
          <td><?= ((int)$r['success_flag'] === 1) ? 'Yes' : 'No' ?></td>
          <td><?= (int)$r['fail_count'] ?></td>
          <td><?= $r['lock_until'] ? e($r['lock_until']) : '-' ?></td>
          <td><?= e($r['attempt_time']) ?></td>
          <td><?= e($r['archived_at']) ?></td>
          <td><?= e($r['archived_reason']) ?></td>
          <td><?= $r['archived_by_user_id'] !== null ? (int)$r['archived_by_user_id'] : '-' ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>

  <p class="muted" style="margin-top:10px;">Showing latest <?= (int)$limit ?> archived records.</p>
</div>

<?php require_once PROJECT_ROOT . '/pages/Irfan/includes/footer.php'; ?>
