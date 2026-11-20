<?php
// Rename this file to db.php and enter your local MySQL credentials
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$db_username = "root";
$db_password = ""; // Enter your local MySQL password here
$dbname = "alumni_db";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>