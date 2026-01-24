<?php

// Session timeout and regeneration (only executed after session is started)
if (session_status() === PHP_SESSION_ACTIVE) {
    // Regenerate session ID on first request
    if (!isset($_SESSION['_regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['_regenerated'] = true;
    }

    // Check session timeout (30 minutes)
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

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

// CRUD functions for HR actions

/**
 * Check if user has permission for action
 */
function hasPermission($user_role, $required_roles) {
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    return in_array($user_role, $required_roles, true);
}

/**
 * Verify user is HR or Admin before allowing HR action
 */
function verifyHRAuthorization($user_role) {
    if (!in_array($user_role, ['hr', 'admin', 'manager'], true)) {
        return ['success' => false, 'error' => 'Insufficient permissions. Only HR, Manager, and Admin users can perform this action.'];
    }
    return ['success' => true];
}

/**
 * CREATE - Add a new HR action
 */
function createHRAction($conn, $hr_user_id, $record_type, $record_id, $action_taken, $previous_status, $new_status, $hr_comments = null, $user_agent) {
    // Validate inputs
    if (!in_array($record_type, ['leave', 'mc'])) {
        return ['success' => false, 'error' => 'Invalid record type'];
    }
    if (!in_array($action_taken, ['approve', 'reject'])) {
        return ['success' => false, 'error' => 'Invalid action taken'];
    }
    if (!in_array($previous_status, ['unapproved', 'approved', 'rejected'])) {
        return ['success' => false, 'error' => 'Invalid previous status'];
    }
    if (!in_array($new_status, ['approved', 'rejected'])) {
        return ['success' => false, 'error' => 'Invalid new status'];
    }

    $stmt = $conn->prepare("INSERT INTO hr_actions (hr_user_id, record_type, record_id, action_taken, hr_comments, previous_status, new_status, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("isisissi", $hr_user_id, $record_type, $record_id, $action_taken, $hr_comments, $previous_status, $new_status, $user_agent);
    
    if ($stmt->execute()) {
        return ['success' => true, 'action_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * READ - Get all HR actions with optional filters
 */
function getAllHRActions($conn, $filters = []) {
    $sql = "SELECT * FROM hr_actions WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($filters['hr_user_id'])) {
        $sql .= " AND hr_user_id = ?";
        $params[] = $filters['hr_user_id'];
        $types .= "i";
    }
    if (!empty($filters['record_type'])) {
        $sql .= " AND record_type = ?";
        $params[] = $filters['record_type'];
        $types .= "s";
    }
    if (!empty($filters['record_id'])) {
        $sql .= " AND record_id = ?";
        $params[] = $filters['record_id'];
        $types .= "i";
    }
    if (!empty($filters['action_taken'])) {
        $sql .= " AND action_taken = ?";
        $params[] = $filters['action_taken'];
        $types .= "s";
    }

    $sql .= " ORDER BY action_timestamp DESC";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        return ['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * READ - Get a single HR action by ID
 */
function getHRActionByID($conn, $action_id) {
    $stmt = $conn->prepare("SELECT * FROM hr_actions WHERE action_id = ?");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("i", $action_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $action = $result->fetch_assoc();
        
        if ($action) {
            return ['success' => true, 'data' => $action];
        } else {
            return ['success' => false, 'error' => 'HR action not found'];
        }
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * UPDATE - Update an HR action
 */
function updateHRAction($conn, $action_id, $updates) {
    // Allowed fields to update
    $allowed_fields = ['action_taken', 'hr_comments', 'previous_status', 'new_status'];
    
    $set_clauses = [];
    $params = [];
    $types = "";

    foreach ($updates as $field => $value) {
        if (in_array($field, $allowed_fields)) {
            if ($field === 'action_taken' && !in_array($value, ['approve', 'reject'])) {
                return ['success' => false, 'error' => 'Invalid action_taken value'];
            }
            if ($field === 'previous_status' && !in_array($value, ['unapproved', 'approved', 'rejected'])) {
                return ['success' => false, 'error' => 'Invalid previous_status value'];
            }
            if ($field === 'new_status' && !in_array($value, ['approved', 'rejected'])) {
                return ['success' => false, 'error' => 'Invalid new_status value'];
            }
            
            $set_clauses[] = "$field = ?";
            $params[] = $value;
            $types .= is_int($value) ? "i" : "s";
        }
    }

    if (empty($set_clauses)) {
        return ['success' => false, 'error' => 'No valid fields to update'];
    }

    $params[] = $action_id;
    $types .= "i";

    $sql = "UPDATE hr_actions SET " . implode(", ", $set_clauses) . " WHERE action_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'HR action updated successfully'];
        } else {
            return ['success' => false, 'error' => 'HR action not found'];
        }
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * DELETE - Delete an HR action
 */
function deleteHRAction($conn, $action_id) {
    $stmt = $conn->prepare("DELETE FROM hr_actions WHERE action_id = ?");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("i", $action_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'HR action deleted successfully'];
        } else {
            return ['success' => false, 'error' => 'HR action not found'];
        }
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// HELPER FUNCTIONS FOR RELATED DATA

function getLeaveRequests($conn) {
    $sql = "SELECT * FROM leave_requests";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getMCRecords($conn) {
    $sql = "SELECT * FROM mc_records";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Create - Approve or reject a leave request
 */
function updateLeaveRequestStatus($conn, $leave_id, $status, $hr_user_id, $user_agent, $hr_comment = null) {
    if (!in_array($status, ['approved', 'rejected', 'unapproved'])) {
        return ['success' => false, 'error' => 'Invalid status'];
    }

    // Get current leave request to find previous status
    $stmt = $conn->prepare("SELECT status FROM leave_requests WHERE leave_id = ?");
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();

    if (!$leave) {
        return ['success' => false, 'error' => 'Leave request not found'];
    }

    $previous_status = $leave['status'];
    $action_taken = ($status === 'approved') ? 'approve' : 'reject';

    // Update leave request status and reason (clear reason if no comment provided)
    $reason = $hr_comment ?? '';
    $stmt = $conn->prepare("UPDATE leave_requests SET status = ?, reason = ? WHERE leave_id = ?");
    $stmt->bind_param("ssi", $status, $reason, $leave_id);
    
    if (!$stmt->execute()) {
        return ['success' => false, 'error' => $stmt->error];
    }

    // Create HR action record
    $stmt = $conn->prepare("INSERT INTO hr_actions (hr_user_id, record_type, record_id, action_taken, hr_comments, previous_status, new_status, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $record_type = 'leave';
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("isisissi", $hr_user_id, $record_type, $leave_id, $action_taken, $hr_comment, $previous_status, $status, $user_agent);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Leave request status updated successfully', 'action_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * Create - Approve or reject an MC record
 */
function updateMCRecordStatus($conn, $mc_id, $status, $hr_user_id, $user_agent, $hr_comment = null) {
    if (!in_array($status, ['approved', 'rejected', 'unapproved'])) {
        return ['success' => false, 'error' => 'Invalid status'];
    }

    // Get current MC record to find previous status
    $stmt = $conn->prepare("SELECT verification_status FROM mc_records WHERE mc_id = ?");
    $stmt->bind_param("i", $mc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mc = $result->fetch_assoc();

    if (!$mc) {
        return ['success' => false, 'error' => 'MC record not found'];
    }

    $previous_status = $mc['verification_status'];
    $action_taken = ($status === 'approved') ? 'approve' : 'reject';

    // Update MC record status only (MC records don't have comments field)
    $stmt = $conn->prepare("UPDATE mc_records SET verification_status = ? WHERE mc_id = ?");
    $stmt->bind_param("si", $status, $mc_id);
    
    if (!$stmt->execute()) {
        return ['success' => false, 'error' => $stmt->error];
    }

    // Create HR action record
    $stmt = $conn->prepare("INSERT INTO hr_actions (hr_user_id, record_type, record_id, action_taken, hr_comments, previous_status, new_status, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $record_type = 'mc';
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("isisissi", $hr_user_id, $record_type, $mc_id, $action_taken, $hr_comment, $previous_status, $status, $user_agent);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'MC record status updated successfully', 'action_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * CREATE - Log a general HR action (edit, delete, etc.)
 */
function logHRAction($conn, $hr_user_id, $record_type, $record_id, $action_type, $description, $user_agent, $previous_status = 'unapproved', $new_status = 'approved') {
    // action_type can be: edit, delete, etc.
    $stmt = $conn->prepare("INSERT INTO hr_actions (hr_user_id, record_type, record_id, action_taken, hr_comments, previous_status, new_status, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("isisissi", $hr_user_id, $record_type, $record_id, $action_type, $description, $previous_status, $new_status, $user_agent);
    
    if ($stmt->execute()) {
        return ['success' => true, 'action_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * UPDATE - Edit a leave request
 */
function updateLeaveRequest($conn, $leave_id, $updates) {
    $allowed_fields = ['reason', 'status'];
    
    $set_clauses = [];
    $params = [];
    $types = "";

    foreach ($updates as $field => $value) {
        if (in_array($field, $allowed_fields)) {
            if ($field === 'status' && !in_array($value, ['approved', 'rejected', 'unapproved'])) {
                return ['success' => false, 'error' => 'Invalid status value'];
            }
            
            $set_clauses[] = "$field = ?";
            $params[] = $value;
            $types .= "s";
        }
    }

    if (empty($set_clauses)) {
        return ['success' => false, 'error' => 'No valid fields to update'];
    }

    $params[] = $leave_id;
    $types .= "i";

    $sql = "UPDATE leave_requests SET " . implode(", ", $set_clauses) . " WHERE leave_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Success - affected_rows might be 0 if values didn't actually change, which is OK
        return ['success' => true, 'message' => 'Leave request updated successfully'];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * DELETE - Delete a leave request
 */
function deleteLeaveRequest($conn, $leave_id) {
    $stmt = $conn->prepare("DELETE FROM leave_requests WHERE leave_id = ?");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("i", $leave_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'Leave request deleted successfully'];
        } else {
            return ['success' => false, 'error' => 'Leave request not found'];
        }
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * UPDATE - Edit an MC record
 */
function updateMCRecord($conn, $mc_id, $updates) {
    $allowed_fields = ['verification_status'];
    
    $set_clauses = [];
    $params = [];
    $types = "";

    foreach ($updates as $field => $value) {
        if (in_array($field, $allowed_fields)) {
            if ($field === 'verification_status' && !in_array($value, ['approved', 'rejected', 'unapproved'])) {
                return ['success' => false, 'error' => 'Invalid verification status value'];
            }
            
            $set_clauses[] = "$field = ?";
            $params[] = $value;
            $types .= "s";
        }
    }

    if (empty($set_clauses)) {
        return ['success' => false, 'error' => 'No valid fields to update'];
    }

    $params[] = $mc_id;
    $types .= "i";

    $sql = "UPDATE mc_records SET " . implode(", ", $set_clauses) . " WHERE mc_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Success - affected_rows might be 0 if values didn't actually change, which is OK
        return ['success' => true, 'message' => 'MC record updated successfully'];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * DELETE - Delete an MC record
 */
function deleteMCRecord($conn, $mc_id) {
    $stmt = $conn->prepare("DELETE FROM mc_records WHERE mc_id = ?");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }

    $stmt->bind_param("i", $mc_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'MC record deleted successfully'];
        } else {
            return ['success' => false, 'error' => 'MC record not found'];
        }
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

/**
 * READ - Get workforce availability (all employees with their leave/MC status)
 */
function getWorkforceAvailability($conn) {
    // Get all users with their leave/MC status using LEFT JOIN
    $stmt = $conn->prepare("
        SELECT 
            u.user_id, 
            u.username, 
            u.role,
            CASE 
                WHEN lr.leave_id IS NOT NULL THEN 'absent'
                WHEN mc.mc_id IS NOT NULL THEN 'absent'
                ELSE 'available'
            END as status,
            CASE 
                WHEN lr.leave_id IS NOT NULL THEN 'leave'
                WHEN mc.mc_id IS NOT NULL THEN 'mc'
                ELSE NULL
            END as absence_type,
            COALESCE(lr.end_date, mc.end_date) as end_date
        FROM users u
        LEFT JOIN leave_requests lr ON u.user_id = lr.user_id AND lr.status = 'approved'
        LEFT JOIN mc_records mc ON u.user_id = mc.user_id AND mc.verification_status = 'approved'
        ORDER BY u.username ASC
    ");
    
    if (!$stmt) {
        return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $availability = [];
        
        while ($row = $result->fetch_assoc()) {
            $availability[] = $row;
        }
        
        return ['success' => true, 'data' => $availability];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}

// POST HANDLER FUNCTIONS

/**
 * Handle approve leave POST
 */
function handleApproveLeavePOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    $auth = verifyHRAuthorization($current_user_role);
    if (!$auth['success']) {
        $message = $auth['error'];
        $message_type = 'error';
        return;
    }

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
function handleRejectLeavePOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    $auth = verifyHRAuthorization($current_user_role);
    if (!$auth['success']) {
        $message = $auth['error'];
        $message_type = 'error';
        return;
    }

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
function handleApproveMCPOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    $auth = verifyHRAuthorization($current_user_role);
    if (!$auth['success']) {
        $message = $auth['error'];
        $message_type = 'error';
        return;
    }

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
function handleRejectMCPOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    $auth = verifyHRAuthorization($current_user_role);
    if (!$auth['success']) {
        $message = $auth['error'];
        $message_type = 'error';
        return;
    }

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
function handleEditLeavePOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    if (!hasPermission($current_user_role, ['hr', 'admin'])) {
        $message = 'Insufficient permissions. Only HR and Admin users can edit leave requests.';
        $message_type = 'error';
        return;
    }

    $leave_id = intval($data['leave_id']);
    $reason = $data['reason'] ?? '';
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

    $updates = [
        'reason' => $reason,
        'status' => $new_status
    ];

    $result = updateLeaveRequest($conn, $leave_id, $updates);
    
    if ($result['success']) {
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
function handleDeleteLeavePOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    if (!hasPermission($current_user_role, ['admin'])) {
        $message = 'Insufficient permissions. Only Admin users can delete leave requests.';
        $message_type = 'error';
        return;
    }

    $leave_id = intval($data['leave_id']);

    $result = deleteLeaveRequest($conn, $leave_id);
    
    if ($result['success']) {
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
function handleEditMCPOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    if (!hasPermission($current_user_role, ['hr', 'admin'])) {
        $message = 'Insufficient permissions. Only HR and Admin users can edit MC records.';
        $message_type = 'error';
        return;
    }

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
function handleDeleteMCPOST($conn, $data, $current_hr_user_id, $current_user_role, $current_user_agent, &$message, &$message_type) {
    if (!hasPermission($current_user_role, ['admin'])) {
        $message = 'Insufficient permissions. Only Admin users can delete MC records.';
        $message_type = 'error';
        return;
    }

    $mc_id = intval($data['mc_id']);

    $result = deleteMCRecord($conn, $mc_id);
    
    if ($result['success']) {
        logHRAction($conn, $current_hr_user_id, 'mc', $mc_id, 'delete', 'MC record deleted', $current_user_agent);
        
        $message = 'MC record deleted successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error: ' . $result['error'];
        $message_type = 'error';
    }
}
?>