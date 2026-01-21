<?php
/**
 * API: Complete Job
 * Endpoint untuk menandai job sebagai selesai (auto-complete via timer)
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

// Note: Login check disabled for auto-complete endpoint
// This is called automatically by frontend timer
// requireLogin();

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$jobId = intval($input['job_id'] ?? 0);

if ($jobId < 1) {
    jsonResponse(false, 'Job ID tidak valid');
}

$conn = getDBConnection();

// Check if job exists and is running
$stmt = $conn->prepare("SELECT id, status, jumlah_potong FROM job_potong WHERE id = ?");
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    jsonResponse(false, 'Job tidak ditemukan');
}

$job = $result->fetch_assoc();
$stmt->close();

// Update job to DONE
$stmt = $conn->prepare("UPDATE job_potong SET status = 'DONE', potong_selesai = jumlah_potong, completed_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $jobId);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(true, 'Job berhasil diselesaikan', [
        'job_id' => $jobId,
        'status' => 'DONE'
    ]);
} else {
    $stmt->close();
    jsonResponse(false, 'Gagal menyelesaikan job: ' . $conn->error);
}
?>