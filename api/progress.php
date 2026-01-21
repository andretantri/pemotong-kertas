<?php
/**
 * API: Receive Progress from ESP8266
 * Endpoint untuk menerima update progress dari ESP8266
 * 
 * ESP8266 akan POST ke endpoint ini dengan data:
 * - job_id: ID pekerjaan
 * - potong_ke: Urutan potong ke berapa
 * - status: SUCCESS, FAILED, atau SKIPPED
 */

require_once '../config/database.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

// Allow CORS for ESP8266
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Fallback to form data if JSON is empty
if (empty($input)) {
    $input = $_POST;
}

$jobId = intval($input['job_id'] ?? 0);
$potongKe = intval($input['potong_ke'] ?? 0);
$status = strtoupper($input['status'] ?? 'SUCCESS');
$panjangMm = intval($input['panjang_mm'] ?? 0);

// Validation
if ($jobId < 1) {
    jsonResponse(false, 'job_id tidak valid');
}

if ($potongKe < 1) {
    jsonResponse(false, 'potong_ke tidak valid');
}

if (!in_array($status, ['SUCCESS', 'FAILED', 'SKIPPED'])) {
    $status = 'SUCCESS';
}

$conn = getDBConnection();

// Check if job exists
$stmt = $conn->prepare("SELECT id, panjang_mm, jumlah_potong, potong_selesai, status FROM job_potong WHERE id = ?");
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    jsonResponse(false, 'Job tidak ditemukan');
}

$job = $result->fetch_assoc();
$stmt->close();

// Use job's panjang_mm if not provided
if ($panjangMm < 1) {
    $panjangMm = $job['panjang_mm'];
}

// Insert log
$stmt = $conn->prepare("INSERT INTO log_potong (job_id, potong_ke, panjang_mm, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $jobId, $potongKe, $panjangMm, $status);
$stmt->execute();
$stmt->close();

// Update job progress
$newPotongSelesai = max($job['potong_selesai'], $potongKe);

// Check if job is complete
$isComplete = ($newPotongSelesai >= $job['jumlah_potong']);

if ($isComplete) {
    // Update job to DONE
    $stmt = $conn->prepare("UPDATE job_potong SET potong_selesai = ?, status = 'DONE', completed_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $newPotongSelesai, $jobId);
    $stmt->execute();
    $stmt->close();
} else {
    // Update progress only
    $stmt = $conn->prepare("UPDATE job_potong SET potong_selesai = ? WHERE id = ?");
    $stmt->bind_param("ii", $newPotongSelesai, $jobId);
    $stmt->execute();
    $stmt->close();
}

jsonResponse(true, 'Progress updated', [
    'job_id' => $jobId,
    'potong_ke' => $potongKe,
    'potong_selesai' => $newPotongSelesai,
    'jumlah_potong' => $job['jumlah_potong'],
    'is_complete' => $isComplete,
    'status' => $isComplete ? 'DONE' : $job['status']
]);

