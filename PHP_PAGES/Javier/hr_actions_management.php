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

// Include CRUD functions
require_once 'hr_actions.php';

// Initialize variables
$message = '';
$message_type = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$leave_requests = [];
$mc_records = [];
$hr_actions = [];

// Current HR user (simulate from session)
$current_hr_user_id = 2; // Javier's user ID
$current_user_agent = 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $post_action = $_POST['action'];

        if ($post_action === 'approve_leave') {
            handleApproveLeavePOST($conn, $_POST);
        } elseif ($post_action === 'reject_leave') {
            handleRejectLeavePOST($conn, $_POST);
        } elseif ($post_action === 'approve_mc') {
            handleApproveMCPOST($conn, $_POST);
        } elseif ($post_action === 'reject_mc') {
            handleRejectMCPOST($conn, $_POST);
        } elseif ($post_action === 'edit_leave') {
            handleEditLeavePOST($conn, $_POST);
        } elseif ($post_action === 'delete_leave') {
            handleDeleteLeavePOST($conn, $_POST);
        } elseif ($post_action === 'edit_mc') {
            handleEditMCPOST($conn, $_POST);
        } elseif ($post_action === 'delete_mc') {
            handleDeleteMCPOST($conn, $_POST);
        }
    }
}

// Load data
$leave_requests = getLeaveRequests($conn);
$mc_records = getMCRecords($conn);
$hr_actions_result = getAllHRActions($conn);
$hr_actions = $hr_actions_result['success'] ? $hr_actions_result['data'] : [];
$workforce_result = getWorkforceAvailability($conn);

// Debug: Check if there's an error
if (!$workforce_result['success']) {
    error_log('Workforce availability error: ' . $workforce_result['error']);
}

$workforce_availability = $workforce_result['success'] ? $workforce_result['data'] : [];

/**
 * Handle approve leave POST
 */
function handleApproveLeavePOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $leave_id = intval($data['leave_id']);
    $comment = trim($data['comment'] ?? '');

    $result = updateLeaveRequestStatus($conn, $leave_id, 'approved', $current_hr_user_id, $current_user_agent, $comment);
    
    if ($result['success']) {
        $message = 'Leave request approved successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle reject leave POST
 */
function handleRejectLeavePOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $leave_id = intval($data['leave_id']);
    $comment = trim($data['comment'] ?? '');

    $result = updateLeaveRequestStatus($conn, $leave_id, 'rejected', $current_hr_user_id, $current_user_agent, $comment);
    
    if ($result['success']) {
        $message = 'Leave request rejected.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle approve MC POST
 */
function handleApproveMCPOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $mc_id = intval($data['mc_id']);

    $result = updateMCRecordStatus($conn, $mc_id, 'approved', $current_hr_user_id, $current_user_agent);
    
    if ($result['success']) {
        $message = 'MC record approved successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle reject MC POST
 */
function handleRejectMCPOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $mc_id = intval($data['mc_id']);

    $result = updateMCRecordStatus($conn, $mc_id, 'rejected', $current_hr_user_id, $current_user_agent);
    
    if ($result['success']) {
        $message = 'MC record rejected.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle edit leave POST
 */
function handleEditLeavePOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $leave_id = intval($data['leave_id']);
    $reason = $data['reason'] ?? '';  // Allow empty string to clear reason
    $new_status = $data['status'] ?? 'unapproved';

    // Get current status before update
    $stmt = $conn->prepare("SELECT status, reason FROM leave_requests WHERE leave_id = ?");
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();

    if (!$leave) {
        $message = 'Leave request not found.';
        $message_type = 'error';
        return;
    }

    $previous_status = $leave['status'];
    $previous_reason = $leave['reason'];

    // Always update both fields
    $updates = [
        'reason' => $reason,
        'status' => $new_status
    ];

    $result = updateLeaveRequest($conn, $leave_id, $updates);
    
    if ($result['success']) {
        // Log the edit action with actual status change
        $description = "Edited leave request: ";
        $changes = [];
        if ($reason !== $previous_reason) $changes[] = "reason updated";
        if ($new_status !== $previous_status) $changes[] = "status changed from {$previous_status} to {$new_status}";
        
        if (!empty($changes)) {
            $description .= implode(", ", $changes);
        } else {
            $description .= "no changes made";
        }
        
        logHRAction($conn, $current_hr_user_id, 'leave', $leave_id, 'edit', $description, $current_user_agent, $previous_status, $new_status);
        
        $message = 'Leave request updated successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle delete leave POST
 */
function handleDeleteLeavePOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $leave_id = intval($data['leave_id']);

    $result = deleteLeaveRequest($conn, $leave_id);
    
    if ($result['success']) {
        // Log the delete action
        logHRAction($conn, $current_hr_user_id, 'leave', $leave_id, 'delete', 'Leave request deleted', $current_user_agent);
        
        $message = 'Leave request deleted successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle edit MC POST
 */
function handleEditMCPOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $mc_id = intval($data['mc_id']);
    $new_status = $data['status'] ?? 'unapproved';

    // Get current status before update
    $stmt = $conn->prepare("SELECT verification_status FROM mc_records WHERE mc_id = ?");
    $stmt->bind_param("i", $mc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mc = $result->fetch_assoc();

    if (!$mc) {
        $message = 'MC record not found.';
        $message_type = 'error';
        return;
    }

    $previous_status = $mc['verification_status'];

    $updates = ['verification_status' => $new_status];

    $result = updateMCRecord($conn, $mc_id, $updates);
    
    if ($result['success']) {
        // Log the edit action with actual status change
        logHRAction($conn, $current_hr_user_id, 'mc', $mc_id, 'edit', "MC record verification status changed from {$previous_status} to {$new_status}", $current_user_agent, $previous_status, $new_status);
        
        $message = 'MC record updated successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

/**
 * Handle delete MC POST
 */
function handleDeleteMCPOST($conn, $data) {
    global $message, $message_type, $current_hr_user_id, $current_user_agent;

    $mc_id = intval($data['mc_id']);

    $result = deleteMCRecord($conn, $mc_id);
    
    if ($result['success']) {
        // Log the delete action
        logHRAction($conn, $current_hr_user_id, 'mc', $mc_id, 'delete', 'MC record deleted', $current_user_agent);
        
        $message = 'MC record deleted successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}

$conn->close();
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
    <div class="container">
        <header>
            <h1>📋 HR Actions Management System</h1>
            <p class="header-subtitle">Approve, reject, and manage leave requests and medical certificates</p>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab-button active" onclick="switchTab('leave')">Leave Requests</button>
            <button class="tab-button" onclick="switchTab('mc')">Medical Certificates</button>
            <button class="tab-button" onclick="switchTab('workforce')">Workforce Availability</button>
            <button class="tab-button" onclick="switchTab('history')">Action History</button>
        </div>

        <!-- Leave Requests Tab -->
        <div id="leave" class="tab-content active">
            <div class="section">
                <h2 class="section-title">Pending Leave Requests</h2>

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
        </div>

        <!-- Medical Certificates Tab -->
        <div id="mc" class="tab-content">
            <div class="section">
                <h2 class="section-title">Pending Medical Certificates</h2>

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
                    <p style="text-align: center; color: #999; padding: 20px;">No processed medical certificates.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Workforce Availability Tab -->
        <div id="workforce" class="tab-content">
            <div class="section">
                <h2 class="section-title">Workforce Availability</h2>

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
                    <div class="no-records">
                        <p>No workforce data available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action History Tab -->
        <div id="history" class="tab-content">
            <div class="section">
                <h2 class="section-title">HR Action History</h2>

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
                    <div class="no-records">
                        <p>No HR actions recorded yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab and mark button as active
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
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
</body>
</html>
