<?php
// login.php (Matched to project_database.sql)
session_start();
require 'db_conn.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // 1. Check against the existing 'users' table
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :u");
    $stmt->execute([':u' => $username_input]);
    $user = $stmt->fetch();

    // 2. Verify Password using 'password_hash' column
    if ($user && password_verify($password_input, $user['password_hash'])) {
        
        // SUCCESS: Login the user
        $_SESSION['user_id'] = $user['user_id'];   // Will be 3 for testemployee1
        $_SESSION['username'] = $user['username']; // Will be 'testemployee1'
        $_SESSION['role'] = $user['role'];         // Will be 'employee'
        
        // Redirect to Dashboard
        header("Location: index.php"); 
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - HR Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; height:100vh;">

    <div class="container" style="width: 100%; max-width: 400px;">
        <div class="form" style="text-align: center;">
            
            <div style="display:flex; justify-content:center; margin-bottom: 20px;">
                <div class="brand-mark" style="width: 50px; height: 50px; font-size: 18px;">TP</div>
            </div>
            
            <h2 style="margin-bottom: 10px;">Staff Login</h2>
            <p style="color:var(--tp-gray); margin-bottom: 20px;">Please sign in to access HR services.</p>

            <?php if($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group" style="text-align:left;">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="e.g. testemployee1">
                </div>

                <div class="form-group" style="text-align:left;">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn" style="width:100%;">Sign In</button>
            </form>

            <p style="margin-top: 20px; font-size: 13px; color: var(--tp-gray);">
                Test Account: <b>testemployee1</b> / <b>password123</b>
            </p>
        </div>
    </div>

</body>
</html>