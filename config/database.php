<?php
/**
 * Konfigurasi Database
 * HMI Pemotong Kertas Roll - ESP32
 */

// Koneksi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hmi_pemotong_kertas');

// ESP8266 Configuration
// Nilai default, akan diupdate dari database jika tersedia
define('DEFAULT_ESP_IP', '192.168.4.1');
define('ESP_TIMEOUT', 5); // timeout dalam detik

/**
 * Mendapatkan IP ESP dari database
 */
function getEspIp()
{
    static $esp_ip = null;
    if ($esp_ip !== null)
        return $esp_ip;

    $conn = getDBConnection();
    $result = $conn->query("SELECT esp_ip FROM machine_config WHERE id = 1 LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $esp_ip = $row['esp_ip'];
    } else {
        $esp_ip = DEFAULT_ESP_IP;
    }
    return $esp_ip;
}

// Override ESP_IP constant using value from database
define('ESP_IP', getEspIp());
define('ESP32_IP', ESP_IP);     // Legacy
define('ESP8266_IP', ESP_IP);  // Legacy


// Session Configuration
define('SESSION_NAME', 'HMI_SESSION');
define('SESSION_LIFETIME', 3600); // 1 jam

// Application Configuration
define('APP_NAME', 'HMI Pemotong Kertas');
define('APP_VERSION', '1.0.0');

// Koneksi Database Function
function getDBConnection()
{
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($conn->connect_error) {
                die("Koneksi database gagal: " . $conn->connect_error);
            }

            // Set charset UTF-8
            $conn->set_charset("utf8mb4");

        } catch (Exception $e) {
            die("Error koneksi database: " . $e->getMessage());
        }
    }

    return $conn;
}

// Close Database Connection
function closeDBConnection()
{
    $conn = getDBConnection();
    if ($conn) {
        $conn->close();
    }
}

