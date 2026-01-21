<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();

$conn = getDBConnection();

// Get filter parameters
$filterJobId = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$whereClause = '';
$params = [];
$types = '';

if ($filterJobId > 0) {
    $whereClause = "WHERE l.job_id = ?";
    $params[] = $filterJobId;
    $types .= 'i';
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM log_potong l $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);
$stmt->close();

// Get logs
$query = "SELECT l.*, j.panjang_mm as job_panjang, j.jumlah_potong 
          FROM log_potong l 
          LEFT JOIN job_potong j ON l.job_id = j.id 
          $whereClause 
          ORDER BY l.waktu_potong DESC 
          LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get job list for filter
$stmt = $conn->prepare("SELECT id, panjang_mm, jumlah_potong, status, created_at FROM job_potong ORDER BY id DESC LIMIT 100");
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Pekerjaan - <?php echo APP_NAME; ?></title>
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
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="log.php">
                        <i class="bi bi-list-ul"></i>
                        <span>Log Pekerjaan</span>
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
                <h1 class="topbar-title">Log Pekerjaan</h1>
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
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-clock-history"></i> Log Detail Potong</h5>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="job_id" class="form-label">Filter Job ID</label>
                                <select class="form-select" id="job_id" name="job_id">
                                    <option value="0">Semua Job</option>
                                    <?php foreach ($jobs as $job): ?>
                                        <option value="<?php echo $job['id']; ?>" <?php echo $filterJobId == $job['id'] ? 'selected' : ''; ?>>
                                            Job #<?php echo $job['id']; ?> - <?php echo $job['panjang_mm']; ?>mm x
                                            <?php echo $job['jumlah_potong']; ?>
                                            (<?php echo getStatusText($job['status']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end mb-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel-fill"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Info -->
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle-fill"></i>
                        Menampilkan <?php echo count($logs); ?> dari <?php echo $totalRows; ?> log
                        <?php if ($filterJobId > 0): ?>
                            (Job ID: <?php echo $filterJobId; ?>)
                        <?php endif; ?>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Job ID</th>
                                    <th>Potong Ke</th>
                                    <th>Panjang (mm)</th>
                                    <th>Status</th>
                                    <th>Waktu Potong</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada data log</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>#<?php echo $log['id']; ?></td>
                                            <td>
                                                <a href="?job_id=<?php echo $log['job_id']; ?>"
                                                    class="text-primary text-decoration-none">
                                                    #<?php echo $log['job_id']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo $log['potong_ke']; ?></td>
                                            <td><?php echo $log['panjang_mm']; ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = 'bg-success';
                                                if ($log['status'] === 'FAILED')
                                                    $badgeClass = 'bg-danger';
                                                if ($log['status'] === 'SKIPPED')
                                                    $badgeClass = 'bg-warning';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo $log['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($log['waktu_potong'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $page - 1; ?><?php echo $filterJobId > 0 ? '&job_id=' . $filterJobId : ''; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $i; ?><?php echo $filterJobId > 0 ? '&job_id=' . $filterJobId : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page=<?php echo $page + 1; ?><?php echo $filterJobId > 0 ? '&job_id=' . $filterJobId : ''; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboardkit.js"></script>
</body>

</html>