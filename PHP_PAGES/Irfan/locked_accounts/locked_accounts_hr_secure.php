<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once PROJECT_ROOT . '/config/database.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/auth.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/helpers.php';

require_role(['hr']);

$username = trim($_GET['username'] ?? '');


// ===================== BEFORE (VULNERABLE) ============================
// If you concatenate untrusted input directly into SQL, payloads like:
//   username=' OR 1=1 --
// can tamper with your WHERE clause.
//
// $sql = "
// SELECT la.username_entered, la.fail_count, la.lock_until, la.attempt_time
// FROM login_attempts la
// WHERE la.lock_until IS NOT NULL
//   AND la.lock_until > NOW()
//   AND la.username_entered LIKE '%$username%'
// ORDER BY la.lock_until ASC
// ";
// $rows = $pdo->query($sql)->fetchAll();

// =====================================================================

// ======================= AFTER (SAFE) ================================
$stmt = $pdo->prepare("
  SELECT la.username_entered, la.fail_count, la.lock_until, la.attempt_time
  FROM login_attempts la
  WHERE la.lock_until IS NOT NULL
    AND la.lock_until > NOW()
    AND la.username_entered LIKE :u
  ORDER BY la.lock_until ASC
");

$stmt->execute([':u' => "%{$username}%"]);
$rows = $stmt->fetchAll();
// =====================================================================


$title = "TP AMC — Locked Accounts (HR)";
require_once PROJECT_ROOT . '/pages/Irfan/includes/header.php';
?>

<section class="hero">
  <h1>Locked Accounts (HR)</h1>
  <p>HR can view locked accounts for monitoring only. Unlock is Admin-only.</p>
  <div class="hero-actions">
    <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/logs/logs.php">Back to Logs</a>
  </div>
</section>

<div class="card" style="margin-top:16px;">
  <form method="get" class="row">
    <div>
      <label>Username contains</label>
      <input type="text" name="username" value="<?= e($username) ?>">
    </div>
    <div style="grid-column:span 2; align-self:end;">
      <button class="btn" type="submit">Apply Filter</button>
      <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/locked_accounts/locked_accounts_hr_secure.php" style="margin-left:8px;">Reset</a>
    </div>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>Username</th>
        <th>Fail Count</th>
        <th>Lock Until</th>
        <th>Last Attempt</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="4" class="muted">No locked accounts right now.</td></tr>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['username_entered']) ?></td>
          <td><?= (int)$r['fail_count'] ?></td>
          <td><?= e($r['lock_until']) ?></td>
          <td><?= e($r['attempt_time']) ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once PROJECT_ROOT . '/pages/Irfan/includes/footer.php'; ?>
