<?php
/**
 * API: Stop Cutting Job
 * Endpoint untuk menghentikan pekerjaan potong
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();

// Find running job
$stmt = $conn->prepare("SELECT id, status FROM job_potong WHERE status = 'RUNNING' ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(false, 'Tidak ada pekerjaan yang sedang berjalan');
}

$job = $result->fetch_assoc();
$jobId = $job['id'];
$stmt->close();

// Send stop command to ESP8266
$espResponse = sendESPRequest('/stop', []);

if ($espResponse['success']) {
    // Update job status to STOPPED
    $stmt = $conn->prepare("UPDATE job_potong SET status = 'STOPPED', stopped_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $stmt->close();

    jsonResponse(true, 'Pekerjaan dihentikan! Job ID: ' . $jobId, [
        'job_id' => $jobId,
        'esp_response' => $espResponse
    ]);
} else {
    // Still update status even if ESP8266 doesn't respond
    $stmt = $conn->prepare("UPDATE job_potong SET status = 'STOPPED', stopped_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $stmt->close();

    jsonResponse(true, 'Perintah stop dikirim (ESP8266 mungkin tidak merespons secara langsung)', [
        'job_id' => $jobId,
        'esp_response' => $espResponse
    ]);
}

