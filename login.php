<?php
/**
 * Halaman login aplikasi peminjaman sarpras
 */
require_once('config/config.php');

// Cek apakah user sudah login
if (isLoggedIn()) {
    redirect(ROOT_URL);
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $_SESSION['message'] = "Username dan password tidak boleh kosong!";
        $_SESSION['message_type'] = "danger";
    } else {
        // Cek user di database
        $sql = "SELECT * FROM users WHERE username = '{$username}'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (verifyPassword($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['foto'] = $user['foto'];
                
                // Redirect ke halaman utama berdasarkan role
                if ($user['role'] == 'admin') {
                    redirect(ROOT_URL);
                } else {
                    // Redirect ke aplikasi Android (dalam kasus ini, tampilkan pesan)
                    $_SESSION['message'] = "Silakan gunakan aplikasi Android untuk pengguna biasa!";
                    $_SESSION['message_type'] = "warning";
                }
            } else {
                $_SESSION['message'] = "Password salah!";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Username tidak ditemukan!";
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $app_name; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo ROOT_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ROOT_URL; ?>/assets/css/style.css">
    
    <style>
        .login-container {
            background-image: url('<?php echo ROOT_URL; ?>/assets/img/bg-login.jpg');
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-header">
                <img src="<?php echo ROOT_URL; ?>/assets/img/logo.png" alt="Logo SMKN 1 Cimahi" class="login-logo">
                <h1 class="login-title">Aplikasi Peminjaman Sarpras</h1>
                <div class="login-subtitle">SMKN 1 Cimahi</div>
            </div>
            
            <div class="login-body">
                <?php showAlert(); ?>
                
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-light"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus>
                        </div>
                        <div class="invalid-feedback">
                            Username tidak boleh kosong!
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-light"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Password tidak boleh kosong!
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <?php echo $app_footer; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ROOT_URL; ?>/assets/js/main.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>