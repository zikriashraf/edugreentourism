<?php
// process_booking.php

// 1. ENABLE ERROR REPORTING
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

function sendError($message, $debug = null) {
    echo json_encode(["success" => false, "message" => $message, "debug" => $debug]);
    exit();
}

// 2. DATABASE CONNECTION
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    sendError("Database Connection Failed: " . $conn->connect_error);
}

// 3. GET DATA
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) sendError("No JSON data received.");

// 4. EXTRACT & SANITIZE
$name  = $data['customer_name'] ?? '';
$phone = $data['customer_phone'] ?? '';
$email = $data['customer_email'] ?? '';
// Normalize Payment Method
$payInput = strtolower(trim($data['payment_method'] ?? 'cash')); 
$isToyyib = ($payInput === 'toyyibpay' || $payInput === 'online banking');

$type = $data['type'] ?? 'standard';
$rawDate = $data['date'] ?? date('Y-m-d');
$totalPrice = $data['totalPrice'] ?? 0.00; 

// Pax & Package Logic
$paxAdults = $data['adults'] ?? 1;
$paxChildren = $data['children'] ?? 0;
$packageId = ($type === 'standard') ? ($data['pkg_id'] ?? null) : null;
$customActivities = ($type === 'custom' && !empty($data['programIds'])) ? json_encode($data['programIds']) : null;

// Date Formatting
$bookingDate = date('Y-m-d', strtotime(str_replace('/', '-', $rawDate)));

// =========================================================
// 5. CALCULATE DONATION (BUT DO NOT INSERT YET)
// =========================================================
$bookingCost = $totalPrice; 
$donationAmount = 0;
$hasDonation = (isset($data['donation_added']) && $data['donation_added'] == true);

if ($hasDonation) {
    $donationAmount = 1.00;
    $bookingCost = $totalPrice - $donationAmount;
}
// NOTE: We moved the INSERT DONATION query to the "Cash" section below 
// so it doesn't save if ToyyibPay fails.

// =========================================================
// 6. INSERT INTO BOOKINGS
// =========================================================
$status = $isToyyib ? 'Pending Payment' : 'Pending Payment'; 
$payMethodStr = $isToyyib ? 'ToyyibPay' : 'Cash';

$stmt = $conn->prepare("INSERT INTO bookings (customer_name, customer_phone, customer_email, booking_type, package_id, custom_activities, booking_date, total_cost, pax_adults, pax_children, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) sendError("SQL Prepare Error: " . $conn->error);

$stmt->bind_param("ssssissdiiss", $name, $phone, $email, $type, $packageId, $customActivities, $bookingDate, $bookingCost, $paxAdults, $paxChildren, $payMethodStr, $status);

if ($stmt->execute()) {
    $bookingId = $stmt->insert_id;

    // =========================================================
    // 7. TOYYIBPAY INTEGRATION
    // =========================================================
    if ($isToyyib) {
        
        $secretKey = 'my0uojzz-ixde-mpj0-mguj-f8doj4dcd2rc'; 
        $categoryCode = '9obqd6a6'; 
        $url_base = 'https://toyyibpay.com'; 
        
        $returnUrl = 'http://development.test/payment_return.php'; 
        $callbackUrl = 'http://development.test/payment_callback.php';

        $amountCents = (int)($totalPrice * 100);

        $bill_data = array(
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
            'billName' => 'EduGreen Booking',
            'billDescription' => 'Booking Ref: ' . $bookingId,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $amountCents, 
            'billReturnUrl' => $returnUrl,
            'billCallbackUrl' => $callbackUrl,
            'billTo' => $name,
            'billEmail' => $email,
            'billPhone' => $phone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => '0',
            'billContentEmail' => 'Thank you for booking!',
            'billChargeToCustomer' => 1
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $url_base . '/index.php/api/createBill');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($bill_data));

        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $obj = json_decode($result);

        if(isset($obj) && is_array($obj) && isset($obj[0]->BillCode)) {
            // SUCCESS
            echo json_encode([
                "success" => true, 
                "payment_url" => $url_base . "/" . $obj[0]->BillCode
            ]);
        } 
        else {
            // FAILURE: TOYYIBPAY REJECTED
            // *** CRITICAL FIX: Delete the booking so it doesn't stay in the system ***
            $conn->query("DELETE FROM bookings WHERE booking_id = $bookingId");

            $debugMsg = empty($result) ? "Empty Response (HTTP $httpCode)" : $result;
            sendError("ToyyibPay Error: API Rejected Request. Booking removed.", $debugMsg);
        }
    } else {
        // =========================================================
        // 8. CASH FALLBACK (CONFIRMED IMMEDIATELY)
        // =========================================================
        
        // *** CRITICAL FIX: Only insert donation here if it is Cash ***
        if ($hasDonation) {
            $stmt_d = $conn->prepare("INSERT INTO donations (donor_name, amount, date, source) VALUES (?, ?, ?, ?)");
            $donor_desc = $name . " (Checkout)";
            $current_date = date('Y-m-d');
            $source = "Checkout";
            
            if ($stmt_d) {
                $stmt_d->bind_param("sdss", $donor_desc, $donationAmount, $current_date, $source);
                $stmt_d->execute();
                $stmt_d->close();
            }

            // Sync Main Stats
            $conn->query("UPDATE stats SET donation = (SELECT SUM(amount) FROM donations) WHERE stats_id = 1");
        }

        echo json_encode(["success" => true, "message" => "Booking successful (Cash Mode)!"]);
    }

} else {
    sendError("Database Insert Failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>