<?php 
include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        $student_id = $_POST['student_id'];
        $last_name = $_POST['last_name'];
        $first_name = $_POST['first_name'];
        $email = $_POST['email'];
        $date_of_birth = $_POST['date_of_birth'];

        // Check if student already exists
        $sql = "INSERT INTO students (student_id, last_name, first_name, email, date_of_birth) 
                VALUES ('$student_id', '$last_name', '$first_name', '$email', '$date_of_birth')";
        $conn->query($sql);

        // Assign student to selected class and school years
        $class_id = $_POST['class'];
        if (isset($_POST['school_years'])) {
            foreach ($_POST['school_years'] as $school_year_id) {
                $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                        VALUES ('$student_id', '$class_id', '$school_year_id')";
                $conn->query($sql);
            }
        }

    }if (isset($_POST['action']) && $_POST['action'] == 'update') {
        // Update student information
        $id = $_POST['id']; // Original ID for reference
        $student_id = $_POST['student_id']; // New student ID
        $last_name = $_POST['last_name'];
        $first_name = $_POST['first_name'];
        $email = $_POST['email'];
        $date_of_birth = $_POST['date_of_birth'];
    
        // Update student record with the new student_id
        $sql = "UPDATE students SET student_id='$student_id', last_name='$last_name', first_name='$first_name', email='$email', 
                date_of_birth='$date_of_birth' WHERE student_id='$id'";
        $conn->query($sql);
    
        // Update class-year assignments
        $conn->query("DELETE FROM student_class_years WHERE student_id='$id'");
        $class_id = $_POST['class'];
        if (isset($_POST['school_years'])) {
            foreach ($_POST['school_years'] as $school_year_id) {
                $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                        VALUES ('$student_id', '$class_id', '$school_year_id')";
                $conn->query($sql);
            }
        }
    }
    
} if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $student_id = $_GET['student_id'];

    // First, delete student class-year assignments
    $conn->query("DELETE FROM student_class_years WHERE student_id='$student_id'");

    // Then, delete the student
    $sql = "DELETE FROM students WHERE student_id='$student_id'";
    $conn->query($sql);

    // Redirect or send a response
    header('Location: ' . $_SERVER['PHP_SELF']); // or use exit(); if necessary
    exit();
}if (isset($_POST['action']) && $_POST['action'] == 'promote') {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['new_class_id']; // New grade/class ID
    $school_years = $_POST['school_years']; // The selected school years

    // Insert new record for the next grade
    foreach ($school_years as $school_year_id) {
        $sql = "INSERT INTO student_class_years (student_id, class_id, school_year_id) 
                VALUES ('$student_id', '$class_id', '$school_year_id')";
        if (!$conn->query($sql)) {
            echo "Error: " . $conn->error; // Add error checking
        }
    }
}

// Fetch students with their school years and classes
$search = $_GET['search'] ?? '';
$classFilter = $_GET['class'] ?? '';
$schoolYearFilter = $_GET['school_year'] ?? '';

$sql = "SELECT s.student_id, s.last_name, s.first_name, s.email, s.date_of_birth, 
               GROUP_CONCAT(DISTINCT CONCAT(c.class_name, ' (', sy.school_year, ')') ORDER BY sy.school_year) AS class_years
        FROM students s
        LEFT JOIN student_class_years scy ON s.student_id = scy.student_id
        LEFT JOIN classes c ON scy.class_id = c.class_id
        LEFT JOIN school_years sy ON scy.school_year_id = sy.school_year_id
        WHERE (s.last_name LIKE '%$search%' OR s.first_name LIKE '%$search%')
        AND (c.class_name LIKE '%$classFilter%' OR '$classFilter' = '')
        AND (sy.school_year LIKE '%$schoolYearFilter%' OR '$schoolYearFilter' = '')
        GROUP BY s.student_id";

$students = $conn->query($sql);

// Fetch classes and school years
$classes = $conn->query("SELECT * FROM classes");
$school_years = $conn->query("SELECT * FROM school_years");

// Function to fetch class and school year assignments for a student
function getStudentClassYears($conn, $student_id) {
    $sql = "SELECT scy.class_id, scy.school_year_id 
            FROM student_class_years scy 
            WHERE scy.student_id = '$student_id'";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Student Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .search-bar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="padding-top: 80px;">

<header>
    <?php include "inc/navbar.php"; ?>
</header>

<main class="pt-5" style="margin-top: -90px;"> 
    <div class="container pt-5" style="margin-top: 60px">
        <div class="card">

            <div class="card-header">
                Student List
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <!-- Left side: Create Student Button -->
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createStudentModal">
                            Create Student
                        </button>
                    </div>

                    <!-- Right side: Search and Filter -->
                    <div class="col-md-6">
                        <div class="input-group col-md-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name">
                            <button class="btn btn-primary" id="searchButton">Search</button>
                        </div>
                    </div>
                </div>

                <!-- Filter inputs -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <input type="text" id="classFilter" class="form-control" placeholder="Filter by Class">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="schoolYearFilter" class="form-control" placeholder="Filter by School Year">
                    </div>
                    <div class="input-group col-md-4 mt-3">
                        <button class="btn btn-primary" id="filterButton">Filter</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Student ID</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Date of Birth</th>
                                <th scope="col">Class and School Years</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTable">
                            <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?= $student['student_id'] ?></td>
                                <td><?= $student['last_name'] ?></td>
                                <td><?= $student['first_name'] ?></td>
                                <td><?= $student['email'] ?></td>
                                <td><?= $student['date_of_birth'] ?></td>
                                <td><?= $student['class_years'] ?? 'None' ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm editBtn" 
                                            data-id="<?= $student['student_id'] ?>"
                                            data-lastname="<?= $student['last_name'] ?>"
                                            data-firstname="<?= $student['first_name'] ?>"
                                            data-email="<?= $student['email'] ?>"
                                            data-dob="<?= $student['date_of_birth'] ?>"
                                            data-classes="<?= htmlspecialchars(json_encode(getStudentClassYears($conn, $student['student_id'])), ENT_QUOTES) ?>">
                                        Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm deleteBtn" 
                                            data-id="<?= $student['student_id'] ?>">
                                        Delete
                                    </button>
                                    <button class="btn btn-warning btn-sm promoteBtn" 
                                            data-id="<?= $student['student_id'] ?>">
                                        Promote
                                    </button>
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


<!-- Create Student Modal -->
<div class="modal fade" id="createStudentModal" tabindex="-1" aria-labelledby="createStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createStudentModalLabel">Create Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" name="student_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth">
                    </div>
                    <div class="mb-3">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-select" name="class" required>
                            <option value="" disabled selected>Select Class</option>
                            <?php while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="school_years" class="form-label">School Years</label>
                        <select multiple class="form-select" name="school_years[]" required>
                            <?php while ($school_year = $school_years->fetch_assoc()): ?>
                                <option value="<?= $school_year['school_year_id'] ?>"><?= $school_year['school_year'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="action" value="create">Create Student</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editStudentId"> <!-- Keep this hidden for the original ID -->
                    <div class="mb-3">
                        <label for="edit_student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" name="student_id" id="edit_student_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth" id="edit_date_of_birth">
                    </div>
                    <div class="mb-3">
                        <label for="edit_class" class="form-label">Class</label>
                        <select class="form-select" name="class" id="edit_class" required>
                            <option value="" disabled>Select Class</option>
                            <?php 
                            // Reset classes fetch
                            $classes = $conn->query("SELECT * FROM classes");
                            while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_school_years" class="form-label">School Years</label>
                        <select multiple class="form-select" name="school_years[]" id="edit_school_years" required>
                            <?php 
                            // Reset school years fetch
                            $school_years = $conn->query("SELECT * FROM school_years");
                            while ($school_year = $school_years->fetch_assoc()): ?>
                                <option value="<?= $school_year['school_year_id'] ?>"><?= $school_year['school_year'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="action" value="update">Update Student</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Promote Student Modal -->
<div class="modal fade" id="promoteStudentModal" tabindex="-1" aria-labelledby="promoteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promoteStudentModalLabel">Promote Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="promoteStudentId">
                    <div class="mb-3">
                        <label for="new_class_id" class="form-label">New Class</label>
                        <select class="form-select" name="new_class_id" id="new_class_id" required>
                            <option value="" disabled>Select Class</option>
                            <?php 
                            // Reset classes fetch
                            $classes = $conn->query("SELECT * FROM classes");
                            while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="school_years" class="form-label">School Years</label>
                        <select multiple class="form-select" name="school_years[]" required>
                            <?php 
                            // Reset school years fetch
                            $school_years = $conn->query("SELECT * FROM school_years");
                            while ($school_year = $school_years->fetch_assoc()): ?>
                                <option value="<?= $school_year['school_year_id'] ?>"><?= $school_year['school_year'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="action" value="promote">Promote Student</button>
                    </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js"></script>
<script>
document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function () {
        const row = button.closest('tr');
        document.getElementById('editStudentId').value = button.getAttribute('data-id'); // This will be the original ID for reference
        document.getElementById('edit_student_id').value = button.getAttribute('data-id'); // Populate the new ID field
        document.getElementById('edit_last_name').value = button.getAttribute('data-lastname');
        document.getElementById('edit_first_name').value = button.getAttribute('data-firstname');
        document.getElementById('edit_email').value = button.getAttribute('data-email');
        document.getElementById('edit_date_of_birth').value = button.getAttribute('data-dob');
        
        // Handle class selection
        document.getElementById('edit_class').value = button.getAttribute('data-class-id');
        
        // Handle school years (JSON parsing as before)
        const classYears = JSON.parse(button.getAttribute('data-classes'));
        const editSchoolYears = document.getElementById('edit_school_years');
        for (let option of editSchoolYears.options) {
            option.selected = classYears.includes(Number(option.value));
        }

        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        editModal.show();
    });
});

document.getElementById('filterButton').addEventListener('click', function() {
        const searchInput = document.getElementById('searchInput').value;
        const classFilter = document.getElementById('classFilter').value;
        const schoolYearFilter = document.getElementById('schoolYearFilter').value;

        // Redirect with the filters as query parameters
        const queryString = new URLSearchParams({
            search: searchInput,
            class: classFilter,
            school_year: schoolYearFilter
        }).toString();
        
        window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?' + queryString;
    });
 
        document.querySelectorAll('.editBtn').forEach(button => {
            button.addEventListener('click', function () {
                const row = button.closest('tr');
                document.getElementById('editStudentId').value = button.getAttribute('data-id');
                document.getElementById('edit_last_name').value = button.getAttribute('data-lastname');
                document.getElementById('edit_first_name').value = button.getAttribute('data-firstname');
                document.getElementById('edit_email').value = button.getAttribute('data-email');
                document.getElementById('edit_date_of_birth').value = button.getAttribute('data-dob');
                
                // Handle class selection
                document.getElementById('edit_class').value = button.getAttribute('data-class-id');
                
                // Handle school years (JSON parsing as before)
                const classYears = JSON.parse(button.getAttribute('data-classes'));
                const editSchoolYears = document.getElementById('edit_school_years');
                for (let option of editSchoolYears.options) {
                    option.selected = classYears.includes(Number(option.value));
                }

                // Show the modal
                const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
                editModal.show();
            });
        });


// Promote Student Modal - Show the modal when promote button is clicked
document.querySelectorAll('.promoteBtn').forEach(button => {
    button.addEventListener('click', function () {
        const row = button.closest('tr');
        document.getElementById('promoteStudentId').value = button.getAttribute('data-id');

        // Show the modal
        const promoteModal = new bootstrap.Modal(document.getElementById('promoteStudentModal'));
        promoteModal.show();
    });
});

    // Delete Student
    document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function () {
        const row = button.closest('tr');
        if (confirm(`Are you sure you want to delete ${row.children[2].textContent} ${row.children[3].textContent}?`)) {
            // Perform delete operation
            const studentId = button.getAttribute('data-id');
            // Use GET method instead of DELETE
            fetch(`?action=delete&student_id=${studentId}`)
                .then(response => {
                    if (response.ok) {
                        row.remove(); // This will remove the row from the table
                        alert('Student deleted successfully.');
                    } else {
                        alert('Error deleting student.');
                    }
                });
        }
    });
});

</script>

</body>
</html>
