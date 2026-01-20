<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'project_database';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$message_type = '';
$users = [];
$current_user = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create') {
            handleCreateUser($conn, $_POST);
        } elseif ($action === 'update') {
            handleUpdateUser($conn, $_POST);
        } elseif ($action === 'delete') {
            handleDeleteUser($conn, $_POST);
        }
    }
}

// Handle GET actions
if ($action === 'edit' && isset($_GET['id'])) {
    $current_user = getUserById($conn, $_GET['id']);
}

if ($action === 'delete_confirm' && isset($_GET['id'])) {
    $current_user = getUserById($conn, $_GET['id']);
}

// Search functionality
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$users = searchUsers($conn, $search_query);

/**
 * Handle user creation
 */
function handleCreateUser($conn, $data) {
    global $message, $message_type;

    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];
    $role = $data['role'];
    $status = $data['status'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required.';
        $message_type = 'error';
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $message_type = 'error';
        return;
    }

    if (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $message_type = 'error';
        return;
    }

    // Check if username already exists
    $check_sql = 'SELECT user_id FROM users WHERE username = ?';
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $username);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Username already exists.';
        $message_type = 'error';
        return;
    }

    // Check if email already exists
    $check_sql = 'SELECT user_id FROM users WHERE email = ?';
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $email);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Email already exists.';
        $message_type = 'error';
        return;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $sql = 'INSERT INTO users (username, password_hash, email, role, status) VALUES (?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $username, $password_hash, $email, $role, $status);

    if ($stmt->execute()) {
        $message = 'User created successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error creating user: ' . $conn->error;
        $message_type = 'error';
    }
}

/**
 * Handle user update
 */
function handleUpdateUser($conn, $data) {
    global $message, $message_type;

    $user_id = intval($data['user_id']);
    $username = trim($data['username']);
    $email = trim($data['email']);
    $role = $data['role'];
    $status = $data['status'];

    // Validation
    if (empty($username) || empty($email)) {
        $message = 'Username and email are required.';
        $message_type = 'error';
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $message_type = 'error';
        return;
    }

    // Check if username already exists (excluding current user)
    $check_sql = 'SELECT user_id FROM users WHERE username = ? AND user_id != ?';
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('si', $username, $user_id);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Username already exists.';
        $message_type = 'error';
        return;
    }

    // Check if email already exists (excluding current user)
    $check_sql = 'SELECT user_id FROM users WHERE email = ? AND user_id != ?';
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('si', $email, $user_id);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Email already exists.';
        $message_type = 'error';
        return;
    }

    // Update user
    $sql = 'UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE user_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $username, $email, $role, $status, $user_id);

    if ($stmt->execute()) {
        $message = 'User updated successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error updating user: ' . $conn->error;
        $message_type = 'error';
    }
}

/**
 * Handle user deletion
 */
function handleDeleteUser($conn, $data) {
    global $message, $message_type;

    $user_id = intval($data['user_id']);

    $sql = 'DELETE FROM users WHERE user_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $message = 'User deleted successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error deleting user: ' . $conn->error;
        $message_type = 'error';
    }
}

/**
 * Get user by ID
 */
function getUserById($conn, $user_id) {
    $sql = 'SELECT * FROM users WHERE user_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Search users
 */
function searchUsers($conn, $query) {
    $users = [];

    if (empty($query)) {
        $sql = 'SELECT user_id, username, email, role, status, created_at, updated_at FROM users ORDER BY created_at DESC';
        $result = $conn->query($sql);
    } else {
        $search_term = '%' . $query . '%';
        $sql = 'SELECT user_id, username, email, role, status, created_at, updated_at FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    return $users;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Management</title>
    <style>
        :root {
            --bg: #f5f6fb;
            --card: #ffffff;
            --primary: #d61f3e;
            --primary-strong: #b91832;
            --muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
            --radius: 18px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: #111827;
            min-height: 100vh;
            padding: 32px;
        }

        a { text-decoration: none; color: inherit; }

        .shell {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .topbar {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: 0.2px;
        }

        .brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: var(--primary);
            display: grid;
            place-items: center;
            color: #fff;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(214, 31, 62, 0.35);
        }

        .nav-links {
            display: flex;
            gap: 18px;
            align-items: center;
            font-weight: 600;
            color: #374151;
        }

        .nav-links a:hover { color: var(--primary); }

        .logout {
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid var(--primary);
            color: var(--primary);
            font-weight: 700;
            background: #fff;
            transition: all 0.2s ease;
        }

        .logout:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 8px 20px rgba(214, 31, 62, 0.25);
        }

        .hero {
            background: linear-gradient(135deg, #ffe9ed, #fef6f8);
            border: 1px solid #ffe0e6;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 26px;
        }

        .hero h1 { font-size: 32px; margin-bottom: 6px; color: #111827; }
        .hero p { color: var(--muted); font-weight: 600; }

        .grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 18px; }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 22px;
        }

        .card-title { font-size: 20px; color: #111827; margin-bottom: 8px; display: flex; gap: 10px; align-items: center; }
        .card-subtitle { color: var(--muted); margin-bottom: 18px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        label { font-weight: 700; color: #1f2937; }

        input[type='text'], input[type='email'], input[type='password'], select {
            width: 100%;
            padding: 12px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #f9fafb;
            font-size: 15px;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(214, 31, 62, 0.12);
        }

        .actions-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }

        .btn {
            padding: 12px 18px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-strong); box-shadow: 0 10px 22px rgba(214, 31, 62, 0.18); transform: translateY(-1px); }
        .btn-secondary { background: #f3f4f6; color: #111827; border: 1px solid var(--border); }
        .btn-danger { background: #dc2626; color: #fff; }

        .search-box { display: flex; gap: 10px; align-items: center; }
        .search-box input { flex: 1; background: #f9fafb; }

        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table thead { background: #f9fafb; border: 1px solid var(--border); }
        table th { text-align: left; padding: 12px 12px; font-size: 13px; color: #4b5563; }
        table td { padding: 14px 12px; border-bottom: 1px solid var(--border); font-size: 15px; }
        table tbody tr:hover { background: #fdf2f4; }

        .badge { display: inline-flex; padding: 6px 12px; border-radius: 999px; font-weight: 700; font-size: 13px; }
        .role-admin { background: rgba(214, 31, 62, 0.12); color: var(--primary); }
        .role-manager { background: rgba(252, 211, 77, 0.3); color: #92400e; }
        .role-employee { background: rgba(59, 130, 246, 0.15); color: #1d4ed8; }
        .role-hr { background: rgba(99, 102, 241, 0.18); color: #4338ca; }

        .status-active { color: #16a34a; font-weight: 700; }
        .status-inactive { color: #d97706; font-weight: 700; }
        .status-suspended { color: #dc2626; font-weight: 700; }

        .empty {
            padding: 28px;
            text-align: center;
            color: var(--muted);
            background: #f9fafb;
            border: 1px dashed var(--border);
            border-radius: 12px;
        }

        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 12px; font-weight: 700; }
        .alert-success { background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal.show { display: flex; }
        .modal-content {
            background: #fff;
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 460px;
            border: 1px solid var(--border);
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 700; color: #111827; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--muted); }

        @media (max-width: 900px) {
            body { padding: 20px; }
            .grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .topbar { flex-wrap: wrap; }
            .nav-links { width: 100%; justify-content: space-between; }
            .form-grid { grid-template-columns: 1fr; }
            .search-box { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <div class="brand">
                <div class="brand-badge">TP</div>
                <div>
                    <div>Temasek Polytechnic</div>
                    <div style="font-size: 12px; color: var(--muted); font-weight: 600;">Advanced Manufacturing Centre</div>
                </div>
            </div>
            <div class="nav-links">
                <a href="#">Home</a>
                <a href="#">Admin</a>
                <a href="#">Logs</a>
                <a href="#">Locked Accounts</a>
                <a class="logout" href="#">Logout</a>
            </div>
        </div>

        <div class="hero">
            <h1>Admin Main Page</h1>
            <p>Simulated admin controls plus your user management feature.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <div class="card-title">User Management</div>
                <div class="card-subtitle">Create, update, delete, and search user accounts.</div>

                <form method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                    <input type="hidden" name="action" value="<?php echo ($action === 'edit' && $current_user) ? 'update' : 'create'; ?>">
                    <?php if ($action === 'edit' && $current_user): ?>
                        <input type="hidden" name="user_id" value="<?php echo $current_user['user_id']; ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username"
                                   value="<?php echo $current_user ? htmlspecialchars($current_user['username']) : ''; ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email"
                                   value="<?php echo $current_user ? htmlspecialchars($current_user['email']) : ''; ?>"
                                   required>
                        </div>
                    </div>

                    <?php if (!($action === 'edit' && $current_user)): ?>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="employee" <?php echo ($current_user && $current_user['role'] === 'employee') ? 'selected' : ''; ?>>Employee</option>
                                <option value="manager" <?php echo ($current_user && $current_user['role'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                                <option value="admin" <?php echo ($current_user && $current_user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="hr" <?php echo ($current_user && $current_user['role'] === 'hr') ? 'selected' : ''; ?>>HR</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="active" <?php echo (!$current_user || $current_user['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($current_user && $current_user['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="suspended" <?php echo ($current_user && $current_user['status'] === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                    </div>

                    <div class="actions-row">
                        <button type="submit" class="btn btn-primary">
                            <?php echo ($action === 'edit' && $current_user) ? 'Update User' : 'Create User'; ?>
                        </button>
                        <?php if ($action === 'edit' && $current_user): ?>
                            <a href="?"><button type="button" class="btn btn-secondary">Cancel</button></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-title">User Directory</div>
                <div class="card-subtitle">Search existing users and manage records.</div>

                <form method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Search by username or email..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="?"><button type="button" class="btn btn-secondary">Clear</button></a>
                    <?php endif; ?>
                </form>

                <?php if (count($users) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><span class="status-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                                    <td class="timestamp"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="actions-row">
                                            <a href="?action=edit&id=<?php echo $user['user_id']; ?>">
                                                <button type="button" class="btn btn-secondary">Edit</button>
                                            </a>
                                            <a href="?action=delete_confirm&id=<?php echo $user['user_id']; ?>">
                                                <button type="button" class="btn btn-danger">Delete</button>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty">
                        <?php echo !empty($search_query) ? 'No users found matching your search.' : 'No users yet. Create one to get started!'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Security Monitoring</div>
            <div class="card-subtitle">View logs and manage locked accounts (Admin unlock).</div>
            <div class="actions-row" style="margin-top: 18px;">
                <a href="#"><button type="button" class="btn btn-primary">View Logs</button></a>
                <a href="#"><button type="button" class="btn btn-primary">Locked Accounts</button></a>
            </div>
        </div>
    </div>

    <?php if ($action === 'delete_confirm' && $current_user): ?>
        <div class="modal show">
            <div class="modal-content">
                <div class="modal-header">
                    <span>Confirm Deletion</span>
                    <a href="?"><button type="button" class="close-btn">&times;</button></a>
                </div>
                <p style="margin-bottom: 18px; color: var(--muted);">
                    Are you sure you want to delete the user <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>? This action cannot be undone.
                </p>
                <form method="POST" class="actions-row" style="margin-top: 0;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?php echo $current_user['user_id']; ?>">
                    <button type="submit" class="btn btn-danger">Yes, delete user</button>
                    <a href="?"><button type="button" class="btn btn-secondary">Cancel</button></a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
