<?php
session_start();

 // db_connect.php = config.php, so rename to config.php if there is any issues if it can't run
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// functions, require_once is because helper function is regex and backend is to send the data to the database
require_once __DIR__. '/helper_function.php';
require_once __DIR__ . '/backend_leaveRequest.php';

$pageTitle = 'Apply Leave';
$leave = new leaveRequest(); // Create Leave object for project db

// In actual use, to be called when the user logged into the system
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$logged_in_user = $_SESSION['user_id']; // get user_id from session data for the use of data retrival from the backend
$errorMsg = null;
$success_msg = null;

// Check whether that one particular user_id form submission
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_id = $logged_in_user; // this bit is the part that needs to have the logged in user_id, grab id from form's named input


        // GET data and sanitisation
        $leave_type = $_POST['leave_type'];
        $start_date= $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = cleanUp($_POST['reason']); 

        if ($leave->checkTime($user_id)) {
        $errorMsg = "Error: Wait for 30 secs before resubmission";
    }
        //datecheck function
        else if (strtotime($end_date) < strtotime($start_date)) {
            $errorMsg = "Error: 'End date' cannot be before 'Start date'";
        } 

        //Clean reason/ validation, || for "if any fields are empty, then flag it out"
        else if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        $errorMsg = "Error: All fields are required";
        }

            else {
            // Store sanitised data in $variable
            $leave->user_id = $user_id;
            $leave->leave_type = $leave_type;
            $leave->start_date = $start_date;
            $leave->end_date = $end_date;
            $leave->reason = $reason;

           if ($leave->saveRequest()) {
            $success_msg = "Success: Your form is pending";
        } else {
            global $pdo; 
            $errorMsg = "Error: Could not submit form";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" >
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body style = "font-family: sans-serif; margin: 0; padding: 0;">
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="container">
        <div class="card" style="max-width: 500px; margin: 20px auto; padding: 20px; text-align: left;">
            <h2>Apply leave</h2>

    <div style="width: 320px; margin: 0 auto; text-align: left; border: 1px solid #ccc; padding: 20px;">
    
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success" style="color: green;"><?php echo $success_msg; ?></div> 
        <?php endif; ?>

        <?php if (isset($errorMsg)): ?>
            <div class="alert alert-error" style="color: red;"><?php echo $errorMsg; ?></div>
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
                    <option value = "Medical"> Long- term medical leave</option>
                    <option value = "Maternity"> Maternity leave</option>
                    <option value = "Compassionate leave"> Compassionate leave</option>
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
            <a href="index.php" class = "btn">Cancel</a>
        </form>
    </div>
    </main>
    <!--'/../includes/footer.php': find the directory path of the footer.php [or just include in the same directory], but why do you need it in a different directory, unless it is something not for access-->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="javascripts/script.js"></script>
</body>
</html>
