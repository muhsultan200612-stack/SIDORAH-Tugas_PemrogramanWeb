<?php
/**
 * SIDORAH - File sementara untuk buat/reset akun Super Admin
 * PENTING: Hapus file ini setelah digunakan!
 */
require_once 'koneksi.php';

$email    = 'superadmin@sidorah.id';
$password = 'SuperAdmin123!';
$hash     = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah email sudah ada
$cek = $koneksi->query("SELECT id_pengguna, email FROM users WHERE email='$email' LIMIT 1");

if ($cek->num_rows > 0) {
    // Update password yang sudah ada
    $koneksi->query("UPDATE users SET password='$hash', status_akun='aktif' WHERE email='$email'");
    echo "<div style='font-family:sans-serif; padding:30px; background:#d4edda; border-radius:10px; max-width:500px; margin:50px auto;'>";
    echo "<h2 style='color:#155724'>✅ Password berhasil direset!</h2>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Password baru:</strong> $password</p>";
    echo "<p><strong>Hash:</strong> <code style='font-size:0.8rem'>$hash</code></p>";
    echo "<hr><a href='login.php' style='color:#155724; font-weight:bold'>→ Klik di sini untuk Login</a>";
    echo "</div>";
} else {
    // Buat akun baru
    $nama = 'Super Administrator';
    $telp = '08100000001';
    $role = 'super_admin';
    $status = 'aktif';

    $stmt = $koneksi->prepare("INSERT INTO users (nama_lengkap, email, password, no_telepon, role, status_akun) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $nama, $email, $hash, $telp, $role, $status);

    if ($stmt->execute()) {
        echo "<div style='font-family:sans-serif; padding:30px; background:#d4edda; border-radius:10px; max-width:500px; margin:50px auto;'>";
        echo "<h2 style='color:#155724'>✅ Akun Super Admin berhasil dibuat!</h2>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<hr><a href='login.php' style='color:#155724; font-weight:bold'>→ Klik di sini untuk Login</a>";
        echo "</div>";
    } else {
        echo "<div style='font-family:sans-serif; padding:30px; background:#f8d7da; border-radius:10px; max-width:500px; margin:50px auto;'>";
        echo "<h2 style='color:#721c24'>❌ Gagal membuat akun</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
        echo "</div>";
    }
    $stmt->close();
}
?>

<div style='font-family:sans-serif; padding:15px 30px; background:#fff3cd; border-radius:10px; max-width:500px; margin:10px auto;'>
    ⚠️ <strong>PENTING:</strong> Hapus file <code>reset_superadmin.php</code> ini setelah berhasil login!
</div>