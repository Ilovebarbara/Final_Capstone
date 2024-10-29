<!-- Sidebar -->
<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse bg-white">
        <div class="position-sticky">
    <!-- Profile Section in Sidebar -->
        <div class="profile-section text-center mb-4">
         <img src="<?php echo isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : '../image/default_profile.png'; ?>" class="rounded-circle" height="100" width="100" alt="User Profile" loading="lazy" />
            <h6 class="mt-2">
                <?php
                if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                    echo htmlspecialchars($_SESSION['first_name']) . " " . htmlspecialchars($_SESSION['last_name']);
                } else {
                    echo "Guest";
                }
                ?>
            </h6>
        </div>

        <!-- Sidebar Links -->
        <div class="list-group list-group-flush mx-3 mt-4">
            <a href="dashboard.php" class="list-group-item list-group-item-action py-2 ripple" aria-current="true">
                <i class="bi bi-house-door me-3"></i><span> Dashboard</span>
            </a>
            <a href="Grades.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="bi bi-book me-3"></i><span>Grades</span>
            </a>
            <a href="Attendance.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="bi bi-calendar-check me-3"></i><span>Attendance</span>
            </a>
            <a href="Profile.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="bi bi-calendar-check me-3"></i><span>Profile</span>
            </a>
        </div>
    </div>
</nav>

<!-- Navbar -->
<nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <!-- Sidebar Toggler Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Branding -->
        <div class="d-flex align-items-center">
            <a class="navbar-brand" href="#">
                <img src="../image/logo.png" height="40" alt="Caniogan High School" loading="lazy" />
            </a>
            <h2 class="mb-0" style="font-size: 1rem;">𝒞𝒶𝓃𝒾𝑜𝑔𝒶𝓃 𝐻𝐼𝑔𝒽 𝒮𝒸𝒽𝑜𝑜𝓁</h2>
        </div>
        <!-- Right Side Icons and Logout -->
        <ul class="navbar-nav ms-auto d-flex align-items-center">
            <li class="nav-item me-2">
                <a class="nav-link" href="#">
                    <i class="fas fa-bell"></i>
                </a>
            </li>
            <li class="nav-item me-2">
                <a class="nav-link" href="#">
                    <i class="fas fa-fill-drip"></i>
                </a>
            </li>
            <li class="nav-item me-2">
                <a class="nav-link" href="#">
                    <i class="fab fa-github"></i>
                </a>
            </li>
            <!-- Logout Button -->
            <li class="nav-item">
                <a class="nav-link" href="../logoutPage.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
<style>
        .profile-section img {
        border: 5px solid green;
        }
</style>