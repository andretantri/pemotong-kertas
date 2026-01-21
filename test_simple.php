<?php
// Simple test tanpa cURL
$url = 'http://192.168.4.1/';

echo "<h2>Test Koneksi ESP8266 (Tanpa cURL)</h2>";
echo "<p>URL: $url</p>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 2,
        'ignore_errors' => true
    ]
]);

echo "<h3>Mencoba koneksi...</h3>";
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    $error = error_get_last();
    echo "<div style='color: red;'>";
    echo "<strong>GAGAL!</strong><br>";
    echo "Error: " . ($error['message'] ?? 'Unknown error');
    echo "</div>";

    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Pastikan laptop terhubung ke WiFi: <strong>Mesin_Potong_Kertas</strong></li>";
    echo "<li>Pastikan ESP8266 sudah menyala dan web server berjalan</li>";
    echo "<li>Coba ping: <code>ping 192.168.4.1</code></li>";
    echo "<li>Coba buka di browser: <a href='http://192.168.4.1/' target='_blank'>http://192.168.4.1/</a></li>";
    echo "</ol>";
} else {
    echo "<div style='color: green;'>";
    echo "<strong>BERHASIL!</strong><br>";
    echo "Response:<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";

    // Parse HTTP code
    if (isset($http_response_header[0])) {
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
        $httpCode = isset($matches[1]) ? (int) $matches[1] : 0;
        echo "<p>HTTP Code: <strong>$httpCode</strong></p>";
    }

    echo "<h3>Headers:</h3>";
    echo "<pre>";
    print_r($http_response_header);
    echo "</pre>";
}
?>