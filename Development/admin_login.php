<?php
// admin_login.php
session_start(); // <--- CRITICAL: Start the session to save login state

if (isset($_POST['admin_login'])) {
    
    // --- CREDENTIALS (Hardcoded) ---
    $valid_username = "admin";
    $valid_email    = "admin@gmail.com";
    $valid_password = "admin123";

    // Get input
    $input_user = $_POST['admin_username']; 
    $input_pass = $_POST['admin_password'];

    // Check Credentials
    if (($input_user === $valid_username || $input_user === $valid_email) && $input_pass === $valid_password) {
        
        // --- LOGIN SUCCESS ---
        // 1. Set the Session Variable (The "Key" to enter the dashboard)
        $_SESSION['admin_id'] = 1; 
        $_SESSION['admin_name'] = "Super Admin";

        // 2. Redirect
        echo "<script>
            alert(\"Login Successful! Welcome Admin.\");
            window.location.href = 'admin_dashboard.php';
        </script>";
        
    } else {
        // --- LOGIN FAILED ---
        echo "<script>
            alert(\"Access Denied! Wrong Username or Password.\");
            window.location.href = 'adminlogin.html';
        </script>";
    }

} else {
    header("Location: adminlogin.html");
    exit();
}
?>