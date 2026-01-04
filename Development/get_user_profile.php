<?php
// get_user_profile.php
mysqli_report(MYSQLI_REPORT_OFF); 
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "edugreentourism";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB Connection Failed"]);
    exit;
}

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (empty($username)) {
    echo json_encode(["success" => false, "message" => "No username provided"]);
    exit;
}

// 1. Get User
// Added 'created_at' to the fetch list
$stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $userEmail = $userData['email']; 

    // --- NEW STEP: Pre-fetch all Programs to create a lookup map (ID -> Name) ---
    // We need this to translate ["1", "2"] into "Jungle Trekking, Night Walk"
    $programMap = [];
    $progSql = "SELECT program_id, program_name FROM programs";
    $progResult = $conn->query($progSql);
    if ($progResult) {
        while($p = $progResult->fetch_assoc()) {
            $programMap[$p['program_id']] = $p['program_name'];
        }
    }

    // 2. JOIN QUERY (Bookings + Packages)
    $sql = "
        SELECT 
            b.*, 
            p.name AS package_title, 
            p.durationDays 
        FROM bookings b
        LEFT JOIN packages p ON b.package_id = p.id
        WHERE b.customer_email = ? 
        ORDER BY b.booking_date ASC 
    ";

    $stmt2 = $conn->prepare($sql);

    if (!$stmt2) {
        $bookings = [];
        $msg = "Query Failed: " . $conn->error;
    } else {
        $stmt2->bind_param("s", $userEmail);
        $stmt2->execute();
        $bookingsResult = $stmt2->get_result();
        
        $bookings = [];
        while ($row = $bookingsResult->fetch_assoc()) {
            
            // --- NEW LOGIC: Resolve Custom Activities ---
            $row['expanded_names'] = ""; // Default empty
            
            if ($row['booking_type'] === 'custom' && !empty($row['custom_activities'])) {
                // Decode the JSON string (e.g. '["1","2"]')
                $ids = json_decode($row['custom_activities'], true);
                
                if (is_array($ids)) {
                    $names = [];
                    foreach($ids as $id) {
                        // Look up the name in our map
                        if (isset($programMap[$id])) {
                            $names[] = $programMap[$id];
                        }
                    }
                    // Join them with commas (e.g. "Activity A, Activity B")
                    $row['expanded_names'] = implode(", ", $names);
                }
            }
            
            $bookings[] = $row;
        }
        $msg = "Success";
    }

    echo json_encode([
        "success" => true,
        "user" => $userData,
        "bookings" => $bookings,
        "debug_msg" => $msg
    ]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$conn->close();
?>