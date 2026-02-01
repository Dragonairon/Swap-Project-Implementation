<?php

// This is the menu that has the "apply MC" and "apply leave" options
session_start();

// fatal error fix for line 10: directory traversal
require_once __DIR__ . '/../../config/config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Irfan/login+logout+homepage/homepage.php"); // when you integrate, where is the login page
    exit();
}

$pageTitle = "Home - Apply MC/Leave";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- href can be 'href="css/style.css' if applicable -->
    <style>
        .top-bar {
            display: flex;
            justify-content: flex-end;
            padding: 15px 30px;
        }
        .logout-button {
            background-color: #c62828;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .logout-button:hover {
            background-color: #a51d1d;
        }
        .menu-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            text-align: center;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .welcome-text {
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .btn-red {
            background-color: #c62828;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-red:hover {
            background-color: #a51d1d;
        }
    </style>
</head>
<body style="background-color:#f4f7f6; font-family: sans-serif;">

    <div class="top-bar">
        <a href="../Irfan/login+logout+homepage/logout.php" class="logout-button">Logout</a>
    </div>

    <?php include __DIR__ . '/includes/header.php'; ?> <!-- You mean like this?-->

    <main class="menu-container">
        <h2 class="welcome-text">Welcome, user <strong><?php echo htmlspecialchars($_SESSION['user_id']); ?>!</strong>
        </h2>
        <p class="color: #666; margin-bottom: 40px;">What would you like to do today?</p>
        <div class="button-group">
            <a href="frontendWithPHP_applyLeave.php" class="btn-red">Apply Leave</a>
            <a href="../Fanan/applyMC.php" class="btn-red">Apply MC</a> <!-- replace with actual MC application page from Fanan -->

            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'hr')): ?>
                <hr style="width:80%; border:0.5px solid #eee; margin: 10px 0;">
                <p style="font-size: 14px; font-weight:bold; color:#333;">Admin Management Tools</p>
                <a href="../Denzyl/admin_users.php" class="btn-red">Admin Dashboard</a>
                <a href="../Javier/hr_actions_management.php" class="btn-red">HR Actions</a>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/header.php'; ?>
    <?php include 'includes/footer.php'; ?>
</body>
</html>