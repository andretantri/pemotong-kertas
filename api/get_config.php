<?php
/**
 * API: Get Configuration for ESP8266
 * Endpoint untuk ESP8266 mengambil konfigurasi mesin
 * Tidak memerlukan autentikasi (POST request dari ESP32)
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => mysqli_connect_error()
    ]);
    exit();
}

$conn->set_charset("utf8mb4");

// Get machine configuration
$result = $conn->query("SELECT * FROM machine_config WHERE id = 1");

if ($result && $result->num_rows > 0) {
    $config = $result->fetch_assoc();

    // Calculate steps for pull distance based on roller diameter
    // Circumference = π * diameter
    // Steps per cm = (200 steps per revolution) / (π * diameter_mm / 10)
    $circumference_cm = (M_PI * $config['roller_diameter_mm']) / 10;
    $steps_per_cm = 200 / $circumference_cm;
    $total_pull_steps = intval($config['pull_distance_cm'] * $steps_per_cm);

    echo json_encode([
        'success' => true,
        'message' => 'Configuration retrieved successfully',
        'config' => [
            'roller_diameter_mm' => intval($config['roller_diameter_mm']),
            'pull_distance_cm' => intval($config['pull_distance_cm']),
            'pull_steps' => $total_pull_steps,
            'pull_delay_ms' => intval($config['pull_delay_ms']),
            'cut_delay_ms' => intval($config['cut_delay_ms']),
            'step_delay_us' => intval($config['step_delay_us']),
            'pull_pause_ms' => intval($config['pull_pause_ms']),
            'cut_pause_ms' => intval($config['cut_pause_ms'])
        ]
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration not found'
    ]);
}

$conn->close();
?>