<?php
class leaveRequest {
    // variable to hold data, on standby when call save()
    var $user_id;
    var $leave_type;
    var $start_date;
    var $end_date;
    var $reason;

    function getLeaveHistory($uid) {
        // call or use $pdo
        global $pdo;

        // secure query with ? to prevent sql injection
        $sql = "SELECT leave_type, start_date, end_date, reason, status
                FROM leave_requests
                WHERE user_id = :uid
                ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$uid]);

        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function checkTime($uid) {
        // call or use $pdo from db_connect.php
        global $pdo;
        
        // Prevents clicking too fast/ Send too many requests
        // "when user logged in, the system will automatically fill in that part for you immediately" scenario
        $sql = "SELECT id FROM leave_requests
                WHERE user_id = :uid
                AND created_at > NOW() - INTERVAL 30 SECOND";


        // prepare & bind_param to seperate sql command from user input
        // This block of code is to prevent sql injection
        $stmt =  $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$uid]);
        return $stmt->rowCount() > 0;

    }


    // Saves leave request into sql table
    function saveRequest() {

        global $pdo;
        // hardcode "Pending" to prevent users who are applying leave to approve it on their own
        $sql = "INSERT INTO leave_requests(user_id, leave_type, start_date, end_date, reason, status, created_at)
        VALUES (:user_id, :leave_type, :start_date, :end_date, :reason, 'Pending', NOW())";

        // Tell DB prep SQL statment
        $stmt =  $pdo->prepare($sql);

        // PDO execution, map "var"  variables to sql placeholders
        return $stmt->execute([
            ':user_id' => (int)$this->user_id,
            ':leave_type' => $this->leave_type,
            ':start_date' => $this->start_date,
            ':end_date' => $this->end_date,
            ':reason' => $this->reason
        ]);
        
    }
}
?>
