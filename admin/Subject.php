<?php
// Database connection
include 'config.php';

// Add Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject = $_POST['subject'];

    $sql = "INSERT INTO subjects (subject_name) VALUES ('$subject')";
    if ($conn->query($sql) === TRUE) {
        echo "New subject created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Edit Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_subject'])) {
    $subject_id = $_POST['subject_id'];
    $subject_name = $_POST['subject_name'];

    $sql = "UPDATE subjects SET subject_name = '$subject_name' WHERE subject_id = $subject_id";
    if ($conn->query($sql) === TRUE) {
        echo "Subject updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Delete Subject
if (isset($_GET['delete_subject'])) {
    $subject_id = $_GET['delete_subject'];
    $sql = "DELETE FROM subjects WHERE subject_id = $subject_id";
    if ($conn->query($sql) === TRUE) {
        echo "Subject deleted successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch Subjects
$sql = "SELECT * FROM subjects";
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
        <div class="container pt-5">
            <div class="card">
                <div class="card-header">
                    Manage Subjects
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="add_subject" value="1">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="subject" class="form-label">Subject</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                        </div>

                        <button type="reset" class="btn btn-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>

                    <div class="mt-4">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Subject</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0) : ?>
                                        <?php while ($row = $result->fetch_assoc()) : ?>
                                            <tr>
                                                <td><?php echo $row['subject_id']; ?></td>
                                                <td><?php echo $row['subject_name']; ?></td>
                                                <td>
                                                    <button class="btn btn-info btn-sm editBtn" data-id="<?php echo $row['subject_id']; ?>" data-name="<?php echo $row['subject_name']; ?>">Edit</button>
                                                    <a href="?delete_subject=<?php echo $row['subject_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="3">No subjects found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="edit_subject" value="1">
                        <input type="hidden" id="editSubjectId" name="subject_id">
                        <div class="mb-3">
                            <label for="editSubjectName" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="editSubjectName" name="subject_name" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Subject</button>
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
                var subjectId = $(this).data('id');
                var subjectName = $(this).data('name');

                $('#editSubjectId').val(subjectId);
                $('#editSubjectName').val(subjectName);

                $('#editSubjectModal').modal('show');
            });
        });
    </script>
</body>

</html>
