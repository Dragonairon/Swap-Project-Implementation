<?php
class leaveRequest {
    // variable to hold data, on standby when call save()
    var $user_id;
    var $leave_type;
    var $start_date;
    var $end_date;
    var $reason;

    /* Not required any more
    // Receives var objects and stores it
    function setDatabase($db_conn) {
        $this->db = $db_conn; // I don't think I was taught about this, I need to check about it
    } */

    function getLeaveHistory($uid) {
        // call or use $conn from db_connect.php
        global $conn;

        // secure query with ? to prevent sql injection
        $sql = "SELECT leave_type, start_date, end_date, reason, status
                FROM individual_leave_requests
                WHERE user_id = ?"; // latest submission on top

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $uid); 
        $stmt->execute();

        $result = $stmt->get_result();

        return $result;
    }

    function checkTime($uid) {
        // call or use $conn from db_connect.php
        global $conn;
        
        // Prevents clicking too fast/ Send too many requests
        // Changed id to user_id, just not sure whether it is a good idea because of "when user logged in, the system will automatically fill in that part for you immediately" scenario
        $sql = "SELECT leave_id FROM individual_leave_requests
                WHERE user_id = ?
                AND submitted_at > NOW() - INTERVAL 30 SECOND";

        // Is it here that needs another $sql statement?

        // prepare & bind_param to seperate sql command from user input
        // This block of code is to prevent sql injection
        $stmt =  $conn->prepare($sql);
        $stmt->bind_param("i", $uid); // "i" being int, I was being taught about it
        $stmt->execute();
        $result=$stmt->get_result();

        return $result->num_rows > 0;

    }

        /*I don't think this is needed
        // check for existing request
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }*/


    // Saves leave request into sql table
    function saveRequest() {

        global $conn;
        // hardcode "Pending" to prevent users who are applying leave to approve it on their own
        $sql = "INSERT INTO individual_leave_requests(user_id, leave_type, start_date, end_date, reason, status)
        VALUES (?, ?, ?, ?, ?, 'Pending')";

        // Tell DB prep SQL statment
        $stmt =  $conn->prepare($sql);

        // bind_params for each data to respective ?
        $stmt->bind_param("issss", // datatypes: int, str*4
        $this->user_id, // "$this->" to tell data it is already been sanitised at frontend
        $this->leave_type,
        $this->start_date,
        $this->end_date,
        $this->reason
    );
    // Execute command
    return $stmt->execute();
    }
}
?>