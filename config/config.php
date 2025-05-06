<?php
// Konfigurasi umum aplikasi
session_start();

// Definisi konstanta untuk path
define('ROOT_URL', 'http://localhost/ta_sarpras/ta_sarpras_web');
define('ROOT_PATH', dirname(dirname(__FILE__)));

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Import file koneksi database
require_once(ROOT_PATH . '/config/database.php');

// Import file fungsi umum
require_once(ROOT_PATH . '/config/functions.php');

// Pengaturan aplikasi
$app_name = "Sistem Peminjaman Sarpras SMKN 1 Cimahi";
$app_version = "1.0.0";
$app_footer = "© " . date('Y') . " SMKN 1 Cimahi - Developed with ❤ by <a class='kitna' href='https://kitna.my.id/'>Kitna M. F.</a>";

// Pengaturan palet warna
$colors = [
    'primary' => '#4E382B',    // Dark brown (warna 3)
    'secondary' => '#937A66',  // Medium brown (warna 4)
    'accent' => '#542827',     // Dark reddish brown (warna 5)
    'danger' => '#3A101C',     // Deep burgundy (warna 6)
    'light' => '#D3CEC8',      // Light grayish cream (warna 1)
    'dark' => '#131315',       // Nearly black (warna 7)
    'gray' => '#747071'        // Medium gray (warna 2)
];
?>