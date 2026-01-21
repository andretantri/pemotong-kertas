<?php
/**
 * Script untuk Reset Password Admin
 * Hapus file ini setelah password direset!
 */

require_once 'config/database.php';

// Generate hash untuk password admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password Hash untuk 'admin123':\n";
echo $hash . "\n\n";

// Update password di database
$conn = getDBConnection();
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "✓ Password admin berhasil direset!\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    echo "⚠️ PENTING: Hapus file reset_password.php setelah digunakan!\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

$stmt->close();
$conn->close();

