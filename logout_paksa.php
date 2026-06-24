<?php
// Hancurkan semua session
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
session_destroy();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}

// Redirect ke login
header('Location: login.php');
exit();
?>