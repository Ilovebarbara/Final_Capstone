<?php
// Start the session to store role information
session_start();

// Check if a role is selected
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    $_SESSION['role'] = $role;

    // Redirect based on role
    if ($role === 'admin') {
        header("Location: loginAdmin.php"); // Admin login page
        exit();
    } elseif ($role === 'student') {
        header("Location: loginPage.php"); // Student login page
        exit();
    } elseif ($role === 'teacher') {
        header("Location: loginPage.php"); // Teacher login page
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootstrap Header</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/User Role Page.css">
  <style>
  body {
    background-image: url('image/background.jpg');
  }
  </style>
</head>
<body>
  <div class="text-center mb-4">
    <a class="navbar-brand" href="#">
      <img src="image/logo.png" alt="Logo" style="height: 200px;">
    </a>
  </div>
  
  <div class="container text-center bg-dark text-white p-5 rounded" style="max-width: 600px; opacity: 0.8;">
    <h1 class="display-4">EduPerformance Tracker</h1>
    <div class="row mt-4">
      <div class="col-12 col-md-6 mb-3">
        <form action="" method="get">
          <input type="hidden" name="role" value="student">
          <button type="submit" class="btn btn-custom btn-lg btn-block">Teacher/Student Login</button>
        </form>
      </div>
      <div class="col-12 col-md-6 mb-3">
        <form action="" method="get">
          <input type="hidden" name="role" value="admin">
          <button type="submit" class="btn btn-custom btn-lg btn-block">Admin</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>