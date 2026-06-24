<?php
/**
 * SIDORAH - lupa_password.php
 * Halaman lupa password — kirim link reset via Gmail
 */
require_once 'koneksi.php';
require_once 'config_email.php';

// Jika sudah login, redirect
if (sudahLogin()) {
    redirect($_SESSION['role'] === 'pendonor' ? 'portal_pendonor.php' : 'dashboard.php');
}

// Ambil nama RS
$res_rs = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='nama_rs' LIMIT 1");
$nama_rs = ($res_rs && $res_rs->num_rows > 0) ? $res_rs->fetch_assoc()['nilai'] : 'RS SIDORAH';

$pesan = '';
$tipe  = '';
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = bersihkan($koneksi, $_POST['email'] ?? '');

    if (empty($email)) {
        $pesan = 'Email tidak boleh kosong.';
        $tipe  = 'danger';
    } else {
        // Cek email ada di database
        $user = $koneksi->query("SELECT id_pengguna, nama_lengkap, email, status_akun FROM users WHERE email='$email' LIMIT 1")->fetch_assoc();

        if (!$user) {
            // Pesan umum agar tidak bocorkan info email
            $pesan = 'Jika email terdaftar, link reset password akan dikirim ke email tersebut.';
            $tipe  = 'info';
        } elseif ($user['status_akun'] !== 'aktif') {
            $pesan = 'Akun ini tidak aktif. Hubungi administrator.';
            $tipe  = 'warning';
        } else {
            // Buat token reset
            $token      = bin2hex(random_bytes(32));
            // Set timezone Indonesia (WITA = UTC+8)
            date_default_timezone_set('Asia/Makassar');
            $expired_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $id         = $user['id_pengguna'];

            // Simpan token ke database
            $koneksi->query("CREATE TABLE IF NOT EXISTS `password_reset` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_pengguna` int(11) NOT NULL,
                `token` varchar(100) NOT NULL,
                `expired_at` datetime NOT NULL,
                `used` tinyint(1) DEFAULT 0,
                `created_at` timestamp DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `token` (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Hapus token lama milik user ini
            $koneksi->query("DELETE FROM password_reset WHERE id_pengguna=$id");

            // Simpan token baru
            $stmt = $koneksi->prepare("INSERT INTO password_reset (id_pengguna, token, expired_at) VALUES (?,?,?)");
            $stmt->bind_param('iss', $id, $token, $expired_at);
            $stmt->execute();
            $stmt->close();

            // Buat link reset
            $res_ip = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='ip_server' LIMIT 1");
            $ip     = ($res_ip && $res_ip->num_rows > 0) ? $res_ip->fetch_assoc()['nilai'] : 'localhost';
            $link_reset = "http://{$ip}/sidorah/reset_password.php?token=$token";

            // Kirim email
            $subject  = "Reset Password Akun SIDORAH";
            $body     = template_email_reset($user['nama_lengkap'], $link_reset, $nama_rs);
            $hasil    = kirim_email($user['email'], $user['nama_lengkap'], $subject, $body);

            if ($hasil['success']) {
                $sukses = true;
                $pesan  = "Link reset password telah dikirim ke <strong>$email</strong>. Cek inbox atau folder spam.";
                $tipe   = 'success';
                catat_log($koneksi, 'LUPA_PASSWORD', 'users', "Request reset password: {$user['email']}");
            } else {
                // Jika email gagal kirim, tampilkan link langsung (mode debug localhost)
                $sukses = true;
                $pesan  = "Email gagal terkirim (mungkin masalah koneksi). <br>Untuk testing di localhost, gunakan link ini:<br>
                           <a href='$link_reset' class='fw-bold'>$link_reset</a>";
                $tipe   = 'warning';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — <?= htmlspecialchars($nama_rs) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f1f5f9; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card-reset { border-radius:20px; border:none; box-shadow:0 8px 32px rgba(0,0,0,0.1); max-width:440px; width:100%; }
        .btn-merah { background:#dc3545; color:white; border:none; border-radius:10px; font-weight:600; }
        .btn-merah:hover { background:#c0392b; color:white; }
    </style>
</head>
<body>
<div class="p-3" style="width:100%;max-width:440px">

    <!-- Brand -->
    <div class="text-center mb-4">
        <a href="login.php" style="text-decoration:none">
            <i class="bi bi-heart-pulse-fill text-danger" style="font-size:2rem"></i>
            <div class="fw-bold fs-5 text-dark"><?= htmlspecialchars($nama_rs) ?></div>
        </a>
    </div>

    <div class="card card-reset p-4">

        <?php if (!$sukses): ?>
        <!-- Form -->
        <div class="text-center mb-4">
            <div style="width:60px;height:60px;border-radius:50%;background:#fef2f2;
                        display:flex;align-items:center;justify-content:center;
                        margin:0 auto 12px;font-size:1.6rem">🔐</div>
            <h5 class="fw-bold mb-1">Lupa Password?</h5>
            <p class="text-muted small">Masukkan email akun kamu. Kami akan kirim link untuk reset password.</p>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe ?> py-2 small">
            <?= $pesan ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0"
                           placeholder="email@rumahsakit.id"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required autofocus>
                </div>
            </div>
            <button type="submit" class="btn btn-merah w-100 py-2 mb-3">
                <i class="bi bi-send me-2"></i>Kirim Link Reset
            </button>
        </form>

        <?php else: ?>
        <!-- Sukses -->
        <div class="text-center py-3">
            <div style="font-size:3rem;margin-bottom:12px">📧</div>
            <h5 class="fw-bold mb-2">Cek Email Kamu!</h5>
            <div class="alert alert-<?= $tipe ?> text-start small">
                <?= $pesan ?>
            </div>
            <p class="text-muted small">Link reset password berlaku selama <strong>1 jam</strong>.</p>
        </div>
        <?php endif; ?>

        <div class="text-center mt-2">
            <a href="login.php" class="text-danger small">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
            </a>
        </div>

    </div>

    <div class="text-center mt-3 text-muted small">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($nama_rs) ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>