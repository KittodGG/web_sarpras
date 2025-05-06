<?php
/**
 * File fungsi umum untuk aplikasi
 */

// Fungsi untuk sanitasi input
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk verifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fungsi untuk membuat kode unik
function generateUniqueCode($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[mt_rand(0, $max)];
    }
    
    return $code;
}

// Fungsi untuk redirect
function redirect($url) {
    // Check if headers have already been sent
    if (headers_sent()) {
        // Use JavaScript for redirection if headers already sent
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
        echo '</noscript>';
        exit();
    } else {
        // Use header redirect if headers not sent yet
        header('Location: ' . $url);
        exit();
    }
}

// Fungsi untuk format tanggal
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Fungsi untuk format tanggal dan waktu
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return date($format, strtotime($datetime));
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek admin
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
}

// Fungsi untuk cek akses admin
function checkAdminAccess() {
    if (!isLoggedIn() || !isAdmin()) {
        $_SESSION['message'] = "Anda tidak memiliki akses ke halaman ini!";
        $_SESSION['message_type'] = "danger";
        redirect(ROOT_URL . '/login.php');
    }
}

// Fungsi untuk menampilkan pesan alert
function showAlert() {
    if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Fungsi untuk upload file
function uploadFile($file, $targetDir = null, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
    // Set default target directory if not provided
    if ($targetDir === null) {
        $targetDir = ROOT_PATH . '/uploads/';
    }
    
    // Cek apakah ada error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'Terjadi error saat upload file.'];
    }
    
    // Ambil info file
    $fileName = basename($file['name']);
    $fileSize = $file['size'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Cek ukuran file
    if ($fileSize > $maxSize) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar. Maksimal ' . ($maxSize / 1048576) . 'MB.'];
    }
    
    // Cek tipe file
    if (!in_array($fileType, $allowedTypes)) {
        return ['status' => false, 'message' => 'Tipe file tidak diperbolehkan. File yang diperbolehkan: ' . implode(', ', $allowedTypes)];
    }
    
    // Buat nama file unik
    $newFileName = md5(time() . $fileName) . '.' . $fileType;
    $targetFile = $targetDir . $newFileName;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['status' => true, 'file_name' => $newFileName, 'file_path' => $targetFile];
    } else {
        return ['status' => false, 'message' => 'Gagal mengupload file.'];
    }
}

// Fungsi untuk pagination
function getPagination($table, $perPage = 10, $conditions = '') {
    global $conn;
    
    // Tentukan halaman aktif
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    
    // Hitung total data
    $sql = "SELECT COUNT(*) as total FROM {$table}";
    if (!empty($conditions)) {
        $sql .= " WHERE {$conditions}";
    }
    
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $totalData = $row['total'];
    
    // Hitung total halaman
    $totalPages = ceil($totalData / $perPage);
    
    // Tentukan offset
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total_data' => $totalData,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

// Fungsi untuk render pagination links
function renderPagination($pagination, $url = '?') {
    $output = '<nav aria-label="Page navigation" class="mt-4">';
    $output .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    $prevDisabled = ($pagination['page'] <= 1) ? 'disabled' : '';
    $output .= '<li class="page-item ' . $prevDisabled . '">';
    $output .= '<a class="page-link" href="' . $url . 'page=' . ($pagination['page'] - 1) . '" aria-label="Previous">';
    $output .= '<span aria-hidden="true">&laquo;</span>';
    $output .= '</a></li>';
    
    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = ($i == $pagination['page']) ? 'active' : '';
        $output .= '<li class="page-item ' . $active . '">';
        $output .= '<a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a>';
        $output .= '</li>';
    }
    
    // Next button
    $nextDisabled = ($pagination['page'] >= $pagination['total_pages']) ? 'disabled' : '';
    $output .= '<li class="page-item ' . $nextDisabled . '">';
    $output .= '<a class="page-link" href="' . $url . 'page=' . ($pagination['page'] + 1) . '" aria-label="Next">';
    $output .= '<span aria-hidden="true">&raquo;</span>';
    $output .= '</a></li>';
    
    $output .= '</ul></nav>';
    
    return $output;
}

// Fungsi untuk mendapatkan hari dalam bahasa Indonesia
function getHariIndonesia($date) {
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $dayOfWeek = date('w', strtotime($date));
    return $hari[$dayOfWeek];
}

// Fungsi untuk mendapatkan bulan dalam bahasa Indonesia
function getBulanIndonesia($date) {
    $bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $monthIndex = (int)date('m', strtotime($date)) - 1;
    return $bulan[$monthIndex];
}

// Fungsi untuk mendapatkan tanggal dalam format Indonesia
function getTanggalIndonesia($date) {
    $day = date('d', strtotime($date));
    $month = getBulanIndonesia($date);
    $year = date('Y', strtotime($date));
    return "{$day} {$month} {$year}";
}
?>