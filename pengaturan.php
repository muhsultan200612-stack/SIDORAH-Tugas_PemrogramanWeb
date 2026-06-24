<?php
/**
 * SIDORAH - pengaturan.php
 * Pengaturan sistem - Super Admin only
 */
require_once 'koneksi.php';
paksa_login();
if ($_SESSION['role'] === 'pendonor') { header('Location: portal_pendonor.php'); exit(); }

$id_user   = $_SESSION['id_pengguna'];
$pesan     = '';
$tipe      = '';
$bisa_edit = cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

// Buat tabel pengaturan jika belum ada
$koneksi->query("
    CREATE TABLE IF NOT EXISTS `pengaturan` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `kunci` varchar(100) NOT NULL,
        `nilai` text DEFAULT NULL,
        `keterangan` varchar(200) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `kunci` (`kunci`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Seed default settings jika kosong
$cek = $koneksi->query("SELECT COUNT(*) as n FROM pengaturan")->fetch_assoc()['n'];
if ($cek == 0) {
    $koneksi->query("INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
        ('nama_rs', 'RS SIDORAH', 'Nama Rumah Sakit'),
        ('alamat_rs', 'Jl. Kesehatan No.1, Makassar', 'Alamat Rumah Sakit'),
        ('telp_rs', '0411-123-456', 'Telepon Rumah Sakit'),
        ('email_rs', 'info@sidorah.id', 'Email Rumah Sakit'),
        ('jam_operasional', '24 Jam / 7 Hari', 'Jam Operasional'),
        ('telp_darurat', '119', 'Nomor Darurat'),
        ('min_usia_donor', '17', 'Usia Minimum Donor'),
        ('max_usia_donor', '65', 'Usia Maksimum Donor'),
        ('min_bb_donor', '45', 'Berat Badan Minimum Donor (kg)'),
        ('min_hb_donor', '12.5', 'Hemoglobin Minimum Donor (g/dL)'),
        ('jeda_donor_bulan', '3', 'Jeda Minimum Antar Donor (bulan)'),
        ('password_default_pendonor', 'Donor123!', 'Password Default Pendonor Baru'),
        ('ip_server', '10.99.133.126', 'IP Server untuk QR Code (ganti sesuai IP WiFi aktif)')
    ");
}

// Ambil semua setting
$settings = [];
$res = $koneksi->query("SELECT kunci, nilai FROM pengaturan");
while ($r = $res->fetch_assoc()) {
    $settings[$r['kunci']] = $r['nilai'];
}

// PROSES SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    // Cek hak akses edit
    if (!$bisa_edit) {
        $_SESSION['pesan'] = 'Anda tidak memiliki izin untuk mengubah pengaturan.';
        $_SESSION['tipe']  = 'danger';
        redirect('pengaturan.php');
    }

    if ($aksi === 'simpan_rs') {
        $fields = ['nama_rs','alamat_rs','telp_rs','email_rs','jam_operasional','telp_darurat','ip_server'];
        foreach ($fields as $f) {
            $val = bersihkan($koneksi, $_POST[$f] ?? '');
            $koneksi->query("UPDATE pengaturan SET nilai='$val' WHERE kunci='$f'");
        }
        catat_log($koneksi,'UPDATE_PENGATURAN','pengaturan','Update info rumah sakit');
        $pesan = 'Informasi rumah sakit berhasil disimpan!';
        $tipe  = 'success';
        // Reload settings
        $settings = [];
        $res = $koneksi->query("SELECT kunci, nilai FROM pengaturan");
        while ($r = $res->fetch_assoc()) $settings[$r['kunci']] = $r['nilai'];
    }

    if ($aksi === 'simpan_donor') {
        $fields = ['min_usia_donor','max_usia_donor','min_bb_donor','min_hb_donor','jeda_donor_bulan','password_default_pendonor'];
        foreach ($fields as $f) {
            $val = bersihkan($koneksi, $_POST[$f] ?? '');
            $koneksi->query("UPDATE pengaturan SET nilai='$val' WHERE kunci='$f'");
        }
        catat_log($koneksi,'UPDATE_PENGATURAN_DONOR','pengaturan','Update aturan donor');
        $pesan = 'Aturan donor berhasil disimpan!';
        $tipe  = 'success';
        $settings = [];
        $res = $koneksi->query("SELECT kunci, nilai FROM pengaturan");
        while ($r = $res->fetch_assoc()) $settings[$r['kunci']] = $r['nilai'];
    }
}

$halaman_aktif = 'pengaturan.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .setting-card { border-radius:16px; border:none; }
        .nav-tabs .nav-link { font-weight:600;color:#6c757d;border:none; }
        .nav-tabs .nav-link.active { color:#dc3545;border-bottom:2px solid #dc3545; }
        .setting-icon {
            width:40px;height:40px;border-radius:10px;
            background:#fef2f2;display:flex;
            align-items:center;justify-content:center;flex-shrink:0;
        }
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
                    <h1 class="h3 mb-0"><i class="bi bi-gear-fill text-danger me-2"></i>Pengaturan Sistem</h1>
                    <ol class="breadcrumb mb-0 mt-1">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaturan</li>
                    </ol>
                </div>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show rounded-3">
                    <i class="bi bi-check-circle-fill me-2"></i><?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4 border-0">
                    <li class="nav-item">
                        <a class="nav-link active border-0" data-bs-toggle="tab" href="#tabRS">
                            <i class="bi bi-hospital-fill me-1"></i>Info Rumah Sakit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border-0" data-bs-toggle="tab" href="#tabDonor">
                            <i class="bi bi-droplet-fill me-1"></i>Aturan Donor
                        </a>
                    </li>
                    <?php if (isSuperAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link border-0" data-bs-toggle="tab" href="#tabSistem">
                            <i class="bi bi-cpu-fill me-1"></i>Info Sistem
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content">

                    <!-- Tab Info RS -->
                    <div class="tab-pane fade show active" id="tabRS">
                        <div class="card setting-card shadow-sm p-4" style="max-width:700px">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="setting-icon">
                                    <i class="bi bi-hospital-fill text-danger fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Informasi Rumah Sakit</h6>
                                    <small class="text-muted">Ditampilkan di halaman publik & laporan</small>
                                </div>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="aksi_form" value="simpan_rs">
                                <?php $disabled = $bisa_edit ? '' : 'disabled'; ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama Rumah Sakit</label>
                                        <input type="text" name="nama_rs" class="form-control"
                                               value="<?= htmlspecialchars($settings['nama_rs'] ?? '') ?>" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">No. Telepon</label>
                                        <input type="text" name="telp_rs" class="form-control"
                                               value="<?= htmlspecialchars($settings['telp_rs'] ?? '') ?>"
                                               placeholder="0411-xxx-xxx" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email Resmi</label>
                                        <input type="email" name="email_rs" class="form-control"
                                               value="<?= htmlspecialchars($settings['email_rs'] ?? '') ?>"
                                               placeholder="info@rumahsakit.id" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">No. Darurat</label>
                                        <input type="text" name="telp_darurat" class="form-control"
                                               value="<?= htmlspecialchars($settings['telp_darurat'] ?? '') ?>"
                                               placeholder="119" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Jam Operasional</label>
                                        <input type="text" name="jam_operasional" class="form-control"
                                               value="<?= htmlspecialchars($settings['jam_operasional'] ?? '') ?>"
                                               placeholder="24 Jam / 7 Hari" <?= $disabled ?>>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            IP Server <span class="text-danger">*</span>
                                            <span class="badge bg-warning text-dark ms-1">Penting untuk QR Code</span>
                                        </label>
                                        <input type="text" name="ip_server" class="form-control"
                                               value="<?= htmlspecialchars($settings['ip_server'] ?? '') ?>"
                                               placeholder="Contoh: 10.99.133.126" <?= $disabled ?>>
                                        <div class="form-text">
                                            IP ini dipakai untuk QR Code pendonor. Cek IP laptop dengan <code>ipconfig</code> di CMD.
                                            IP aktif saat ini: <strong><?= $_SERVER['SERVER_ADDR'] ?? 'unknown' ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                                        <textarea name="alamat_rs" class="form-control" rows="2"
                                                  placeholder="Jl. Nama Jalan No.X, Kota" <?= $disabled ?>><?= htmlspecialchars($settings['alamat_rs'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <?php if ($bisa_edit): ?>
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bi bi-save me-2"></i>Simpan Informasi RS
                                        </button>
                                        <?php else: ?>
                                        <div class="alert alert-warning py-2 small mb-0">
                                            <i class="bi bi-eye-fill me-1"></i>
                                            Anda hanya bisa melihat pengaturan. Hubungi Admin untuk mengubah.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab Aturan Donor -->
                    <div class="tab-pane fade" id="tabDonor">
                        <div class="card setting-card shadow-sm p-4" style="max-width:700px">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="setting-icon">
                                    <i class="bi bi-droplet-fill text-danger fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Aturan & Syarat Donor Darah</h6>
                                    <small class="text-muted">Standar medis untuk kelayakan donor</small>
                                </div>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="aksi_form" value="simpan_donor">
                                <?php $disabled = $bisa_edit ? '' : 'disabled'; ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Usia Minimum (tahun)</label>
                                        <input type="number" name="min_usia_donor" class="form-control"
                                               value="<?= $settings['min_usia_donor'] ?? 17 ?>" min="1" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Usia Maksimum (tahun)</label>
                                        <input type="number" name="max_usia_donor" class="form-control"
                                               value="<?= $settings['max_usia_donor'] ?? 65 ?>" min="1" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">BB Minimum (kg)</label>
                                        <input type="number" name="min_bb_donor" class="form-control"
                                               value="<?= $settings['min_bb_donor'] ?? 45 ?>" min="1" step="0.5" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Hb Minimum (g/dL)</label>
                                        <input type="number" name="min_hb_donor" class="form-control"
                                               value="<?= $settings['min_hb_donor'] ?? 12.5 ?>" min="1" step="0.1" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jeda Antar Donor (bulan)</label>
                                        <input type="number" name="jeda_donor_bulan" class="form-control"
                                               value="<?= $settings['jeda_donor_bulan'] ?? 3 ?>" min="1" <?= $disabled ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Password Default Pendonor</label>
                                        <?php if (isSuperAdmin()): ?>
                                        <input type="text" name="password_default_pendonor" class="form-control"
                                               value="<?= htmlspecialchars($settings['password_default_pendonor'] ?? 'Donor123!') ?>">
                                        <div class="form-text">Password saat akun pendonor baru dibuat</div>
                                        <?php else: ?>
                                        <input type="password" class="form-control" value="••••••••••" disabled>
                                        <input type="hidden" name="password_default_pendonor"
                                               value="<?= htmlspecialchars($settings['password_default_pendonor'] ?? 'Donor123!') ?>">
                                        <div class="form-text text-warning">
                                            <i class="bi bi-lock-fill me-1"></i>Hanya Super Admin yang bisa mengubah
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info py-2 small">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Perubahan ini hanya untuk referensi — validasi medis tetap dilakukan petugas secara manual.
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <?php if ($bisa_edit): ?>
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bi bi-save me-2"></i>Simpan Aturan Donor
                                        </button>
                                        <?php else: ?>
                                        <div class="alert alert-warning py-2 small mb-0">
                                            <i class="bi bi-eye-fill me-1"></i>
                                            Anda hanya bisa melihat pengaturan. Hubungi Admin untuk mengubah.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab Info Sistem -->
                    <?php if (isSuperAdmin()): ?>
                    <div class="tab-pane fade" id="tabSistem">
                        <div class="card setting-card shadow-sm p-4" style="max-width:700px">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="setting-icon">
                                    <i class="bi bi-cpu-fill text-danger fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Informasi Sistem</h6>
                                    <small class="text-muted">Detail teknis server & database</small>
                                </div>
                            </div>
                            <?php
                            $db_size = $koneksi->query("
                                SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) as size
                                FROM information_schema.tables
                                WHERE table_schema='dbsidorah'
                            ")->fetch_assoc()['size'];
                            $total_users = $koneksi->query("SELECT COUNT(*) as n FROM users")->fetch_assoc()['n'];
                            $total_pendonor = $koneksi->query("SELECT COUNT(*) as n FROM pendonor")->fetch_assoc()['n'];
                            $total_riwayat = $koneksi->query("SELECT COUNT(*) as n FROM riwayat_donor")->fetch_assoc()['n'];
                            ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Versi PHP</div>
                                        <div class="fw-bold"><?= phpversion() ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Database</div>
                                        <div class="fw-bold">MySQL / MariaDB</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Ukuran Database</div>
                                        <div class="fw-bold"><?= $db_size ?> MB</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Total Akun Pengguna</div>
                                        <div class="fw-bold"><?= $total_users ?> akun</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Total Pendonor</div>
                                        <div class="fw-bold"><?= $total_pendonor ?> pendonor</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Total Riwayat Donor</div>
                                        <div class="fw-bold"><?= $total_riwayat ?> rekord</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Server Time</div>
                                        <div class="fw-bold"><?= date('d/m/Y H:i:s') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="small text-muted mb-1">Aplikasi</div>
                                        <div class="fw-bold">SIDORAH v1.0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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
</body>
</html>