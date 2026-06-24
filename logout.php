<?php
/**
 * SIDORAH - Sistem Informasi Donor Darah
 * File: logout.php
 * Deskripsi: Hancurkan session dan arahkan ke login
 */

require_once 'koneksi.php';

// Hancurkan semua data session
$_SESSION = [];

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect ke halaman login
redirect('login.php');
?>