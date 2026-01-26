<?php

// session_start(to act as a key to access user's logged in session data, specifically their "user id"for leave application and it autofills for them & "user role" whether they are 'normal user'/ 'admin')
session_start();

//this is for demo purposes only, please remove in production
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 123456;
}

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    // I would have used "header("Location: " . BASE_URL . "/pages/login.php");", but i) Since I don't know group's inner workings of their codes, ii) I would just have to use die() to make sure users do not try to access any of the project pages without logging in first
    die("Access denied. Login required to view your leave status");
}

// open db connection`
require_once __DIR__ . '/../db_connect.php';
// include backend to hide SQL logic
include_once __DIR__ . '/../backend_leaveRequest.php';

$pageTitle = 'View Leave Status';

// Data retrieval through the backend; done by getting the actual user id (that the user logged into) instead of hardcoding
$logged_in_user = $_SESSION['user_id']; // get user_id from session data for the use of data retrival from the backend

// create backend object and have it our hidden function
$backend = new leaveRequest();
$result = $backend->getLeaveHistory($logged_in_user);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body style="text-align: center; font-family: sans-serif;">
    
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class = "container">
    <div class = "card" style = "max-width: 800px; margin: 20px; border: 1px solid #ccc;">
        <h2>Leave Status</h2>
        <p>Welcome Back,<strong><?php echo htmlspecialchars($logged_in_user); ?></strong></p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f4f4f4; border-bottom: 2px solid #ccc;">
                    <th style="padding: 10px;">Leave Type</th>
                    <th style="padding: 10px;">Start Date</th>
                    <th style="padding: 10px;">End Date</th>
                    <th style="padding: 10px;">Reason</th>
                    <th style="padding: 10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td style="padding: 10px;">
                                <span style="font-weight: bold; color: <?php
                                    if($row['status'] === 'Pending') echo 'orange';
                                    elseif ($row['status'] === 'Approved') echo 'green';
                                    else echo 'red';
                                ?>;">
                                <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; color: #888;">No leave requests found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style = "margin-top: 20px;">
            <a href = "frontendWithPHP_applyLeave.php" class= "btn btn-primary">Apply for Leave</a>
        </div>
    </div>
</main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

<?php
// since sql logic is handled in backend_leaveRequest.php, we close the connection
$conn->close();
?>