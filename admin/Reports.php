<?php
// Database connection
include 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch classes, school years, and categories for dropdowns
$classQuery = "SELECT class_id, class_name FROM classes";
$classResult = $conn->query($classQuery);

// Fetch school years with quarters for dropdown
$schoolYearQuery = "SELECT school_year_id, CONCAT(school_year, ' (', quarter, ')') AS school_year_display FROM school_years";
$schoolYearResult = $conn->query($schoolYearQuery);

// Fetch soft skills and hard skills subjects (assumed IDs)
$softSkillSubjects = [3, 5, 6, 9]; // Replace with actual soft skill subject IDs
$hardSkillSubjects = [2, 8, 10]; // Replace with actual hard skill subject IDs

// Function to calculate average grade for subjects
function calculateAverageGrade($conn, $subjectIds, $schoolYearId, $classId) {
    $subjectIdsString = implode(',', $subjectIds);
    $sql = "SELECT AVG(final_grade) as average FROM final_grades WHERE subject_id IN ($subjectIdsString) AND school_year_id = $schoolYearId AND class_id = $classId";
    $result = $conn->query($sql);
    return round($result->fetch_assoc()['average'] ?? 0, 2);
}

// Function to get distribution of grades for each subject
function getGradeDistribution($conn, $subjectIds, $schoolYearId, $classId) {
    $distribution = [];
    foreach ($subjectIds as $subjectId) {
        $grades = [
            '0-50' => 0,
            '51-74' => 0,
            '75-79' => 0,
            '80-89' => 0,
            '90-100' => 0,
        ];

        $sql = "SELECT final_grade FROM final_grades WHERE subject_id = $subjectId AND school_year_id = $schoolYearId AND class_id = $classId";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $grade = $row['final_grade'];
            if ($grade >= 0 && $grade <= 50) $grades['0-50']++;
            elseif ($grade >= 51 && $grade <= 74) $grades['51-74']++;
            elseif ($grade >= 75 && $grade <= 79) $grades['75-79']++;
            elseif ($grade >= 80 && $grade <= 89) $grades['80-89']++;
            elseif ($grade >= 90 && $grade <= 100) $grades['90-100']++;
        }
        $distribution[$subjectId] = $grades;
    }
    return $distribution;
}

// Initialize variables
$softSkillAverage = 0;
$hardSkillAverage = 0;
$softSkillDistribution = [];
$hardSkillDistribution = [];
$selectedType = 'soft';
$selectedClassId = '';
$selectedSchoolYearId = '';

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedClassId = $_POST['class_id'];
    $selectedSchoolYearId = $_POST['school_year_id'];
    $selectedType = $_POST['subject_type'] ?? 'soft'; // default to soft skills

    // Calculate averages
    $softSkillAverage = calculateAverageGrade($conn, $softSkillSubjects, $selectedSchoolYearId, $selectedClassId);
    $hardSkillAverage = calculateAverageGrade($conn, $hardSkillSubjects, $selectedSchoolYearId, $selectedClassId);

    // Get grade distributions
    $softSkillDistribution = getGradeDistribution($conn, $softSkillSubjects, $selectedSchoolYearId, $selectedClassId);
    $hardSkillDistribution = getGradeDistribution($conn, $hardSkillSubjects, $selectedSchoolYearId, $selectedClassId);
}
?>
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
    <!-- Highcharts JS -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <!-- Optional: Highcharts modules (e.g., exporting) -->
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: auto;
        }
        .form-container {
            width: 80%;
            margin: 20px auto;
        }
        .form-container label {
            display: block;
            margin-top: 10px;
        }
        .form-container select, .form-container button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        .form-container button {
            margin-top: 15px;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body style="padding-top: 80px;">
    <header>
        <?php include "inc/navbar.php"; ?>
    </header>
    <main class="pt-5" style="margin-top: -90px;">
        <div class="container mt-5">
            <div class="form-container">
                <form method="POST" action="">
                    <label for="class_id">Select Class:</label>
                    <select name="class_id" id="class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php while ($row = $classResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['class_id']) ?>" <?= ($row['class_id'] == $selectedClassId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="school_year_id">Select School Year:</label>
                    <select name="school_year_id" id="school_year_id" required>
                        <option value="">-- Select School Year --</option>
                        <?php while ($row = $schoolYearResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['school_year_id']) ?>" <?= ($row['school_year_id'] == $selectedSchoolYearId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['school_year_display']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="subject_type">Select Subject Type:</label>
                    <select name="subject_type" id="subject_type">
                        <option value="soft" <?= ($selectedType === 'soft') ? 'selected' : '' ?>>Soft Skills</option>
                        <option value="hard" <?= ($selectedType === 'hard') ? 'selected' : '' ?>>Hard Skills</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
            </div>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="chart-container mt-5">
                    <div id="performanceChart"></div>
                    <div id="subjectChart" class="mt-5"></div>
                </div>

                <div class="table-responsive mt-5">
                    <h4>Grade Distribution Table for <?= ucfirst($selectedType) ?> Skills</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>0-50%</th>
                                <th>51-74%</th>
                                <th>75-79%</th>
                                <th>80-89%</th>
                                <th>90-100%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Function to get subject names
                            function getSubjectNames($conn, $subjectIds) {
                                $names = [];
                                $idList = implode(',', $subjectIds);
                                $sql = "SELECT subject_id, subject_name FROM subjects WHERE subject_id IN ($idList)";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    $names[$row['subject_id']] = $row['subject_name'];
                                }
                                return $names;
                            }

                            $softSkillSubjectNames = getSubjectNames($conn, $softSkillSubjects);
                            $hardSkillSubjectNames = getSubjectNames($conn, $hardSkillSubjects);

                            if ($selectedType === 'soft') {
                                $currentDistribution = $softSkillDistribution;
                                $currentSubjectNames = $softSkillSubjectNames;
                            } else {
                                $currentDistribution = $hardSkillDistribution;
                                $currentSubjectNames = $hardSkillSubjectNames;
                            }

                            foreach ($currentDistribution as $subjectId => $grades): ?>
                                <tr>
                                    <td><?= isset($currentSubjectNames[$subjectId]) ? htmlspecialchars($currentSubjectNames[$subjectId]) : "Subject $subjectId" ?></td>
                                    <td><?= $grades['0-50'] ?></td>
                                    <td><?= $grades['51-74'] ?></td>
                                    <td><?= $grades['75-79'] ?></td>
                                    <td><?= $grades['80-89'] ?></td>
                                    <td><?= $grades['90-100'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                // Performance Chart - Average Grades
                Highcharts.chart('performanceChart', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Average Grades'
                    },
                    xAxis: {
                        categories: ['Soft Skills', 'Hard Skills'],
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Average Grade'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat:
                            '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                            '<td style="padding:0"><b>{point.y:.2f}</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            borderWidth: 0
                        }
                    },
                    series: [{
                        name: 'Average Grade',
                        data: [<?= $softSkillAverage ?>, <?= $hardSkillAverage ?>],
                        color: 'rgba(54, 162, 235, 0.7)'
                    }]
                });

                // Prepare data for Grade Distribution Chart
                Highcharts.chart('subjectChart', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Grade Distribution for <?= ucfirst($selectedType) ?> Skills'
                    },
                    xAxis: {
                        categories: <?= json_encode(array_values($currentSubjectNames)) ?>, // Use subject names
                        title: {
                            text: 'Subjects'
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Number of Students'
                        },
                    },
                    legend: {
                        align: 'right',
                        x: -30,
                        verticalAlign: 'top',
                        y: 25,
                        floating: true,
                        backgroundColor:
                            Highcharts.defaultOptions.legend.backgroundColor || 'white',
                        borderColor: '#CCC',
                        borderWidth: 1,
                        shadow: false
                    },
                    tooltip: {
                        headerFormat: '<b>{point.x}</b><br/>',
                        pointFormat: '{series.name}: {point.y}'
                    },
                    plotOptions: {
                        column: {
                            stacking: null,
                            dataLabels: {
                                enabled: false
                            }
                        }
                    },
                    series: [
                        { name: '0-50%', data: <?= json_encode(array_column($currentDistribution, '0-50')) ?> },
                        { name: '51-74%', data: <?= json_encode(array_column($currentDistribution, '51-74')) ?> },
                        { name: '75-79%', data: <?= json_encode(array_column($currentDistribution, '75-79')) ?> },
                        { name: '80-89%', data: <?= json_encode(array_column($currentDistribution, '80-89')) ?> },
                        { name: '90-100%', data: <?= json_encode(array_column($currentDistribution, '90-100')) ?> }
                    ]
                });


            <?php endif; ?>
        });
    </script>
</body>
</html>
