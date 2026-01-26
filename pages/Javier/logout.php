<?php
// Start session to access session data
session_name('HRSESSION');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php?logout=1');
exit;
?>
