<?php
// Configure session settings BEFORE session_start()
session_name('HRSESSION');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Test users for development
$test_users = [
    'employee1' => ['password' => 'pass123', 'user_id' => 1, 'role' => 'employee', 'name' => 'John Employee'],
    'manager1' => ['password' => 'pass123', 'user_id' => 2, 'role' => 'manager', 'name' => 'Jane Manager'],
    'hr1' => ['password' => 'pass123', 'user_id' => 3, 'role' => 'hr', 'name' => 'Bob HR'],
    'admin1' => ['password' => 'pass123', 'user_id' => 4, 'role' => 'admin', 'name' => 'Alice Admin'],
];

$error = '';
$success = '';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: hr_actions_management.php');
    exit;
}

// Check for logout
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully.';
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (isset($test_users[$username]) && $test_users[$username]['password'] === $password) {
        // Set session variables
        $_SESSION['user_id'] = $test_users[$username]['user_id'];
        $_SESSION['role'] = $test_users[$username]['role'];
        $_SESSION['name'] = $test_users[$username]['name'];
        $_SESSION['last_activity'] = time();

        $success = 'Login successful! Redirecting...';
        header('Refresh: 1; url=hr_actions_management.php');
    } else {
        $error = 'Invalid username or password.';
    }
}

// Check for expired session
if (isset($_GET['expired'])) {
    $error = 'Your session has expired. Please login again.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HR System Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .error-message {
            background-color: #fee;
            color: #c00;
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c00;
        }

        .success-message {
            background-color: #efe;
            color: #0a0;
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #0a0;
        }

        .test-users {
            background: #f5f6fb;
            border-radius: 8px;
            padding: 16px;
            margin-top: 30px;
        }

        .test-users h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 12px;
            text-align: center;
        }

        .user-card {
            background: white;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 8px;
            border: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .user-card:last-child {
            margin-bottom: 0;
        }

        .user-role {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        .role-employee {
            background: #e0f2fe;
            color: #0369a1;
        }

        .role-manager {
            background: #fef08a;
            color: #854d0e;
        }

        .role-hr {
            background: #dbeafe;
            color: #1e40af;
        }

        .role-admin {
            background: #fed7aa;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>HR System</h1>
            <p>Test Login</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-button">Login</button>
        </form>

        <div class="test-users">
            <h3>📋 Test Users (Password: pass123)</h3>
            <div class="user-card">
                <strong>employee1</strong>
                <span class="user-role role-employee">Employee</span>
                <div style="font-size: 11px; color: #666; margin-top: 4px;">Cannot access HR actions</div>
            </div>
            <div class="user-card">
                <strong>manager1</strong>
                <span class="user-role role-manager">Manager</span>
                <div style="font-size: 11px; color: #666; margin-top: 4px;">Can approve/reject, but not delete</div>
            </div>
            <div class="user-card">
                <strong>hr1</strong>
                <span class="user-role role-hr">HR</span>
                <div style="font-size: 11px; color: #666; margin-top: 4px;">Can approve/reject/edit, but not delete</div>
            </div>
            <div class="user-card">
                <strong>admin1</strong>
                <span class="user-role role-admin">Admin</span>
                <div style="font-size: 11px; color: #666; margin-top: 4px;">Full access to all actions</div>
            </div>
        </div>
    </div>
</body>
</html>
