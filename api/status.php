<?php
/**
 * API: Get Current Status
 * Endpoint untuk mendapatkan status mesin dan progress tanpa reload halaman
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

requireLogin();

header('Content-Type: application/json');

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

// Get recent jobs
$recentJobs = [];
$stmt = $conn->prepare("SELECT j.*, u.nama as user_nama FROM job_potong j LEFT JOIN users u ON j.user_id = u.id ORDER BY j.created_at DESC LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentJobs[] = $row;
}
$stmt->close();

jsonResponse(true, 'Status retrieved', [
    'current_job' => $currentJob,
    'stats' => $stats,
    'recent_jobs' => $recentJobs,
    'machine_ip' => ESP_IP,
    'status' => $currentJob ? $currentJob['status'] : 'READY',
    'status_text' => getStatusText($currentJob ? $currentJob['status'] : 'READY'),
    'status_badge' => getStatusBadgeClass($currentJob ? $currentJob['status'] : 'READY')
]);

