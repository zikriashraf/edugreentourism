<?php
header('Content-Type: application/json');
error_reporting(0); // Disable error reporting for cleaner JSON output

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) { 
    echo json_encode([]); 
    exit(); 
}

// 1. Sanitize Input to prevent SQL errors
$arrivalDate = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';

// If no date provided, return empty
if (!$arrivalDate) {
    echo json_encode([]);
    exit();
}

$availablePackages = [];

// 2. Get all packages
$sql = "SELECT * FROM packages";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($pkg = $result->fetch_assoc()) {
        $pkgId = intval($pkg['id']);
        $maxSlots = intval($pkg['maxSlots']); 
        
        // --- CHECK 1: DATE RESTRICTIONS (JSON) ---
        $datesJson = $pkg['availableDates'];
        $isDateRestricted = false;

        // Only check dates if the JSON is valid AND contains items
        if (!empty($datesJson) && $datesJson !== 'null') {
            $allowedDates = json_decode($datesJson, true);
            
            // If it's an array and has at least one date, we enforce the check.
            // If it is an empty array [], we assume the package runs every day.
            if (is_array($allowedDates) && count($allowedDates) > 0) {
                if (!in_array($arrivalDate, $allowedDates)) {
                    continue; // Date not found in allowed list -> Skip this package
                }
            }
        }

        // --- CHECK 2: SLOT AVAILABILITY (bookings table) ---
        // We sum adults + children. 
        // COALESCE(..., 0) ensures we get '0' instead of NULL if no bookings exist.
        $bookingSql = "SELECT COALESCE(SUM(pax_adults + pax_children), 0) as total_booked 
                       FROM bookings 
                       WHERE package_id = $pkgId 
                       AND booking_date = '$arrivalDate' 
                       AND status != 'Cancelled'";
        
        $bookingRes = $conn->query($bookingSql);
        $currentPax = 0;

        if ($bookingRes && $row = $bookingRes->fetch_assoc()) {
            $currentPax = intval($row['total_booked']);
        }

        $remainingSlots = $maxSlots - $currentPax;

        // IF THERE ARE SLOTS LEFT, ADD TO LIST
        if ($remainingSlots > 0) {
            
            // Fix Image URL (Consistent with get_packages.php)
            $imgRaw = '';
            if (isset($pkg['imageUrl'])) $imgRaw = trim($pkg['imageUrl']);
            elseif (isset($pkg['image_url'])) $imgRaw = trim($pkg['image_url']);
            
            // Handle relative paths
            $img = $imgRaw;
            if ($img !== '' && !preg_match('/^https?:\/\//i', $img)) {
                $img = preg_replace('/^\//', '', $img);
                if (strpos($img, '/') === false) {
                    $img = 'img/' . $img;
                }
            }

            $pkgData = [
                'id' => $pkg['id'],
                'name' => $pkg['name'],
                'type' => $pkg['type'],
                'pricePerPax' => (float)$pkg['pricePerPax'],
                'durationDays' => (int)$pkg['durationDays'],
                'description' => $pkg['description'],
                'imageUrl' => $img,
                'slots_left' => $remainingSlots // Useful for debugging or UI
            ];
            $availablePackages[] = $pkgData;
        }
    }
}

echo json_encode($availablePackages);
$conn->close();
?>