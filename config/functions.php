<?php
/**
 * Helper Functions
 */

// Require database config for constants
require_once __DIR__ . '/database.php';

/**
 * Sanitize input
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Send HTTP GET request to ESP8266
 */
function sendESPRequest($endpoint, $params = [], $timeout = 2)
{
    $url = 'http://' . ESP_IP . $endpoint;

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    // Log untuk debugging
    error_log("[ESP Request] URL: " . $url);

    // Check if cURL is available
    if (function_exists('curl_init')) {
        // Use cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close($ch);

        // Log hasil
        error_log("[ESP Response] HTTP Code: " . $httpCode . ", Error: " . ($error ?: 'none'));

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300) && empty($error),
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'errno' => $errno
        ];
    } else {
        // Fallback to file_get_contents
        error_log("[ESP Request] Using file_get_contents (cURL not available)");

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        $error = '';
        $httpCode = 0;

        if ($response === false) {
            $error = error_get_last()['message'] ?? 'Unknown error';
            error_log("[ESP Response] Error: " . $error);
        } else {
            // Parse HTTP response code from headers
            if (isset($http_response_header[0])) {
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
                $httpCode = isset($matches[1]) ? (int) $matches[1] : 200;
            }
            error_log("[ESP Response] HTTP Code: " . $httpCode);
        }

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300) && empty($error),
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'errno' => 0
        ];
    }
}

// Keep legacy function for compatibility if needed
function sendESP32Request($endpoint, $params = [], $timeout = ESP_TIMEOUT)
{
    return sendESPRequest($endpoint, $params, $timeout);
}

/**
 * Format response JSON
 */
function jsonResponse($success, $message = '', $data = [], $httpCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_PRETTY_PRINT);
    exit();
}

/**
 * Get machine status text
 */
function getStatusText($status)
{
    $statuses = [
        'READY' => 'Siap',
        'RUNNING' => 'Berjalan',
        'STOPPED' => 'Dihentikan',
        'DONE' => 'Selesai',
        'ERROR' => 'Error'
    ];

    return $statuses[$status] ?? $status;
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status)
{
    $classes = [
        'READY' => 'bg-secondary',
        'RUNNING' => 'bg-primary',
        'STOPPED' => 'bg-warning',
        'DONE' => 'bg-success',
        'ERROR' => 'bg-danger'
    ];

    return $classes[$status] ?? 'bg-secondary';
}

