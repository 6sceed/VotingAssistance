<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "voting_system"; // your actual DB name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]));
}
?>