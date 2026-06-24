<?php
/**
 * SIDORAH - cari.php
 * Halaman hasil pencarian global
 */
require_once 'koneksi.php';
paksa_login();

if ($_SESSION['role'] === 'pendonor') redirect('portal_pendonor.php');

$q = bersihkan($koneksi, $_GET['q'] ?? '');
$q_like = '%' . $q . '%';

$hasil_pendonor    = [];
$hasil_kegiatan    = [];
$hasil_permintaan  = [];
$hasil_stok        = [];

if (strlen($q) >= 2) {
    // Cari pendonor
    $stmt = $koneksi->prepare("
        SELECT p.id_pendonor, u.nama_lengkap, u.email, u.no_telepon,
               p.golongan_darah, p.rhesus, p.total_donor, p.status_aktif
        FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna
        WHERE u.nama_lengkap LIKE ? OR u.email LIKE ? OR p.nik LIKE ?
        LIMIT 6
    ");
    $stmt->bind_param('sss', $q_like, $q_like, $q_like);
    $stmt->execute();
    $hasil_pendonor = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Cari kegiatan donor
    $stmt2 = $koneksi->prepare("
        SELECT id_kegiatan, nama_kegiatan, tanggal_kegiatan, lokasi,
               status_kegiatan, jumlah_terdaftar, kuota_peserta
        FROM kegiatan_donor
        WHERE nama_kegiatan LIKE ? OR lokasi LIKE ?
        ORDER BY tanggal_kegiatan DESC LIMIT 5
    ");
    $stmt2->bind_param('ss', $q_like, $q_like);
    $stmt2->execute();
    $hasil_kegiatan = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();

    // Cari permintaan darah
    $stmt3 = $koneksi->prepare("
        SELECT id_permintaan, nama_pasien, no_rekam_medis,
               golongan_darah, rhesus, jumlah_kantong,
               tingkat_urgensi, status_permintaan, tanggal_permintaan
        FROM permintaan_darah
        WHERE nama_pasien LIKE ? OR no_rekam_medis LIKE ?
        ORDER BY tanggal_permintaan DESC LIMIT 5
    ");
    $stmt3->bind_param('ss', $q_like, $q_like);
    $stmt3->execute();
    $hasil_permintaan = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt3->close();

    // Cari stok darah
    $stmt4 = $koneksi->prepare("
        SELECT id_stok, golongan_darah, rhesus, jumlah_kantong,
               jenis_darah, status_stok, tanggal_kadaluarsa
        FROM stok_darah
        WHERE (golongan_darah LIKE ? OR rhesus LIKE ?)
        AND status_stok != 'expired'
        ORDER BY tanggal_kadaluarsa ASC LIMIT 5
    ");
    $stmt4->bind_param('ss', $q_like, $q_like);
    $stmt4->execute();
    $hasil_stok = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt4->close();
}

$total_hasil = count($hasil_pendonor) + count($hasil_kegiatan) + count($hasil_permintaan) + count($hasil_stok);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pencarian: <?= htmlspecialchars($q) ?> — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .hasil-item {
            padding: .75rem 1rem;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            margin-bottom: .5rem;
            transition: all .15s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .hasil-item:hover {
            background: #fef2f2;
            border-color: #dc3545;
            color: inherit;
        }
        .section-title {
            font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: #6b7280; margin-bottom: .75rem;
            padding-bottom: .4rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .highlight { background: #fef08a; border-radius: 3px; padding: 0 2px; }
        .badge-urgensi-darurat  { background:#fee2e2; color:#991b1b; }
        .badge-urgensi-mendesak { background:#fef3c7; color:#92400e; }
        .badge-urgensi-normal   { background:#f0fdf4; color:#166534; }
    </style>
</head>
<body class="sb-nav-fixed">
<?php include 'includes/topnav.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidenav.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">

                <div class="mt-4 mb-4">
                    <h1 class="h3 mb-1">
                        <i class="bi bi-search text-danger me-2"></i>Hasil Pencarian
                    </h1>
                    <?php if ($q): ?>
                    <p class="text-muted mb-0">
                        <?= $total_hasil ?> hasil untuk
                        <strong>"<?= htmlspecialchars($q) ?>"</strong>
                    </p>
                    <?php endif; ?>
                </div>

                <?php if (strlen($q) < 2): ?>
                <!-- Belum ada query -->
                <div class="card border-0 shadow-sm p-5 text-center">
                    <i class="bi bi-search fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Ketik minimal 2 karakter untuk mencari</h5>
                    <p class="text-muted small">Cari pendonor, kegiatan donor, permintaan darah, atau stok darah</p>
                </div>

                <?php elseif ($total_hasil === 0): ?>
                <!-- Tidak ada hasil -->
                <div class="card border-0 shadow-sm p-5 text-center">
                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada hasil untuk "<?= htmlspecialchars($q) ?>"</h5>
                    <p class="text-muted small">Coba kata kunci lain seperti nama pendonor, lokasi kegiatan, atau golongan darah</p>
                </div>

                <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">

                        <!-- PENDONOR -->
                        <?php if (!empty($hasil_pendonor)): ?>
                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <div class="section-title">
                                <i class="bi bi-droplet-fill text-danger me-1"></i>
                                Pendonor (<?= count($hasil_pendonor) ?>)
                            </div>
                            <?php foreach ($hasil_pendonor as $p):
                                $sym = $p['rhesus']==='Positif'?'+':'-';
                            ?>
                            <a href="riwayat_donor.php?id=<?= $p['id_pendonor'] ?>" class="hasil-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:36px;height:36px;border-radius:50%;background:#dc3545;
                                                display:flex;align-items:center;justify-content:center;
                                                color:white;font-weight:700;font-size:.85rem;flex-shrink:0">
                                        <?= strtoupper(substr($p['nama_lengkap'],0,1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?= htmlspecialchars($p['nama_lengkap']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($p['email']) ?></div>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-danger fw-bold"><?= $p['golongan_darah'].$sym ?></span>
                                        <span class="badge bg-primary-subtle text-primary"><?= $p['total_donor'] ?> kali</span>
                                        <span class="badge <?= $p['status_aktif']?'bg-success':'bg-secondary' ?>">
                                            <?= $p['status_aktif']?'Aktif':'Nonaktif' ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                            <a href="pendonor.php?cari=<?= urlencode($q) ?>" class="small text-danger text-decoration-none">
                                Lihat semua hasil pendonor →
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- KEGIATAN DONOR -->
                        <?php if (!empty($hasil_kegiatan)): ?>
                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <div class="section-title">
                                <i class="bi bi-calendar-event-fill text-danger me-1"></i>
                                Kegiatan Donor (<?= count($hasil_kegiatan) ?>)
                            </div>
                            <?php foreach ($hasil_kegiatan as $k): ?>
                            <a href="kegiatan_donor.php" class="hasil-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($k['nama_kegiatan']) ?></div>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-calendar3 me-1"></i><?= tanggal_indo($k['tanggal_kegiatan']) ?>
                                            &nbsp;·&nbsp;
                                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($k['lokasi']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= $k['jumlah_terdaftar'] ?>/<?= $k['kuota_peserta'] ?> peserta
                                        </div>
                                    </div>
                                    <?php
                                    $st_color = match($k['status_kegiatan']) {
                                        'aktif'      => 'bg-success',
                                        'selesai'    => 'bg-secondary',
                                        'dibatalkan' => 'bg-danger',
                                        default      => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $st_color ?>"><?= ucfirst($k['status_kegiatan']) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- PERMINTAAN DARAH -->
                        <?php if (!empty($hasil_permintaan)): ?>
                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <div class="section-title">
                                <i class="bi bi-bandaid-fill text-danger me-1"></i>
                                Permintaan Darah (<?= count($hasil_permintaan) ?>)
                            </div>
                            <?php foreach ($hasil_permintaan as $r):
                                $sym = $r['rhesus']==='Positif'?'+':'-';
                            ?>
                            <a href="permintaan_darah.php" class="hasil-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($r['nama_pasien']) ?></div>
                                        <div class="small text-muted">
                                            RM: <?= htmlspecialchars($r['no_rekam_medis'] ?: '-') ?>
                                            &nbsp;·&nbsp; <?= $r['jumlah_kantong'] ?> kantong
                                            &nbsp;·&nbsp; <?= tanggal_indo(date('Y-m-d', strtotime($r['tanggal_permintaan']))) ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-danger fw-bold"><?= $r['golongan_darah'].$sym ?></span>
                                        <span class="badge badge-urgensi-<?= $r['tingkat_urgensi'] ?>">
                                            <?= ucfirst($r['tingkat_urgensi']) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- STOK DARAH -->
                        <?php if (!empty($hasil_stok)): ?>
                        <div class="card border-0 shadow-sm p-4 mb-4">
                            <div class="section-title">
                                <i class="bi bi-bag-heart-fill text-danger me-1"></i>
                                Stok Darah (<?= count($hasil_stok) ?>)
                            </div>
                            <?php foreach ($hasil_stok as $s):
                                $sym = $s['rhesus']==='Positif'?'+':'-';
                                $st_badge = match($s['status_stok']) {
                                    'tersedia' => 'bg-success',
                                    'kritis'   => 'bg-warning text-dark',
                                    'habis'    => 'bg-danger',
                                    default    => 'bg-secondary'
                                };
                            ?>
                            <a href="stok_darah.php" class="hasil-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-danger fw-bold fs-6"><?= $s['golongan_darah'].$sym ?></span>
                                        <div>
                                            <div class="fw-semibold"><?= $s['jumlah_kantong'] ?> kantong · <?= $s['jenis_darah'] ?></div>
                                            <div class="small text-muted">Exp: <?= tanggal_indo($s['tanggal_kadaluarsa']) ?></div>
                                        </div>
                                    </div>
                                    <span class="badge <?= $st_badge ?>"><?= ucfirst($s['status_stok']) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Sidebar Tips -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm p-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Tips Pencarian</h6>
                            <ul class="small text-muted ps-3">
                                <li class="mb-2">Ketik <strong>nama pendonor</strong> untuk cari data pendonor</li>
                                <li class="mb-2">Ketik <strong>A, B, AB, O</strong> untuk cari stok/permintaan per golongan darah</li>
                                <li class="mb-2">Ketik <strong>nama kegiatan</strong> atau <strong>lokasi</strong> untuk cari jadwal donor</li>
                                <li class="mb-2">Ketik <strong>nama pasien</strong> atau <strong>nomor RM</strong> untuk cari permintaan darah</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

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