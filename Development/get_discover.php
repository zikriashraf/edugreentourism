<?php
// get_discover.php
header('Content-Type: application/json');
include 'db_connect.php'; // Ensure you have your DB connection here

$sql = "SELECT * FROM explore_section ORDER BY display_order ASC";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
echo json_encode($data);
$conn->close();
?>