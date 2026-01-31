<?php
// db_conn.php

$servername = "localhost";
$username   = "root";      // Default XAMPP user
$password   = "";          // Default XAMPP password is empty
$dbname     = "tpamc_database"; // Must match your SQL file

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>