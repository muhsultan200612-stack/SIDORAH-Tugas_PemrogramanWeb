<?php
/**
 * SIDORAH - profil.php
 * Profil untuk admin/petugas/manajemen
 */
require_once 'koneksi.php';
paksa_login();
if ($_SESSION['role'] === 'pendonor') { header('Location: portal_pendonor.php'); exit(); }

$id_user = $_SESSION['id_pengguna'];
$user = $koneksi->query("SELECT * FROM users WHERE id_pengguna=$id_user")->fetch_assoc();

$pesan = '';
$tipe  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'update_profil') {
        $nama = bersihkan($koneksi, $_POST['nama_lengkap']);
        $telp = bersihkan($koneksi, $_POST['no_telepon']);

        $koneksi->query("UPDATE users SET nama_lengkap='$nama', no_telepon='$telp' WHERE id_pengguna=$id_user");
        $_SESSION['nama_lengkap'] = $nama;
        catat_log($koneksi,'UPDATE_PROFIL','users',"$nama update profil");
        $pesan = 'Profil berhasil diperbarui!';
        $tipe  = 'success';
        $user  = $koneksi->query("SELECT * FROM users WHERE id_pengguna=$id_user")->fetch_assoc();
    }

    if ($aksi === 'ganti_password') {
        $pw_lama  = trim($_POST['password_lama']);
        $pw_baru  = trim($_POST['password_baru']);
        $pw_ulang = trim($_POST['password_ulang']);

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
            catat_log($koneksi,'GANTI_PASSWORD','users',"{$user['nama_lengkap']} ganti password");
            $pesan = 'Password berhasil diubah!';
            $tipe  = 'success';
        }
    }
}

$halaman_aktif = 'profil.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .avatar-xl {
            width:80px;height:80px;border-radius:50%;
            background:linear-gradient(135deg,#c0392b,#e74c3c);
            display:flex;align-items:center;justify-content:center;
            font-size:2rem;font-weight:800;color:white;flex-shrink:0;
        }
        .profile-card { border-radius:16px;border:none; }
        .nav-tabs .nav-link { font-weight:600;color:#6c757d;border:none; }
        .nav-tabs .nav-link.active { color:#dc3545;border-bottom:2px solid #dc3545; }
        .info-item { padding:10px 0;border-bottom:1px solid #f3f4f6; }
        .info-item:last-child { border-bottom:none; }
    </style>
</head>
<body class="sb-nav-fixed">
<?php include 'includes/topnav.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidenav.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">

                <div class="mt-4 mb-3">
                    <h1 class="h3 mb-0"><i class="bi bi-person-circle text-danger me-2"></i>Profil Saya</h1>
                    <ol class="breadcrumb mb-0 mt-1">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil Saya</li>
                    </ol>
                </div>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show rounded-3">
                    <i class="bi bi-<?= $tipe==='success'?'check-circle-fill':'exclamation-circle-fill' ?> me-2"></i>
                    <?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row g-4">

                    <!-- Sidebar Info -->
                    <div class="col-md-4">
                        <div class="card profile-card shadow-sm p-4 text-center mb-3">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="avatar-xl">
                                    <?= strtoupper(substr($user['nama_lengkap'],0,1)) ?>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['nama_lengkap']) ?></h5>
                            <div class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></div>
                            <?= badge_role($user['role']) ?>
                            <hr>
                            <div class="text-start">
                                <div class="info-item d-flex justify-content-between small">
                                    <span class="text-muted">No. Telepon</span>
                                    <span class="fw-semibold"><?= htmlspecialchars($user['no_telepon'] ?: '-') ?></span>
                                </div>
                                <div class="info-item d-flex justify-content-between small">
                                    <span class="text-muted">Status Akun</span>
                                    <?= badge_status_akun($user['status_akun']) ?>
                                </div>
                                <div class="info-item d-flex justify-content-between small">
                                    <span class="text-muted">Bergabung</span>
                                    <span class="fw-semibold"><?= tanggal_indo(substr($user['created_at'],0,10)) ?></span>
                                </div>
                                <?php if (!empty($user['last_login'])): ?>
                                <div class="info-item d-flex justify-content-between small">
                                    <span class="text-muted">Login Terakhir</span>
                                    <span class="fw-semibold"><?= format_waktu_singkat($user['last_login']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit -->
                    <div class="col-md-8">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-4 border-0" id="profilTab">
                            <li class="nav-item">
                                <a class="nav-link active border-0" data-bs-toggle="tab" href="#tabProfil">
                                    <i class="bi bi-person-fill me-1"></i>Edit Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link border-0" data-bs-toggle="tab" href="#tabPassword">
                                    <i class="bi bi-lock-fill me-1"></i>Ganti Password
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            <!-- Tab Edit Profil -->
                            <div class="tab-pane fade show active" id="tabProfil">
                                <div class="card profile-card shadow-sm p-4">
                                    <h6 class="fw-bold mb-4">
                                        <i class="bi bi-pencil-fill text-danger me-2"></i>Edit Data Profil
                                    </h6>
                                    <form method="POST">
                                        <input type="hidden" name="aksi_form" value="update_profil">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_lengkap" class="form-control" required
                                                       value="<?= htmlspecialchars($user['nama_lengkap']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Email</label>
                                                <input type="email" class="form-control"
                                                       value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                                <div class="form-text">Email tidak bisa diubah</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">No. Telepon</label>
                                                <input type="text" name="no_telepon" class="form-control"
                                                       value="<?= htmlspecialchars($user['no_telepon'] ?? '') ?>"
                                                       placeholder="08xxxxxxxxxx">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Role</label>
                                                <input type="text" class="form-control"
                                                       value="<?= ucfirst(str_replace('_',' ',$user['role'])) ?>" disabled>
                                                <div class="form-text">Role tidak bisa diubah sendiri</div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-danger px-4">
                                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Tab Ganti Password -->
                            <div class="tab-pane fade" id="tabPassword">
                                <div class="card profile-card shadow-sm p-4">
                                    <h6 class="fw-bold mb-4">
                                        <i class="bi bi-lock-fill text-danger me-2"></i>Ganti Password
                                    </h6>
                                    <form method="POST" style="max-width:420px">
                                        <input type="hidden" name="aksi_form" value="ganti_password">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Password Lama <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password_lama" id="pw_lama"
                                                       class="form-control" required placeholder="Password saat ini">
                                                <button class="btn btn-outline-secondary" type="button"
                                                        onclick="togglePw('pw_lama',this)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password_baru" id="pw_baru"
                                                       class="form-control" required minlength="6"
                                                       placeholder="Min. 6 karakter">
                                                <button class="btn btn-outline-secondary" type="button"
                                                        onclick="togglePw('pw_baru',this)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password_ulang" id="pw_ulang"
                                                       class="form-control" required
                                                       placeholder="Ulangi password baru">
                                                <button class="btn btn-outline-secondary" type="button"
                                                        onclick="togglePw('pw_ulang',this)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bi bi-lock me-2"></i>Ubah Password
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="text-muted small">SIDORAH &copy; <?= date('Y') ?></div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}
<?php if ($pesan && isset($_POST['aksi_form']) && $_POST['aksi_form'] === 'ganti_password'): ?>
document.querySelector('[href="#tabPassword"]').click();
<?php endif; ?>
</script>
</body>
</html>