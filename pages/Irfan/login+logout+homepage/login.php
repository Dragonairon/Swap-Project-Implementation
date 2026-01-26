<?php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once PROJECT_ROOT . '/config/database.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$MAX_FAILS = 3;
$LOCK_SECONDS = 1000; #put 180 for 3 minutes lock

// Already logged in
if (!empty($_SESSION['user_id'])) {
  $r = $_SESSION['role'] ?? '';
  if ($r === 'admin') redirect_to('pages/Denzyl/admin_users.php');
  if ($r === 'hr') redirect_to('pages/Javier/hr_actions_management.php');
  if ($r === 'employee') redirect_to('xxx');
  redirect_to('pages/Irfan/login+logout+homepage/homepage.php');
}

function latest_state(PDO $pdo, string $username): array {
  $stmt = $pdo->prepare("
    SELECT fail_count, lock_until
    FROM login_attempts
    WHERE username_entered = :u
    ORDER BY attempt_time DESC, attempt_id DESC
    LIMIT 1
  ");
  $stmt->execute([':u' => $username]);
  return $stmt->fetch() ?: ['fail_count' => 0, 'lock_until' => null];
}

function log_attempt(PDO $pdo, ?int $userId, string $username, int $success, int $failCount, ?int $lockSeconds, ?string $lockUntilExact): void {
  if ($lockSeconds !== null && $lockSeconds > 0) {
    $stmt = $pdo->prepare("
      INSERT INTO login_attempts
      (user_id, username_entered, success_flag, fail_count, lock_until, attempt_time)
      VALUES (:uid, :u, :s, :fc, DATE_ADD(NOW(), INTERVAL :secs SECOND), NOW())
    ");
    $stmt->execute([
      ':uid'  => $userId,
      ':u'    => $username,
      ':s'    => $success,
      ':fc'   => $failCount,
      ':secs' => $lockSeconds
    ]);
    return;
  }

  $stmt = $pdo->prepare("
    INSERT INTO login_attempts
    (user_id, username_entered, success_flag, fail_count, lock_until, attempt_time)
    VALUES (:uid, :u, :s, :fc, :lu, NOW())
  ");
  $stmt->execute([
    ':uid' => $userId,
    ':u'   => $username,
    ':s'   => $success,
    ':fc'  => $failCount,
    ':lu'  => $lockUntilExact
  ]);
}

$err = '';
$lockSecondsRemaining = null;

// ===================== LOCKED GET HANDLER (FIXED) =====================
// If redirected to login.php?locked=1, show a countdown reliably.
// Uses session lock_user if available; otherwise fallback to URL param u.
if (isset($_GET['locked'])) {
  $u = $_SESSION['lock_user'] ?? ($_GET['u'] ?? '');
  $u = trim((string)$u);

  if ($u !== '') {
    $stmt = $pdo->prepare("
      SELECT
        lock_until,
        TIMESTAMPDIFF(SECOND, NOW(), lock_until) AS remaining_seconds
      FROM login_attempts
      WHERE username_entered = :u
      ORDER BY attempt_time DESC, attempt_id DESC
      LIMIT 1
    ");
    $stmt->execute([':u' => $u]);
    $row = $stmt->fetch();

    $remaining = (int)($row['remaining_seconds'] ?? 0);

    if ($remaining > 0) {
      $lockSecondsRemaining = $remaining;
      $err = "Account is temporarily locked after $MAX_FAILS failed attempts. Please try again in <strong><span id='lockTimer'></span></strong>.";
    } else {
      // Lock expired; clean up any old session values.
      unset($_SESSION['lock_user'], $_SESSION['lock_until']);
      // Optional: show a small message that it’s unlocked now
      // $err = "Lock expired. You may try again.";
    }
  } else {
    // Worst-case fallback
    $err = "Account is temporarily locked. Please try again later.";
  }
}
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $genericErr = 'Invalid username or password.';
  $demo_info = '';

  if ($username === '' || $password === '') {
    $err = $genericErr;
  } else {

    $state = latest_state($pdo, $username);

    // If already locked, redirect with username param for reliable lock banner
    if (!empty($state['lock_until']) && strtotime($state['lock_until']) > time()) {
      $_SESSION['lock_user'] = $username;
      $_SESSION['lock_until'] = $state['lock_until'];
      header('Location: ' . BASE_URL . 'pages/Irfan/login+logout+homepage/login.php?locked=1&u=' . urlencode($username));
      exit;
    }

    // Lock has expired, reset fail_count for fresh attempts
    if (!empty($state['lock_until']) && strtotime($state['lock_until']) <= time()) {
      $state['fail_count'] = 0;
    }

    // ===================== BEFORE (VULNERABLE) ============================
    // try {
    //   $sql = "SELECT user_id, username, password_hash, role, status
    //           FROM users
    //           WHERE username = '$username'
    //           LIMIT 1";
    //   $user = $pdo->query($sql)->fetch();
    
    //   // DEMO: show extracted DB data
    //   if ($user) {
    //     $demo_info =
    //       "DEMO SQLi Extracted → " .
    //       "username=" . e((string)$user['username']) .
    //       " | role=" . e((string)$user['role']) .
    //       " | status=" . e((string)$user['status']) .
    //       " | password_hash=" . e((string)$user['password_hash']);
    //   }
    
    // } catch (PDOException $ex) {
    //   $user = false;
    //   $err = "DEMO: SQL error triggered by injection.";
    // }
    // =====================================================================


    // ======================= AFTER (SAFE) ================================
    $stmt = $pdo->prepare("
      SELECT user_id, username, password_hash, role, status
      FROM users
      WHERE username = :u
      LIMIT 1
    ");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();
    // =====================================================================

    $userId = $user ? (int)$user['user_id'] : null;

    if ($user && $user['status'] !== 'active') {
      log_attempt($pdo, $userId, $username, 0, 0, null, null);
      $err = 'Account is not active. Please contact Admin.';
      if ($demo_info) $err = $demo_info . "<br>" . $err;

    } elseif ($user && password_verify($password, $user['password_hash'])) {

      log_attempt($pdo, $userId, $username, 1, 0, null, null);

      session_regenerate_id(true);
      $_SESSION['user_id'] = $userId;
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['last_activity'] = time();

      unset($_SESSION['lock_user'], $_SESSION['lock_until']);

      if ($user['role'] === 'admin') redirect_to('pages/Denzyl/admin_users.php');
      if ($user['role'] === 'hr') redirect_to('pages/Javier/hr_actions_management.php');
      if ($user['role'] === 'employee') redirect_to('pages/Irfan/xxx');
      redirect_to('pages/Irfan/login+logout+homepage/homepage.php');

    } else {

      $newFailCount = (int)($state['fail_count'] ?? 0) + 1;

      if ($newFailCount >= $MAX_FAILS) {
        log_attempt($pdo, $userId, $username, 0, $newFailCount, $LOCK_SECONDS, null);

        $_SESSION['lock_user'] = $username;
        $state2 = latest_state($pdo, $username);
        $_SESSION['lock_until'] = $state2['lock_until'] ?? null;

        // Redirect with username param so lock banner always shows
        header('Location: ' . BASE_URL . 'pages/Irfan/login+logout+homepage/login.php?locked=1&u=' . urlencode($username));
        exit;
      } else {
        log_attempt($pdo, $userId, $username, 0, $newFailCount, null, null);
      }

      $err = $genericErr;
      if ($demo_info) $err = $demo_info . "<br>" . $err;
    }
  }
}

$title = "TP AMC — Login";
require_once PROJECT_ROOT . '/pages/Irfan/includes/header.php';
?>

<section class="hero">
  <h1>Login</h1>
  <p>Please login to proceed.</p>
</section>

<div class="card">
  <?php if (!empty($err)): ?>
    <div class="alert error"><?= $err ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <div style="position:relative; margin-bottom:14px;">
      <input id="pw" type="password" name="password" required style="padding-right:44px;">
      <button type="button" id="pwToggle" onclick="togglePw()"
        style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px;">
        👁
      </button>
    </div>

    <button class="btn" type="submit">Login</button>
  </form>
</div>

<script>
// Password show/hide (👁 ↔ ⌣)
function togglePw(){
  const input = document.getElementById('pw');
  const btn = document.getElementById('pwToggle');

  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '⌣';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}

// Lock countdown (only runs if PHP set remaining seconds)
<?php if ($lockSecondsRemaining !== null): ?>
(function(){
  let remaining = <?= (int)$lockSecondsRemaining ?>;
  const el = document.getElementById('lockTimer');

  function fmt(sec){
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return m + ":" + String(s).padStart(2,'0');
  }

  function tick(){
    if (!el) return;
    el.textContent = fmt(Math.max(remaining, 0));
    if (remaining <= 0) return;
    remaining -= 1;
    setTimeout(tick, 1000);
  }

  tick();
})();
<?php endif; ?>
</script>

<?php require_once PROJECT_ROOT . '/pages/Irfan/includes/footer.php'; ?>
