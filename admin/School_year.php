<?php
include 'config.php'; // Ensure you include your database configuration here

// Handle Add Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_school_year'])) {
    $school_year = $_POST['school_year'];
    $quarter = $_POST['quarter'];

    $sql = "INSERT INTO school_years (school_year, quarter) VALUES ('$school_year', '$quarter')";
    if ($conn->query($sql) === TRUE) {
        header('Location: School_year.php'); // Redirect to the same page to prevent form resubmission
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Edit Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_school_year'])) {
    $school_year_id = $_POST['school_year_id'];
    $school_year = $_POST['school_year'];
    $quarter = $_POST['quarter'];

    $sql = "UPDATE school_years SET school_year = '$school_year', quarter = '$quarter' WHERE school_year_id = $school_year_id";
    if ($conn->query($sql) === TRUE) {
        header('Location: School_year.php'); // Redirect to the same page
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Delete Request
if (isset($_GET['delete_school_year'])) {
    $school_year_id = $_GET['delete_school_year'];
    $sql = "DELETE FROM school_years WHERE school_year_id = $school_year_id";
    if ($conn->query($sql) === TRUE) {
        header('Location: School_year.php'); // Redirect to the same page
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch School Years
$sql = "SELECT * FROM school_years";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage School Year & Quarter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="padding-top: 80px;">
    <header>
        <?php include "inc/navbar.php"; ?>
    </header>

    <main class="pt-5" style="margin-top: -90px;">
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <div class="card h-100 border">
                        <div class="card-header">
                            <h5>Manage School Year & Quarter</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">You can add, edit, or delete the school year and quarter from the database.</p>

                            <!-- Add New School Year & Quarter -->
                            <form method="POST" action="" class="mt-4">
                                <input type="hidden" name="add_school_year" value="1">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="schoolYear" class="form-label">School Year</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="school_year" id="schoolYear" placeholder="Enter School Year (e.g., 2023-2024)" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="quarter" class="form-label">Quarter</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="quarter" id="quarter" placeholder="Enter Quarter (e.g., Q1, Q2)" required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="reset" class="btn btn-secondary me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary">Add</button>
                                </div>
                            </form>

                            <!-- Display Existing Records -->
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Academic Year / Quarter</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0) : ?>
                                            <?php while ($row = $result->fetch_assoc()) : ?>
                                                <tr>
                                                    <td><?php echo $row['school_year'] . ' - ' . $row['quarter']; ?></td>
                                                    <td>
                                                        <button class="btn btn-info btn-sm editBtn" data-id="<?php echo $row['school_year_id']; ?>" data-schoolyear="<?php echo $row['school_year']; ?>" data-quarter="<?php echo $row['quarter']; ?>">Edit</button>
                                                        <a href="School_year.php?delete_school_year=<?php echo $row['school_year_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="3">No records found.</td>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit School Year & Quarter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="editForm">
                        <input type="hidden" name="edit_school_year" value="1">
                        <input type="hidden" id="editId" name="school_year_id">
                        <div class="mb-3">
                            <label for="editSchoolYear" class="form-label">School Year</label>
                            <input type="text" class="form-control" id="editSchoolYear" name="school_year" required>
                        </div>
                        <div class="mb-3">
                            <label for="editQuarter" class="form-label">Quarter</label>
                            <input type="text" class="form-control" id="editQuarter" name="quarter" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary ms-2">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editBtn').click(function() {
                var schoolYearId = $(this).data('id');
                var schoolYear = $(this).data('schoolyear');
                var quarter = $(this).data('quarter');

                $('#editId').val(schoolYearId);
                $('#editSchoolYear').val(schoolYear);
                $('#editQuarter').val(quarter);

                $('#editModal').modal('show');
            });
        });
    </script>
</body>
</html>
