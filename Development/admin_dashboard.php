<?php
session_start();
include 'db_connect.php';

// --- 1. PHP LOGIC (THE BRAIN) ---

// Security Check
if (!isset($_SESSION['admin_id'])) {
    header('location: login.php');
    exit();
}

// Fetch Dashboard Statistics
$user_query = "SELECT COUNT(*) as total_users FROM users";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
$total_users = $user_data['total_users'];

$booking_query = "SELECT COUNT(*) as total_bookings FROM bookings";
$booking_result = mysqli_query($conn, $booking_query);
$booking_data = mysqli_fetch_assoc($booking_result);
$total_bookings = $booking_data['total_bookings'];

// 1. Calculate Booking Revenue
$booking_rev_query = "SELECT SUM(total_cost) as total_booking FROM bookings"; 
$booking_rev_result = mysqli_query($conn, $booking_rev_query);
$booking_data = mysqli_fetch_assoc($booking_rev_result);
$total_booking_revenue = $booking_data['total_booking'] ?? 0;

// 2. Calculate Donation Revenue
$donation_rev_query = "SELECT SUM(amount) as total_donation FROM donations";
$donation_rev_result = mysqli_query($conn, $donation_rev_query);
$donation_data = mysqli_fetch_assoc($donation_rev_result);
$total_donation_revenue = $donation_data['total_donation'] ?? 0;

$package_query = "SELECT COUNT(*) as total_packages FROM packages";
$package_result = mysqli_query($conn, $package_query);
$package_data = mysqli_fetch_assoc($package_result);
$total_packages = $package_data['total_packages'];

// Program Count
$program_query = "SELECT COUNT(*) as total_programs FROM programs";
$program_result = mysqli_query($conn, $program_query);
$program_data = mysqli_fetch_assoc($program_result);
$total_programs = $program_data['total_programs'];

// Fetch Recent Bookings
$recent_query = "SELECT b.id, b.customer_name, p.name AS package_name, b.booking_date, b.status 
                 FROM bookings b
                 LEFT JOIN packages p ON b.package_id = p.id
                 ORDER BY b.id DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query) or die("Query Failed: " . mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduGreenTourism</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <h2>EduGreenTourism</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_analytical.php"><i class="fas fa-chart-line"></i> Analytical</a></li>
                    <li><a href="manage_pages.php"><i class="fas fa-file-alt"></i> Pages</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="manage_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
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
            <h1>Dashboard Overview</h1>
            <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Super Admin'); ?>!</p>
          </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
               <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p>RM <?php echo number_format($total_booking_revenue, 2); ?></p>
               </div>

               <div class="stat-card" style="border-top-color: #f1c40f;">
                    <h3>Donations</h3>
                    <p style="color: #f1c40f;">RM <?php echo number_format($total_donation_revenue, 2); ?></p>
               </div>
                <div class="stat-card">
                    <h3>Total Packages</h3>
                    <p><?php echo $total_packages; ?></p>
                </div>
                <div class="stat-card">
                  <h3>Total Programs</h3>
                  <p><?php echo $total_programs; ?></p>
                </div>
            </div>

            <section class="recent-bookings">
                <h2>Recent Bookings</h2>
                <table>
                    <thead>
    <tr>
        <th>No.</th>  <th>Customer</th>
        <th>Package</th>
        <th>Date</th>
        <th>Status</th>
    </tr>
</thead>
                    <tbody>
    <?php 
    if (mysqli_num_rows($recent_result) > 0) {
        $counter = 1; // <--- 1. Initialize the counter here
        while ($row = mysqli_fetch_assoc($recent_result)) { 
    ?>
        <tr>
            <td><?php echo $counter++; ?></td> 
            
            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
            <td><?php echo htmlspecialchars($row['package_name'] ?? 'Custom'); ?></td>
            <td><?php echo $row['booking_date']; ?></td>
            <td>
                <span class="status <?php echo strtolower($row['status']); ?>">
                    <?php echo $row['status']; ?>
                </span>
            </td>
        </tr>
    <?php 
        } 
    } else {
        echo "<tr><td colspan='5'>No bookings found</td></tr>";
    }
    ?>
</tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="admin_script.js"></script>
</body>
</html>