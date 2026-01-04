<?php
include 'db_connect.php'; 

// --- 1. HANDLE FORM SUBMISSIONS ---

// SAVE STAT (Add or Update)
if (isset($_POST['save_stat'])) {
    $id = $_POST['stat_id']; // Hidden ID field
    $key = $_POST['stat_key'];
    $val = $_POST['stat_value'];
    $lbl = $_POST['stat_label'];
    $vis = isset($_POST['is_visible']) ? 1 : 0;

    if (!empty($id)) {
        // Update existing stat
        $stmt = $conn->prepare("UPDATE analytics_stats SET stat_key=?, stat_value=?, stat_label=?, is_visible=? WHERE id=?");
        $stmt->bind_param("sssii", $key, $val, $lbl, $vis, $id);
    } else {
        // Create new stat
        $stmt = $conn->prepare("INSERT INTO analytics_stats (stat_key, stat_value, stat_label, is_visible) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $key, $val, $lbl, $vis);
    }
    $stmt->execute();
    echo "<script>window.location.href='manage_analytical.php';</script>";
}

// DELETE STAT
if (isset($_GET['del_stat'])) {
    $id = $_GET['del_stat'];
    $conn->query("DELETE FROM analytics_stats WHERE id=$id");
    echo "<script>window.location.href='manage_analytical.php';</script>";
}

// --- HANDLE FEEDBACK (Reviews) ---

// SAVE FEEDBACK (Add New OR Update Existing)
if (isset($_POST['save_feedback'])) {
    $id = $_POST['feedback_id']; // Hidden ID to identify if editing
    $name = $conn->real_escape_string($_POST['name']);
    $rating = (int) $_POST['rating'];
    $msg = $conn->real_escape_string($_POST['message']);
    
    // Checkbox: If checked = 1 (Visible), If unchecked = 0 (Hidden)
    $status = isset($_POST['status']) ? 1 : 0; 

    if (!empty($id)) {
        // UPDATE Existing Review
        $sql = "UPDATE feedback SET name='$name', rating='$rating', message='$msg', status='$status' WHERE feedback_id='$id'";
    } else {
        // INSERT New Review
        $sql = "INSERT INTO feedback (name, rating, message, status) VALUES ('$name', '$rating', '$msg', '$status')";
    }

    if ($conn->query($sql)) {
        echo "<script>window.location.href='manage_analytical.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// DELETE FEEDBACK
if (isset($_GET['del_feedback'])) {
    $id = $_GET['del_feedback'];
    $conn->query("DELETE FROM feedback WHERE feedback_id=$id");
    echo "<script>window.location.href='manage_analytical.php';</script>";
}

// --- 3. HANDLE LIST ITEMS (Activities/Destinations) ---

// SAVE ITEM (Add New OR Update Existing)
if (isset($_POST['save_item'])) {
    $id = $_POST['list_id']; // Hidden ID to track if we are editing
    $cat = $_POST['category'];
    $name = $conn->real_escape_string($_POST['item_name']);
    $order = (int) $_POST['order_num']; 
    // Checkbox: Checked = 1 (Visible), Unchecked = 0 (Hidden)
    $status = isset($_POST['status']) ? 1 : 0; 

    if (!empty($id)) {
        // UPDATE Existing Item
        $sql = "UPDATE analytics_lists SET category='$cat', item_name='$name', order_num='$order', status='$status' WHERE id='$id'";
    } else {
        // INSERT New Item
        $sql = "INSERT INTO analytics_lists (category, item_name, order_num, status) VALUES ('$cat', '$name', '$order', '$status')";
    }

    if ($conn->query($sql)) {
        // Success - Refresh page
        echo "<script>window.location.href='manage_analytical.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// DELETE ITEM
if (isset($_GET['del_item'])) {
    $id = $_GET['del_item'];
    $conn->query("DELETE FROM analytics_lists WHERE id=$id");
    echo "<script>window.location.href='manage_analytical.php';</script>";
}

// --- HANDLE VISITOR DATA (Chart) ---

// SAVE VISITOR DATA (Add New OR Update Existing)
if (isset($_POST['save_visitor'])) {
    $id = $_POST['visitor_id']; // Hidden ID
    $month = $_POST['month'];
    $year = (int) $_POST['year'];
    $count = (int) $_POST['visitors_count'];

    if (!empty($id)) {
        // UPDATE Existing Record
        $sql = "UPDATE visitors_monthly SET month='$month', year='$year', visitors_count='$count' WHERE visitors_id='$id'";
    } else {
        // INSERT New Record
        $sql = "INSERT INTO visitors_monthly (month, year, visitors_count) VALUES ('$month', '$year', '$count')";
    }

    if ($conn->query($sql)) {
        echo "<script>window.location.href='manage_analytical.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// DELETE VISITOR DATA
if (isset($_GET['del_visitor'])) {
    $id = $_GET['del_visitor'];
    $conn->query("DELETE FROM visitors_monthly WHERE visitors_id=$id");
    echo "<script>window.location.href='manage_analytical.php';</script>";
}

// --- 2. FETCH DATA ---

// Fetch stats into array
$stats_res = $conn->query("SELECT * FROM analytics_stats");
$stats = [];
while($row = $stats_res->fetch_assoc()) {
    $stats[$row['stat_key']] = $row['stat_value'];
}

$feedback_res = $conn->query("SELECT * FROM feedback ORDER BY feedback_id DESC");
$activities_res = $conn->query("SELECT * FROM analytics_lists WHERE category='activity'");
$destinations_res = $conn->query("SELECT * FROM analytics_lists WHERE category='destination'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Analytics - EduGreenTourism</title>
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
                    <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_analytical.php" class="active"><i class="fas fa-chart-line"></i> Analytical</a></li>
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
                <h1>Manage Analytical Dashboard</h1>
            </header>

<div class="admin-card">
                <h2>Manage Statistics</h2>
                
                <form method="POST" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <input type="hidden" name="stat_id" id="stat_id">
                    
                    <div class="lists-grid">
                        <div class="form-group">
                            <label>Stat Key (for icon):</label>
                            <input type="text" name="stat_key" id="stat_key" placeholder="e.g. co2, trees" required>
                            <small style="color:gray;">Use 'co2', 'tourists', or 'trees' for automatic icons.</small>
                        </div>
                        <div class="form-group">
                            <label>Value:</label>
                            <input type="text" name="stat_value" id="stat_value" placeholder="e.g. 1,500" required>
                        </div>
                        <div class="form-group">
                            <label>Label (User View):</label>
                            <input type="text" name="stat_label" id="stat_label" placeholder="e.g. Trees Planted" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin: 10px 0;">
                        <label style="cursor: pointer;">
                            <input type="checkbox" name="is_visible" id="is_visible" checked> 
                            Show this on website?
                        </label>
                    </div>

                    <div class="form-actions">
        <button type="submit" name="save_stat" class="btn-action btn-update">Save Statistic</button>
        <button type="button" onclick="resetStatForm()" class="btn-action btn-cancel-edit">Cancel / Clear</button>
    </div>
</form>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ecf0f1;">

                <h3>Current Statistics List</h3>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse; text-align: left;">
                        <tr style="background: #ecf0f1; border-bottom: 2px solid #bdc3c7;">
                            <th style="padding: 10px;">Key</th>
                            <th style="padding: 10px;">Value</th>
                            <th style="padding: 10px;">Label</th>
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px;">Action</th>
                        </tr>
                        <?php 
                        // Re-fetch stats as a list for the table
                        $list_res = $conn->query("SELECT * FROM analytics_stats");
                        while($row = $list_res->fetch_assoc()): 
                            $visStatus = $row['is_visible'] ? '<span style="color:green; font-weight:bold;">Visible</span>' : '<span style="color:red;">Hidden</span>';
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><?php echo $row['stat_key']; ?></td>
                            <td style="padding: 10px; font-weight:bold;"><?php echo $row['stat_value']; ?></td>
                            <td style="padding: 10px;"><?php echo $row['stat_label']; ?></td>
                            <td style="padding: 10px;"><?php echo $visStatus; ?></td>
                            <td style="padding: 10px;">
                                <button type="button" 
                                    onclick='editStat(<?php echo json_encode($row); ?>)'
                                    style="background:#3498db; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; margin-right:5px;">
                                    Edit
                                </button>
                                <a href="?del_stat=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Delete this statistic?')"
                                   style="color: #e74c3c; text-decoration: none;">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

<div class="admin-card">
                <h2>Manage Monthly Visitors</h2>
                
                <form method="POST" style="margin-bottom: 25px;">
                    <input type="hidden" name="visitor_id" id="vis_id">

                    <div class="lists-grid">
                        <div class="form-group">
                            <label>Month</label>
                            <select name="month" id="vis_month" required>
                                <option value="Jan">Jan</option>
                                <option value="Feb">Feb</option>
                                <option value="Mar">Mar</option>
                                <option value="Apr">Apr</option>
                                <option value="May">May</option>
                                <option value="Jun">Jun</option>
                                <option value="Jul">Jul</option>
                                <option value="Aug">Aug</option>
                                <option value="Sep">Sep</option>
                                <option value="Oct">Oct</option>
                                <option value="Nov">Nov</option>
                                <option value="Dec">Dec</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Year</label>
                            <input type="number" name="year" id="vis_year" value="<?php echo date('Y'); ?>" placeholder="2025" required>
                        </div>

                        <div class="form-group">
                            <label>Visitor Count</label>
                            <input type="number" name="visitors_count" id="vis_count" placeholder="e.g. 500" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
    <button type="submit" name="save_visitor" class="btn-action btn-update" id="btn_save_vis">Save Record</button>
    <button type="button" onclick="resetVisitorForm()" class="btn-action btn-cancel-edit">Cancel</button>
</div>
                </form>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ecf0f1;">

                <h3>Existing Visitor Records</h3>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse; text-align: left;">
                        <tr style="background: #ecf0f1; border-bottom: 2px solid #bdc3c7;">
                            <th style="padding: 10px;">Year</th>
                            <th style="padding: 10px;">Month</th>
                            <th style="padding: 10px;">Count</th>
                            <th style="padding: 10px;">Action</th>
                        </tr>
                        <?php 
                        // Fetch All Records (Ordered by Year DESC, then Month logic handled visually)
                        $vis_res = $conn->query("SELECT * FROM visitors_monthly ORDER BY year DESC, visitors_id DESC");
                        while($row = $vis_res->fetch_assoc()): 
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><?php echo $row['year']; ?></td>
                            <td style="padding: 10px; font-weight:bold; color:#20621E;"><?php echo $row['month']; ?></td>
                            <td style="padding: 10px;"><?php echo number_format($row['visitors_count']); ?></td>
                            <td style="padding: 10px;">
                                <button type="button" 
                                    onclick='editVisitor(<?php echo json_encode($row); ?>)'
                                    style="background:#3498db; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; margin-right:5px;">
                                    Edit
                                </button>
                                <a href="?del_visitor=<?php echo $row['visitors_id']; ?>" 
                                   onclick="return confirm('Delete this record?')"
                                   style="color: #e74c3c; text-decoration: none;">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
            
<div class="admin-card">
                <h2>Manage Customer Feedback</h2>
                
                <form method="POST" style="margin-bottom: 30px;">
                    <input type="hidden" name="feedback_id" id="fb_id">

                    <div class="lists-grid">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" name="name" id="fb_name" placeholder="Enter Name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Rating</label>
                            <select name="rating" id="fb_rating">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>

                        <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:10px;">
                            <label style="cursor: pointer;">
                                <input type="checkbox" name="status" id="fb_status" checked> 
                                Visible on site?
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" id="fb_msg" placeholder="Message/Comment" rows="2"></textarea>
                    </div>
                    
                    <div class="form-actions">
    <button type="submit" name="save_feedback" class="btn-action btn-update" id="btn_save_fb">Save Feedback</button>
    <button type="button" onclick="resetFeedbackForm()" class="btn-action btn-cancel-edit">Cancel</button>
</div>
                </form>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ecf0f1;">

<h3>Existing Feedback</h3>
                <div class="feedback-list">
                    <?php 
                    // Fetch latest feedback first
                    $feedback_res = $conn->query("SELECT * FROM feedback ORDER BY feedback_id DESC");
                    
                    while($row = $feedback_res->fetch_assoc()): 
                        // LOGIC: Determine Status Styles
                        $is_visible = ($row['status'] == 1);
                        
                        // 1. Opacity: Fade out if hidden
                        $opacity = $is_visible ? '1' : '0.6'; 
                        
                        // 2. Border Color: Green if Visible, Red if Hidden
                        $borderColor = $is_visible ? '#2ecc71' : '#e74c3c'; 
                        
                        // 3. Status Text Badge
                        $statusBadge = $is_visible 
                            ? '<span style="background:#eafaf1; color:#2ecc71; padding:2px 6px; border-radius:4px; font-size:12px; font-weight:bold; border:1px solid #2ecc71;">Visible</span>' 
                            : '<span style="background:#fdeaea; color:#e74c3c; padding:2px 6px; border-radius:4px; font-size:12px; font-weight:bold; border:1px solid #e74c3c;">Hidden</span>';
                    ?>
                        <div class="list-item" style="opacity: <?php echo $opacity; ?>; border-left: 5px solid <?php echo $borderColor; ?>; padding-left: 15px; margin-bottom: 15px;">
                            
                            <div class="item-info">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    
                                    <?php echo $statusBadge; ?>
                                </div>

                                <div style="color: gold; font-size: 14px; margin-bottom: 5px;">
                                    <?php echo str_repeat('★', $row['rating']); ?>
                                    <span style="color: #ccc;"><?php echo str_repeat('★', 5 - $row['rating']); ?></span>
                                </div>
                                
                                <small style="color: #555;">"<?php echo htmlspecialchars($row['message']); ?>"</small>
                            </div>
                            
                            <div class="actions">
                                <button type="button" onclick='editFeedback(<?php echo json_encode($row); ?>)' style="color: #3498db; border:none; background:none; cursor:pointer; font-weight:bold; margin-right:10px;">Edit</button>
                                
                                <a href="?del_feedback=<?php echo $row['feedback_id']; ?>" class="delete-badge" style="background:#e74c3c; color:white; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px;" onclick="return confirm('Delete this feedback?');">Delete</a>
                            </div>

                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

<div class="admin-card">
                <h2>Manage Top Lists</h2>
                
                <form method="POST" style="margin-bottom: 25px;">
                    <input type="hidden" name="list_id" id="list_id">

                    <div class="lists-grid">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" id="list_category">
                                <option value="activity">Green Activity</option>
                                <option value="destination">Eco-Destination</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Item Name</label>
                            <input type="text" name="item_name" id="list_name" placeholder="e.g. Jungle Trekking" required>
                        </div>

                        <div class="form-group">
                            <label>Order #</label>
                            <input type="number" name="order_num" id="list_order" placeholder="1" style="width: 80px;" required>
                        </div>

                        <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:10px;">
                            <label style="cursor: pointer;">
                                <input type="checkbox" name="status" id="list_status" checked> 
                                Visible?
                            </label>
                        </div>
                    </div>
                    
                   <div class="form-actions">
        <button type="submit" name="save_item" class="btn-action btn-update" id="btn_save_list">Save Item</button>
        <button type="button" onclick="resetListForm()" class="btn-action btn-cancel-edit">Cancel</button>
    </div>
</form>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ecf0f1;">

                <div class="lists-grid">
                    
                    <div>
                        <h3>Activities List</h3>
                        <?php 
                        // Fetch ordered by number
                        $act_res = $conn->query("SELECT * FROM analytics_lists WHERE category='activity' ORDER BY order_num ASC");
                        while($row = $act_res->fetch_assoc()): 
                            // Visual cue: Fade out if hidden
                            $opacity = ($row['status'] == 1) ? '1' : '0.5'; 
                            $statusText = ($row['status'] == 0) ? '<small style="color:red">(Hidden)</small>' : '';
                        ?>
                          <div class="list-item" style="opacity: <?php echo $opacity; ?>;">
                               <span class="item-info">
                                   <b>#<?php echo $row['order_num']; ?></b> <?php echo $row['item_name']; ?> <?php echo $statusText; ?>
                               </span>
                               
                               <div class="actions">
                                   <button type="button" onclick='editList(<?php echo json_encode($row); ?>)' style="color: blue; border:none; background:none; cursor:pointer; margin-right:5px;">Edit</button>
                                   <a href="?del_item=<?php echo $row['id']; ?>" class="delete-badge" onclick="return confirm('Delete?');">Delete</a>
                               </div>
                          </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div>
                        <h3>Destinations List</h3>
                        <?php 
                        $dest_res = $conn->query("SELECT * FROM analytics_lists WHERE category='destination' ORDER BY order_num ASC");
                        while($row = $dest_res->fetch_assoc()): 
                            $opacity = ($row['status'] == 1) ? '1' : '0.5';
                            $statusText = ($row['status'] == 0) ? '<small style="color:red">(Hidden)</small>' : '';
                        ?>
                          <div class="list-item" style="opacity: <?php echo $opacity; ?>;">
                               <span class="item-info">
                                   <b>#<?php echo $row['order_num']; ?></b> <?php echo $row['item_name']; ?> <?php echo $statusText; ?>
                               </span>
                               
                               <div class="actions">
                                   <button type="button" onclick='editList(<?php echo json_encode($row); ?>)' style="color: blue; border:none; background:none; cursor:pointer; margin-right:5px;">Edit</button>
                                   <a href="?del_item=<?php echo $row['id']; ?>" class="delete-badge" onclick="return confirm('Delete?');">Delete</a>
                               </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                </div> 
            </div>

        </main>
    </div>

    <script src="admin_script.js"></script>

</body>
</html>