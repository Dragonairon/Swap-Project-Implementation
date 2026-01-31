<?php
// submit_mc.php (Theme: TP Red)
session_start();
require 'db_conn.php';

// HARDCODED USER (Change logic if you have login)
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 3; } 

$message = "";
$msg_type = ""; // To control color (green vs red)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $clinic_name_text = $_POST['clinic_name']; 
    $start_date_ts = strtotime($_POST['start_date']);
    $end_date_ts = strtotime($_POST['end_date']);
    $submitted_at = time();

    $allowed_types = ['jpg' => 1, 'jpeg' => 1, 'png' => 2, 'pdf' => 3];
    
    if (!isset($_FILES["mc_file"]) || $_FILES["mc_file"]["error"] != 0) {
        $message = "Error: Please upload a file.";
        $msg_type = "error";
    } else {
        $filename = $_FILES["mc_file"]["name"];
        $file_tmp = $_FILES["mc_file"]["tmp_name"];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!array_key_exists($file_ext, $allowed_types)) {
            $message = "Error: Invalid file type! Only JPG, PNG, and PDF.";
            $msg_type = "error";
        } else {
            $file_ref_id = rand(100000, 999999); 
            $mime_type_int = $allowed_types[$file_ext]; 
            
            $upload_dir = __DIR__ . "/uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $target_file = $upload_dir . $file_ref_id . "." . $file_ext; 

            if (move_uploaded_file($file_tmp, $target_file)) {
                $sql = "INSERT INTO mc_records 
                        (user_id, clinic_name, start_date, end_date, mc_file_path, mime_type, verification_status, submitted_at, verified_at) 
                        VALUES 
                        (:uid, :clinic, :start, :end, :file_path, :mime, 'unapproved', :sub_at, 0)";
                $stmt = $conn->prepare($sql);
                try {
                    $stmt->execute([
                        ':uid' => $user_id,
                        ':clinic' => $clinic_name_text,
                        ':start' => $start_date_ts,
                        ':end' => $end_date_ts,
                        ':file_path' => $file_ref_id,
                        ':mime' => $mime_type_int,
                        ':sub_at' => $submitted_at
                    ]);
                    $message = "MC Submitted Successfully!";
                    $msg_type = "success";
                } catch (PDOException $e) {
                    $message = "Database Error: " . $e->getMessage();
                    $msg_type = "error";
                }
            } else {
                $message = "Error: Failed to save file.";
                $msg_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit MC</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-mark">TP</div>
                <div>HR Portal</div>
            </div>
            <a href="view_history.php" class="btn btn-secondary" style="padding: 8px 16px;">View History</a>
        </div>
    </div>

    <div class="container" style="max-width: 600px;">
        <div class="form">
            <h2>Submit Medical Certificate</h2>
            <p style="color:var(--tp-gray); margin-bottom:20px;">Please upload your MC details below.</p>
            
            <?php if($message): ?>
                <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Clinic Name</label>
                    <input type="text" name="clinic_name" required placeholder="e.g. Bedok Polyclinic">
                </div>

                <div class="row" style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Upload MC (JPG/PNG/PDF)</label>
                    <input type="file" name="mc_file" required>
                </div>

                <button type="submit" class="btn" style="width:100%; margin-top:10px;">Submit Request</button>
            </form>
        </div>
    </div>

</body>
</html>