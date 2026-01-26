<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inactivity timeout (seconds) - set to 5 for testing, change to 300 (5 mins) for real
define('INACTIVITY_TIMEOUT', 300);

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'pages/Irfan/login+logout+homepage/login.php');
        exit;
    }
    enforce_inactivity_timeout();
}

function require_role(array $roles): void {
    require_login();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo "403 Forbidden";
        exit;
    }
}

function enforce_inactivity_timeout(): void {
  if (session_status() === PHP_SESSION_NONE) session_start();

  // Only enforce if logged in
  if (empty($_SESSION['user_id'])) return;

  $now = time();
  $last = $_SESSION['last_activity'] ?? $now;

  if (($now - $last) > INACTIVITY_TIMEOUT) {
    // Destroy session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
      );
    }
    session_destroy();

    // Redirect to homepage with a message flag
    header('Location: ' . BASE_URL . 'pages/Irfan/login+logout+homepage/homepage.php?timeout=1');
    exit;
  }

  // Update activity timestamp
  $_SESSION['last_activity'] = $now;
}
