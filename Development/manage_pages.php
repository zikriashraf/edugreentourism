<?php
session_start();
include 'db_connect.php'; // Ensure this matches your connection file

// --- SECURITY CHECK ---
if (!isset($_SESSION['admin_id'])) {
    header('location: login.php');
    exit();
}

// --- HELPER: GET IMAGE PATH ---
function get_image_path($rawPath) {
    if (empty($rawPath) || $rawPath == 'null') {
        return 'https://placehold.co/80x80?text=No+Img';
    }
    if (strpos($rawPath, 'http') === 0) return $rawPath;
    if (file_exists('uploads/' . basename($rawPath))) return 'uploads/' . basename($rawPath);
    if (file_exists('img/' . basename($rawPath))) return 'img/' . basename($rawPath);
    return 'img/' . basename($rawPath);
}

// --- HELPER: DELETE OLD IMAGE ---
// Removes file from server if it exists in 'uploads/'
function delete_old_image($path) {
    if (!empty($path) && strpos($path, 'uploads/') === 0 && file_exists($path)) {
        unlink($path);
    }
}

$active_tab = isset($_GET['page']) ? $_GET['page'] : 'home';

// =========================================================
// 1. LOGIC FOR HOMEPAGE
// =========================================================
if ($active_tab == 'home') {
    
    // --- A. HERO SLIDES ---
    
    // 1. Add New Slide
    if (isset($_POST['add_slide'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $order = $_POST['order_number'];
        $img = ""; 

        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $img = $target_file;
        }

        $stmt = $conn->prepare("INSERT INTO hero_slides (title, description, image_url, order_number, status) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("sssi", $title, $desc, $img, $order);
        $stmt->execute();
        header("Location: manage_pages.php?page=home"); exit();
    }

    // 2. Update Existing Slide
    if (isset($_POST['update_slide'])) {
        $id = $_POST['slide_id'];
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $order = $_POST['order_number'];
        $img = $_POST['current_image'];

        if (!empty($_FILES['image']['name'])) {
            delete_old_image($img); // Clean up old file
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $img = $target_file;
        }

        $stmt = $conn->prepare("UPDATE hero_slides SET title=?, description=?, image_url=?, order_number=? WHERE slide_id=?");
        $stmt->bind_param("sssii", $title, $desc, $img, $order, $id);
        $stmt->execute();
        header("Location: manage_pages.php?page=home"); exit();
    }

    // 3. Delete Slide
    if (isset($_GET['delete_slide'])) {
        $id = $_GET['delete_slide'];
        $res = $conn->query("SELECT image_url FROM hero_slides WHERE slide_id=$id");
        if ($res->num_rows > 0) {
            delete_old_image($res->fetch_assoc()['image_url']);
        }
        $conn->query("DELETE FROM hero_slides WHERE slide_id = $id");
        header("Location: manage_pages.php?page=home"); exit();
    }

    // 4. Toggle Slide Status
    if (isset($_GET['toggle_slide'])) {
        $id = $_GET['toggle_slide'];
        $current = $_GET['status'];
        $new = ($current == 1) ? 0 : 1;
        $conn->query("UPDATE hero_slides SET status = $new WHERE slide_id = $id");
        header("Location: manage_pages.php?page=home"); exit();
    }

    // --- B. EXPLORE SECTION ---
    if (isset($_POST['update_explore'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $status = isset($_POST['status']) ? 1 : 0; 

        $check = $conn->query("SELECT id FROM explore_deeper LIMIT 1");
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO explore_deeper (title, content, status) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $title, $content, $status);
        } else {
            $stmt = $conn->prepare("UPDATE explore_deeper SET title=?, content=?, status=? WHERE id=?");
            $stmt->bind_param("ssii", $title, $content, $status, $id);
        }
        $stmt->execute();
        header("Location: manage_pages.php?page=home"); exit();
    }

    // --- C. MAIN STATISTICS ---
    if (isset($_POST['update_stats'])) {
        $participants = $_POST['participants'];
        $donation = $_POST['donation'];
        $vendors = $_POST['vendors'];

        $check = $conn->query("SELECT stats_id FROM stats LIMIT 1");
        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $id = $row['stats_id'];
            $stmt = $conn->prepare("UPDATE stats SET participants=?, donation=?, vendors=? WHERE stats_id=?");
            $stmt->bind_param("idii", $participants, $donation, $vendors, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO stats (participants, donation, vendors) VALUES (?, ?, ?)");
            $stmt->bind_param("idi", $participants, $donation, $vendors);
        }
        $stmt->execute();
        header("Location: manage_pages.php?page=home&msg=stats_updated"); exit();
    }

    // --- D. GALLERY MANAGEMENT (UPDATED FOR FILES) ---

    // 7. Add New Gallery Image
    if (isset($_POST['add_gallery'])) {
        $cap = $_POST['caption'];
        $ord = $_POST['order_number'];
        $img = "";

        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $img = $target_file;
        }
        
        $stmt = $conn->prepare("INSERT INTO gallery_images (image_url, caption, order_number) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $img, $cap, $ord);
        $stmt->execute();
        header("Location: manage_pages.php?page=home&msg=gallery_added"); exit();
    }

    // 8. Update Gallery Image
    if (isset($_POST['update_gallery'])) {
        $id = $_POST['gallery_id'];
        $cap = $_POST['caption'];
        $ord = $_POST['order_number'];
        $img = $_POST['current_image'];

        if (!empty($_FILES['image']['name'])) {
            delete_old_image($img); // Clean up
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $img = $target_file;
        }

        $stmt = $conn->prepare("UPDATE gallery_images SET image_url=?, caption=?, order_number=? WHERE image_id=?");
        $stmt->bind_param("ssii", $img, $cap, $ord, $id);
        $stmt->execute();
        header("Location: manage_pages.php?page=home&msg=gallery_updated"); exit();
    }

    // 9. Delete Gallery Image
    if (isset($_GET['delete_gallery'])) {
        $id = $_GET['delete_gallery'];
        $res = $conn->query("SELECT image_url FROM gallery_images WHERE image_id=$id");
        if ($res->num_rows > 0) {
            delete_old_image($res->fetch_assoc()['image_url']);
        }
        $conn->query("DELETE FROM gallery_images WHERE image_id = $id");
        header("Location: manage_pages.php?page=home&msg=gallery_deleted"); exit();
    }

} 

// =========================================================
// 2. LOGIC FOR DISCOVER PAGE
// =========================================================
if ($active_tab == 'discover') {
    if (isset($_POST['add_attraction'])) {
        $stmt = $conn->prepare("INSERT INTO explore_section (title, content, image_url, display_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $_POST['title'], $_POST['content'], $_POST['image_url'], $_POST['display_order']);
        $stmt->execute(); header("Location: manage_pages.php?page=discover&msg=added"); exit();
    }
    if (isset($_POST['update_attraction'])) {
        $stmt = $conn->prepare("UPDATE explore_section SET title=?, content=?, image_url=?, display_order=? WHERE explore_id=?");
        $stmt->bind_param("sssii", $_POST['title'], $_POST['content'], $_POST['image_url'], $_POST['display_order'], $_POST['explore_id']);
        $stmt->execute(); header("Location: manage_pages.php?page=discover&msg=updated"); exit();
    }
    if (isset($_GET['delete_attraction'])) {
        $conn->query("DELETE FROM explore_section WHERE explore_id = " . intval($_GET['delete_attraction']));
        header("Location: manage_pages.php?page=discover&msg=deleted"); exit();
    }
}

// =========================================================
// 3. LOGIC FOR LEARN PAGE
// =========================================================
if ($active_tab == 'learn') {
    if (isset($_POST['add_learn'])) {
        $stmt = $conn->prepare("INSERT INTO learn_content (title, content, image_url, order_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $_POST['title'], $_POST['content'], $_POST['image_url'], $_POST['order_number']);
        $stmt->execute(); header("Location: manage_pages.php?page=learn&msg=added"); exit();
    }
    if (isset($_POST['update_learn'])) {
        $stmt = $conn->prepare("UPDATE learn_content SET title=?, content=?, image_url=?, order_number=? WHERE sections_id=?");
        $stmt->bind_param("sssii", $_POST['title'], $_POST['content'], $_POST['image_url'], $_POST['order_number'], $_POST['sections_id']);
        $stmt->execute(); header("Location: manage_pages.php?page=learn&msg=updated"); exit();
    }
    if (isset($_GET['delete_learn'])) {
        $conn->query("DELETE FROM learn_content WHERE sections_id = " . intval($_GET['delete_learn']));
        header("Location: manage_pages.php?page=learn&msg=deleted"); exit();
    }
}

// =========================================================
// 4. LOGIC FOR CONTACT PAGE
// =========================================================
if ($active_tab == 'contact') {
    if (isset($_POST['add_contact'])) {
        $stmt = $conn->prepare("INSERT INTO contact_details (email, phone, address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $_POST['email'], $_POST['phone'], $_POST['address']);
        $stmt->execute(); header("Location: manage_pages.php?page=contact&msg=added"); exit();
    }
    if (isset($_POST['update_contact'])) {
        $stmt = $conn->prepare("UPDATE contact_details SET email=?, phone=?, address=? WHERE contact_id=?");
        $stmt->bind_param("sssi", $_POST['email'], $_POST['phone'], $_POST['address'], $_POST['contact_id']);
        $stmt->execute(); header("Location: manage_pages.php?page=contact&msg=updated"); exit();
    }
    if (isset($_GET['delete_contact'])) {
        $conn->query("DELETE FROM contact_details WHERE contact_id = " . intval($_GET['delete_contact']));
        header("Location: manage_pages.php?page=contact&msg=deleted"); exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - EduGreenTourism</title>
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
                    <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="manage_analytical.php"><i class="fas fa-chart-line"></i> Analytical</a></li>
                    <li><a href="manage_pages.php" class="active"><i class="fas fa-file-alt"></i> Pages</a></li>
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
            <header style="margin-bottom: 20px;">
                <h1>Manage Website Content</h1>
            </header>

            <div class="page-tabs">
                <a href="?page=home" class="tab-link <?php echo $active_tab == 'home' ? 'active' : ''; ?>">Homepage</a>
                <a href="?page=discover" class="tab-link <?php echo $active_tab == 'discover' ? 'active' : ''; ?>">Discover</a>
                <a href="?page=learn" class="tab-link <?php echo $active_tab == 'learn' ? 'active' : ''; ?>">Learn More</a>
                <a href="?page=contact" class="tab-link <?php echo $active_tab == 'contact' ? 'active' : ''; ?>">Contact Us</a>
            </div>

            <div class="editor-section">
                <?php if ($active_tab == 'home'): ?>
                    <?php 
                        // Fetch All Slides
                        $slides = $conn->query("SELECT * FROM hero_slides ORDER BY order_number ASC");
                        
                        // Fetch Data for Editing (If 'edit_slide' is in URL)
                        $edit_slide_data = null;
                        if (isset($_GET['edit_slide'])) {
                            $edit_id = $_GET['edit_slide'];
                            $stmt_edit = $conn->prepare("SELECT * FROM hero_slides WHERE slide_id = ?");
                            $stmt_edit->bind_param("i", $edit_id);
                            $stmt_edit->execute();
                            $edit_slide_data = $stmt_edit->get_result()->fetch_assoc();
                        }

                        // Fetch Explore Section
                        $explore_query = $conn->query("SELECT * FROM explore_deeper LIMIT 1");
                        $explore = ($explore_query->num_rows > 0) ? $explore_query->fetch_assoc() : [];
                    ?>
                    
                    <h3><i class="fas fa-images"></i> Hero Slideshow</h3>
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Preview</th>
                                <th>Title & Description</th> <th>Status</th>
                                <th style="min-width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($slides->num_rows > 0): ?>
                                <?php while($row = $slides->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['order_number']; ?></td>
                                    <td><img src="<?php echo get_image_path($row['image_url']); ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] == 1 ? 'visible' : 'hidden'; ?>">
                                            <?php echo $row['status'] == 1 ? 'Visible' : 'Hidden'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="manage_pages.php?page=home&edit_slide=<?php echo $row['slide_id']; ?>" class="btn btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                        <a href="manage_pages.php?page=home&toggle_slide=<?php echo $row['slide_id']; ?>&status=<?php echo $row['status']; ?>" class="btn btn-toggle" title="Toggle"><i class="fas fa-eye"></i></a>
                                        <a href="manage_pages.php?page=home&delete_slide=<?php echo $row['slide_id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this slide?')" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5">No slides found. Add one below.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if ($edit_slide_data): ?>
                        <div style="background: #eef9f0; padding: 20px; border-radius: 5px; border: 1px solid #28a745; margin-bottom: 30px;">
                            <h4 style="margin-top:0; color:#20621E;">Edit Slide: <?php echo htmlspecialchars($edit_slide_data['title']); ?></h4>
                            
                            <form action="manage_pages.php?page=home" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="slide_id" value="<?php echo $edit_slide_data['slide_id']; ?>">
                                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_slide_data['image_url']); ?>">
                                
                                <div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($edit_slide_data['title']); ?>" required></div>
                                <div class="form-group"><label>Description</label><textarea name="description" rows="3" required><?php echo htmlspecialchars($edit_slide_data['description']); ?></textarea></div>
                                
                                <div class="form-group">
                                    <label>Slide Image</label>
                                    <?php if($edit_slide_data['image_url']): ?>
                                        <div style="margin-bottom:5px;">
                                            <img src="<?php echo get_image_path($edit_slide_data['image_url']); ?>" style="height:50px; border-radius:4px; border:1px solid #ddd;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="image" accept="image/*">
                                </div>

                                <div class="form-group"><label>Order</label><input type="number" name="order_number" value="<?php echo $edit_slide_data['order_number']; ?>" required></div>
                                <div class="form-actions">
    <button type="submit" name="update_slide" class="btn-action btn-update">
        <i class="fas fa-save"></i> Update Slide
    </button>
    
    <a href="manage_pages.php?page=home" class="btn-action btn-cancel-edit">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>
                            </form>
                        </div>
                    <?php else: ?>
                        <details style="background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee;">
                            <summary style="cursor: pointer; font-weight: bold; color: #28a745;">+ Add New Slide</summary>
                            
                            <form action="manage_pages.php?page=home" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                                <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                                <div class="form-group"><label>Description</label><textarea name="description" rows="2" required></textarea></div>
                                
                                <div class="form-group">
                                    <label>Slide Image</label>
                                    <input type="file" name="image" accept="image/*" required>
                                </div>
                                
                                <div class="form-group"><label>Order</label><input type="number" name="order_number" value="1" required></div>
                                <button type="submit" name="add_slide" class="btn btn-add">Save Slide</button>
                            </form>
                        </details>
                    <?php endif; ?>

                    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

                    <h3><i class="fas fa-edit"></i> Explore Section</h3>
                    
                    <form action="manage_pages.php?page=home" method="POST">
                        <input type="hidden" name="id" value="<?php echo $explore['id'] ?? ''; ?>">
                        
                        <div class="form-group">
                            <label>Section Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($explore['title'] ?? ''); ?>" placeholder="Enter title (e.g. EXPLORE DEEPER)">
                        </div>
                        
                        <div class="form-group">
                            <label>Content Text</label>
                            <textarea name="content" rows="6" placeholder="Enter main content text here..."><?php echo htmlspecialchars($explore['content'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label style="cursor: pointer;">
                                <input type="checkbox" name="status" <?php if(isset($explore['status']) && $explore['status'] == 1) echo "checked"; ?>> 
                                Display this section on homepage
                            </label>
                        </div>
                        
                       <button type="submit" name="update_explore" class="btn-submit" style="width: auto; display: inline-block; padding: 10px 25px;">
    Update Section
</button>
                    </form>

                <?php 
                        // Fetch Existing Stats Data
                        $stats_query = $conn->query("SELECT * FROM stats LIMIT 1");
                        $stats_data = ($stats_query->num_rows > 0) ? $stats_query->fetch_assoc() : ['participants'=>0, 'donation'=>0.00, 'vendors'=>0];
                    ?>

                    <h3 class="section-header"><i class="fas fa-chart-pie"></i> Main Page Statistics</h3>

                    <div class="stats-container">
                        <form action="manage_pages.php?page=home" method="POST" class="stats-form" id="statsForm" onsubmit="return validateStats()">
                            <div class="form-group">
                                <label>Participants</label>
                                <input type="number" id="stat_participants" name="participants" value="<?php echo htmlspecialchars($stats_data['participants']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Donation Collected (RM)</label>
                                <input type="number" step="0.01" id="stat_donation" name="donation" value="<?php echo htmlspecialchars($stats_data['donation']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Vendors Involved</label>
                                <input type="number" id="stat_vendors" name="vendors" value="<?php echo htmlspecialchars($stats_data['vendors']); ?>">
                            </div>
                            <div class="form-group" style="flex: 0;">
                                <button type="submit" name="update_stats" class="btn-update-stats">
                                    <i class="fas fa-save"></i> Update Stats
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr class="section-divider">
                    
                    <h3 class="section-header"><i class="fas fa-images"></i> Image Gallery</h3>

                    <div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="flex: 2; min-width: 300px;">
                            <table class="page-table">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th width="80">Image</th>
                                        <th>Caption</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $gallery = $conn->query("SELECT * FROM gallery_images ORDER BY order_number ASC");
                                    if($gallery->num_rows > 0):
                                        while($row = $gallery->fetch_assoc()): 
                                            $displayImg = get_image_path($row['image_url']);
                                    ?>
                                    <tr>
                                        <td><?php echo $row['order_number']; ?></td>
                                        <td><img src="<?php echo $displayImg; ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"></td>
                                        <td><?php echo htmlspecialchars($row['caption']); ?></td>
                                        <td>
                                            <button class="btn-edit" onclick="editGallery(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" title="Edit">
                                            <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="manage_pages.php?page=home&delete_gallery=<?php echo $row['image_id']; ?>" 
                                            class="btn-delete" onclick="return confirm('Delete this image?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; 
                                    else: ?>
                                    <tr><td colspan="4" style="text-align:center;">No images found. Add one -></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div style="flex: 1; min-width: 250px;">
                            <div class="stats-container" id="galleryFormContainer" style="padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                                <h4 id="galleryFormTitle" style="margin-top:0; color: #20621E;">+ Add New Image</h4>
                                
                                <form action="manage_pages.php?page=home" method="POST" enctype="multipart/form-data" onsubmit="return validateGalleryOrder()">
                                    <input type="hidden" name="gallery_id" id="gallery_id">
                                    <input type="hidden" name="current_image" id="gallery_current_image">
                                    
                                    <div class="form-group">
                                        <label>Caption / Alt Text</label>
                                        <input type="text" name="caption" id="gallery_caption" required placeholder="e.g. Waterfall View">
                                    </div>

                                    <div id="gallery_preview_container" style="display:none; margin-bottom:10px;">
                                        <label style="display:block; font-size:0.85rem; color:#666; margin-bottom:5px;">Current Image:</label>
                                        <img id="gallery_preview_img" src="" style="height:80px; width:auto; border-radius:4px; border:1px solid #ccc;">
                                    </div>

                                    <div class="form-group">
                                        <label>Upload Image</label>
                                        <input type="file" name="image" id="gallery_file" accept="image/*">
                                        <small style="color:#888;">Leave empty to keep existing image</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Order Number</label>
                                        <input type="number" name="order_number" id="gallery_order" value="1" required>
                                    </div>

                                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                                        <button type="submit" name="add_gallery" id="btn_save_gallery" class="btn-add" style="flex:1; background: #20621E; color: white; padding: 10px; border:none; border-radius: 4px; cursor: pointer;">
                                            Save Image
                                        </button>
                                        <button type="button" onclick="resetGalleryForm()" id="btn_cancel_gallery" style="display:none; background: #ccc; color: #333; padding: 10px; border:none; border-radius: 4px; cursor: pointer;">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <hr class="section-divider">

                <?php elseif ($active_tab == 'discover'): ?>
                    <h3 class="section-header"><i class="fas fa-map-marked-alt"></i> Manage Eco-Attractions</h3>

                    <div class="admin-row">
                        <div class="col-list">
                            <table class="page-table">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th width="100">Image</th>
                                        <th>Title & Content</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $attractions = $conn->query("SELECT * FROM explore_section ORDER BY display_order ASC");
                                    if($attractions && $attractions->num_rows > 0):
                                        while($row = $attractions->fetch_assoc()): 
                                            $img = get_image_path($row['image_url']);
                                    ?>
                                    <tr>
                                        <td><?php echo $row['display_order']; ?></td>
                                        <td><img src="<?php echo $img; ?>" class="table-img-preview"></td>
                                        <td>
                                            <strong style="font-size: 1.1em; color: #333;"><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                            <small style="color:#666; line-height: 1.4; display: block; margin-top: 4px;">
                                                <?php echo htmlspecialchars(substr($row['content'], 0, 100)) . '...'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button class="btn-edit" onclick="editAttraction(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                                            <a href="manage_pages.php?page=discover&delete_attraction=<?php echo $row['explore_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this attraction?')" title="Delete"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; 
                                    else: ?>
                                    <tr><td colspan="4" style="text-align:center; padding: 20px;">No attractions found. Add your first one on the right! -></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-form">
                            <div class="admin-form-container" id="attractionFormContainer">
                                <h4 class="admin-form-title" id="attractionFormTitle">+ Add New Attraction</h4>
                                <form action="manage_pages.php?page=discover" method="POST">
                                    <input type="hidden" name="explore_id" id="explore_id">
                                    <div class="form-group"><label>Attraction Title</label><input type="text" name="title" id="attr_title" required placeholder="e.g. Sungai Bil Waterfall"></div>
                                    <div class="form-group"><label>Description</label><textarea name="content" id="attr_content" rows="5" required placeholder="Describe the activities and scenery..."></textarea></div>
                                    <div class="form-group"><label>Image Filename / URL</label><input type="text" name="image_url" id="attr_image" required placeholder="e.g. waterfall.jpg"><small style="color:#888;">Ensure this file is in your 'img/' folder</small></div>
                                    <div class="form-group"><label>Display Order</label><input type="number" name="display_order" id="attr_order" value="1" required></div>
                                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                                        <button type="submit" name="add_attraction" id="btn_save_attr" class="btn-submit" style="flex:1;">Save Attraction</button>
                                        <button type="button" onclick="resetAttractionForm()" id="btn_cancel_attr" style="display:none; background: #ccc;">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>

            <?php if ($active_tab == 'learn'): ?>
    <div class="editor-section"> <h3 class="section-header"><i class="fas fa-book-open"></i> Manage Educational Content</h3>
        <div class="admin-row">
                    <div class="col-list">
                        <table class="page-table">
                            <thead><tr><th width="50">#</th><th width="80">Image</th><th>Title & Content</th><th width="100">Actions</th></tr></thead>
                            <tbody>
                                <?php 
                                $learn = $conn->query("SELECT * FROM learn_content ORDER BY order_number ASC");
                                if($learn->num_rows > 0):
                                    while($row = $learn->fetch_assoc()): 
                                       $img = get_image_path($row['image_url']);
                                ?>
                                <tr>
                                    <td><?php echo $row['order_number']; ?></td>
                                    <td><?php if(!empty($row['image_url'])): ?><img src="<?php echo $img; ?>" class="table-img-preview"><?php else: ?><span style="color:#ccc;">No Image</span><?php endif; ?></td>
                                    <td><strong style="color: #333;"><?php echo htmlspecialchars($row['title']); ?></strong><br><small style="color:#666;"><?php echo htmlspecialchars(substr(strip_tags($row['content']), 0, 100)) . '...'; ?></small></td>
                                    <td>
                                        <button class="btn-edit" onclick="editLearn(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-pen"></i></button>
                                        <a href="manage_pages.php?page=learn&delete_learn=<?php echo $row['sections_id']; ?>" class="btn-delete" onclick="return confirm('Delete this section?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?><tr><td colspan="4">No content found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-form">
                        <div class="admin-form-container" id="learnFormContainer">
                            <h4 class="admin-form-title" id="learnFormTitle">+ Add Learn Section</h4>
                            <form action="manage_pages.php?page=learn" method="POST">
                                <input type="hidden" name="sections_id" id="learn_id">
                                <div class="form-group"><label>Section Title</label><input type="text" name="title" id="learn_title" required></div>
                                <div class="form-group"><label>Content</label><textarea name="content" id="learn_content" rows="6"></textarea></div>
                                <div class="form-group"><label>Image URL</label><input type="text" name="image_url" id="learn_image"></div>
                                <div class="form-group"><label>Order</label><input type="number" name="order_number" id="learn_order" value="1" required></div>
                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <button type="submit" name="add_learn" id="btn_save_learn" class="btn-submit" style="flex:1;">Save Section</button>
                                    <button type="button" onclick="resetLearnForm()" id="btn_cancel_learn" style="display:none; background: #ccc;">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($active_tab == 'contact'): ?>
    <div class="editor-section"> <h3 class="section-header"><i class="fas fa-address-book"></i> Manage Contact Details</h3>
        <div class="admin-row">
                    <div class="col-list">
                        <table class="page-table">
                            <thead><tr><th width="50">#</th><th>Email</th><th>Phone</th><th>Address</th><th width="100">Actions</th></tr></thead>
                            <tbody>
                                <?php 
                                $contacts = $conn->query("SELECT * FROM contact_details ORDER BY contact_id DESC");
                                if($contacts->num_rows > 0):
                                    while($row = $contacts->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $row['contact_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><small><?php echo htmlspecialchars($row['address']); ?></small></td>
                                    <td>
                                        <button class="btn-edit" onclick="editContact(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-pen"></i></button>
                                        <a href="manage_pages.php?page=contact&delete_contact=<?php echo $row['contact_id']; ?>" class="btn-delete" onclick="return confirm('Delete this contact info?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?><tr><td colspan="5">No contact details found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-form">
                        <div class="admin-form-container" id="contactFormContainer">
                            <h4 class="admin-form-title" id="contactFormTitle">+ Add New Contact Info</h4>
                            <form action="manage_pages.php?page=contact" method="POST">
                                <input type="hidden" name="contact_id" id="contact_id">
                                <div class="form-group"><label>Email Address</label><input type="email" name="email" id="contact_email" required></div>
                                <div class="form-group"><label>Phone Number</label><input type="text" name="phone" id="contact_phone" required></div>
                                <div class="form-group"><label>Full Address</label><textarea name="address" id="contact_address" rows="4" required></textarea></div>
                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <button type="submit" name="add_contact" id="btn_save_contact" class="btn-submit" style="flex:1;">Save Details</button>
                                    <button type="button" onclick="resetContactForm()" id="btn_cancel_contact" style="display:none; background: #ccc;">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <?php
        // Gallery Validation Data
        $gallery_check = $conn->query("SELECT image_id, order_number FROM gallery_images");
        $gallery_data = [];
        if($gallery_check) { while($row = $gallery_check->fetch_assoc()) { $gallery_data[] = $row; } }
    ?>
    <script>
        const existingGalleryData = <?php echo json_encode($gallery_data); ?>;

        // --- UPDATED GALLERY JS FUNCTIONS ---
        function editGallery(data) {
            document.getElementById('gallery_id').value = data.image_id;
            document.getElementById('gallery_caption').value = data.caption;
            document.getElementById('gallery_order').value = data.order_number;
            document.getElementById('gallery_current_image').value = data.image_url;

            // Handle Preview Logic
            const previewBox = document.getElementById('gallery_preview_container');
            const previewImg = document.getElementById('gallery_preview_img');
            let src = data.image_url;
            
            // Check path type for preview
            if (src && src.indexOf('http') === -1 && src.indexOf('uploads/') === -1 && src.indexOf('img/') === -1) {
                src = 'img/' + src; 
            }
            
            if(src) {
                previewImg.src = src;
                previewBox.style.display = 'block';
            } else {
                previewBox.style.display = 'none';
            }

            // UI Changes
            const title = document.getElementById('galleryFormTitle');
            title.textContent = "Edit Image";
            title.style.color = "#3498db";

            const btn = document.getElementById('btn_save_gallery');
            btn.textContent = "Update Image";
            btn.name = "update_gallery"; 
            btn.style.backgroundColor = "#3498db"; 

            document.getElementById('btn_cancel_gallery').style.display = "inline-block";
            
            // File input is optional when editing
            document.getElementById('gallery_file').removeAttribute('required');

            const container = document.getElementById('galleryFormContainer');
            container.style.borderColor = "#3498db";
            container.style.backgroundColor = "#f0fff4";
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function resetGalleryForm() {
            document.getElementById('gallery_id').value = '';
            document.getElementById('gallery_caption').value = '';
            document.getElementById('gallery_order').value = '1';
            document.getElementById('gallery_current_image').value = '';
            document.getElementById('gallery_file').value = '';

            // Hide Preview
            document.getElementById('gallery_preview_container').style.display = 'none';
            document.getElementById('gallery_preview_img').src = '';

            const title = document.getElementById('galleryFormTitle');
            title.textContent = "+ Add New Image";
            title.style.color = "#20621E";

            const btn = document.getElementById('btn_save_gallery');
            btn.textContent = "Save Image";
            btn.name = "add_gallery"; 
            btn.style.backgroundColor = "#20621E"; 
            
            document.getElementById('btn_cancel_gallery').style.display = "none";
            
            const container = document.getElementById('galleryFormContainer');
            container.style.borderColor = "#ddd";
            container.style.backgroundColor = "#fff";
        }
    </script>
    <script src="admin_script.js"></script>
</body>
</html>