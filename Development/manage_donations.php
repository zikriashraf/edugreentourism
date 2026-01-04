<?php
// manage_donations.php

// 1. DATABASE CONNECTION
$servername = "localhost";
$username_db = "root"; 
$password_db = "";     
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- AUTO-SYNC FUNCTION ---
// This calculates the total from 'donations' and updates the 'stats' table
function syncDonationStats($conn) {
    // 1. Get total from donations table
    $sql = "SELECT SUM(amount) as total FROM donations";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $new_total = $row['total'] ?? 0.00;

    // 2. Update the 'donation' column in 'stats' table (stats_id = 1)
    $conn->query("UPDATE stats SET donation = $new_total WHERE stats_id = 1");
}

// Initialize variables
$update_mode = false;
$id = 0;
$donor_name = "";
$amount = "";
$date = date('Y-m-d');
$message = "";

// 2. DELETE DONATION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM donations WHERE id=$id");
    syncDonationStats($conn); // Sync after delete
    header("location: manage_donations.php?msg=deleted");
    exit();
}

// 3. EDIT DONATION (Load Data)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $update_mode = true;
    $result = $conn->query("SELECT * FROM donations WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $donor_name = $row['donor_name'];
        $amount = $row['amount'];
        $date = $row['date'];
    }
}

// 4. SAVE DONATION (Create or Update)
if (isset($_POST['save'])) {
    $name = $_POST['donor_name'];
    $amt = $_POST['amount'];
    $dt = $_POST['date'];
    $src = "Manual"; // Default for admin entry

    if (isset($_POST['id']) && $_POST['id'] != 0) {
        // --- UPDATE ---
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE donations SET donor_name=?, amount=?, date=? WHERE id=?");
        $stmt->bind_param("sdsi", $name, $amt, $dt, $id);
        if ($stmt->execute()) {
            syncDonationStats($conn); // Sync after update
            $message = "Donation updated & Stats synced!";
            $update_mode = false; $donor_name = ""; $amount = ""; $id = 0;
        }
    } else {
        // --- CREATE ---
        $stmt = $conn->prepare("INSERT INTO donations (donor_name, amount, date, source) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $amt, $dt, $src);
        if ($stmt->execute()) {
            syncDonationStats($conn); // Sync after insert
            $message = "Donation added & Stats synced!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Donations | EduGreenTourism</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo"><h2>EduGreenTourism</h2></div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_analytical.php"><i class="fas fa-chart-line"></i> Analytical</a></li>
                    <li><a href="manage_pages.php"><i class="fas fa-file-alt"></i> Pages</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="manage_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="manage_donations.php" class="active"><i class="fas fa-hand-holding-heart"></i> Donations</a></li>
                    <li><a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header><h1>Manage Donations</h1></header>

            <?php if ($message): ?>
                <div class="alert"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <div class="stats-grid" style="margin-bottom: 20px;">
                <?php 
                // Fetch current synced total directly from donations table for display
                $total_query = $conn->query("SELECT SUM(amount) as total FROM donations");
                $total_res = $total_query->fetch_assoc();
                $display_total = $total_res['total'] ?? 0;
                ?>
                <div class="stat-card" style="border-top-color: #f1c40f;">
                    <h3>Total Donations Collected</h3>
                    <p style="color: #f1c40f;">RM <?php echo number_format($display_total, 2); ?></p>
                    <small>Auto-synced to Homepage Stats</small>
                </div>
            </div>

            <div class="admin-row">
                <div class="col-list">
                    <div class="admin-card">
                        <h2>Donation History</h2>
                        <table>
                            <thead>
    <tr>
        <th width="50">No.</th> <th>Date</th>
        <th>Donor</th>
        <th>Amount</th>
        <th>Source</th>
        <th>Action</th>
    </tr>
</thead>
                            <tbody>
    <?php
    // Sorted by Date (Newest) -> Then ID (Newest added)
    $sql = "SELECT * FROM donations ORDER BY date DESC, id DESC";
    $res = $conn->query($sql);
    
    if ($res->num_rows > 0) {
        $counter = 1; // <--- 1. Start Counter
        while ($row = $res->fetch_assoc()) {
            echo "<tr>";
            
            // 2. Display Counter
            echo "<td>" . $counter++ . "</td>"; 
            
            echo "<td>" . $row['date'] . "</td>";
            echo "<td>" . htmlspecialchars($row['donor_name']) . "</td>";
            echo "<td><strong>RM " . number_format($row['amount'], 2) . "</strong></td>";
            echo "<td><span class='badge " . ($row['source'] == 'Manual' ? 'hidden' : 'visible') . "'>" . $row['source'] . "</span></td>";
            echo "<td>
                <a href='manage_donations.php?edit=" . $row['id'] . "' class='action-btn btn-edit'><i class='fas fa-pen'></i></a>
                <a href='manage_donations.php?delete=" . $row['id'] . "' class='action-btn btn-delete' onclick=\"return confirm('Delete?')\"><i class='fas fa-trash'></i></a>
            </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center;'>No donations found.</td></tr>";
    }
    ?>
</tbody>
                        </table>
                    </div>
                </div>

                <div class="col-form">
                    <div class="admin-card">
                        <h2><?php echo $update_mode ? "Edit Donation" : "Add Manual Donation"; ?></h2>
                        <form action="manage_donations.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <div class="form-group">
                                <label>Donor Name / Reference</label>
                                <input type="text" name="donor_name" value="<?php echo htmlspecialchars($donor_name); ?>" required placeholder="e.g. Cash Box">
                            </div>
                            <div class="form-group">
                                <label>Amount (RM)</label>
                                <input type="number" step="0.01" name="amount" value="<?php echo $amount; ?>" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Date Received</label>
                                <input type="date" name="date" value="<?php echo $date; ?>" required>
                            </div>

                            <button type="submit" name="save" class="btn-submit" style="background-color: #f1c40f; color: #333;">
                                <i class="fas fa-save"></i> <?php echo $update_mode ? "Update" : "Add Donation"; ?>
                            </button>
                            <?php if ($update_mode): ?>
                                <a href="manage_donations.php" class="cancel-link">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>