<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

requireLogin();

$conn = getDBConnection();

// Get current machine configuration
$stmt = $conn->prepare("SELECT * FROM machine_config WHERE id = 1");
$stmt->execute();
$configResult = $stmt->get_result();
$machineConfig = $configResult->fetch_assoc();
$stmt->close();

// Calculate circumference and steps
$circumference_cm = (M_PI * $machineConfig['roller_diameter_mm']) / 10;
$steps_per_cm = 200 / $circumference_cm;
$total_steps = intval($machineConfig['pull_distance_cm'] * $steps_per_cm);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?php echo APP_NAME; ?></title>
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
                    <a class="nav-link" href="log.php">
                        <i class="bi bi-list-ul"></i>
                        <span>Log Pekerjaan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="settings.php">
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
                <h1 class="topbar-title">Pengaturan Mesin</h1>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <span><?php echo $_SESSION['username']; ?></span>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Settings Form -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-sliders"></i> Konfigurasi Mesin</h5>
                            </div>
                            <div class="card-body">
                                <form id="settingsForm">
                                    <!-- Roller Section -->
                                    <div class="section-group mb-4">
                                        <h6 class="section-title">
                                            <i class="bi bi-circle-fill text-primary"></i> Konfigurasi Roller
                                        </h6>
                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="roller_diameter" class="form-label">
                                                    Diameter Roller (mm)
                                                    <small class="text-muted">*roller saat ini 17mm</small>
                                                </label>
                                                <input type="number" class="form-control" id="roller_diameter"
                                                    name="roller_diameter_mm" min="1" max="100"
                                                    value="<?php echo $machineConfig['roller_diameter_mm']; ?>"
                                                    required>
                                                <small class="text-muted">Range: 1-100 mm</small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="pull_distance" class="form-label">
                                                    Jarak Tarik Kertas (cm)
                                                </label>
                                                <input type="number" class="form-control" id="pull_distance"
                                                    name="pull_distance_cm" min="1" max="50"
                                                    value="<?php echo $machineConfig['pull_distance_cm']; ?>" required>
                                                <small class="text-muted">Range: 1-50 cm | Steps: <span
                                                        id="totalSteps"><?php echo $total_steps; ?></span></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Motor Speed Section -->
                                    <div class="section-group mb-4">
                                        <h6 class="section-title">
                                            <i class="bi bi-speedometer-alt text-success"></i> Kecepatan Motor
                                        </h6>
                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="step_delay" class="form-label">
                                                    Step Delay Motor (µs)
                                                </label>
                                                <input type="number" class="form-control" id="step_delay"
                                                    name="step_delay_us" min="500" max="5000" step="100"
                                                    value="<?php echo $machineConfig['step_delay_us']; ?>" required>
                                                <small class="text-muted">Range: 500-5000 µs (lebih besar = lebih
                                                    lambat)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timing Section -->
                                    <div class="section-group mb-4">
                                        <h6 class="section-title">
                                            <i class="bi bi-hourglass-split text-warning"></i> Timing (ms)
                                        </h6>
                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pull_delay" class="form-label">
                                                    Delay Penarik (ms)
                                                </label>
                                                <input type="number" class="form-control" id="pull_delay"
                                                    name="pull_delay_ms" min="100" max="5000" step="100"
                                                    value="<?php echo $machineConfig['pull_delay_ms']; ?>" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="cut_delay" class="form-label">
                                                    Delay Pemotong (ms)
                                                </label>
                                                <input type="number" class="form-control" id="cut_delay"
                                                    name="cut_delay_ms" min="100" max="5000" step="100"
                                                    value="<?php echo $machineConfig['cut_delay_ms']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pull_pause" class="form-label">
                                                    Pause Setelah Penarik (ms)
                                                </label>
                                                <input type="number" class="form-control" id="pull_pause"
                                                    name="pull_pause_ms" min="100" max="5000" step="100"
                                                    value="<?php echo $machineConfig['pull_pause_ms']; ?>" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="cut_pause" class="form-label">
                                                    Pause Setelah Pemotong (ms)
                                                </label>
                                                <input type="number" class="form-control" id="cut_pause"
                                                    name="cut_pause_ms" min="100" max="5000" step="100"
                                                    value="<?php echo $machineConfig['cut_pause_ms']; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Network Section -->
                                    <div class="section-group mb-4">
                                        <h6 class="section-title">
                                            <i class="bi bi-wifi text-info"></i> Konfigurasi Network
                                        </h6>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="esp_ip" class="form-label">
                                                    Alamat IP Mesin (ESP8266)
                                                </label>
                                                <input type="text" class="form-control" id="esp_ip"
                                                    name="esp_ip" 
                                                    value="<?php echo $machineConfig['esp_ip']; ?>" required>
                                                <small class="text-muted">Default AP: 192.168.4.1</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-check-circle"></i> Simpan Pengaturan
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" id="resetBtn">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Info Panel -->
                    <div class="col-lg-4">
                        <!-- Current Values -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Nilai Saat Ini</h6>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <label>Diameter Roller:</label>
                                    <span class="value"><?php echo $machineConfig['roller_diameter_mm']; ?> mm</span>
                                </div>
                                <div class="info-item">
                                    <label>Jarak Tarik:</label>
                                    <span class="value"><?php echo $machineConfig['pull_distance_cm']; ?> cm</span>
                                </div>
                                <div class="info-item">
                                    <label>Keliling Roller:</label>
                                    <span class="value"><?php echo number_format($circumference_cm, 2); ?> cm</span>
                                </div>
                                <div class="info-item">
                                    <label>Steps per cm:</label>
                                    <span class="value"><?php echo number_format($steps_per_cm, 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Total Steps (Penarik):</label>
                                    <span class="value"><?php echo $total_steps; ?></span>
                                </div>
                                <hr>
                                <div class="info-item">
                                    <label>Step Delay:</label>
                                    <span class="value"><?php echo $machineConfig['step_delay_us']; ?> µs</span>
                                </div>
                                <div class="info-item">
                                    <label>Alamat IP Mesin:</label>
                                    <span class="value text-primary font-monospace"><?php echo $machineConfig['esp_ip']; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Help -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-question-circle"></i> Panduan</h6>
                            </div>
                            <div class="card-body">
                                <div class="help-section mb-3">
                                    <strong>Diameter Roller:</strong>
                                    <p class="small">Ukuran diameter roller penarik kertas. Digunakan untuk menghitung
                                        berapa steps motor yang diperlukan untuk menarik kertas sejarak yang ditentukan.
                                    </p>
                                </div>
                                <div class="help-section mb-3">
                                    <strong>Jarak Tarik:</strong>
                                    <p class="small">Berapa cm kertas ditarik setiap kali penarik bergerak maju.
                                        Disesuaikan dengan ukuran kertas yang akan dipotong.</p>
                                </div>
                                <div class="help-section mb-3">
                                    <strong>Step Delay:</strong>
                                    <p class="small">Jeda waktu antar step motor (dalam mikrosecond). Nilai lebih besar
                                        = motor lebih lambat tapi lebih aman. Nilai minimal 500µs.</p>
                                </div>
                                <div class="help-section">
                                    <strong>Timing:</strong>
                                    <p class="small">Delay dan pause untuk mengatur kecepatan penarik dan pemotong,
                                        serta waktu tunggu antar fase gerakan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <div id="alertBox" class="alert alert-info alert-dismissible fade show d-none" role="alert">
            <span id="alertText"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate steps when roller diameter or distance changes
        document.getElementById('roller_diameter').addEventListener('change', calculateSteps);
        document.getElementById('pull_distance').addEventListener('change', calculateSteps);

        function calculateSteps() {
            const diameter = parseFloat(document.getElementById('roller_diameter').value) || 17;
            const distance = parseFloat(document.getElementById('pull_distance').value) || 5;

            const circumference = Math.PI * diameter / 10;  // cm
            const stepsPerCm = 200 / circumference;
            const totalSteps = Math.round(distance * stepsPerCm);

            document.getElementById('totalSteps').textContent = totalSteps;
        }

        // Form submission
        document.getElementById('settingsForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('api/config.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Pengaturan berhasil disimpan!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        });

        // Reset button
        document.getElementById('resetBtn').addEventListener('click', function () {
            if (confirm('Anda yakin ingin mereset ke nilai default?')) {
                location.reload();
            }
        });

        // Show alert function
        function showAlert(message, type = 'info') {
            const alertBox = document.getElementById('alertBox');
            const alertText = document.getElementById('alertText');

            alertBox.className = `alert alert-${type} alert-dismissible fade show`;
            alertText.textContent = message;

            setTimeout(() => {
                alertBox.classList.add('d-none');
            }, 5000);
        }

        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>

</html>