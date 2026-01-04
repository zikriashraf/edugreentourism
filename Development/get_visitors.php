<?php
include 'db_connect.php';
header('Content-Type: application/json');

// Fetch ALL records so JavaScript can determine the true "latest 6 months" across different years.
$query = "SELECT * FROM visitors_monthly";
$result = mysqli_query($conn, $query);

$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>