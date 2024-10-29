<?php
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        die('Passwords do not match.');
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO admins (firstName, lastName, email, username, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $firstName, $lastName, $email, $username, $hashedPassword);
    $stmt->execute();
    $stmt->close();

    $token = bin2hex(random_bytes(50));

    $stmt = $conn->prepare("INSERT INTO email_verifications (email, token) VALUES (?, ?)");
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $stmt->close();

    // Send verification email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'cristinmaygojocruz04@gmail.com';  // Your Gmail address
        $mail->Password = 'mrpsnebelljbiexa';  // Your Gmail password or app-specific password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('cristinmaygojocruz04@gmail.com', 'EduPerformance Tracker');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Account Verification';
        $mail->Body    = 'Click <a href="http://localhost/final_capstone/verify_emailAdmin.php?token=' . $token . '">here</a> to verify your email.';

        $mail->send();

        // Redirect to "Please verify your email" page after the email is sent
        header('Location: Verify_email.php');
        exit;
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
                background: url('image/background.jpg') center/cover no-repeat;
                height: 100vh;
                margin: 0; 
                display: flex;
                flex-direction: column;
                align-items: center;
                color: white; 
            }

            header {
                background-color: #5cc28c;
                padding: 20px;
                width: 100%;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1000; 
                display: flex;
                align-items: center;
                justify-content: space-between; /* Adjusted for better spacing */
            }

            header img {
                height: 100px;
            }

            header h1 {
                margin: 0; /* Remove default margin */
            }

            .login-container {
                background: rgba(33, 37, 41, 0.9); 
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
                max-width: 500px;
                width: 90%;
                margin-top: 80px; /* Adjusted margin-top */
                display: flex;
                flex-direction: column;
                align-items: center; /* Center align */
                position: relative;
                top: 50%;
                transform: translateY(-50%);
            }

            .login-container h1,
            .login-container p {
                color: white; /* Changed to white for better readability */
            }

            .login-container a {
                color: #00bcd4;
            }

            .login-container a:hover {
                color: #0288d1;
            }
               .form-group input {
                margin-bottom: 15px; 
            }

            .form-group input:last-child {
                margin-bottom: 0; 
            }


    </style>
</head>
<body>

    <header>
        <img src="image/logo.png" alt="Logo">
        <h1 class="ml-4" id="role-title">EduPerformance Tracker - Admin</h1>
    </header>

    <div class="login-container">
                <h1 class="text-center mb-4">Admin Registration</h1>
            <div class="card-body">
            <form id="registrationForm" method="POST" action="">
                    <div class="form-group">
                        <input type="text" id="firstName" name="firstName" class="form-control" placeholder="First Name" required>
                        <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Last Name" required>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email Address" required>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Confirm Password" required>
                     </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="loginAdmin.php">Click to sign in</a></p>
                    </div>
                </form>
            </div>
        
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                event.preventDefault(); // Prevent form submission
            }
        });
    </script>

</body>
</html>
