<?php
// File debug sementara - hapus setelah selesai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<h2>Isi Session:</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h2>Session ID:</h2>";
echo session_id();

echo "<br><br><a href='login.php'>→ Login</a> | ";
echo "<a href='portal_pendonor.php'>→ Portal Pendonor</a> | ";
echo "<a href='logout_paksa.php'>→ Logout Paksa</a>";
?>