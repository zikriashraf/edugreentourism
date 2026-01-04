<?php
// save_booking.php

// 1. Set Header & Handle CORS (if needed)
header('Content-Type: application/json');

// 2. Include Database Connection
require 'db_connect.php';

// 3. Get the Raw POST Data from JavaScript
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// 4. Validate Data Exists
if (!isset($data['package_id']) || !isset($data['contact_email'])) {
    echo json_encode(["status" => "error", "message" => "Missing required booking details."]);
    exit;
}

// 5. Extract Variables
$pkg_id = $data['package_id'];
$email = $data['contact_email'];
$date = $data['selected_date']; // Format: YYYY-MM-DD
$total_cost = $data['total_cost'];
$adults = $data['adults'];
$children = $data['children'];
$payment_method = $data['payment_method']; // 'ToyyibPay' or 'Cash'

// 6. Prepare SQL Statement (Prevents SQL Injection)
$stmt = $conn->prepare("INSERT INTO bookings (package_id, customer_email, booking_date, total_cost, pax_adults, pax_children, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

// Determine initial status based on payment method
$initial_status = ($payment_method === 'Cash') ? 'Confirmed' : 'Pending Payment';

// Bind Parameters: i=integer, s=string, d=double
$stmt->bind_param("issdiiss", $pkg_id, $email, $date, $total_cost, $adults, $children, $payment_method, $initial_status);

// 7. Execute and Return Result
if ($stmt->execute()) {
    $booking_id = $stmt->insert_id;

    // --- TOYYIBPAY LOGIC (Optional Placeholder) ---
    // If you were integrating real ToyyibPay, you would generate the BillCode here
    // and return 'payment_url' => 'https://toyyibpay.com/...'
    
    echo json_encode([
        "status" => "success", 
        "booking_id" => $booking_id,
        "message" => "Booking saved successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Database error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>