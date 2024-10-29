<?php
session_start();

if (isset($_SESSION['admin_firstName']) && isset($_SESSION['admin_firstName'])) {
    $adminName = htmlspecialchars($_SESSION['admin_firstName'] . ' ' . $_SESSION['admin_lastName']);
} else {
    $adminName = 'Guest'; // Default name if the user is not logged in
}
?>
<!-- Sidebar -->
<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse bg-white">
  <div class="position-sticky">
   <!-- Profile Section -->
   <div class="text-center my-4">
      <img src="<?php echo isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : '../image/profile.png'; ?>" class="rounded-circle mb-2" height="80" alt="User Profile" loading="lazy" />
      <div class="text-center">
        <span style="font-size: 1rem; font-weight: bold;"><?php echo $adminName; ?></span>
      </div>
    </div>
      <!-- Navigation Links -->
      <div class="list-group list-group-flush mx-2">
      <a href="dashboard.php" class="list-group-item list-group-item-action py-1 ripple" aria-current="true">
        <i class="bi bi-house-door me-3"></i><span>Dashboard</span>
      </a>
      <a href="profile.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-person-circle me-3"></i><span>Profile</span>
      </a>
      <a href="class.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-grid me-3"></i><span>Class</span>
      </a>
      <a href="subject.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-book me-3"></i><span>Subject</span>
      </a>
      <a href="school_year.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-calendar me-3"></i><span>School Year</span>
      </a>
      <a href="class_per_subject.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-file-earmark-text me-3"></i><span>Class per Subject</span>
      </a>
      <a href="Student.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-person-badge me-3"></i><span>Students</span>
      </a>
      <a href="Teacher.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-person-badge me-3"></i><span>Teachers</span>
      </a>
      <a href="user_creation.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-people me-3"></i><span>Accounts</span>
      </a>
      <a href="reports.php" class="list-group-item list-group-item-action py-1 ripple">
        <i class="bi bi-bar-chart me-3"></i><span>Reports</span>
      </a>
    </div>
  </div>
</nav>



<!-- Main Navbar -->
<nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a class="navbar-brand" href="#">
                <img src="../image/logo.png" height="40" alt="Caniogan High School" loading="lazy" />
            </a>
            <h2 class="mb-0" style="font-size: 1rem;">𝒞𝒶𝓃𝒾𝑜𝑔𝒶𝓃 𝐻𝐼𝑔𝒽 𝒮𝒸𝒽𝑜𝑜𝓁</h2>
        </div>
        <!-- Logout Button -->
        <div>
            <a href="../logoutAdmin.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

<!-- Optional CSS for styling -->
<style>
/* Make profile picture circular */
#sidebarMenu .text-center img {
  width: 80px;
  height: 80px;
  border-radius: 50%; /* Ensure the picture is circular */
  margin-bottom: 10px;
}

#sidebarMenu .text-center span {
  font-size: 1rem;
  font-weight: bold;
}

#main-navbar .btn-outline-danger {
  font-size: 0.875rem;
}
</style>


<!-- Toggler Button -->
<button class="btn toggler-btn" type="button" data-bs-toggle="collapse" data-bs-target="#contentContainer" aria-expanded="false" aria-controls="contentContainer">
    <i class="fa fa-toggle-down"></i>
</button>

<!-- Collapsible Container -->
<div class="container collapse" id="contentContainer" style="margin-top: 10px;">
    <div class="d-flex flex-column justify-content-center">
        <div class="row g-1 text-center">
            <div class="col-12 mt-2">
                <a href="dashboard.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-dashboard fs-2" aria-hidden="true"></i><br>
                    Dashboard
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="class.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-cubes fs-2" aria-hidden="true"></i><br>
                    Class
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="subject.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-book fs-2" aria-hidden="true"></i><br>
                    Subject
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="school_year.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-calendar fs-2" aria-hidden="true"></i><br>
                    School Year
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="class_per_subject.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-sitemap fs-2" aria-hidden="true"></i><br>
                    Class per Subject
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="student.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-graduation-cap fs-2" aria-hidden="true"></i><br>
                    Student
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="teacher.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-user-md fs-2" aria-hidden="true"></i><br>
                    Teacher
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="lobby.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-users fs-2" aria-hidden="true"></i><br>
                    Lobby
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="reports.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-bar-chart fs-2" aria-hidden="true"></i><br>
                    Reports
                </a>
            </div>
            <div class="col-12 mt-2">
                <a href="user_creation.php" class="btn btn-dark w-100 py-2 fs-5">
                    <i class="fa fa-users fs-2" aria-hidden="true"></i><br>
                    Accounts
                </a>
            </div>
        </div>
    </div>
</div>
