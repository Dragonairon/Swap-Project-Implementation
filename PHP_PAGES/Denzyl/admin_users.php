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
    die("Connection failed: " . $conn->connect_error);
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
    $check_sql = "SELECT user_id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Username already exists.';
        $message_type = 'error';
        return;
    }

    // Check if email already exists
    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Email already exists.';
        $message_type = 'error';
        return;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $sql = "INSERT INTO users (username, password_hash, email, role, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password_hash, $email, $role, $status);

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
    $check_sql = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $username, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Username already exists.';
        $message_type = 'error';
        return;
    }

    // Check if email already exists (excluding current user)
    $check_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $message = 'Email already exists.';
        $message_type = 'error';
        return;
    }

    // Update user
    $sql = "UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $email, $role, $status, $user_id);

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

    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

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
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
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
        $sql = "SELECT user_id, username, email, role, status, created_at, updated_at FROM users ORDER BY created_at DESC";
        $result = $conn->query($sql);
    } else {
        $search_term = "%" . $query . "%";
        $sql = "SELECT user_id, username, email, role, status, created_at, updated_at FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if ($result->num_rows > 0) {
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
    <title>Admin User Management System</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }

        header {
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }

        h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header-subtitle {
            color: #666;
            font-size: 1em;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 5px;
            height: 25px;
            background-color: #667eea;
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            background-color: #f8f9ff;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        button {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn-primary {
            background-color: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .search-box button {
            margin-right: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background-color: #667eea;
            color: white;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:hover {
            background-color: #f8f9ff;
            transition: background-color 0.3s;
        }

        .status-active {
            color: #28a745;
            font-weight: 600;
        }

        .status-inactive {
            color: #ffc107;
            font-weight: 600;
        }

        .status-suspended {
            color: #dc3545;
            font-weight: 600;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-admin {
            background-color: #667eea;
            color: white;
        }

        .role-manager {
            background-color: #ffc107;
            color: #333;
        }

        .role-employee {
            background-color: #17a2b8;
            color: white;
        }

        .role-hr {
            background-color: #6f42c1;
            color: white;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .actions button {
            padding: 8px 15px;
            font-size: 0.9em;
            margin: 0;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #999;
            padding: 0;
            margin: 0;
        }

        .close-btn:hover {
            color: #333;
        }

        .no-users {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 1.2em;
        }

        .timestamp {
            color: #999;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.8em;
            }

            table {
                font-size: 0.9em;
            }

            table th, table td {
                padding: 10px;
            }

            .actions {
                flex-direction: column;
            }

            .actions button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>👥 Admin User Management System</h1>
            <p class="header-subtitle">Create, Read, Update, and Delete user accounts</p>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Create/Edit User Form Section -->
        <div class="section">
            <h2 class="section-title"><?php echo ($action === 'edit' && $current_user) ? 'Edit User' : 'Create New User'; ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo ($action === 'edit' && $current_user) ? 'update' : 'create'; ?>">
                <?php if ($action === 'edit' && $current_user): ?>
                    <input type="hidden" name="user_id" value="<?php echo $current_user['user_id']; ?>">
                <?php endif; ?>

                <div class="form-row">
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

                <div class="form-row">
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

                <button type="submit" class="btn-primary">
                    <?php echo ($action === 'edit' && $current_user) ? '✓ Update User' : '➕ Create User'; ?>
                </button>
                <?php if ($action === 'edit' && $current_user): ?>
                    <a href="?"><button type="button" class="btn-secondary">Cancel</button></a>
                <?php endif; ?>
            </form>
        </div>

        <!-- User Search and List Section -->
        <div class="section">
            <h2 class="section-title">User Directory</h2>

            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search by username or email..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn-primary">🔍 Search</button>
                <?php if (!empty($search_query)): ?>
                    <a href="?"><button type="button" class="btn-secondary">Clear</button></a>
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
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td class="timestamp"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?action=edit&id=<?php echo $user['user_id']; ?>">
                                            <button type="button" class="btn-warning">✏️ Edit</button>
                                        </a>
                                        <a href="?action=delete_confirm&id=<?php echo $user['user_id']; ?>">
                                            <button type="button" class="btn-danger">🗑️ Delete</button>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-users">
                    <?php echo !empty($search_query) ? 'No users found matching your search.' : 'No users found. Create one to get started!'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <?php if ($action === 'delete_confirm' && $current_user): ?>
        <div class="modal show">
            <div class="modal-content">
                <div class="modal-header">
                    <span>Confirm Deletion</span>
                    <a href="?"><button type="button" class="close-btn">&times;</button></a>
                </div>
                <p style="margin-bottom: 20px; color: #666;">
                    Are you sure you want to delete the user <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>? This action cannot be undone.
                </p>
                <form method="POST" style="margin-bottom: 20px;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?php echo $current_user['user_id']; ?>">
                    <button type="submit" class="btn-danger">Yes, Delete User</button>
                    <a href="?"><button type="button" class="btn-secondary">Cancel</button></a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
