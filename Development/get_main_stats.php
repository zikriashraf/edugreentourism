<?php
// Enable error reporting to find issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';
header('Content-Type: application/json');

// Use backticks around table name `stats` to prevent keyword conflicts
$query = "SELECT * FROM `stats` LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        // Force these to be numbers so JS handles them correctly
        'participants' => (int)$data['participants'],
        'donation' => (float)$data['donation'],
        'vendors' => (int)$data['vendors']
    ]);
} else {
    // Send the specific MySQL error if the query failed
    echo json_encode([
        'success' => false,
        'error' => mysqli_error($conn),
        'participants' => 0,
        'donation' => 0.00,
        'vendors' => 0
    ]);
}
?>