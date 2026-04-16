<?php
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $host = "localhost";
    $dbname = "courtconnect";
    $username = "root";
    $password = "";
} else {
    $host = "localhost";
    $dbname = "u668523202_courtconnect";
    $username = "Tennis";
    $password = "ClubTennisIsAwesome7";
}

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>