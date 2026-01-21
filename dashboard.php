<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();

$conn = getDBConnection();

// Get current active job
$currentJob = null;
$stmt = $conn->prepare("SELECT * FROM job_potong WHERE status IN ('READY', 'RUNNING') ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $currentJob = $result->fetch_assoc();
}
$stmt->close();

// Get recent jobs
$recentJobs = [];
$stmt = $conn->prepare("SELECT j.*, u.nama as user_nama FROM job_potong j LEFT JOIN users u ON j.user_id = u.id ORDER BY j.created_at DESC LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentJobs[] = $row;
}
$stmt->close();

// Get statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_job,
    SUM(CASE WHEN status = 'DONE' THEN 1 ELSE 0 END) as done_job,
    SUM(CASE WHEN status = 'RUNNING' THEN 1 ELSE 0 END) as running_job,
    SUM(potong_selesai) as total_potong
FROM job_potong");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dashboardkit.css">
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo">
                <i class="bi bi-gear-fill"></i>
                <span><?php echo APP_NAME; ?></span>
            </a>
        </div>
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="log.php">
                        <i class="bi bi-list-ul"></i>
                        <span>Log Pekerjaan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="bi bi-sliders"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn btn-link d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">Dashboard</h1>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <span><?php echo $_SESSION['username']; ?></span>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-md-inline">Logout</span>
                </a>
            </div>
        </header>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- React Dashboard Component (Auto-refresh) -->
            <div id="react-dashboard"></div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalTitle">Notifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="alertModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Local Vendor Scripts -->
    <script src="assets/vendor/react/react.production.min.js"></script>
    <script src="assets/vendor/react/react-dom.production.min.js"></script>
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboardkit.js"></script>

    <!-- React Dashboard Component (tanpa JSX) -->
    <script src="assets/js/dashboard-react.js?v=<?php echo time(); ?>"></script>
</body>

</html>