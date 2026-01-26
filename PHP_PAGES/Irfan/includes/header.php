<?php
require_once PROJECT_ROOT . '/config/config.php';
require_once PROJECT_ROOT . '/pages/Irfan/includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navRole = $_SESSION['role'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'TP AMC') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/pages/Irfan/css/style.css">
</head>
<body>

<header class="topbar">
  <div class="container topbar-inner">
    <div class="brand">
      <div class="brand-mark">TP</div>
      <div class="brand-text">
        <div class="brand-title">Temasek Polytechnic</div>
        <div class="brand-subtitle">Advanced Manufacturing Centre</div>
      </div>
    </div>

    <nav class="nav">
      <a class="nav-link" href="<?= BASE_URL ?>pages/Irfan/login+logout+homepage/homepage.php">Home</a>

      <?php if (!empty($_SESSION['user_id'])): ?>
        <?php if ($navRole === 'admin'): ?>
          <a class="nav-link" href="<?= BASE_URL ?>pages/Denzyl/admin_users.php">Admin</a>
        <?php elseif ($navRole === 'hr'): ?>
          <a class="nav-link" href="<?= BASE_URL ?>pages/Javier/hr_actions_management.php">HR</a>
        <?php elseif ($navRole === 'employee'): ?>
          <a class="nav-link" href="<?= BASE_URL ?>pages/Irfan/xxx">Employee</a>
        <?php endif; ?>

        <a class="nav-link" href="<?= BASE_URL ?>pages/Irfan/logs/logs.php">Logs</a>

        <?php if ($navRole === 'admin'): ?>
          <a class="nav-link" href="<?= BASE_URL ?>pages/Irfan/locked_accounts/locked_accounts_admin_secure.php">Locked Accounts</a>
        <?php elseif ($navRole === 'hr'): ?>
          <a class="nav-link" href="<?= BASE_URL ?>pages/Irfan/locked_accounts/locked_accounts_hr_secure.php">Locked Accounts</a>
        <?php endif; ?>

        <a class="btn btn-outline" href="<?= BASE_URL ?>pages/Irfan/login+logout+homepage/logout.php">Logout</a>
      <?php else: ?>
        <a class="btn" href="<?= BASE_URL ?>pages/Irfan/login+logout+homepage/login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
