<?php
// get_learn_content.php
header('Content-Type: application/json');
include 'db_connect.php';

if (!$conn) {
    echo json_encode([]);
    exit();
}

// Fetch content sorted by order_number
$sql = "SELECT * FROM learn_content ORDER BY order_number ASC";
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>