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

// Initialize variables
$update_mode = false;
$id = 0;
$username = "";
$email = "";
$message = "";

// 2. DELETE USER
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id");
    // Redirect to clear the URL parameter
    header("location: manage_users.php"); 
    exit();
}

// 3. EDIT USER (Load data into form)
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update_mode = true;
    $result = $conn->query("SELECT * FROM users WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];
    }
}

// 4. SAVE USER (Create or Update)
if (isset($_POST['save'])) {
    $username_input = $_POST['username'];
    $email_input = $_POST['email'];
    $password_input = $_POST['password'];
    
    if (isset($_POST['id']) && $_POST['id'] != 0) {
        // --- UPDATE ---
        $id = $_POST['id'];
        if (!empty($password_input)) {
            $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username='$username_input', email='$email_input', password='$hashed_password' WHERE id=$id";
        } else {
            $sql = "UPDATE users SET username='$username_input', email='$email_input' WHERE id=$id";
        }
        
        if($conn->query($sql)) {
            $message = "User updated successfully!";
            // Reset variables to "Add New" mode
            $update_mode = false; $username = ""; $email = ""; $id = 0; 
        } else {
            $message = "Error updating user: " . $conn->error;
        }

    } else {
        // --- CREATE ---
        if (!empty($password_input)) {
            $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES ('$username_input', '$email_input', '$hashed_password')";
            if ($conn->query($sql)) {
                $message = "User created successfully!";
            } else {
                $message = "Error creating user: " . $conn->error;
            }
        } else {
            $message = "Password is required for new users.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | EduGreenTourism</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="admin_style.css">

    <script src="admin_script.js" defer></script>
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
                    <li><a href="manage_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
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
                <h1>User Management</h1>
            </header>

            <?php if ($message): ?>
                <div class="alert">
                    <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i> 
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="admin-row">
                
                <div class="col-list">
                    <div class="admin-card">
                        <h2>Registered Users</h2>
                        <table>
                            <thead>
    <tr>
        <th width="5%">No.</th> <th>Username</th>
        <th>Email</th>
        <th>Created</th>
        <th width="15%">Actions</th>
    </tr>
</thead>
                            <tbody>
                                <?php
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $counter = 1; // <--- 1. Start Counter
    while($row = $result->fetch_assoc()) {
        $created = date("d M Y", strtotime($row['created_at']));
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>"; // <--- 2. Use Counter instead of ID
        echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . $created . "</td>";
        echo "<td>
                <a href='manage_users.php?edit=" . $row['id'] . "' class='action-btn btn-edit' title='Edit'><i class='fas fa-edit'></i></a>
                <a href='manage_users.php?delete=" . $row['id'] . "' class='action-btn btn-delete' title='Delete'><i class='fas fa-trash'></i></a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding: 20px;'>No users found.</td></tr>";
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-form" id="form-section">
                    <div class="admin-card">
                        <h2><?php echo $update_mode ? 'Edit User' : 'Add New User'; ?></h2>
                        
                        <form action="manage_users.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" value="<?php echo $username; ?>" required placeholder="Enter username">
                            </div>
                            
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo $email; ?>" required placeholder="user@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" placeholder="<?php echo $update_mode ? 'Leave blank to keep current' : 'Enter password'; ?>">
                            </div>
                            
                            <button type="submit" name="save" class="btn-submit">
                                <i class="fas fa-save"></i> <?php echo $update_mode ? 'Update User' : 'Create User'; ?>
                            </button>
                            
                            <?php if ($update_mode): ?>
                                <a href="manage_users.php" class="cancel-link">Cancel Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

            </div> </main>
    </div>

</body>
</html>