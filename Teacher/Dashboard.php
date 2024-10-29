<?php
session_start();
$teacher_id = $_SESSION['teacher_id'];

// Include database connection
include 'config.php';

// Fetch necessary data for dropdowns
function getSchoolYears() {
    global $conn;
    $sql = "SELECT * FROM school_years";
    return $conn->query($sql);
}

function getClassesByTeacher($teacher_id) {
    global $conn;
    $sql = "SELECT DISTINCT c.class_id, c.class_name 
            FROM classes c 
            JOIN class_per_subject cps ON c.class_id = cps.class_id 
            WHERE cps.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getSubjectsByTeacher($teacher_id) {
    global $conn;
    $sql = "SELECT DISTINCT s.subject_id, s.subject_name 
            FROM subjects s 
            JOIN class_per_subject cps ON s.subject_id = cps.subject_id 
            WHERE cps.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getStudentsInClass($class_id, $school_year_id) {
    global $conn;
    $sql = "SELECT s.student_id, CONCAT(s.first_name, ' ', s.last_name) AS name 
            FROM students s 
            JOIN student_class_years sc ON s.student_id = sc.student_id 
            WHERE sc.class_id = ? AND sc.school_year_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $class_id, $school_year_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Ranking Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
</head>
<body>

<header class="fixed-top bg-light shadow-sm">
    <?php include "inc/Navbar.php"; ?>
</header>   

<main class="col-md-9 col-lg-10 ml-sm-auto px-md-4 py-4" style="margin-top: 85px;">
    <h1>Dashboard</h1>

    <!-- Box Container for Filters -->
    <div class="p-4 border rounded bg-light shadow-sm">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="schoolYear">School Year</label>
                <select id="schoolYear" class="form-control">
                    <option value="">Select School Year</option>
                    <?php $years = getSchoolYears(); while($year = $years->fetch_assoc()): ?>
                        <option value="<?php echo $year['school_year_id']; ?>"><?php echo $year['school_year']; ?> - <?php echo $year['quarter']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="classSelect">Class</label>
                <select id="classSelect" class="form-control">
                    <option value="">Select Class</option>
                    <?php $classes = getClassesByTeacher($teacher_id); while($class = $classes->fetch_assoc()): ?>
                        <option value="<?php echo $class['class_id']; ?>"><?php echo $class['class_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="subjectSelect">Subject</label>
                <select id="subjectSelect" class="form-control">
                    <option value="">Select Subject</option>
                    <?php $subjects = getSubjectsByTeacher($teacher_id); while($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $subject['subject_id']; ?>"><?php echo $subject['subject_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button id="loadData" class="btn btn-primary">Load Data</button>
    </div>

    <!-- Chart Container -->
    <div id="chartContainer" style="height: 400px; margin-top: 20px;"></div>
    <!-- Pagination Controls -->
<div id="paginationControls" class="mt-4">
    <button id="prevPage" class="btn btn-secondary" disabled>Previous</button>
    <span id="pageInfo" class="mx-2">Page 1</span>
    <button id="nextPage" class="btn btn-secondary">Next</button>
</div>
    <div id="attendanceStatusContainer" class="mt-4"></div>
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 0;
    const studentsPerPage = 10; // Number of students to show per page
    let studentData = [];

    $('#loadData').click(function() {
        const schoolYearId = $('#schoolYear').val();
        const classId = $('#classSelect').val();
        const subjectId = $('#subjectSelect').val();

        if (schoolYearId && classId && subjectId) {
            $.ajax({
                url: 'fetch_student_ranking.php',
                method: 'POST',
                data: {
                    school_year_id: schoolYearId,
                    class_id: classId,
                    subject_id: subjectId
                },
                success: function(data) {
                    studentData = JSON.parse(data);
                    renderChart();
                    updatePaginationControls();
                    loadAttendanceData(studentData);
                },
                error: function(err) {
                    console.error(err);
                }
            });
        } else {
            alert('Please select all fields.');
        }
    });

    function renderChart() {
        const start = currentPage * studentsPerPage;
        const end = start + studentsPerPage;
        const slicedData = studentData.slice(start, end);

        Highcharts.chart('chartContainer', {
            chart: {
                type: 'bar'
            },
            title: {
                text: 'Student Rankings'
            },
            xAxis: {
                categories: slicedData.map(student => student.name),
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Final Grade',
                    align: 'high'
                }
            },
            series: [{
                name: 'Final Grade',
                data: slicedData.map(student => parseFloat(student.final_grade))
            }]
        });
    }

    function updatePaginationControls() {
        $('#pageInfo').text(`Page ${currentPage + 1}`);
        $('#prevPage').prop('disabled', currentPage === 0);
        $('#nextPage').prop('disabled', (currentPage + 1) * studentsPerPage >= studentData.length);
    }

    $('#nextPage').click(function() {
        if ((currentPage + 1) * studentsPerPage < studentData.length) {
            currentPage++;
            renderChart();
            updatePaginationControls();
        }
    });

    $('#prevPage').click(function() {
        if (currentPage > 0) {
            currentPage--;
            renderChart();
            updatePaginationControls();
        }
    });

    function loadAttendanceData(students) {
        const attendanceStatusContainer = $('#attendanceStatusContainer');
        attendanceStatusContainer.empty(); // Clear previous data

        students.forEach(student => {
            $.ajax({
                url: 'fetch_attendance_data.php',
                method: 'POST',
                data: { student_id: student.student_id, school_year_id: $('#schoolYear').val() },
                success: function(attendanceData) {
                    const attendanceResult = JSON.parse(attendanceData);
                    renderAttendancePieChart(student, attendanceResult);
                }
            });
        });
    }

    function renderAttendancePieChart(student, attendance) {
        const attendanceStatusContainer = $('#attendanceStatusContainer');
        const pieChartId = `pieChart_${student.student_id}`; // Use backticks here

        // Create a new div for each student's pie chart
        attendanceStatusContainer.append(`<div id="${pieChartId}" style="width: 250px; height: 300px; margin: 20px; display: inline-block;"></div>`); // Use backticks here

        // Prepare data for the pie chart
        const presentCount = attendance.present.length;
        const absentCount = attendance.absent.length;
        const lateCount = attendance.late.length;

        // Define the data for the main pie chart
        const data = [
            {
                name: 'Present',
                y: presentCount,
                drilldown: 'present'
            },
            {
                name: 'Absent',
                y: absentCount,
                drilldown: 'absent'
            },
            {
                name: 'Late',
                y: lateCount,
                drilldown: 'late'
            }
        ];

        // Define drilldown data with counts for each month
        const drilldownData = [
            {
                id: 'present',
                name: 'Present Status by Month',
                data: countAttendanceByMonth(attendance.present)
            },
            {
                id: 'absent',
                name: 'Absent Status by Month',
                data: countAttendanceByMonth(attendance.absent)
            },
            {
                id: 'late',
                name: 'Late Status by Month',
                data: countAttendanceByMonth(attendance.late)
            }
        ];

        // Main chart configuration
        Highcharts.chart(pieChartId, {
            chart: {
                type: 'pie'
            },
            title: {
                text: `${student.name}'s Attendance Status`,
                style: {
                    fontSize: '15px' // Adjust the font size as needed
                }
            },
            series: [{
                name: 'Attendance',
                colorByPoint: true,
                data: data
            }],
            drilldown: {
                series: drilldownData
            },
            tooltip: {
                shared: true
            }
        });
    }

    function countAttendanceByMonth(attendanceArray) {
        const monthlyCount = {};
        attendanceArray.forEach(date => {
            const month = new Date(date).toLocaleString('default', { month: 'long' });
            if (monthlyCount[month]) {
                monthlyCount[month]++;
            } else {
                monthlyCount[month] = 1;
            }
        });

        return Object.entries(monthlyCount).map(([month, count]) => [month, count]);
    }
});
</script>

</body>
</html>
