<?php

// 2. DATABASE CONNECTION
$servername = "localhost";
$username_db = "root"; 
$password_db = "";     
$dbname = "edugreentourism";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- IMAGE PATH HELPER ---
function get_image_path($rawPath) {
    if (empty($rawPath) || $rawPath == 'null') {
        return 'https://placehold.co/80x80?text=No+Img';
    }
    if (strpos($rawPath, 'http') === 0) return $rawPath;
    if (file_exists('uploads/' . basename($rawPath))) return 'uploads/' . basename($rawPath);
    if (file_exists('img/' . basename($rawPath))) return 'img/' . basename($rawPath);
    return 'img/' . basename($rawPath);
}

// Initialize Variables
$update_mode = false;
$id = 0;
$name = ""; $price = ""; $startTime = ""; $endTime = ""; 
$duration = ""; $description = ""; $current_image = "";

// 3. DELETE PROGRAM
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM programs WHERE program_id=$id");
    header("location: manage_program.php");
    exit();
}

// 4. EDIT PROGRAM
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update_mode = true;
    $result = $conn->query("SELECT * FROM programs WHERE program_id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['program_name'];
        $price = $row['price'];
        $startTime = $row['start_time'];
        $endTime = $row['end_time'];
        $duration = $row['duration_hours'];
        $description = $row['description'];
        $current_image = $row['imageUrl'];
    }
}

// 5. SAVE PROGRAM
if (isset($_POST['save'])) {
    $name_input = $_POST['program_name'];
    $price_input = $_POST['price'];
    $start_input = $_POST['start_time'];
    $end_input = $_POST['end_time'];
    $duration_input = $_POST['duration_hours'];
    $desc_input = $_POST['description'];

    // Handle Image
    $image_path = $_POST['current_image']; 
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    }

    if (isset($_POST['program_id']) && $_POST['program_id'] != 0) {
        // UPDATE
        $id = $_POST['program_id'];
        $stmt = $conn->prepare("UPDATE programs SET program_name=?, price=?, start_time=?, end_time=?, duration_hours=?, description=?, imageUrl=? WHERE program_id=?");
        $stmt->bind_param("sdssissi", $name_input, $price_input, $start_input, $end_input, $duration_input, $desc_input, $image_path, $id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Program Updated Successfully'); window.location='manage_program.php';</script>";
    } else {
        // CREATE
        $stmt = $conn->prepare("INSERT INTO programs (program_name, price, start_time, end_time, duration_hours, description, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssiss", $name_input, $price_input, $start_input, $end_input, $duration_input, $desc_input, $image_path);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('New Program Added'); window.location='manage_program.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs | EduGreenTourism</title>
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
                    <li><a href="manage_packages.php"><i class="fas fa-box"></i> Packages</a></li>
                    <li><a href="manage_program.php" class="active"><i class="fas fa-clipboard-list"></i> Programs</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header><h1>Program Management</h1></header>

            <div class="admin-row">
                
                <div class="col-list">
                    <div class="admin-card">
                        <h2>Existing Programs</h2>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Program Info</th>
                                        <th>Timing</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM programs ORDER BY program_id DESC";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $imgSrc = get_image_path($row['imageUrl']);
                                            $sTime = date("g:i A", strtotime($row['start_time']));
                                            $eTime = date("g:i A", strtotime($row['end_time']));

                                            echo "<tr>";
                                            echo "<td>" . $row['program_id'] . "</td>";
                                            echo "<td><img src='$imgSrc' class='table-img-preview'></td>";
                                            echo "<td>
                                                    <strong>" . htmlspecialchars($row['program_name']) . "</strong><br>
                                                    <span style='color:#27ae60; font-weight:bold;'>RM " . $row['price'] . "</span>
                                                  </td>";
                                            echo "<td>
                                                    <small><i class='far fa-clock'></i> $sTime - $eTime</small><br>
                                                    <small class='text-muted'>(" . $row['duration_hours'] . " Hours)</small>
                                                  </td>";
                                            echo "<td>
                                                    <a href='manage_program.php?edit=" . $row['program_id'] . "' class='action-btn btn-edit'><i class='fas fa-edit'></i></a>
                                                    <a href='manage_program.php?delete=" . $row['program_id'] . "' class='action-btn btn-delete' onclick='return confirm(\"Are you sure?\");'><i class='fas fa-trash'></i></a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' align='center'>No programs found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-form" id="form-section">
                    <div class="admin-card <?php echo $update_mode ? 'highlight-border' : ''; ?>">
                        <h2><?php echo $update_mode ? 'Edit Program' : 'Add New Program'; ?></h2>
                        
                        <form action="manage_program.php" method="POST" enctype="multipart/form-data" id="programForm">
                            <input type="hidden" name="program_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">

                            <div class="form-group">
                                <label>Program Name</label>
                                <input type="text" name="program_name" id="p_name" value="<?php echo $name; ?>">
                            </div>

                            <div class="form-group">
                                <label>Price (RM)</label>
                                <input type="number" step="0.01" name="price" id="p_price" value="<?php echo $price; ?>">
                            </div>

                            <div class="form-row">
                                <div class="form-group half">
                                    <label>Start Time</label>
                                    <input type="time" name="start_time" id="prog_start" value="<?php echo $startTime; ?>">
                                </div>
                                <div class="form-group half">
                                    <label>End Time</label>
                                    <input type="time" name="end_time" id="prog_end" value="<?php echo $endTime; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                 <label>Duration (Hours)</label>
                                 <input type="number" name="duration_hours" id="prog_duration" value="<?php echo $duration; ?>" step="0.5">
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
                                <textarea name="description" id="p_desc" rows="3"><?php echo $description; ?></textarea>
                            </div>

                            <button type="submit" name="save" class="btn-submit">
                                <i class="fas fa-save"></i> <?php echo $update_mode ? 'Update Program' : 'Add Program'; ?>
                            </button>
                            <?php if ($update_mode): ?>
                                <a href="manage_program.php" class="cancel-link">Cancel Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

            </div> 
        </main>
    </div>

</body>
</html>