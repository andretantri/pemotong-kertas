<?php
/**
 * API: Get/Update Machine Configuration
 * Endpoint untuk mendapatkan dan mengubah konfigurasi mesin
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get current machine configuration
    $stmt = $conn->prepare("SELECT * FROM machine_config WHERE id = 1");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $config = $result->fetch_assoc();
        jsonResponse(true, 'Machine configuration retrieved', $config);
    } else {
        jsonResponse(false, 'Configuration not found');
    }
    $stmt->close();

} else if ($method === 'POST') {
    // Update machine configuration
    $input = json_decode(file_get_contents('php://input'), true);

    $roller_diameter = intval($input['roller_diameter_mm'] ?? 17);
    $pull_distance = intval($input['pull_distance_cm'] ?? 5);
    $pull_delay = intval($input['pull_delay_ms'] ?? 500);
    $cut_delay = intval($input['cut_delay_ms'] ?? 500);
    $step_delay = intval($input['step_delay_us'] ?? 1200);
    $pull_pause = intval($input['pull_pause_ms'] ?? 1000);
    $cut_pause = intval($input['cut_pause_ms'] ?? 2000);
    $esp_ip = sanitize($input['esp_ip'] ?? DEFAULT_ESP_IP);

    // Validation
    if ($roller_diameter < 1 || $roller_diameter > 100) {
        jsonResponse(false, 'Diameter roller harus antara 1-100 mm');
    }
    if ($pull_distance < 1 || $pull_distance > 50) {
        jsonResponse(false, 'Jarak penarik harus antara 1-50 cm');
    }
    if ($step_delay < 500 || $step_delay > 5000) {
        jsonResponse(false, 'Step delay harus antara 500-5000 µs');
    }

    // Update configuration
    $stmt = $conn->prepare("UPDATE machine_config SET 
        roller_diameter_mm = ?,
        pull_distance_cm = ?,
        pull_delay_ms = ?,
        cut_delay_ms = ?,
        step_delay_us = ?,
        pull_pause_ms = ?,
        cut_pause_ms = ?,
        esp_ip = ?,
        updated_by = ?,
        updated_at = NOW()
        WHERE id = 1");

    $userId = $_SESSION['user_id'];
    $stmt->bind_param("iiiiisisi", $roller_diameter, $pull_distance, $pull_delay, $cut_delay, $step_delay, $pull_pause, $cut_pause, $esp_ip, $userId);

    if ($stmt->execute()) {
        // Get updated config
        $stmt2 = $conn->prepare("SELECT * FROM machine_config WHERE id = 1");
        $stmt2->execute();
        $result = $stmt2->get_result();
        $config = $result->fetch_assoc();
        $stmt2->close();

        jsonResponse(true, 'Konfigurasi mesin berhasil diubah', $config);
    } else {
        jsonResponse(false, 'Gagal mengubah konfigurasi: ' . $conn->error);
    }

    $stmt->close();

} else {
    jsonResponse(false, 'Metode ' . $method . ' tidak didukung', [], 405);
}

$conn->close();
?>