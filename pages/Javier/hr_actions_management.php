<?php
// Configure session settings BEFORE session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) session_start();

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

// Include CRUD functions
require_once 'hr_actions.php';

// Authentication check - ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../Irfan/login+logout+homepage/login.php');
    exit;
}

// Authorization check - ensure user has HR or Admin role
if (!in_array($_SESSION['role'], ['hr', 'admin', 'manager'])) {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$leave_requests = [];
$mc_records = [];
$hr_actions = [];

// Current HR user from session
$current_hr_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['role'];
$current_user_agent = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $post_action = $_POST['action'];

        if ($post_action === 'approve_leave') {
            handleApproveLeavePOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'reject_leave') {
            handleRejectLeavePOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'approve_mc') {
            handleApproveMCPOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'reject_mc') {
            handleRejectMCPOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'edit_leave') {
            handleEditLeavePOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'delete_leave') {
            handleDeleteLeavePOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'edit_mc') {
            handleEditMCPOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        } elseif ($post_action === 'delete_mc') {
            handleDeleteMCPOST($conn, $_POST, $current_hr_user_id, $current_user_role, $current_user_agent, $message, $message_type);
        }
    }
}

// Load data
$leave_requests = getLeaveRequests($conn);
$mc_records = getMCRecords($conn);
$hr_actions_result = getAllHRActions($conn);
$hr_actions = $hr_actions_result['success'] ? $hr_actions_result['data'] : [];
$workforce_result = getWorkforceAvailability($conn);

// Debug: Check if there's an error in workforce availability retrieval
if (!$workforce_result['success']) {
    error_log('Workforce availability error: ' . $workforce_result['error']);
}

$workforce_availability = $workforce_result['success'] ? $workforce_result['data'] : [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Actions Management System</title>
    <link rel="stylesheet" href="hr_actions.css">
</head>
<body>
    <div class="shell">
        <!-- Topbar -->
        <div class="topbar">
            <div class="brand">
                <div class="brand-badge">TP</div>
                <div>
                    <div>Temasek Polytechnic</div>
                    <div style="font-size: 12px; color: var(--muted); font-weight: 600;">HR Management System</div>
                </div>
            </div>
            <div class="nav-links">
                <a href="#">Home</a>
                <a href="#">HR</a>
                <a href="#">Logs</a>
                <a href="#">Locked Accounts</a>
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>

        <div class="hero">
            <h1>📋 HR Actions Management</h1>
            <p>Approve, reject, and manage leave requests and medical certificates</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Left Column: Approvals -->
            <div class="card">
                <div class="card-title">Pending Approvals</div>
                <div class="card-subtitle">Approve or reject pending leave requests and medical certificates.</div>

                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button class="tab-button active" onclick="switchTab('leave', this)">Leave Requests</button>
                    <button class="tab-button" onclick="switchTab('mc', this)">Medical Certificates</button>
                </div>

                <!-- Leave Requests Tab -->
                <div id="leave" class="tab-content active">
                    <?php
                    $pending_leaves = array_filter($leave_requests, function($leave) {
                        return $leave['status'] === 'unapproved';
                    });
                    ?>

                <?php if (count($pending_leaves) > 0): ?>
                    <div class="records-grid">
                        <?php foreach ($pending_leaves as $leave): ?>
                            <div class="record-card">
                                <div class="record-header">
                                    <h3>Leave Request #<?php echo $leave['leave_id']; ?></h3>
                                    <span class="status-badge status-unapproved">Pending</span>
                                </div>

                                <div class="record-details">
                                    <div class="detail-row">
                                        <span class="label">User ID:</span>
                                        <span><?php echo $leave['user_id']; ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Reason:</span>
                                        <span><?php echo htmlspecialchars($leave['reason']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Period:</span>
                                        <span>Day <?php echo $leave['start_date']; ?> - Day <?php echo $leave['end_date']; ?></span>
                                    </div>
                                </div>

                                <form method="POST" class="action-form">
                                    <div class="form-group">
                                        <label for="comment_<?php echo $leave['leave_id']; ?>">Comments</label>
                                        <textarea id="comment_<?php echo $leave['leave_id']; ?>" 
                                                  name="comment" 
                                                  placeholder="Add your comments (optional)"
                                                  rows="3"></textarea>
                                    </div>

                                    <div class="action-buttons">
                                        <input type="hidden" name="action" value="approve_leave">
                                        <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                        <button type="submit" class="btn-success">✓ Approve</button>
                                    </div>
                                </form>

                                <form method="POST" class="action-form">
                                    <input type="hidden" name="action" value="reject_leave">
                                    <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                    <div class="form-group">
                                        <label for="comment_reject_<?php echo $leave['leave_id']; ?>">Rejection Reason</label>
                                        <textarea id="comment_reject_<?php echo $leave['leave_id']; ?>" 
                                                  name="comment" 
                                                  placeholder="Explain why you're rejecting this request"
                                                  rows="3"></textarea>
                                    </div>
                                    <div class="action-buttons">
                                        <button type="submit" class="btn-danger">✗ Reject</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-records">
                        <p>✓ No pending leave requests at the moment.</p>
                    </div>
                <?php endif; ?>

                <h3 class="section-subtitle">Processed Leave Requests</h3>
                <?php
                $processed_leaves = array_filter($leave_requests, function($leave) {
                    return $leave['status'] !== 'unapproved';
                });
                ?>

                <?php if (count($processed_leaves) > 0): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Reason</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processed_leaves as $leave): ?>
                                <tr>
                                    <td><?php echo $leave['leave_id']; ?></td>
                                    <td><?php echo $leave['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                    <td>Day <?php echo $leave['start_date']; ?> - <?php echo $leave['end_date']; ?></td>
                                    <td><span class="status-badge status-<?php echo $leave['status']; ?>"><?php echo ucfirst($leave['status']); ?></span></td>
                                    <td class="action-cell">
                                        <button type="button" class="btn-small btn-warning" onclick="openEditLeaveModal(<?php echo $leave['leave_id']; ?>, '<?php echo htmlspecialchars($leave['reason']); ?>', '<?php echo $leave['status']; ?>')">✏️ Edit</button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this leave request? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete_leave">
                                            <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
                                            <button type="submit" class="btn-small btn-danger">🗑️ Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">No processed leave requests.</p>
                <?php endif; ?>
                </div>

                <!-- Medical Certificates Tab -->
                <div id="mc" class="tab-content">
                    <?php
                    $pending_mcs = array_filter($mc_records, function($mc) {
                        return $mc['verification_status'] === 'unapproved';
                    });
                    ?>

                    <?php if (count($pending_mcs) > 0): ?>
                        <div class="records-grid">
                            <?php foreach ($pending_mcs as $mc): ?>
                                <div class="record-card">
                                    <div class="record-header">
                                        <h3>MC Record #<?php echo $mc['mc_id']; ?></h3>
                                        <span class="status-badge status-unapproved">Pending</span>
                                    </div>

                                    <div class="record-details">
                                        <div class="detail-row">
                                            <span class="label">User ID:</span>
                                            <span><?php echo $mc['user_id']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Clinic:</span>
                                            <span><?php echo $mc['clinic_name']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="label">Period:</span>
                                            <span>Day <?php echo $mc['start_date']; ?> - Day <?php echo $mc['end_date']; ?></span>
                                        </div>
                                    </div>

                                    <form method="POST" class="action-form">
                                        <div class="action-buttons">
                                            <input type="hidden" name="action" value="approve_mc">
                                            <input type="hidden" name="mc_id" value="<?php echo $mc['mc_id']; ?>">
                                            <button type="submit" class="btn-success">✓ Approve</button>
                                        </div>
                                    </form>

                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="reject_mc">
                                        <input type="hidden" name="mc_id" value="<?php echo $mc['mc_id']; ?>">
                                        <div class="action-buttons">
                                            <button type="submit" class="btn-danger">✗ Reject</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <p>✓ No pending medical certificates at the moment.</p>
                        </div>
                    <?php endif; ?>

                    <h3 class="section-subtitle">Processed Medical Certificates</h3>
                    <?php
                    $processed_mcs = array_filter($mc_records, function($mc) {
                        return $mc['verification_status'] !== 'unapproved';
                    });
                    ?>

                    <?php if (count($processed_mcs) > 0): ?>
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Clinic</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($processed_mcs as $mc): ?>
                                    <tr>
                                        <td><?php echo $mc['mc_id']; ?></td>
                                        <td><?php echo $mc['user_id']; ?></td>
                                        <td><?php echo $mc['clinic_name']; ?></td>
                                        <td>Day <?php echo $mc['start_date']; ?> - <?php echo $mc['end_date']; ?></td>
                                        <td><span class="status-badge status-<?php echo $mc['verification_status']; ?>"><?php echo ucfirst($mc['verification_status']); ?></span></td>
                                        <td class="action-cell">
                                            <button type="button" class="btn-small btn-warning" onclick="openEditMCModal(<?php echo $mc['mc_id']; ?>, '<?php echo $mc['verification_status']; ?>')">✏️ Edit</button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this MC record? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete_mc">
                                                <input type="hidden" name="mc_id" value="<?php echo $mc['mc_id']; ?>">
                                                <button type="submit" class="btn-small btn-danger">🗑️ Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty">No pending medical certificates.</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-title">Workforce Availability</div>
                <div class="card-subtitle">Current status of employees on leave or medical leave.</div>

                <?php if (count($workforce_availability) > 0): ?>
                    <?php
                        // Separate absent and available employees
                        $absent_employees = array_filter($workforce_availability, function($emp) {
                            return $emp['status'] === 'absent';
                        });
                        $available_employees = array_filter($workforce_availability, function($emp) {
                            return $emp['status'] === 'available';
                        });
                    ?>

                    <?php if (count($absent_employees) > 0): ?>
                        <h3 class="section-subtitle">Currently Absent</h3>
                        <table class="availability-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Absence Type</th>
                                    <th>Return Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($absent_employees as $employee): ?>
                                    <tr class="availability-row availability-absent">
                                        <td><?php echo $employee['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($employee['username']); ?></td>
                                        <td><span class="role-badge"><?php echo ucfirst($employee['role']); ?></span></td>
                                        <td>
                                            <span class="status-badge-availability status-absent">
                                                Absent
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($employee['absence_type'] === 'leave') {
                                                    echo '<span class="absence-badge absence-leave">Leave</span>';
                                                } elseif ($employee['absence_type'] === 'mc') {
                                                    echo '<span class="absence-badge absence-mc">Medical Certificate</span>';
                                                } else {
                                                    echo '—';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($employee['end_date'] !== null) {
                                                    echo 'Day ' . htmlspecialchars($employee['end_date']);
                                                } else {
                                                    echo '—';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (count($available_employees) > 0): ?>
                        <h3 class="section-subtitle">Available Employees</h3>
                        <table class="availability-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($available_employees as $employee): ?>
                                    <tr class="availability-row availability-available">
                                        <td><?php echo $employee['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($employee['username']); ?></td>
                                        <td><span class="role-badge"><?php echo ucfirst($employee['role']); ?></span></td>
                                        <td>
                                            <span class="status-badge-availability status-available">
                                                Available
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty">No workforce data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action History Section -->
        <div class="card">
            <div class="card-title">HR Action History</div>
            <div class="card-subtitle">Complete audit trail of all actions performed.</div>

                <?php if (count($hr_actions) > 0): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Action ID</th>
                                <th>Record Type</th>
                                <th>Record ID</th>
                                <th>Action</th>
                                <th>Status Change</th>
                                <th>Comments</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hr_actions as $hr_action): ?>
                                <tr>
                                    <td><?php echo $hr_action['action_id']; ?></td>
                                    <td><span class="badge-type"><?php echo strtoupper($hr_action['record_type']); ?></span></td>
                                    <td><?php echo $hr_action['record_id']; ?></td>
                                    <td>
                                        <span class="action-badge action-<?php echo $hr_action['action_taken']; ?>">
                                            <?php echo ucfirst($hr_action['action_taken']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo ucfirst($hr_action['previous_status']); ?> → 
                                        <?php echo ucfirst($hr_action['new_status']); ?>
                                    </td>
                                    <td class="comment-cell"><?php echo htmlspecialchars($hr_action['hr_comments'] ?? 'N/A'); ?></td>
                                    <td class="timestamp"><?php echo date('M d, Y H:i', strtotime($hr_action['action_timestamp'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty">No HR actions recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName, button) {
            document.querySelectorAll('.tab-content')
                .forEach(tab => tab.classList.remove('active'));

            document.querySelectorAll('.tab-button')
                .forEach(btn => btn.classList.remove('active'));

            document.getElementById(tabName).classList.add('active');
            button.classList.add('active');
        }

        // Edit Leave Modal Functions
        function openEditLeaveModal(leaveId, reason, status) {
            document.getElementById('editLeaveId').value = leaveId;
            document.getElementById('editLeaveReason').value = reason;
            document.getElementById('editLeaveStatus').value = status;
            document.getElementById('editLeaveModal').classList.add('show');
        }

        function closeEditLeaveModal() {
            document.getElementById('editLeaveModal').classList.remove('show');
        }

        // Edit MC Modal Functions
        function openEditMCModal(mcId, status) {
            document.getElementById('editMCId').value = mcId;
            document.getElementById('editMCStatus').value = status;
            document.getElementById('editMCModal').classList.add('show');
        }

        function closeEditMCModal() {
            document.getElementById('editMCModal').classList.remove('show');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const leaveModal = document.getElementById('editLeaveModal');
            const mcModal = document.getElementById('editMCModal');
            
            if (event.target === leaveModal) {
                leaveModal.classList.remove('show');
            }
            if (event.target === mcModal) {
                mcModal.classList.remove('show');
            }
        }
    </script>

    <!-- Edit Leave Modal -->
    <div id="editLeaveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Edit Leave Request</span>
                <button type="button" class="close-btn" onclick="closeEditLeaveModal()">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="edit_leave">
                <input type="hidden" id="editLeaveId" name="leave_id">

                <div class="form-group">
                    <label for="editLeaveReason">Reason</label>
                    <textarea id="editLeaveReason" name="reason" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="editLeaveStatus">Status</label>
                    <select id="editLeaveStatus" name="status" required>
                        <option value="unapproved">Unapproved</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <button type="button" class="btn-secondary" onclick="closeEditLeaveModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit MC Modal -->
    <div id="editMCModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Edit Medical Certificate</span>
                <button type="button" class="close-btn" onclick="closeEditMCModal()">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="edit_mc">
                <input type="hidden" id="editMCId" name="mc_id">

                <div class="form-group">
                    <label for="editMCStatus">Verification Status</label>
                    <select id="editMCStatus" name="status" required>
                        <option value="unapproved">Unapproved</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <button type="button" class="btn-secondary" onclick="closeEditMCModal()">Cancel</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</body>
</html>
