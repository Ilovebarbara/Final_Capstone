<?php
include 'config.php'; // Database connection

// Handle the creation of a new teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    if (isset($_POST['create_teacher'])) {
        $sql = "INSERT INTO teachers (teacher_id, first_name, last_name, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $teacher_id, $first_name, $last_name, $email);

        if ($stmt->execute()) {
            header("Location: Teacher.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Handle the update of an existing teacher
    if (isset($_POST['update_teacher'])) {
        $sql = "UPDATE teachers SET first_name=?, last_name=?, email=? WHERE teacher_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $teacher_id);

        if ($stmt->execute()) {
            header("Location: Teacher.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

// Handle deletion of a teacher
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['teacher_id'])) {
    $teacher_id = $_GET['teacher_id'];
    $sql = "DELETE FROM teachers WHERE teacher_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $teacher_id);

    if ($stmt->execute()) {
        header("Location: Teacher.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch teachers
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM teachers WHERE last_name LIKE ? OR first_name LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$teachers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .search-bar {
            margin-bottom: 20px;
        }
        .btn-edit {
            background-color: #0dcaf0; 
            border-color: #0dcaf0; 
        }
        .btn-edit:hover {
            background-color: #31d2f2; 
            border-color: #31d2f2; 
        }
        .btn-edit:click{
            background-color: #31d2f2; 
            border-color: #31d2f2; 
        }
        .btn-edit::after{
            background-color: #31d2f2; 
            border-color: #31d2f2; 
        }

    </style>
</head>
<body style="padding-top: 80px;">
    <header>
        <?php include "inc/navbar.php"; ?>
    </header>

    <main class="pt-5" style="margin-top: -90px;">
        <div class="container pt-5" style="margin-top: 60px">
            <!-- Modal for Creating Teacher -->
            <div class="modal fade" id="createTeacherModal" tabindex="-1" aria-labelledby="createTeacherModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createTeacherModalLabel">Create New Teacher</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createTeacherForm" action="" method="POST">
                                <div class="mb-3">
                                    <label for="new_teacher_id" class="form-label">Teacher ID</label>
                                    <input type="text" class="form-control" id="new_teacher_id" name="teacher_id" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary" name="create_teacher">ADD</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">BACK</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Editing Teacher -->
            <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editTeacherForm" action="" method="POST">
                                <input type="hidden" id="edit_teacher_id" name="teacher_id">
                                <div class="mb-3">
                                    <label for="edit_last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary" name="update_teacher">UPDATE</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">BACK</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                   Teacher List
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTeacherModal">
                                Create Teacher
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search by name">
                                <button class="btn btn-primary" id="searchButton">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Teacher ID</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teacherTable">
                                <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $teacher['teacher_id'] ?></td>
                                    <td><?= $teacher['last_name'] ?></td>
                                    <td><?= $teacher['first_name'] ?></td>
                                    <td><?= $teacher['email'] ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm btn-edit editBtn"
                                                data-id="<?= $teacher['teacher_id'] ?>"
                                                data-lastname="<?= $teacher['last_name'] ?>"
                                                data-firstname="<?= $teacher['first_name'] ?>"
                                                data-email="<?= $teacher['email'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTeacherModal">
                                            Edit
                                        </button>
                                        <a href="Teacher.php?action=delete&teacher_id=<?= $teacher['teacher_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.editBtn').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const lastName = button.getAttribute('data-lastname');
                    const firstName = button.getAttribute('data-firstname');
                    const email = button.getAttribute('data-email');

                    document.getElementById('edit_teacher_id').value = id;
                    document.getElementById('edit_last_name').value = lastName;
                    document.getElementById('edit_first_name').value = firstName;
                    document.getElementById('edit_email').value = email;
                });
            });

            document.getElementById('searchButton').addEventListener('click', () => {
                const searchQuery = document.getElementById('searchInput').value;
                window.location.href = `?search=${searchQuery}`;
            });
        });
    </script>
</body>
</html>
