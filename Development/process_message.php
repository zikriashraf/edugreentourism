<?php
// process_message.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// 1. Connect to Database
$conn = new mysqli("localhost", "root", "", "edugreentourism");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database Connection Failed"]);
    exit();
}

// 2. Get Data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit();
}

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$subject = $data['subject'] ?? '';
$message = $data['message'] ?? '';

// 3. Validation
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
    exit();
}

// 4. Insert into Database
$sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Message sent successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error saving message."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
}

$conn->close();
?>