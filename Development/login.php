<?php
// 1. Start the session
session_start();

// 2. Connect to the database
require_once 'db_connect.php';

// 3. Check if data was sent via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        echo "<script>
                alert('Please enter both username and password.');
                window.location.href = 'login.html';
              </script>";
        exit;
    }

    // 4. Check Database
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        
        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $db_username, $db_password_hash);
                $stmt->fetch();

                // 5. Verify Password
                if (password_verify($password, $db_password_hash)) {
                    
                    // --- SUCCESS ---
                    
                    // A. Server Session
                    session_regenerate_id();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $db_username;

                    // B. Client Sync (Fixing your script.js issue)
                    // We print a tiny script to save data to the browser before redirecting
                    echo "<script>
                            localStorage.setItem('isLoggedIn', 'true');
                            localStorage.setItem('username', '" . htmlspecialchars($db_username) . "');
                            alert('Login Successful! Welcome back.');
                            window.location.href = 'index.html'; 
                          </script>";
                    exit;

                } else {
                    // --- WRONG PASSWORD ---
                    echo "<script>
                            alert('Incorrect password. Please try again.');
                            window.location.href = 'login.html';
                          </script>";
                    exit;
                }
            } else {
                // --- USER NOT FOUND ---
                echo "<script>
                        alert('No account found with that username.');
                        window.location.href = 'login.html';
                      </script>";
                exit;
            }
        } else {
            echo "<script>alert('Database error.'); window.location.href = 'login.html';</script>";
        }
        $stmt->close();
    }
    $conn->close();
} else {
    // If someone tries to open login.php directly without submitting the form
    header("location: login.html");
    exit;
}
?>