<?php

// 1. DATABASE CONNECTION
$servername = "localhost";
$username_db = "root"; 
$password_db = "";     
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- IMAGE PATH HELPER FUNCTION ---
function get_image_path($rawPath) {
    if (empty($rawPath) || $rawPath == 'null') {
        return 'https://placehold.co/80x80?text=No+Img';
    }
    if (strpos($rawPath, 'http') === 0) return $rawPath;
    if (file_exists('uploads/' . basename($rawPath))) {
        return 'uploads/' . basename($rawPath);
    }
    if (file_exists('img/' . basename($rawPath))) {
        return 'img/' . basename($rawPath);
    }
    return 'img/' . basename($rawPath);
}

// --- FETCH ALL PROGRAMS ---
$programs_list = [];
$prog_sql = $conn->query("SELECT program_id, program_name FROM programs ORDER BY program_name ASC");
if ($prog_sql) {
    while ($p = $prog_sql->fetch_assoc()) {
        $programs_list[] = $p;
    }
}

// Initialize Variables
$update_mode = false;
$id = 0;
$name = ""; $pricePerPax = ""; $type = "All-Inclusive"; $durationDays = ""; 
$description = ""; $maxSlots = 30; $startTime = ""; $endTime = "";
$current_image = ""; 
$selected_activities = []; 
$available_dates = [];     

// 2. DELETE PACKAGE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM packages WHERE id=$id");
    header("location: manage_packages.php");
    exit();
}

// 3. EDIT PACKAGE
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update_mode = true;
    $result = $conn->query("SELECT * FROM packages WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $pricePerPax = $row['pricePerPax'];
        $type = $row['type'];
        $durationDays = $row['durationDays'];
        $description = $row['description'];
        $maxSlots = $row['maxSlots'];
        $startTime = $row['startTime'];
        $endTime = $row['endTime'];
        $current_image = $row['imageUrl'];
        $selected_activities = json_decode($row['activitiesIds'], true) ?? [];
        $available_dates = json_decode($row['availableDates'], true) ?? [];
    }
}

// 4. SAVE PACKAGE
if (isset($_POST['save'])) {
    $name_input = $_POST['name'];
    $price_input = $_POST['pricePerPax'];
    $type_input = $_POST['type'];
    $duration_input = $_POST['durationDays'];
    $desc_input = $_POST['description'];
    $slots_input = $_POST['maxSlots'];
    $start_input = $_POST['startTime'];
    $end_input = $_POST['endTime'];

    $activities_input = isset($_POST['activities']) ? json_encode($_POST['activities']) : '[]';
    
    // Sort Dates & Remove Duplicates
    $dates_raw = isset($_POST['dates']) ? $_POST['dates'] : [];
    $dates_clean = array_unique(array_filter($dates_raw)); 
    sort($dates_clean); 
    $dates_input = json_encode(array_values($dates_clean));

    // Handle Image
    $image_path = $_POST['current_image']; 
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    }

    if (isset($_POST['id']) && $_POST['id'] != 0) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE packages SET name=?, pricePerPax=?, type=?, durationDays=?, description=?, activitiesIds=?, maxSlots=?, availableDates=?, startTime=?, endTime=?, imageUrl=? WHERE id=?");
        $stmt->bind_param("sdsississssi", $name_input, $price_input, $type_input, $duration_input, $desc_input, $activities_input, $slots_input, $dates_input, $start_input, $end_input, $image_path, $id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Package Updated Successfully'); window.location='manage_packages.php';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO packages (name, pricePerPax, type, durationDays, description, activitiesIds, maxSlots, availableDates, startTime, endTime, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsississss", $name_input, $price_input, $type_input, $duration_input, $desc_input, $activities_input, $slots_input, $dates_input, $start_input, $end_input, $image_path);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('New Package Created'); window.location='manage_packages.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages | EduGreenTourism</title>
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
                    <li><a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                    <li><a href="manage_packages.php" class="active"><i class="fas fa-box"></i> Packages</a></li>
                    <li><a href="manage_program.php"><i class="fas fa-clipboard-list"></i> Programs</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header><h1>Package Management</h1></header>

            <div class="admin-row">
                
                <div class="col-list">
                    <div class="admin-card">
                        <h2>Existing Packages</h2>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Info</th>
                                        <th>Stats</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM packages ORDER BY id DESC";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $imgSrc = get_image_path($row['imageUrl']);
                                            echo "<tr>";
                                            echo "<td>" . $row['id'] . "</td>";
                                            echo "<td><img src='$imgSrc' class='table-img-preview'></td>";
                                            echo "<td>
                                                    <strong>" . htmlspecialchars($row['name']) . "</strong><br>
                                                    <small class='text-muted'>" . $row['type'] . " | RM" . $row['pricePerPax'] . "</small>
                                                  </td>";
                                            echo "<td>
                                                    <small><i class='far fa-clock'></i> " . $row['durationDays'] . " Days</small><br>
                                                    <small><i class='fas fa-user-friends'></i> Max " . $row['maxSlots'] . "</small>
                                                  </td>";
                                            echo "<td>
                                                    <a href='manage_packages.php?edit=" . $row['id'] . "' class='action-btn btn-edit'><i class='fas fa-edit'></i></a>
                                                    <a href='manage_packages.php?delete=" . $row['id'] . "' class='action-btn btn-delete' onclick='return confirm(\"Are you sure?\");'><i class='fas fa-trash'></i></a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' align='center'>No packages found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-form" id="form-section">
                    <div class="admin-card <?php echo $update_mode ? 'highlight-border' : ''; ?>">
                        <h2><?php echo $update_mode ? 'Edit Package' : 'Add New Package'; ?></h2>
                        
                        <form action="manage_packages.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">

                            <div class="form-group">
                                <label>Package Name</label>
                                <input type="text" name="name" value="<?php echo $name; ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group half">
                                    <label>Price (RM)</label>
                                    <input type="number" step="0.01" name="pricePerPax" value="<?php echo $pricePerPax; ?>" required>
                                </div>
                                <div class="form-group half">
                                    <label>Type</label>
                                    <select name="type">
                                        <option value="All-Inclusive" <?php if($type=='All-Inclusive') echo 'selected'; ?>>All-Inclusive</option>
                                        <option value="Customizable" <?php if($type=='Customizable') echo 'selected'; ?>>Customizable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group half">
                                    <label>Duration (Days)</label>
                                    <input type="number" name="durationDays" value="<?php echo $durationDays; ?>">
                                </div>
                                <div class="form-group half">
                                    <label>Max Slots</label>
                                    <input type="number" name="maxSlots" value="<?php echo $maxSlots; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Image</label>
                                <?php if($current_image): ?>
                                    <div style="margin-bottom:5px;">
                                        <img src="<?php echo get_image_path($current_image); ?>" style="height:50px; border-radius:4px; border:1px solid #ddd;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" accept="image/*">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="3"><?php echo $description; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Included Programs/Activities</label>
                                <div class="checkbox-container">
                                    <?php foreach ($programs_list as $prog): ?>
                                        <?php $isChecked = in_array($prog['program_id'], $selected_activities) ? 'checked' : ''; ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="activities[]" value="<?php echo $prog['program_id']; ?>" <?php echo $isChecked; ?>>
                                            <?php echo htmlspecialchars($prog['program_name']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Available Dates</label>
                                
                                <div class="bulk-date-tool">
                                    <small style="display:block; margin-bottom:5px; color:#555;"><strong>Option 1:</strong> Bulk Generate Dates</small>
                                    <div style="display:flex; gap:5px; align-items:center;">
                                        <input type="date" id="bulk_start" placeholder="Start" style="padding:5px;">
                                        <span>to</span>
                                        <input type="date" id="bulk_end" placeholder="End" style="padding:5px;">
                                        <button type="button" class="btn-bulk-add" onclick="generateDateRange()">Generate</button>
                                    </div>
                                </div>

                                <small style="display:block; margin:10px 0 5px; color:#555;"><strong>Option 2:</strong> Individual Dates</small>
                                <div id="dates-wrapper">
                                    <?php 
                                    if (!empty($available_dates)) {
                                        foreach($available_dates as $date) {
                                            echo '<div class="date-row"><input type="date" name="dates[]" value="'.$date.'"><button type="button" class="btn-remove" onclick="removeDate(this)"><i class="fas fa-times"></i></button></div>';
                                        }
                                    } else {
                                        if (!$update_mode) echo '<div class="date-row"><input type="date" name="dates[]"><button type="button" class="btn-remove" onclick="removeDate(this)"><i class="fas fa-times"></i></button></div>';
                                    }
                                    ?>
                                </div>
                                <button type="button" class="btn-add-single" onclick="addDateInput()">+ Add Single Date</button>
                            </div>

                            <div class="form-row">
                                <div class="form-group half">
                                    <label>Start Time</label>
                                    <input type="time" name="startTime" value="<?php echo $startTime; ?>">
                                </div>
                                <div class="form-group half">
                                    <label>End Time</label>
                                    <input type="time" name="endTime" value="<?php echo $endTime; ?>">
                                </div>
                            </div>

                            <button type="submit" name="save" class="btn-submit">
                                <i class="fas fa-save"></i> <?php echo $update_mode ? 'Update Package' : 'Create Package'; ?>
                            </button>
                            <?php if ($update_mode): ?>
                                <a href="manage_packages.php" class="cancel-link">Cancel Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

            </div> 
        </main>
    </div>

</body>
</html>