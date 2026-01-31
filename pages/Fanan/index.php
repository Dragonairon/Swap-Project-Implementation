<?php
// index.php - Dashboard for the MC Module
session_start();

// SECURITY CHECK: If user is not logged in, send them to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MC Portal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-mark">TP</div>
                <div>HR Portal</div>
            </div>
            
            <div style="display:flex; gap:15px; align-items:center;">
                <span style="font-size:13px; font-weight:600; color:var(--tp-dark);">
                    Hello, <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                
                <a href="logout.php" class="btn btn-secondary" style="padding: 8px 16px; font-size:12px;">Logout</a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 800px; padding-top: 40px;">
        
        <div class="card" style="text-align: center; padding: 40px;">
            <h1 style="color: var(--tp-red); margin-bottom: 10px;">Medical Certificate Portal</h1>
            <p style="color: var(--tp-gray); margin-bottom: 30px;">Manage your sick leave submissions and view approval status.</p>

            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                
                <a href="submit_mc.php" style="text-decoration: none;">
                    <div style="border: 2px solid var(--border); border-radius: 12px; padding: 20px; width: 200px; transition: 0.2s; background: #fff; cursor: pointer;">
                        <h3 style="color: var(--tp-dark); margin-top:0;">+ New Request</h3>
                        <p style="font-size: 13px; color: var(--tp-gray); margin-bottom:0;">Submit a new MC for approval</p>
                    </div>
                </a>

                <a href="view_history.php" style="text-decoration: none;">
                    <div style="border: 2px solid var(--border); border-radius: 12px; padding: 20px; width: 200px; transition: 0.2s; background: #fff; cursor: pointer;">
                        <h3 style="color: var(--tp-dark); margin-top:0;">View History</h3>
                        <p style="font-size: 13px; color: var(--tp-gray); margin-bottom:0;">Check status of past submissions</p>
                    </div>
                </a>

            </div>

            <div style="margin-top: 40px;">
                <a href="../../index.php" style="color: var(--tp-gray); font-size: 12px; text-decoration: none;">&larr; Back to Main Home</a>
            </div>
        </div>

    </div>

</body>
</html>