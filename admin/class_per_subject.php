<?php
// Database connection
include 'config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for dropdowns and store in arrays
$classes = [];
$classes_result = $conn->query("SELECT class_id, class_name FROM classes");
if ($classes_result) {
    while ($row = $classes_result->fetch_assoc()) {
        $classes[] = $row;
    }
} else {
    die("Error fetching classes: " . $conn->error);
}

$school_years = [];
$school_years_result = $conn->query("SELECT school_year_id, CONCAT(school_year, ' - ', quarter) AS school_year_quarter FROM school_years");
if ($school_years_result) {
    while ($row = $school_years_result->fetch_assoc()) {
        $school_years[] = $row;
    }
} else {
    die("Error fetching school years: " . $conn->error);
}

$subjects = [];
$subjects_result = $conn->query("SELECT subject_id, subject_name FROM subjects");
if ($subjects_result) {
    while ($row = $subjects_result->fetch_assoc()) {
        $subjects[] = $row;
    }
} else {
    die("Error fetching subjects: " . $conn->error);
}

$teachers = [];
$teachers_result = $conn->query("SELECT teacher_id, CONCAT(last_name, ', ', first_name) AS teacher_name FROM teachers");
if ($teachers_result) {
    while ($row = $teachers_result->fetch_assoc()) {
        $teachers[] = $row;
    }
} else {
    die("Error fetching teachers: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Insert new record
        $class_id = $_POST['class'];
        $school_year_id = $_POST['school_year'];
        $subject_id = $_POST['subject'];
        $teacher_id = $_POST['teacher'];
        $sql = "INSERT INTO class_per_subject (teacher_id, class_id, school_year_id, subject_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiii', $teacher_id, $class_id, $school_year_id, $subject_id);
        $stmt->execute();
        if ($stmt->error) {
            die("Error inserting record: " . $stmt->error);
        }
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        // Update existing record
        $id = $_POST['id'];
        $class_id = $_POST['class'];
        $school_year_id = $_POST['school_year'];
        $subject_id = $_POST['subject'];
        $teacher_id = $_POST['teacher'];
        $sql = "UPDATE class_per_subject SET teacher_id=?, class_id=?, school_year_id=?, subject_id=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiiii', $teacher_id, $class_id, $school_year_id, $subject_id, $id);
        $stmt->execute();
        if ($stmt->error) {
            die("Error updating record: " . $stmt->error);
        }
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete record
        $id = $_POST['id'];
        $sql = "DELETE FROM class_per_subject WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->error) {
            die("Error deleting record: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Fetch data for table with error checking
$results = $conn->query("SELECT cps.id, c.class_id, c.class_name, sy.school_year_id, CONCAT(sy.school_year, ' - ', sy.quarter) AS school_year_quarter, s.subject_id, s.subject_name, t.teacher_id, CONCAT(t.last_name, ', ', t.first_name) AS teacher_name
                        FROM class_per_subject cps
                        JOIN classes c ON cps.class_id = c.class_id
                        JOIN school_years sy ON cps.school_year_id = sy.school_year_id
                        JOIN subjects s ON cps.subject_id = s.subject_id
                        JOIN teachers t ON cps.teacher_id = t.teacher_id");
if (!$results) {
    die("Error fetching records: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Create Class Per Subject</title>
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
    <div class="container pt-4">
        <!-- Form Card -->
        <div class="card mb-4">
            <div class="card-header">
                Create New Class Per Subject
            </div>
            <div class="card-body">
                <form action="class_per_subject.php" method="POST">
                    <!-- Class Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="class" class="form-label">Class</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="class" name="class" required>
                                <option value="" disabled selected>Select Class</option>
                                <?php foreach ($classes as $row) { ?>
                                    <option value="<?php echo $row['class_id']; ?>"><?php echo $row['class_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- School Year/Quarter Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="school_year" class="form-label">School Year/Quarter</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="school_year" name="school_year" required>
                                <option value="" disabled selected>Select School Year/Quarter</option>
                                <?php foreach ($school_years as $row) { ?>
                                    <option value="<?php echo $row['school_year_id']; ?>"><?php echo $row['school_year_quarter']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Subject Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="subject" class="form-label">Subject</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" disabled selected>Select Subject</option>
                                <?php foreach ($subjects as $row) { ?>
                                    <option value="<?php echo $row['subject_id']; ?>"><?php echo $row['subject_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Teacher Dropdown -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="teacher" class="form-label">Teacher</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select" id="teacher" name="teacher" required>
                                <option value="" disabled selected>Select Teacher</option>
                                <?php foreach ($teachers as $row) { ?>
                                    <option value="<?php echo $row['teacher_id']; ?>"><?php echo $row['teacher_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="create" class="btn btn-primary">Add</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-header">
                Class Per Subject List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
    
                                <th scope="col">Class</th>
                                <th scope="col">School Year/Quarter</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Teacher</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="classTable">
                            <?php while ($row = $results->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['class_name']; ?></td>
                                    <td><?php echo $row['school_year_quarter']; ?></td>
                                    <td><?php echo $row['subject_name']; ?></td>
                                    <td><?php echo $row['teacher_name']; ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-class="<?php echo $row['class_id']; ?>"
                                            data-school-year="<?php echo $row['school_year_id']; ?>"
                                            data-subject="<?php echo $row['subject_id']; ?>"
                                            data-teacher="<?php echo $row['teacher_id']; ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <form action="class_per_subject.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
                <h5 class="modal-title" id="editModalLabel">Edit Class Per Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="class_per_subject.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <!-- Class Dropdown -->
                    <div class="mb-3">
                        <label for="editClass" class="form-label">Class</label>
                        <select class="form-select" id="editClass" name="class" required>
                            <?php foreach ($classes as $row) { ?>
                                <option value="<?php echo $row['class_id']; ?>"><?php echo $row['class_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <!-- School Year/Quarter Dropdown -->
                    <div class="mb-3">
                        <label for="editSchoolYear" class="form-label">School Year/Quarter</label>
                        <select class="form-select" id="editSchoolYear" name="school_year" required>
                            <?php foreach ($school_years as $row) { ?>
                                <option value="<?php echo $row['school_year_id']; ?>"><?php echo $row['school_year_quarter']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <!-- Subject Dropdown -->
                    <div class="mb-3">
                        <label for="editSubject" class="form-label">Subject</label>
                        <select class="form-select" id="editSubject" name="subject" required>
                            <?php foreach ($subjects as $row) { ?>
                                <option value="<?php echo $row['subject_id']; ?>"><?php echo $row['subject_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <!-- Teacher Dropdown -->
                    <div class="mb-3">
                        <label for="editTeacher" class="form-label">Teacher</label>
                        <select class="form-select" id="editTeacher" name="teacher" required>
                            <?php foreach ($teachers as $row) { ?>
                                <option value="<?php echo $row['teacher_id']; ?>"><?php echo $row['teacher_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
                

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js"></script>
<script>
    // Populate Edit Modal
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var class_id = button.getAttribute('data-class');
        var school_year_id = button.getAttribute('data-school-year');
        var subject_id = button.getAttribute('data-subject');
        var teacher_id = button.getAttribute('data-teacher');
        var form = editModal.querySelector('form');
        form.querySelector('#editId').value = id;
        form.querySelector('#editClass').value = class_id;
        form.querySelector('#editSchoolYear').value = school_year_id;
        form.querySelector('#editSubject').value = subject_id;
        form.querySelector('#editTeacher').value = teacher_id;
    });
</script>

</body>
</html>
