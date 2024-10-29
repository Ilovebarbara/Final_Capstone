<?php 
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer setup
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to send email
function sendEmail($recipientEmail, $username, $password) {
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
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your Login Credentials';
        $mail->Body = "Hello,<br>Your account has been created.<br><strong>Username:</strong> $username<br><strong>Password:</strong> $password<br>Please log in.";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle form submission for creating a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'], $_POST['user_id'])) {
    $role = $_POST['role'];
    $user_id = $_POST['user_id'];
    
    // Fetch user details
    if ($role == 'teacher') {
        $query = "SELECT email, first_name, last_name FROM teachers WHERE teacher_id = ?";
    } else if ($role == 'student') {
        $query = "SELECT email, first_name, last_name FROM students WHERE student_id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Generate random password
        $password = bin2hex(random_bytes(8));  // 16 characters
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users table
        $insertQuery = "INSERT INTO users (username, password, role, email, teacher_id, student_id, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $teacher_id = ($role == 'teacher') ? $user_id : NULL;
        $student_id = ($role == 'student') ? $user_id : NULL;
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $stmt->bind_param('ssssiiss', $user_id, $hashedPassword, $role, $user['email'], $teacher_id, $student_id, $first_name, $last_name);
        $stmt->execute();

        // Send email
        sendEmail($user['email'], $user_id, $password);
        
        header("Location: user_creation.php");
    } else {
        echo "User not found.";
    }
    exit();
}

// Handle the AJAX request for user data
if (isset($_POST['role'])) {
    $role = $_POST['role'];
    $query = '';

    if ($role == 'teacher') {
        $query = "SELECT teacher_id AS user_id, first_name, last_name, email FROM teachers";
    } else if ($role == 'student') {
        $query = "SELECT student_id AS user_id, first_name, last_name, email FROM students";
    }

    $result = $conn->query($query);
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user_id'])) {
    $deleteUserId = $_POST['delete_user_id'];
    
    $deleteQuery = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('s', $deleteUserId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

// Fetch all users for display in the table
$usersResult = $conn->query("SELECT * FROM users");
$usersList = $usersResult->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Creation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <header>
        <?php include "inc/navbar.php"; ?>
    </header>
    <main class="pt-5" style="margin-top: -40px;">
        <div class="container mt-5">
            <!-- User Creation Form -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card h-100 border">
                        <div class="card-header text-dark">Create User</div>
                        <div class="card-body">
                            <form method="POST" action="" class="mt-4">
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="role" class="form-label mb-0">Role</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-select" name="role" id="role" required>
                                            <option value="" disabled selected>Select Role</option>
                                            <option value="student">Student</option>
                                            <option value="teacher">Teacher</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="user_id" class="form-label mb-0">User</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-select" name="user_id" id="user_id" required>
                                            <option value="" disabled selected>Select User</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="email" class="form-label mb-0">Email</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required readonly>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="first_name" class="form-label mb-0">First Name</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="last_name" class="form-label mb-0">Last Name</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" required>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Create User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table to Display Created Accounts with Delete Option -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card h-100 border">
                        <div class="card-header text-dark">Created Accounts</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($usersList)) : ?>
                                            <?php foreach ($usersList as $user) : ?>
                                                <tr>
                                                    <td><?= $user['username']; ?></td>
                                                    <td><?= $user['email']; ?></td>
                                                    <td><?= ucfirst($user['role']); ?></td>
                                                    <td><?= $user['first_name']; ?></td>
                                                    <td><?= $user['last_name']; ?></td>
                                                    <td>
                                                        <button class="btn btn-danger btn-sm delete-user" data-username="<?= $user['username']; ?>">Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No accounts created yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Populate user dropdown based on role
        $('#role').on('change', function() {
            var role = $(this).val();
            if (role) {
                $.post('user_creation.php', {role: role}, function(data) {
                    var users = JSON.parse(data);
                    var options = '<option value="" disabled selected>Select User</option>';
                    users.forEach(function(user) {
                        options += '<option value="' + user.user_id + '" data-email="' + user.email + '" data-first_name="' + user.first_name + '" data-last_name="' + user.last_name + '">' + user.first_name + ' ' + user.last_name + '</option>';
                    });
                    $('#user_id').html(options);
                });
            }
        });

        // Populate email, first name, and last name when user is selected
        $('#user_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var email = selectedOption.data('email');
            var firstName = selectedOption.data('first_name');
            var lastName = selectedOption.data('last_name');
            
            $('#email').val(email);
            $('#first_name').val(firstName);
            $('#last_name').val(lastName);
        });

        // Handle user deletion
        $('.delete-user').on('click', function() {
            var username = $(this).data('username');
            if (confirm('Are you sure you want to delete this user?')) {
                $.post('user_creation.php', {delete_user_id: username}, function(response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error deleting user.');
                    }
                });
            }
        });
    </script>
</body>
</html>
