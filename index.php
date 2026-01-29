<?php
// This is the menu that has the "apply MC" and "apply leave" options
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first.");
}

require_once 'db_connect.php';
$pageTitle = "Home - Apply MC/Leave";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- href can be 'href="css/style.css' if applicable -->
    <style>
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

    <?php include 'includes/header.php'; ?>

    <main class="menu-container">
        <h2 class="welcome-text">Welcome, user <strong><?php echo htmlspecialchars($_SESSION['user_id']); ?>!</strong>
        </h2>
        <p class="color: #666; margin-bottom: 40px;">What would you like to do today?</p>
        <div class="button-group">
            <a href="pages/frontendWithPHP_applyLeave.php" class="btn-red">Apply Leave</a>
            <a href="pages/(apply leave php, Fanan's part)" class="btn-red">Apply MC</a>/* This part is purposely all wrong because I do not know what is Fanan's naming convetion, so change that to his project file names*/
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>