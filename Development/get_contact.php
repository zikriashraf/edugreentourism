<?php
// get_contact.php
header('Content-Type: application/json; charset=utf-8');
include 'db_connect.php';

// Force UTF-8
if ($conn) $conn->set_charset("utf8mb4");

// Fetch the latest contact details
$sql = "SELECT * FROM contact_details ORDER BY contact_id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}
?>