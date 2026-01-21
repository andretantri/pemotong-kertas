<?php
/**
 * API: Start Cutting Job
 * Endpoint untuk memulai pekerjaan potong
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

requireLogin();

header('Content-Type: application/json');

// Check if there's already a running job
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, status FROM job_potong WHERE status IN ('READY', 'RUNNING') LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existingJob = $result->fetch_assoc();
    jsonResponse(false, 'Masih ada pekerjaan yang aktif (ID: ' . $existingJob['id'] . ')');
}

$stmt->close();

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$panjang = intval($input['panjang'] ?? 0);
$jumlah = intval($input['jumlah'] ?? 0);

// Validation
if ($panjang < 1 || $panjang > 10000) {
    jsonResponse(false, 'Panjang harus antara 1-10000 mm');
}

if ($jumlah < 1 || $jumlah > 1000) {
    jsonResponse(false, 'Jumlah harus antara 1-1000 potong');
}

// Create new job
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO job_potong (panjang_mm, jumlah_potong, status, user_id, started_at) VALUES (?, ?, 'READY', ?, NOW())");
$stmt->bind_param("iii", $panjang, $jumlah, $userId);
$stmt->execute();
$jobId = $conn->insert_id;
$stmt->close();

// Get machine configuration
$configStmt = $conn->prepare("SELECT * FROM machine_config WHERE id = 1");
$configStmt->execute();
$config = $configStmt->get_result()->fetch_assoc();
$configStmt->close();

// Calculate pull_steps based on panjang_mm (length in mm)
// Formula: steps = (panjang_mm / 10) * (200 / keliling_roller)
// Keliling roller = π * diameter
$keliling_cm = M_PI * $config['roller_diameter_mm'] / 10; // keliling dalam cm
$steps_per_cm = 333 / $keliling_cm; // 200 steps per rotation
$panjang_cm = $panjang / 10; // convert mm to cm
$pull_steps = intval($panjang_cm * $steps_per_cm);

// Calculate cut_steps based on pull_distance_cm (jarak pemotong dalam cm)
// Pemotong menggunakan jarak tetap dari config
$cut_steps = intval($config['pull_distance_cm'] * $steps_per_cm);

// Calculate estimated time (in seconds)
// Formula: (pull_steps * step_delay_us / 1000000 + delays) * jumlah
$timePerCut = ($pull_steps * $config['step_delay_us'] / 1000000) +
    ($cut_steps * $config['step_delay_us'] / 1000000) +
    ($config['pull_delay_ms'] / 1000) +
    ($config['pull_pause_ms'] / 1000) +
    ($config['cut_delay_ms'] / 1000) +
    ($config['cut_pause_ms'] / 1000);
$estimatedSeconds = ceil($timePerCut * $jumlah) + 5; // +5 detik buffer

// Send command to ESP8266 with machine config
$espResponse = sendESPRequest('/start', [
    'panjang' => $panjang,
    'jumlah' => $jumlah,
    'job_id' => $jobId,
    'pull_steps' => $pull_steps,  // Calculated based on panjang_mm (penarik)
    'cut_steps' => $cut_steps,     // Calculated based on pull_distance_cm (pemotong)
    'step_delay_us' => $config['step_delay_us'],
    'pull_delay_ms' => $config['pull_delay_ms'],
    'cut_delay_ms' => $config['cut_delay_ms'],
    'pull_pause_ms' => $config['pull_pause_ms'],
    'cut_pause_ms' => $config['cut_pause_ms']
]);

// Update job status to RUNNING with estimated completion time
$stmt = $conn->prepare("UPDATE job_potong SET status = 'RUNNING' WHERE id = ?");
$stmt->bind_param("i", $jobId);
$stmt->execute();
$stmt->close();

jsonResponse(true, 'Pekerjaan dimulai! Job ID: ' . $jobId, [
    'job_id' => $jobId,
    'panjang' => $panjang,
    'jumlah' => $jumlah,
    'pull_steps' => $pull_steps,
    'cut_steps' => $cut_steps,
    'roller_diameter' => $config['roller_diameter_mm'],
    'pull_distance_cm' => $config['pull_distance_cm'],
    'estimated_seconds' => $estimatedSeconds,
    'esp_response' => $espResponse,
    'note' => 'Pekerjaan telah didaftarkan. ' . (!$espResponse['success'] ? 'Peringatan: ESP tidak terjangkau langsung oleh server (Normal untuk mode Hosting).' : 'ESP Berhasil merespon.')
]);

