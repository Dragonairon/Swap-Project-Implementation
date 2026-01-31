<?php
// view_history.php (Theme: TP Red)
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 3; }
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM mc_records WHERE user_id = :uid ORDER BY submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([':uid' => $user_id]);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My MC History</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-mark">TP</div>
                <div>HR Portal</div>
            </div>
            <a href="submit_mc.php" class="btn">Submit New MC</a>
        </div>
    </div>

    <div class="container">
        
        <div class="card" style="padding: 0; border: none; box-shadow: none; background: transparent;">
            <h2 style="margin-bottom: 5px;">My History</h2>
            <p style="color:var(--tp-gray); margin-top:0;">Track your past MC submissions and approval status.</p>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table>
                <thead>
                    <tr>
                        <th>Clinic Name</th>
                        <th>Dates</th>
                        <th>File Ref</th>
                        <th>Submitted On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($row['clinic_name']) ?></td>
                                
                                <td>
                                    <?= date('d M Y', $row['start_date']) ?> 
                                    <span style="color:var(--tp-gray); font-size:12px;">to</span> 
                                    <?= date('d M Y', $row['end_date']) ?>
                                </td>
                                
                                <td style="color:var(--tp-gray);">#<?= htmlspecialchars($row['mc_file_path']) ?></td>
                                
                                <td><?= date('d M Y, h:i A', $row['submitted_at']) ?></td>
                                
                                <td>
                                    <span class="status status-<?= strtolower($row['verification_status']) ?>">
                                        <?= ucfirst($row['verification_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 30px; color: var(--tp-gray);">
                                No MC records found. <br>
                                <a href="submit_mc.php" style="color:var(--tp-red); font-weight:600;">Submit your first one now</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>