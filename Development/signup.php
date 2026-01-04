<?php
include 'db_connect.php';  // Include database connection

if(isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        // Email already registered
        echo "<script>
                alert('This email is already registered. Please login or use another email.');
                window.history.back();
              </script>";
        exit();
    }

    // Optional: Check if username already exists
    $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();

    if($checkUsername->num_rows > 0){
        echo "<script>
                alert('This username is already taken. Please choose another username.');
                window.history.back();
              </script>";
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if($stmt->execute()){
        // Signup successful
        echo "<script>
                alert('Your account has been successfully created!');
                window.location.href='index.html';
              </script>";
        exit();
    } else {
        // Signup failed
        echo "<script>
                alert('Signup failed: " . $stmt->error . "');
                window.history.back();
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
