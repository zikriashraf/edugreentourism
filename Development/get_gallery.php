<?php
// Filename: get_gallery.php
header('Content-Type: application/json');
include 'db_connect.php'; 

// Check connection
if (!$conn) {
    // Return empty array if connection fails
    echo json_encode([]); 
    exit();
}

// Fetch images sorted by order_number
$sql = "SELECT * FROM gallery_images ORDER BY order_number ASC";
$result = $conn->query($sql);

$images = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}

// Return the JSON data
echo json_encode($images);
?>