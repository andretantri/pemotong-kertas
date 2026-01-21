<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password, nama FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (verifyPassword($password, $user['password'])) {
                loginUser($user['id'], $user['username']);
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dashboardkit.css">
</head>

<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-gear-fill"></i>
            <h3><?php echo APP_NAME; ?></h3>
            <p>Sistem Kontrol Mesin Pemotong</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-fill"></i> Username
                    </label>
                    <input type="text" class="form-control form-control-lg" id="username" name="username"
                        placeholder="Masukkan username" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock-fill"></i> Password
                    </label>
                    <input type="password" class="form-control form-control-lg" id="password" name="password"
                        placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>

            <div class="mt-4 text-center text-muted small">
                <p class="mb-0">Default: admin / admin123</p>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html>