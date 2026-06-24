<?php
/**
 * SIDORAH - profil_pendonor.php
 * Halaman edit profil untuk pendonor
 */
require_once 'koneksi.php';

if (empty($_SESSION['id_pengguna'])) { header('Location: login.php'); exit(); }
if ($_SESSION['role'] !== 'pendonor') { header('Location: dashboard.php'); exit(); }

$id_user = $_SESSION['id_pengguna'];
$pendonor = $koneksi->query("
    SELECT p.*, u.nama_lengkap, u.email, u.no_telepon
    FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna
    WHERE p.id_pengguna=$id_user
")->fetch_assoc();

if (!$pendonor) { header('Location: portal_pendonor.php'); exit(); }

$pesan = '';
$tipe  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'ganti_password') {
        $pw_lama  = trim($_POST['password_lama']);
        $pw_baru  = trim($_POST['password_baru']);
        $pw_ulang = trim($_POST['password_ulang']);

        $user = $koneksi->query("SELECT password FROM users WHERE id_pengguna=$id_user")->fetch_assoc();

        if (!password_verify($pw_lama, $user['password'])) {
            $pesan = 'Password lama salah.';
            $tipe  = 'danger';
        } elseif (strlen($pw_baru) < 6) {
            $pesan = 'Password baru minimal 6 karakter.';
            $tipe  = 'danger';
        } elseif ($pw_baru !== $pw_ulang) {
            $pesan = 'Konfirmasi password tidak cocok.';
            $tipe  = 'danger';
        } else {
            $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
            $koneksi->query("UPDATE users SET password='$hash' WHERE id_pengguna=$id_user");
            catat_log($koneksi,'GANTI_PASSWORD','users',"Pendonor {$pendonor['nama_lengkap']} ganti password");
            $pesan = 'Password berhasil diubah!';
            $tipe  = 'success';
        }
    }
}

$sym = $pendonor['rhesus']==='Positif'?'+':'-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya — SIDORAH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8f9fa; }
        .topbar { background: #1a1a2e; height: 60px; display: flex; align-items: center; padding: 0 1.5rem; position: sticky; top: 0; z-index: 100; }
        .topbar .brand { color: white; font-weight: 800; font-size: 1.2rem; text-decoration: none; }
        .topbar .brand i { color: #dc3545; }
        .card { border-radius: 16px; border: none; }
        .avatar-big {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; font-weight: 800; color: white;
        }
        .form-label { font-weight: 600; font-size: 0.875rem; }
        .form-control, .form-select { border-radius: 10px; }
        .btn-save { background: #dc3545; color: white; border: none; border-radius: 10px; font-weight: 600; }
        .btn-save:hover { background: #c0392b; color: white; }
        .nav-tabs .nav-link { font-weight: 600; color: #6c757d; }
        .nav-tabs .nav-link.active { color: #dc3545; border-bottom: 2px solid #dc3545; }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <a href="portal_pendonor.php" class="brand">
        <i class="bi bi-heart-pulse-fill"></i> SIDORAH
    </a>
    <div class="ms-auto d-flex gap-3 align-items-center">
        <a href="portal_pendonor.php" class="text-white-50 small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <a href="logout.php" class="text-white-50 small text-decoration-none">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</div>

<div class="container py-4" style="max-width:700px">

    <!-- Header Profil -->
    <div class="card shadow-sm p-4 mb-4">
        <div class="d-flex align-items-center gap-4">
            <div class="avatar-big">
                <?= strtoupper(substr($pendonor['nama_lengkap'],0,1)) ?>
            </div>
            <div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($pendonor['nama_lengkap']) ?></h4>
                <div class="text-muted small"><?= htmlspecialchars($pendonor['email']) ?></div>
                <div class="d-flex gap-2 mt-2">
                    <span class="badge bg-danger fw-bold">
                        <?= $pendonor['golongan_darah'] ?? '?' ?><?= $sym ?>
                    </span>
                    <span class="badge bg-success">
                        <i class="bi bi-droplet-fill me-1"></i><?= $pendonor['total_donor'] ?> kali donor
                    </span>
                    <?php if ($pendonor['tanggal_lahir']): ?>
                    <span class="badge bg-secondary"><?= hitung_umur($pendonor['tanggal_lahir']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe ?> alert-dismissible fade show rounded-3">
        <i class="bi bi-<?= $tipe==='success'?'check-circle-fill':'exclamation-circle-fill' ?> me-2"></i>
        <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Ganti Password -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabPassword">
            <div class="card shadow-sm p-4">
                <h6 class="fw-bold mb-4"><i class="bi bi-lock-fill text-danger me-2"></i>Ganti Password</h6>
                <form method="POST" style="max-width:400px">
                    <input type="hidden" name="aksi_form" value="ganti_password">
                    <div class="mb-3">
                        <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                        <input type="password" name="password_lama" class="form-control" required
                               placeholder="Masukkan password saat ini">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password_baru" class="form-control" required
                               minlength="6" placeholder="Min. 6 karakter">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password_ulang" class="form-control" required
                               placeholder="Ulangi password baru">
                    </div>
                    <button type="submit" class="btn btn-save px-4 py-2">
                        <i class="bi bi-lock me-2"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>