<?php
$servername = "localhost";    // Usually localhost
$username = "root";           // Your DB username
$password = "";               // Your DB password (empty for XAMPP default)
$dbname = "edugreentourism";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Connected successfully"; // For testing
?>
