<?php
// This is important, but this is just for testing purposes and to be removed; this page is to show that user's loggged in session has data in it, , to be removed in production
session_start();
$_SESSION['user_id'] = 123456; // hardcoded user_id for testing purposes only
$_SESSION['user_role'] = 'normal_user'; // hardcoded user role: admin or 'normal_user'
?>