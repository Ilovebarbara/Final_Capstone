<?php
include 'config.php';

// Add Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $gradeLevel = $_POST['gradeLevel'];
    $section = $_POST['section'];
    $class_name = $gradeLevel . " - " . $section;

    $sql = "INSERT INTO classes (class_name) VALUES ('$class_name')";
    if ($conn->query($sql) === TRUE) {
        echo "New class created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Edit Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_class'])) {
    $class_id = $_POST['class_id'];
    $gradeLevel = $_POST['gradeLevel'];
    $section = $_POST['section'];
    $class_name = $gradeLevel . " - " . $section;

    $sql = "UPDATE classes SET class_name = '$class_name' WHERE class_id = $class_id";
    if ($conn->query($sql) === TRUE) {
        echo "Class updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Delete Class
if (isset($_GET['delete_class'])) {
    $class_id = $_GET['delete_class'];
    $sql = "DELETE FROM classes WHERE class_id = $class_id";
    if ($conn->query($sql) === TRUE) {
        echo "Class deleted successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch Classes
$sql = "SELECT * FROM classes";
$result = $conn->query($sql);
?>

<!-- HTML/PHP below -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <header>
        <?php include "inc/navbar.php"; ?>
    </header>

    <main class="pt-5">
        <div class="container mt-5">
            <!-- Manage Classes -->
            <div class="row">
                <div class="col">
                    <div class="card h-100 border">
                        <!-- Card Header -->
                        <div class="card-header text-dark">
                            Manage Classes
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <p class="card-text">The administrator can manage the class grade section. You can create, modify, and delete classes below.</p>

                            <form method="POST" action="" class="mt-4">
                                <input type="hidden" name="add_class" value="1">
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="gradeLevel" class="form-label mb-0">Grade Level</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="gradeLevel" id="gradeLevel" placeholder="Enter Grade Level" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="section" class="form-label mb-0">Section</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="section" id="section" placeholder="Enter Section" required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary">Add Class</button>
                                </div>
                            </form>

                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Class</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0) : ?>
                                            <?php while ($row = $result->fetch_assoc()) : ?>
                                                <tr>
                                                    <td><?php echo $row['class_name']; ?></td>
                                                    <td>
                                                        <button class="btn btn-info btn-sm editBtn" data-id="<?php echo $row['class_id']; ?>" data-class="<?php echo $row['class_name']; ?>">Edit</button>
                                                        <a href="?delete_class=<?php echo $row['class_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="3">No classes found.</td>
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

    <!-- Edit Class Modal -->
    <div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassModalLabel">Edit Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="edit_class" value="1">
                        <input type="hidden" id="editClassId" name="class_id">
                        <div class="mb-3">
                            <label for="editGradeLevel" class="form-label">Grade Level</label>
                            <input type="text" class="form-control" id="editGradeLevel" name="gradeLevel" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSection" class="form-label">Section</label>
                            <input type="text" class="form-control" id="editSection" name="section" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editBtn').click(function() {
                var classId = $(this).data('id');
                var className = $(this).data('class');
                var classDetails = className.split(" - ");

                $('#editClassId').val(classId);
                $('#editGradeLevel').val(classDetails[0]);
                $('#editSection').val(classDetails[1]);

                $('#editClassModal').modal('show');
            });
        });
    </script>
</body>

</html>
