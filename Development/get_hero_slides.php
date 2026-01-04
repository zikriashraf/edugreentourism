<?php
// get_hero_slides.php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
include 'db_connect.php';

if (!$conn) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT * FROM hero_slides WHERE status = 1 ORDER BY order_number ASC";
$result = $conn->query($sql);

$slides = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $slides[] = $row;
    }
}

echo json_encode($slides);
?>