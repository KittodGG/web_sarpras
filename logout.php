<?php
/**
 * Halaman logout aplikasi
 */
require_once('config/config.php');

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login
redirect(ROOT_URL . '/login.php');
?>