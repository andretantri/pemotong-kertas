<?php
// Test koneksi ke ESP8266
require_once 'config/database.php';
require_once 'config/functions.php';

echo "<h2>Test Koneksi ESP8266</h2>";
echo "<p>IP ESP: " . ESP_IP . "</p>";

echo "<h3>Test 1: Ping Root Endpoint</h3>";
$result = sendESPRequest('/');
echo "<pre>";
print_r($result);
echo "</pre>";

echo "<h3>Test 2: Direct cURL Test</h3>";
$url = 'http://' . ESP_IP . '/';
echo "URL: $url<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_error($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);

echo "<strong>Response:</strong><br>";
echo htmlspecialchars($response);
echo "<br><br>";

echo "<strong>Info:</strong><br><pre>";
print_r($info);
echo "</pre>";

echo "<strong>Error:</strong> " . ($error ?: 'none') . "<br><br>";

echo "<strong>Verbose Log:</strong><br><pre>";
echo htmlspecialchars($verboseLog);
echo "</pre>";

curl_close($ch);
?>