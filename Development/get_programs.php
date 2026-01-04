<?php
header('Content-Type: application/json');
error_reporting(0);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT * FROM programs";
$result = $conn->query($sql);

$programs = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $img = isset($row['imageUrl']) ? $row['imageUrl'] : '';

        $programs[] = [
            // FIX: Mapping 'program_id' to 'id' for JS compatibility
            'id' => $row['program_id'], 
            'name' => $row['program_name'],
            'price' => (float)$row['price'],
            'startTime' => substr($row['start_time'], 0, 5),
            'endTime' => substr($row['end_time'], 0, 5),
            'description' => $row['description'],
            'imageUrl' => $img
        ];
    }
}

echo json_encode($programs);
$conn->close();
?>