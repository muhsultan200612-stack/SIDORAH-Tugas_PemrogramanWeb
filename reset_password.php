<?php
/**
 * SIDORAH - reset_password.php
 * Halaman isi password baru via link token
 */
require_once 'koneksi.php';

if (sudahLogin()) {
    redirect($_SESSION['role'] === 'pendonor' ? 'portal_pendonor.php' : 'dashboard.php');
}

// Ambil nama RS
$res_rs  = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='nama_rs' LIMIT 1");
$nama_rs = ($res_rs && $res_rs->num_rows > 0) ? $res_rs->fetch_assoc()['nilai'] : 'RS SIDORAH';

$token   = bersihkan($koneksi, $_GET['token'] ?? '');
$pesan   = '';
$tipe    = '';
$sukses  = false;
$valid   = false;
$user    = null;

// Set timezone Indonesia
date_default_timezone_set('Asia/Makassar');

// Validasi token
if ($token) {
    $reset = $koneksi->query("
        SELECT pr.*, u.nama_lengkap, u.email
        FROM password_reset pr
        JOIN users u ON pr.id_pengguna = u.id_pengguna
        WHERE pr.token = '$token'
        AND pr.used = 0
        AND pr.expired_at > NOW()
        LIMIT 1
    ")->fetch_assoc();

    if ($reset) {
        $valid = true;
        $user  = $reset;
    } else {
        $pesan = 'Link reset password tidak valid atau sudah kadaluarsa.';
        $tipe  = 'danger';
    }
}

// Proses simpan password baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $pw_baru  = trim($_POST['password_baru'] ?? '');
    $pw_ulang = trim($_POST['password_ulang'] ?? '');

    if (strlen($pw_baru) < 6) {
        $pesan = 'Password minimal 6 karakter.';
        $tipe  = 'danger';
    } elseif ($pw_baru !== $pw_ulang) {
        $pesan = 'Konfirmasi password tidak cocok.';
        $tipe  = 'danger';
    } else {
        $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
        $id   = $user['id_pengguna'];

        // Update password
        $koneksi->query("UPDATE users SET password='$hash' WHERE id_pengguna=$id");

        // Tandai token sudah dipakai
        $koneksi->query("UPDATE password_reset SET used=1 WHERE token='$token'");

        catat_log($koneksi, 'RESET_PASSWORD', 'users', "Reset password berhasil: {$user['email']}");

        $sukses = true;
        $pesan  = 'Password berhasil diubah! Silakan login dengan password baru.';
        $tipe   = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — <?= htmlspecialchars($nama_rs) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f1f5f9; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card-reset { border-radius:20px; border:none; box-shadow:0 8px 32px rgba(0,0,0,0.1); max-width:440px; width:100%; }
        .btn-merah { background:#dc3545; color:white; border:none; border-radius:10px; font-weight:600; }
        .btn-merah:hover { background:#c0392b; color:white; }
        .strength-bar { height:4px; border-radius:4px; transition:width 0.3s,background 0.3s; }
    </style>
</head>
<body>
<div class="p-3" style="width:100%;max-width:440px">

    <div class="text-center mb-4">
        <a href="login.php" style="text-decoration:none">
            <i class="bi bi-heart-pulse-fill text-danger" style="font-size:2rem"></i>
            <div class="fw-bold fs-5 text-dark"><?= htmlspecialchars($nama_rs) ?></div>
        </a>
    </div>

    <div class="card card-reset p-4">

        <?php if ($sukses): ?>
        <!-- Sukses -->
        <div class="text-center py-3">
            <div style="font-size:3rem;margin-bottom:12px">✅</div>
            <h5 class="fw-bold mb-2">Password Berhasil Diubah!</h5>
            <p class="text-muted small mb-4"><?= $pesan ?></p>
            <a href="login.php" class="btn btn-merah w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
            </a>
        </div>

        <?php elseif (!$valid): ?>
        <!-- Token tidak valid -->
        <div class="text-center py-3">
            <div style="font-size:3rem;margin-bottom:12px">❌</div>
            <h5 class="fw-bold mb-2 text-danger">Link Tidak Valid</h5>
            <div class="alert alert-danger small"><?= $pesan ?></div>
            <p class="text-muted small mb-4">Link mungkin sudah kadaluarsa atau sudah digunakan.</p>
            <a href="lupa_password.php" class="btn btn-merah w-100 py-2 mb-2">
                <i class="bi bi-arrow-repeat me-2"></i>Minta Link Baru
            </a>
            <a href="login.php" class="btn btn-outline-secondary w-100 py-2">
                Kembali ke Login
            </a>
        </div>

        <?php else: ?>
        <!-- Form Reset Password -->
        <div class="text-center mb-4">
            <div style="width:60px;height:60px;border-radius:50%;background:#fef2f2;
                        display:flex;align-items:center;justify-content:center;
                        margin:0 auto 12px;font-size:1.6rem">🔑</div>
            <h5 class="fw-bold mb-1">Buat Password Baru</h5>
            <p class="text-muted small">
                Halo <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong>,
                buat password baru untuk akunmu.
            </p>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe ?> py-2 small"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password_baru" id="pw_baru"
                           class="form-control border-start-0" required minlength="6"
                           placeholder="Min. 6 karakter"
                           oninput="cekKekuatan(this.value)">
                    <button class="btn btn-outline-secondary border-start-0" type="button"
                            onclick="togglePw('pw_baru', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <!-- Indikator kekuatan password -->
                <div class="mt-2">
                    <div style="background:#e5e7eb;border-radius:4px;height:4px">
                        <div id="strength_bar" class="strength-bar" style="width:0%;background:#dc3545"></div>
                    </div>
                    <div id="strength_text" class="small text-muted mt-1"></div>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-lock-fill text-muted"></i>
                    </span>
                    <input type="password" name="password_ulang" id="pw_ulang"
                           class="form-control border-start-0" required
                           placeholder="Ulangi password baru">
                    <button class="btn btn-outline-secondary border-start-0" type="button"
                            onclick="togglePw('pw_ulang', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-merah w-100 py-2">
                <i class="bi bi-check-circle me-2"></i>Simpan Password Baru
            </button>
        </form>
        <?php endif; ?>

    </div>

    <div class="text-center mt-3 text-muted small">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($nama_rs) ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}

function cekKekuatan(pw) {
    const bar  = document.getElementById('strength_bar');
    const text = document.getElementById('strength_text');
    let score  = 0;
    if (pw.length >= 6)  score++;
    if (pw.length >= 10) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = [
        { pct:'20%', color:'#dc3545', label:'Sangat Lemah' },
        { pct:'40%', color:'#f97316', label:'Lemah' },
        { pct:'60%', color:'#eab308', label:'Sedang' },
        { pct:'80%', color:'#22c55e', label:'Kuat' },
        { pct:'100%', color:'#15803d', label:'Sangat Kuat' },
    ];
    const level = levels[Math.min(score-1, 4)] || levels[0];
    bar.style.width  = level.pct;
    bar.style.background = level.color;
    text.textContent = pw.length > 0 ? level.label : '';
    text.style.color = level.color;
}
</script>
</body>
</html>