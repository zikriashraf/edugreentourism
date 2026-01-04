<?php
// 1. DATABASE CONNECTION
$servername = "localhost";
$username_db = "root"; 
$password_db = "";     
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- PRE-FETCH DATA FOR NAMES ---
$packages_map = [];
$activities_map = [];

// 1. FETCH PACKAGES (Using 'id', 'name', 'pricePerPax')
$pkg_sql = $conn->query("SELECT id, name, pricePerPax FROM packages");
if ($pkg_sql) {
    while ($p = $pkg_sql->fetch_assoc()) {
        $packages_map[$p['id']] = $p; 
    }
}

// 2. FETCH PROGRAMS (Fixed: Using 'program_id', 'program_name', 'price')
$act_sql = $conn->query("SELECT program_id, program_name, price FROM programs"); 
if ($act_sql) {
    while ($a = $act_sql->fetch_assoc()) {
        // Map using program_id as the key
        $activities_map[$a['program_id']] = $a;
    }
}

// Initialize variables
$update_mode = false;
$id = 0;
$customer_name = ""; $customer_phone = ""; $booking_date = ""; 
$status = "Pending"; $total_cost = 0;
$pax_adults = 1; $pax_children = 0; $payment_method = "";
$booked_item_name = ""; 
$current_price_per_pax = 0; // For JS Calculation

// 2. DELETE BOOKING
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM bookings WHERE id=$id");
    header("location: manage_bookings.php");
    exit();
}

// 3. EDIT BOOKING
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update_mode = true;
    $result = $conn->query("SELECT * FROM bookings WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $customer_name = $row['customer_name'];
        $customer_phone = $row['customer_phone'];
        $booking_date = $row['booking_date'];
        $status = $row['status'];
        $total_cost = $row['total_cost'];
        $pax_adults = $row['pax_adults'];
        $pax_children = $row['pax_children'];
        $payment_method = $row['payment_method'];
        
        // --- DETERMINE BOOKING DETAILS & PRICE ---
        if ($row['booking_type'] == 'standard' && isset($packages_map[$row['package_id']])) {
            // Standard Package
            $pkg = $packages_map[$row['package_id']];
            $booked_item_name = "Package: " . $pkg['name'];
            $current_price_per_pax = $pkg['pricePerPax']; 
            
        } elseif ($row['booking_type'] == 'custom') {
            // Custom: Calculate sum of all selected programs
            $booked_item_name = "Custom Activity Bundle";
            $act_ids = json_decode($row['custom_activities'], true);
            $custom_total_price = 0;
            
            if (is_array($act_ids)) {
                $names = [];
                foreach($act_ids as $aid) {
                    if (isset($activities_map[$aid])) {
                        $names[] = $activities_map[$aid]['program_name'];
                        $custom_total_price += $activities_map[$aid]['price'];
                    }
                }
                if(!empty($names)) {
                    $booked_item_name .= " (" . implode(", ", $names) . ")";
                }
            }
            $current_price_per_pax = $custom_total_price;
        }
    }
}

// 4. UPDATE BOOKING
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $booking_date = $_POST['booking_date'];
    $total_cost = $_POST['total_cost'];
    $pax_adults = $_POST['pax_adults'];
    $pax_children = $_POST['pax_children'];
    $payment_method = $_POST['payment_method'];
    
    $sql = "UPDATE bookings SET 
            status='$status', 
            booking_date='$booking_date', 
            total_cost='$total_cost',
            pax_adults='$pax_adults',
            pax_children='$pax_children',
            payment_method='$payment_method'
            WHERE id=$id";

    if ($conn->query($sql)) {
        echo "<script>alert('Booking Updated Successfully'); window.location='manage_bookings.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | EduGreenTourism</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="admin_script.js" defer></script>
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
                    <li><a href="manage_donations.php"><i class="fas fa-hand-holding-heart"></i> Donations</a></li>
                    <li><a href="manage_bookings.php" class="active"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                    <li><a href="manage_packages.php"><i class="fas fa-box"></i> Packages</a></li>
                    <li><a href="manage_program.php"><i class="fas fa-clipboard-list"></i> Programs</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header><h1>Booking Management</h1></header>

            <div class="admin-row">
                
                <div class="col-list">
                    <div class="admin-card">
                        <h2>Existing Bookings</h2>
                        <div class="table-responsive">
                            <table>
                                <thead>
    <tr>
        <th>No.</th> <th>Customer Info</th>
        <th>Booking Details</th>
        <th>Date</th>
        <th>Pax</th>
        <th>Total (RM)</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
</thead>
                                <tbody>
    <?php
    $sql = "SELECT * FROM bookings ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $counter = 1; // <--- 1. Initialize counter
        while($row = $result->fetch_assoc()) {
            $bDate = date("d M Y", strtotime($row['booking_date']));
            $statusClass = strtolower($row['status']); 
            
            // --- DETERMINE DETAILS NAME (Existing logic remains unchanged) ---
            $details_html = "";
            if ($row['booking_type'] == 'standard') {
                $pid = $row['package_id'];
                $pName = isset($packages_map[$pid]) ? $packages_map[$pid]['name'] : "Package #$pid";
                $details_html = "<span class='badge-pkg'><i class='fas fa-box'></i> $pName</span>";
            } 
            elseif ($row['booking_type'] == 'custom') {
                $act_ids = json_decode($row['custom_activities'], true);
                $act_names = [];
                if (is_array($act_ids)) {
                    foreach($act_ids as $aid) {
                        $act_names[] = isset($activities_map[$aid]) ? $activities_map[$aid]['program_name'] : "Act #$aid";
                    }
                }
                $details_html = "<span class='badge-custom'><i class='fas fa-tools'></i> Custom</span><br><small>" . implode(", ", $act_names) . "</small>";
            }

            echo "<tr>";
            
            // 2. Use counter here instead of $row['id']
            echo "<td>" . $counter++ . "</td>"; 
            
            echo "<td>
                    <strong>" . htmlspecialchars($row['customer_name']) . "</strong><br>
                    <small class='text-muted'>" . htmlspecialchars($row['customer_phone']) . "</small>
                  </td>";
            echo "<td>" . $details_html . "</td>";
            echo "<td>" . $bDate . "</td>";
            echo "<td>" . $row['pax_adults'] . "</td>";
            echo "<td>" . $row['total_cost'] . "</td>";
            echo "<td><span class='status-badge $statusClass'>" . $row['status'] . "</span></td>";
            echo "<td>
                    <a href='manage_bookings.php?edit=" . $row['id'] . "' class='action-btn btn-edit'><i class='fas fa-edit'></i></a>
                    <a href='manage_bookings.php?delete=" . $row['id'] . "' class='action-btn btn-delete'><i class='fas fa-trash'></i></a>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8' style='text-align:center;'>No bookings found.</td></tr>";
    }
    ?>
</tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-form" id="form-section">
                    <?php if ($update_mode): ?>
                    <div class="admin-card highlight-border">
                        <h2>Edit Booking #<?php echo $id; ?></h2>
                        <div style="background:#f8f9fa; padding:10px; border-radius:5px; margin-bottom:15px; font-size:0.9em;">
                            <strong>Booked Item:</strong><br> <?php echo $booked_item_name; ?>
                        </div>

                        <form action="manage_bookings.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" id="unit_price" value="<?php echo $current_price_per_pax; ?>">

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Confirmed" <?php if($status=='Confirmed') echo 'selected'; ?>>Confirmed</option>
                                    <option value="Paid" <?php if($status=='Paid') echo 'selected'; ?>>Paid</option>
                                    <option value="Cancelled" <?php if($status=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Visit Date</label>
                                <input type="date" name="booking_date" value="<?php echo $booking_date; ?>" required>
                            </div>

                            <div class="form-group">
    <label>Total Pax</label>
    <input type="number" name="pax_adults" id="pax_adults" class="pax-input" value="<?php echo $pax_adults; ?>" min="1">
    
    <input type="hidden" name="pax_children" id="pax_children" value="0">
</div>

                            <div class="form-group">
                                <label>Total Cost (RM)</label>
                                <input type="number" step="0.01" name="total_cost" id="total_cost" value="<?php echo $total_cost; ?>">
                            </div>

                            <div class="form-group">
                                <label>Payment Method</label>
                                <input type="text" name="payment_method" value="<?php echo $payment_method; ?>">
                            </div>

                            <button type="submit" name="update" class="btn-submit" style="background-color: #f39c12;">
                                <i class="fas fa-save"></i> Update Booking
                            </button>
                            <a href="manage_bookings.php" class="cancel-link">Cancel</a>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="admin-card placeholder-card">
                        <i class="fas fa-hand-pointer"></i>
                        <p>Select a booking to view details.</p>
                    </div>
                    <?php endif; ?>
                </div>

            </div> 
        </main>
    </div>

</body>
</html>