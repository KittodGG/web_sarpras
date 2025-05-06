<?php
/**
 * File utama aplikasi peminjaman sarpras
 */
require_once('config/config.php');

// Cek login untuk halaman admin
checkAdminAccess();

// Routing sederhana
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Default controller dan method
$controller = isset($url[0]) && $url[0] != '' ? $url[0] : 'dashboard';
$method = isset($url[1]) ? $url[1] : 'index';
$param = isset($url[2]) ? $url[2] : '';

// Load view header dan sidebar
include('views/includes/header.php');
include('views/includes/sidebar.php');

// Load content berdasarkan controller
echo '<div class="content" id="content">';

switch ($controller) {
    case 'dashboard':
        include('views/dashboard.php');
        break;
        
    case 'sarpras':
        switch ($method) {
            case 'index':
                include('views/sarpras/index.php');
                break;
            case 'add':
                include('views/sarpras/add.php');
                break;
            case 'edit':
                include('views/sarpras/edit.php');
                break;
            case 'detail':
                include('views/sarpras/detail.php');
                break;
            default:
                include('views/sarpras/index.php');
                break;
        }
        break;
        
    case 'kategori':
        switch ($method) {
            case 'index':
                include('views/kategori/index.php');
                break;
            case 'add':
                include('views/kategori/add.php');
                break;
            case 'edit':
                include('views/kategori/edit.php');
                break;
            default:
                include('views/kategori/index.php');
                break;
        }
        break;
        
    case 'peminjaman':
        switch ($method) {
            case 'index':
                include('views/peminjaman/index.php');
                break;
            case 'add':
                include('views/peminjaman/add.php');
                break;
            case 'edit':
                include('views/peminjaman/edit.php');
                break;
            case 'detail':
                include('views/peminjaman/detail.php');
                break;
            case 'approve':
                include('views/peminjaman/approve.php');
                break;
            default:
                include('views/peminjaman/index.php');
                break;
        }
        break;
        
    case 'pengembalian':
        switch ($method) {
            case 'index':
                include('views/pengembalian/index.php');
                break;
            case 'add':
                include('views/pengembalian/add.php');
                break;
            case 'verify':
                include('views/pengembalian/verify.php');
                break;
            case 'detail':
                include('views/pengembalian/detail.php');
                break;
            default:
                include('views/pengembalian/index.php');
                break;
        }
        break;
        
    case 'users':
        switch ($method) {
            case 'index':
                include('views/users/index.php');
                break;
            case 'add':
                include('views/users/add.php');
                break;
            case 'edit':
                include('views/users/edit.php');
                break;
            case 'detail':
                include('views/users/detail.php');
                break;
            default:
                include('views/users/index.php');
                break;
        }
        break;
        
    case 'masalah':
        switch ($method) {
            case 'index':
                include('views/masalah/index.php');
                break;
            case 'detail':
                include('views/masalah/detail.php');
                break;
            case 'process':
                include('views/masalah/process.php');
                break;
            default:
                include('views/masalah/index.php');
                break;
        }
        break;
        
    case 'laporan':
        switch ($method) {
            case 'peminjaman':
                include('views/laporan/peminjaman.php');
                break;
            case 'pengembalian':
                include('views/laporan/pengembalian.php');
                break;
            case 'sarpras':
                include('views/laporan/sarpras.php');
                break;
            case 'masalah':
                include('views/laporan/masalah.php');
                break;
            default:
                include('views/laporan/index.php');
                break;
        }
        break;
        
    case 'jadwal':
        include('views/jadwal/index.php');
        break;
        
    case 'profile':
        // Redirect to the user detail page for the current logged-in user
        $user_id = $_SESSION['user_id'];
        redirect(ROOT_URL . '/users/detail/' . $user_id);
        break;
        
    default:
        include('views/sarpras/dashboard.php');
        break;
}

echo '</div>';


// Load view footer
include('views/includes/footer.php');
?>