<?php
require 'config.php'; // Database connection

session_start();

// Clear any existing session variables
$_SESSION = array();
session_destroy();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input to prevent SQL injection
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if username and password are provided
    if (empty($username) || empty($password)) {
        echo 'Please enter both username and password.';
        exit();
    }

    // Prepare the SQL statement to fetch admin details including profile_picture
    $stmt = $conn->prepare("SELECT id, password, firstName, lastName, is_verified, profile_picture FROM admins WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($adminId, $hashedPassword, $firstName, $lastName, $is_verified, $profilePicture);

        if ($stmt->num_rows === 1) {
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                if ($is_verified) {
                    // Store admin details in the session
                    $_SESSION['admin_id'] = $adminId;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_firstName'] = $firstName;
                    $_SESSION['admin_lastName'] = $lastName;
                    $_SESSION['role'] = 'admin';
                    $_SESSION['profile_picture'] = $profilePicture; // Store profile picture

                    // Redirect to the admin dashboard
                    header('Location: admin/Dashboard.php');
                    exit();
                } else {
                    echo 'Your account is not verified.';
                }
            } else {
                echo 'Invalid username or password.';
            }
        } else {
            echo 'No such admin found.';
        }

        // Close the statement
        $stmt->close();
    } else {
        echo 'Database query error: ' . $conn->error;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role-Based Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Login Page of the Student.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style> 
    body {
    margin: 0; /* Remove default body margin */
    padding: 0; /* Remove default body padding */
    height: 100vh; /* Full viewport height */
    display: flex; /* Use Flexbox */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    background: url('image/background.jpg') center/cover no-repeat;
}

    </style>
</head>
<body>

    <header>
        <img src="image/logo.png" alt="Logo">
        <h1 class="ml-4" id="role-title">EduPerformance Tracker</h1>
    </header>

    <div class="login-container">
        <h1 class="text-center mb-4">Login to your Account</h1>
        <form id="loginForm" action="" method="post">
            <div class="form-group">
                <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="adminRegistration.php">Click to sign up</a></p>
                <p>Forgot password? <a href="forgot_passwordAdmin.php">Click here</a></p>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

   
</body>
</html>