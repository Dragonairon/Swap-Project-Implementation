<?php

// credentials to be connected, with actual credentials put in later

$host = $_SERVER['HTTP_HOST']; // host for project db
$base = '/sample_project'; // your project folder name
define('BASE_URL', 'http://' . $host . $base); // defines base url for project

// Prevents accidental changes with define()
define('DB_HOST', 'localhost'); // localhost db
define('DB_USER', 'root'); // username db
define('DB_PASS', ''); // password db to be filled in
define('DB_NAME', 'project_database'); // database name for project db

// To follow the group's SQL file & syntax
define('DB_NAME', 'project_database');

// new mysqli object for OOP connection & secret handshake to project db
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// checks connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Sanitisation for special symbols (think of accent marks)
$conn->set_charset("utf8");
?>