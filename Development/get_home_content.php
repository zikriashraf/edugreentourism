<?php
// get_home_content.php
header('Content-Type: application/json');

// 1. Connect to Database
$conn = mysqli_connect("localhost", "root", "", "edugreentourism"); 

if (!$conn) { 
    echo json_encode(["error" => "Database connection failed"]); 
    exit; 
}

$response = [
    'stats' => [],
    'activities' => [],
    'destinations' => [],
    'feedback' => []
];

// 2. Fetch Visible Stats
// (Make sure your analytics_stats table has 'is_visible' column)
$stats_sql = "SELECT * FROM analytics_stats WHERE is_visible = 1";
if ($result = $conn->query($stats_sql)) {
    while($row = $result->fetch_assoc()) {
        $response['stats'][] = $row;
    }
}

// 3. Fetch Top 3 Visible Activities
// (Make sure analytics_lists table has 'status' and 'order_num')
$act_sql = "SELECT item_name FROM analytics_lists WHERE category='activity' AND status=1 ORDER BY order_num ASC LIMIT 3";
if ($result = $conn->query($act_sql)) {
    while($row = $result->fetch_assoc()) {
        $response['activities'][] = $row['item_name'];
    }
}

// 4. Fetch Top 3 Visible Destinations
$dest_sql = "SELECT item_name FROM analytics_lists WHERE category='destination' AND status=1 ORDER BY order_num ASC LIMIT 3";
if ($result = $conn->query($dest_sql)) {
    while($row = $result->fetch_assoc()) {
        $response['destinations'][] = $row['item_name'];
    }
}

// 5. Fetch Top 3 Visible Feedback
$feed_sql = "SELECT name, rating, message FROM feedback WHERE status=1 ORDER BY rating DESC LIMIT 3";
if ($result = $conn->query($feed_sql)) {
    while($row = $result->fetch_assoc()) {
        $response['feedback'][] = $row;
    }
}

echo json_encode($response);
?>