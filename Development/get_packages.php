<?php
header('Content-Type: application/json');
error_reporting(0);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { echo json_encode([]); exit(); }

// Choose table
$sql = "SELECT * FROM packages";
$result = $conn->query($sql);
$packages = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Try multiple column names for image field
        $imgRaw = '';
        if (isset($row['imageUrl'])) $imgRaw = trim($row['imageUrl']);
        elseif (isset($row['image_url'])) $imgRaw = trim($row['image_url']);
        elseif (isset($row['image'])) $imgRaw = trim($row['image']);

        // If it's empty, leave blank so client shows placeholder
        $img = $imgRaw;

        // If not a full URL and not containing a folder, prefix with 'img/'
        if ($img !== '' && !preg_match('/^https?:\\/\\//i', $img)) {
            // remove leading slash
            $img = preg_replace('/^\\//', '', $img);
            // if there is no slash at all, assume it's a filename and prefix with img/
            if (strpos($img, '/') === false) {
                $img = 'img/' . $img;
            }
            // else, keep as-is (it already has a folder like uploads/ or images/)
        }

        $packages[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'type' => $row['type'],
            'pricePerPax' => isset($row['pricePerPax']) ? (float)$row['pricePerPax'] : (isset($row['price_per_pax']) ? (float)$row['price_per_pax'] : 0),
            'durationDays' => isset($row['durationDays']) ? (int)$row['durationDays'] : (isset($row['duration_days']) ? (int)$row['duration_days'] : 1),
            'description' => $row['description'] ?? '',
            'imageUrl' => $img,
            'activitiesIds' => $row['activitiesIds'] ?? ($row['activities_ids'] ?? null),
            'availableDates' => $row['availableDates'] ?? ($row['available_dates'] ?? null)
        ];
    }
}

echo json_encode($packages);
$conn->close();
?>
