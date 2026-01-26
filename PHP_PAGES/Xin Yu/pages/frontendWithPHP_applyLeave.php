<?php
session_start();

// require_once make sure db_connect.php is loaded
require_once __DIR__ . '/../db_connect.php'; // db_connect.php = config.php, so rename to config.php if there is any issues if it can't run
//call the two other files
include __DIR__ . '/../helper_function.php';
include __DIR__ . '/../backend_leaveRequest.php';

$pageTitle = 'Apply leave';
// Create Leave object for project db
$leave = new leaveRequest();

// simulated user_id 123456. In actual use, to be called when the user logged into the system
$logged_in_user = 123456;

$err_msg = null;
$success_msg = null;

// Check whether that one particular user_id form submission
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_id = $_POST['user_id']; // this bit is the part that needs to have the logged in user_id, grab id from form's named input

    // rate limit security check
    if ($leave->checkTime($user_id)) {
        $err_msg = "Error: Wait for 30 secs before resubmission";
    }
    
    // Validation: Empty fields (also speaking of fields, I do want the logged in user_id's field to be automatically filled in, with the same user_id [if it's user_id 123456, then that field is obviously 123456, I wonder which part of the code needs adding that bit])
    /*else if ($_POST['leave_type'] == "" || $_POST['start_date'] == "" || $_POST['end_date'] == "" || $_POST['reason'] == "") {
        $err_msg = "Error: Missing fields";
    } */
    else {
        // GET data and sanitisation
        $leave_type = $_POST['leave_type'];
        $start_date= $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = cleanUp($_POST['reason']); 

        // Clean reason/ validation
        if (!empty($leave_type) && !empty($start_date) && !empty($end_date) && !empty($reason)) {

            // Store sanitised data in $variable
            $leave->user_id = $user_id;
            $leave->leave_type = $leave_type;
            $leave->start_date = $start_date;
            $leave->end_date = $end_date;
            $leave->reason = $reason;

            // Form submission, connection successful or error (but why? Can you explain?)
        if ($leave->saveRequest()) {
            $success_msg = "Success: Your form is pending";
        } else {
            global $conn; // Declare global, knows connection from db_connect file
            $err_msg = "Error: Could not submit form" . $conn->error;
            }
        } else {
            $err_msg = "Error: Input all fields";
        }
    }
}

// Close connection safely
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" >
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>

<body style = "text-align: center; font-family: sans-serif;"> <!-- This one is fine, it is just styling, right?-->
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="container">
        <div class="card" style="max-width: 500px; margin: 20px auto; padding: 20px; text-align: left;">
            <h2>Apply leave</h2>

    <div style="width: 320px; margin: 0 auto; text-align: left; border: 1px solid #ccc; padding: 20px;">
    
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success" style="color: green;"><?php echo $success_msg; ?></div> 
        <?php endif; ?>

        <?php if (isset($err_msg)): ?>
            <div class="alert alert-error" style="color: red;"><?php echo $err_msg; ?></div>
        <?php endif; ?>

        <form id="leaveForm" method = "POST" action = "" onsubmit="return validateDates()">
            <div class="form-group">
                <label>Employee ID:</label>
                <input type="text" name="user_id" value="<?php echo $logged_in_user; ?>" readonly style="background-color: #f4f4f4;">
            </div>

            <div class="form-group">
                <label for = "leave_type">Leave Type: </label><br>
                <select name = "leave_type" id = "leave_type" required>
                    <option value = ""> -- Choose -- </option>
                    <option value = "Annual">Annual leave</option>
                    <option value = "Medical"> Medical leave</option>
                </select><br><br>
            </div>

            <div class="form-group">
                <label for = "start_date">Start Date:</label><br>
                <!--The date in this format: day-month-year-->
                <input type="date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required> <br><br>
            </div>


            <div class="form-group">
                <label for = "end_date">End Date:</label><br>
            <!--Can I have the the date in this format: day-month-year-->
                <input type="date" name="end_date" min="<?php echo date('Y-m-d'); ?>" required> <br><br>
            </div>


            <div class="form-group">
                <label for = "reason">Reason:</label><br>
                <textarea name="reason" id="reason"  placeholder="Enter reason" required style="width:100%; height:100px;"></textarea><br><br>
            </div>

            <button type="submit" class = "btn btn-primary">Submit Request</button>
            
            <!--Here is to redirect user back to main menu; user error handling purposes-->
            <a href="<?php echo BASE_URL; ?>/index.php" class = "btn">Cancel</a>
        </form>
    </div>
    </main>
    <!--'/../includes/footer.php': find the directory path of the footer.php [or just include in the same directory], but why do you need it in a different directory, unless it is something not for access-->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/javascripts/script.js"></script> <!-- Remember to create 1) The javascripts/ directory, 2) Create the script.js and ask AI how is it related to the project? And if so can you modify it to the context of the project [but first, explain before you modify the script.js template]-->
</body>
</html>