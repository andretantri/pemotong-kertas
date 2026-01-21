<?php
/**
 * API: Start Job (untuk dikomunikasikan ke ESP32)
 * Endpoint ini dapat dipanggil oleh web interface
 * dan akan mengirimkan perintah ke ESP32 melalui HTTP
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $panjang = intval($input['panjang'] ?? 0);
    $jumlah_potong = intval($input['jumlah_potong'] ?? 0);
    
    // Validation
    if ($panjang < 1 || $panjang > 10000) {
        jsonResponse(false, 'Panjang harus antara 1-10000 mm');
    }
    
    if ($jumlah_potong < 1 || $jumlah_potong > 1000) {
        jsonResponse(false, 'Jumlah potong harus antara 1-1000');
    }
    
    // Check if there's already a running job
    $stmt = $conn->prepare("SELECT id, status FROM job_potong WHERE status IN ('READY', 'RUNNING') LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $existingJob = $result->fetch_assoc();
        jsonResponse(false, 'Masih ada pekerjaan aktif (ID: ' . $existingJob['id'] . '). Hentikan dulu sebelum memulai yang baru.');
    }
    $stmt->close();
    
    // Get machine config
    $stmt = $conn->prepare("SELECT * FROM machine_config WHERE id = 1");
    $stmt->execute();
    $configResult = $stmt->get_result();
    $config = $configResult->fetch_assoc();
    $stmt->close();
    
    // Create new job
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO job_potong (panjang_mm, jumlah_potong, status, user_id, started_at) VALUES (?, ?, 'READY', ?, NOW())");
    $stmt->bind_param("iii", $panjang, $jumlah_potong, $userId);
    $stmt->execute();
    $jobId = $conn->insert_id;
    $stmt->close();
    
    // Siapkan data untuk ESP32
    $esp32Payload = [
        'panjang_mm' => $panjang,
        'jumlah_potong' => $jumlah_potong,
        'config' => [
            'roller_diameter_mm' => intval($config['roller_diameter_mm']),
            'pull_distance_cm' => intval($config['pull_distance_cm']),
            'step_delay_us' => intval($config['step_delay_us']),
            'pull_delay_ms' => intval($config['pull_delay_ms']),
            'cut_delay_ms' => intval($config['cut_delay_ms']),
            'pull_pause_ms' => intval($config['pull_pause_ms']),
            'cut_pause_ms' => intval($config['cut_pause_ms'])
        ]
    ];
    
    // Update job status to RUNNING
    $stmt = $conn->prepare("UPDATE job_potong SET status = 'RUNNING' WHERE id = ?");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $stmt->close();
    
    jsonResponse(true, 'Pekerjaan dimulai! Job ID: ' . $jobId, [
        'job_id' => $jobId,
        'panjang_mm' => $panjang,
        'jumlah_potong' => $jumlah_potong,
        'config' => $esp32Payload['config'],
        'message' => 'Kirimkan data ini ke ESP32 via HTTP request'
    ]);
    
} else {
    jsonResponse(false, 'Metode ' . $method . ' tidak didukung. Gunakan POST.');
}

$conn->close();
?>
