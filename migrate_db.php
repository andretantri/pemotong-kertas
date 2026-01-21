<?php
require_once 'config/database.php';
$conn = getDBConnection();

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM `machine_config` LIKE 'esp_ip'");
if ($result->num_rows == 0) {
    echo "Adding esp_ip column to machine_config table...\n";
    $conn->query("ALTER TABLE `machine_config` ADD `esp_ip` VARCHAR(50) NOT NULL DEFAULT '192.168.4.1' AFTER `cut_pause_ms` ");
    echo "Column added.\n";
} else {
    echo "Column esp_ip already exists.\n";
}

$conn->close();
?>