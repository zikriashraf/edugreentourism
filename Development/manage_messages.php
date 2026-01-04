<?php

// 1. DATABASE CONNECTION
$servername = "localhost";
$username_db = "root"; 
$password_db = "";     
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message_alert = "";

// 2. DELETE MESSAGE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Sanitize ID
    if ($conn->query("DELETE FROM contact_messages WHERE id=$id")) {
        // Redirect to clear URL parameter
        header("location: manage_messages.php?msg=deleted");
        exit();
    } else {
        $message_alert = "Error deleting message: " . $conn->error;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message_alert = "Message deleted successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages | EduGreenTourism</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="admin_script.js" defer></script>

    <style>
        /* Specific styles for message table to handle long text */
        .msg-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #555;
        }
        /* Tooltip or expansion on hover (optional simple effect) */
        .msg-row:hover .msg-content {
            white-space: normal;
            word-wrap: break-word;
        }
        .date-col {
            font-size: 0.85em;
            color: #777;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        
        <aside class="sidebar">
            <div class="logo">
                <h2>EduGreenTourism</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_analytical.php"><i class="fas fa-chart-line"></i> Analytical</a></li>
                    <li><a href="manage_pages.php"><i class="fas fa-file-alt"></i> Pages</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="manage_messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="manage_donations.php"><i class="fas fa-hand-holding-heart"></i> Donations</a></li>
                    <li><a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                    <li><a href="manage_packages.php"><i class="fas fa-box"></i> Packages</a></li>
                    <li><a href="manage_program.php"><i class="fas fa-clipboard-list"></i> Programs</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header>
                <h1>Incoming Messages</h1>
            </header>

            <?php if ($message_alert): ?>
                <div class="alert">
                    <i class="fas fa-info-circle"></i> <?php echo $message_alert; ?>
                </div>
            <?php endif; ?>

            <div class="admin-card">
                <h2>Inbox</h2>
                <table>
                    <thead>
    <tr>
        <th width="5%">No.</th> <th width="15%">Date</th>
        <th width="15%">Sender</th>
        <th width="20%">Subject</th>
        <th>Message</th>
        <th width="10%">Action</th>
    </tr>
</thead>
                    <tbody>
    <?php
    // Fetch messages ordered by newest first (Already correct in your code)
    $sql = "SELECT * FROM contact_messages ORDER BY submitted_at DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $counter = 1; // <--- 1. Initialize counter here
        while($row = $result->fetch_assoc()) {
            // Format date nicely
            $dateObj = new DateTime($row['submitted_at']);
            $dateFormatted = $dateObj->format('d M Y, h:i A');

            // Encode row data for JavaScript
            $jsonData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

            echo "<tr class='msg-row'>";
            // 2. Use Counter instead of ID
            echo "<td>" . $counter++ . "</td>"; 
            
            echo "<td class='date-col'>" . $dateFormatted . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong><br><small style='color:#888'>" . htmlspecialchars($row['email']) . "</small></td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            
            // Limit text length for table view
            $shortMsg = mb_strimwidth(htmlspecialchars($row['message']), 0, 50, "...");
            echo "<td><div class='msg-content'>" . $shortMsg . "</div></td>";
            
            echo "<td>
                    <button type='button' class='action-btn btn-view' onclick='viewMessage($jsonData)' title='View Full Message'>
                        <i class='fas fa-eye'></i>
                    </button>

                    <a href='manage_messages.php?delete=" . $row['id'] . "' class='action-btn btn-delete' onclick=\"return confirm('Delete this message?');\" title='Delete'>
                        <i class='fas fa-trash'></i>
                    </a>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color: #999;'>No new messages found.</td></tr>";
    }
    ?>
</tbody>
</table> <div id="msgModal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalSubject">Subject Here</h3>
            <span class="close-modal" onclick="closeMsgModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="sender-info">
                <strong>From:</strong> <span id="modalName"></span> (<span id="modalEmail"></span>)<br>
                <strong>Date:</strong> <span id="modalDate"></span>
            </div>
            <hr>
            <div class="full-message" id="modalContent">
                </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeMsgModal()" class="btn-action btn-cancel-edit">Close</button>
        </div>
    </div>
</div>
                </table>
            </div>

        </main>
    </div>

</body>
</html>