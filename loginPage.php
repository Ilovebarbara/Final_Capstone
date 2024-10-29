<?php
session_start();
include 'config.php';

// Function to validate login
function validateLogin($username, $password, $conn) {
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, username, password, role, first_login, teacher_id, student_id FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = validateLogin($username, $password, $conn);

    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_login'] = $user['first_login'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Add teacher_id or student_id to the session
        if ($user['role'] === 'teacher') {
            $_SESSION['teacher_id'] = $user['teacher_id'];

            // Fetch profile picture from the database
            $profilePictureQuery = "SELECT profile_picture FROM teachers WHERE teacher_id = ?";
            $profilePictureStmt = $conn->prepare($profilePictureQuery);
            $profilePictureStmt->bind_param("i", $user['teacher_id']);
            $profilePictureStmt->execute();
            $pictureResult = $profilePictureStmt->get_result();
            $pictureData = $pictureResult->fetch_assoc();
                        
            // Set profile picture in session
            $_SESSION['profile_picture'] = $pictureData['profile_picture'];
        } else if ($user['role'] === 'student') {
            $_SESSION['student_id'] = $user['student_id'];
            
            // Fetch profile picture from the database
            $profilePictureQuery = "SELECT profile_picture FROM students WHERE student_id = ?";
            $profilePictureStmt = $conn->prepare($profilePictureQuery);
            $profilePictureStmt->bind_param("i", $user['student_id']);
            $profilePictureStmt->execute();
            $pictureResult = $profilePictureStmt->get_result();
            $pictureData = $pictureResult->fetch_assoc();
            
            // Set profile picture in session
            $_SESSION['profile_picture'] = $pictureData['profile_picture'];
        }
        
        // Redirect based on role
        if ($user['first_login']) {
            header("Location: change_password.php");
        } else {
            if ($user['role'] === 'teacher') {
                header("Location: Teacher/Dashboard.php");
            } else if ($user['role'] === 'student') {
                header("Location: Student/Dashboard.php");
            }
        }
        exit();
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
            <p>Forgot password? <a href="forgot_password.php">Click here</a></p>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
