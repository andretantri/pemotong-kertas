<?php
/**
 * API: Check ESP32 Connection
 * Check if ESP32 is reachable
 */

require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/functions.php';

requireLogin();

header('Content-Type: application/json');

// Ping ESP8266 (request root path)
// Since the laptop is connected to ESP8266 WiFi, it can now reach the ESP directly
$response = sendESPRequest('/', [], 2);

if ($response['success']) {
    jsonResponse(true, 'ESP8266 Terhubung', [
        'connected' => true,
        'ip' => ESP_IP,
        'mode' => 'access_point'
    ]);
} else {
    jsonResponse(false, 'ESP8266 Tidak Terjangkau', [
        'connected' => false,
        'error' => $response['error'],
        'tip' => 'Pastikan laptop terhubung ke WiFi: Mesin_Potong_Kertas'
    ]);
}
