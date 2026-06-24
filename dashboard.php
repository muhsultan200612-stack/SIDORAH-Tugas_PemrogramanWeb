<?php
/**
 * SIDORAH - Sistem Informasi Donor Darah
 * File: dashboard.php
 */
require_once 'koneksi.php';
paksa_login();

// Pendonor tidak boleh akses dashboard admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'pendonor') {
    redirect('portal_pendonor.php');
}

// ── STATISTIK UTAMA ───────────────────────────────────────────
// Total pendonor aktif
$total_pendonor = $koneksi->query("SELECT COUNT(*) as n FROM pendonor WHERE status_aktif=1")->fetch_assoc()['n'];

// Total donor bulan ini
$donor_bulan_ini = $koneksi->query("
    SELECT COUNT(*) as n FROM riwayat_donor
    WHERE MONTH(tanggal_donor)=MONTH(CURDATE())
    AND YEAR(tanggal_donor)=YEAR(CURDATE())
    AND hasil_pemeriksaan='layak'
")->fetch_assoc()['n'];

// Total stok darah (semua kantong tersedia)
$total_stok = $koneksi->query("
    SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah
    WHERE status_stok='tersedia'
")->fetch_assoc()['n'];

// Total semua stok masuk (tersedia + kritis + habis)
$total_stok_masuk = $koneksi->query("
    SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah
    WHERE status_stok != 'expired'
")->fetch_assoc()['n'];

// Total stok telah digunakan
$total_digunakan = (int)$koneksi->query("
    SELECT COALESCE(
        (SELECT COUNT(*) FROM transfusi_darah WHERE status='selesai') +
        (SELECT COUNT(*) FROM permintaan_darah WHERE status_permintaan='terpenuhi'),
    0) as n")->fetch_assoc()['n'];

// Permintaan darah menunggu
$permintaan_pending = $koneksi->query("
    SELECT COUNT(*) as n FROM permintaan_darah WHERE status_permintaan='menunggu'
")->fetch_assoc()['n'];

// Kegiatan aktif mendatang
$kegiatan_aktif = $koneksi->query("
    SELECT COUNT(*) as n FROM kegiatan_donor
    WHERE status_kegiatan='aktif' AND tanggal_kegiatan >= CURDATE()
")->fetch_assoc()['n'];

// Pendaftaran menunggu verifikasi
$pendaftaran_pending = $koneksi->query("
    SELECT COUNT(*) as n FROM pendaftaran WHERE status_pendaftaran='menunggu'
")->fetch_assoc()['n'];

// ── STOK PER GOLONGAN DARAH ───────────────────────────────────
$stok_goldar = $koneksi->query("
    SELECT golongan_darah, rhesus,
           COALESCE(SUM(jumlah_kantong),0) as total,
           MAX(status_stok) as status
    FROM stok_darah
    WHERE status_stok != 'expired'
    GROUP BY golongan_darah, rhesus
    ORDER BY FIELD(golongan_darah,'A','B','AB','O'), rhesus DESC
")->fetch_all(MYSQLI_ASSOC);

// ── DONOR TERAKHIR (5 terbaru) ────────────────────────────────
$donor_terakhir = $koneksi->query("
    SELECT rd.tanggal_donor, rd.hasil_pemeriksaan, rd.volume_darah_ml,
           u.nama_lengkap, p.golongan_darah, p.rhesus
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor = p.id_pendonor
    JOIN users u ON p.id_pengguna = u.id_pengguna
    ORDER BY rd.tanggal_donor DESC, rd.id_riwayat DESC
    LIMIT 5
");

// ── KEGIATAN MENDATANG ────────────────────────────────────────
$kegiatan_mendatang = $koneksi->query("
    SELECT kd.*, u.nama_lengkap as nama_admin,
           (kd.kuota_peserta - kd.jumlah_terdaftar) as sisa_kuota
    FROM kegiatan_donor kd
    LEFT JOIN users u ON kd.id_admin = u.id_pengguna
    WHERE kd.status_kegiatan='aktif' AND kd.tanggal_kegiatan >= CURDATE()
    ORDER BY kd.tanggal_kegiatan ASC
    LIMIT 4
");

// ── KEGIATAN DONOR BULAN INI (untuk kalender mini) ──────────
$bulan_ini = date('Y-m');
$kegiatan_kalender = $koneksi->query("
    SELECT tanggal_kegiatan, nama_kegiatan, status_kegiatan,
           jumlah_terdaftar, kuota_peserta, id_kegiatan
    FROM kegiatan_donor
    WHERE DATE_FORMAT(tanggal_kegiatan, '%Y-%m') = '$bulan_ini'
    ORDER BY tanggal_kegiatan ASC
")->fetch_all(MYSQLI_ASSOC);
$kalender_map = [];
foreach ($kegiatan_kalender as $k) {
    $tgl = (int)date('j', strtotime($k['tanggal_kegiatan']));
    $kalender_map[$tgl][] = $k;
}
$hari_ini      = (int)date('j');
$total_hari    = (int)date('t');
$hari_pertama  = (int)date('N', strtotime(date('Y-m-01'))); // 1=Sen..7=Min

// ── GRAFIK DONOR PER BULAN (6 bulan terakhir) ────────────────
$grafik_data = [];
for ($i = 5; $i >= 0; $i--) {
    $res = $koneksi->query("
        SELECT COUNT(*) as n,
               DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL $i MONTH), '%b %Y') as label
        FROM riwayat_donor
        WHERE MONTH(tanggal_donor) = MONTH(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
        AND YEAR(tanggal_donor) = YEAR(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
        AND hasil_pemeriksaan = 'layak'
    ")->fetch_assoc();
    $grafik_data[] = $res;
}

// ── GRAFIK TREN PERMINTAAN VS STOK ───────────────────────────
$tren_permintaan = [];
$tren_stok_masuk = [];
for ($i = 5; $i >= 0; $i--) {
    // Permintaan per bulan
    $res_p = $koneksi->query("
        SELECT COUNT(*) as n,
               SUM(jumlah_kantong) as kantong,
               DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL $i MONTH), '%b %Y') as label
        FROM permintaan_darah
        WHERE MONTH(tanggal_permintaan) = MONTH(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
        AND YEAR(tanggal_permintaan) = YEAR(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
    ")->fetch_assoc();
    $tren_permintaan[] = [
        'label'   => $res_p['label'],
        'total'   => (int)$res_p['n'],
        'kantong' => (int)($res_p['kantong'] ?? 0)
    ];

    // Stok masuk (donor layak) per bulan
    $res_s = $koneksi->query("
        SELECT COUNT(*) as n
        FROM riwayat_donor
        WHERE MONTH(tanggal_donor) = MONTH(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
        AND YEAR(tanggal_donor) = YEAR(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
        AND hasil_pemeriksaan = 'layak'
    ")->fetch_assoc();
    $tren_stok_masuk[] = (int)$res_s['n'];
}

// Stok saat ini per goldar untuk chart
$stok_chart = $koneksi->query("
    SELECT CONCAT(golongan_darah, IF(rhesus='Positif','+','-')) as label,
           COALESCE(SUM(jumlah_kantong),0) as kantong
    FROM stok_darah WHERE status_stok != 'expired'
    GROUP BY golongan_darah, rhesus
    ORDER BY FIELD(golongan_darah,'A','B','AB','O'), rhesus DESC
")->fetch_all(MYSQLI_ASSOC);

// ── CEK DARAH MENDEKATI KADALUARSA (7 hari) ─────────────────
$hampir_exp = $koneksi->query("
    SELECT golongan_darah, rhesus, jumlah_kantong, tanggal_kadaluarsa,
           DATEDIFF(tanggal_kadaluarsa, CURDATE()) as sisa_hari
    FROM stok_darah
    WHERE status_stok != 'expired'
    AND tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND jumlah_kantong > 0
    ORDER BY tanggal_kadaluarsa ASC
")->fetch_all(MYSQLI_ASSOC);

// ── PERMINTAAN DARAH DARURAT ──────────────────────────────────
$permintaan_darurat = $koneksi->query("
    SELECT * FROM permintaan_darah
    WHERE tingkat_urgensi IN ('darurat','mendesak')
    AND status_permintaan='menunggu'
    ORDER BY FIELD(tingkat_urgensi,'darurat','mendesak'), tanggal_permintaan ASC
    LIMIT 5
");

// ── AKTIVITAS LOG TERBARU (super admin only) ──────────────────
$log_terbaru = null;
if (isSuperAdmin()) {
    $log_terbaru = $koneksi->query("
        SELECT * FROM audit_log
        ORDER BY waktu DESC LIMIT 6
    ");
}

// Notif stok kritis — berdasarkan total kantong per goldar+rhesus
$stok_kritis = $koneksi->query("
    SELECT golongan_darah, rhesus,
           SUM(jumlah_kantong) as jumlah_kantong,
           CASE
               WHEN SUM(jumlah_kantong) <= 0 THEN 'habis'
               ELSE 'kritis'
           END as status_stok
    FROM stok_darah
    WHERE status_stok != 'expired'
    GROUP BY golongan_darah, rhesus
    HAVING SUM(jumlah_kantong) <= 5
    ORDER BY SUM(jumlah_kantong) ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* ── Stat Cards ── */
        .stat-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12) !important;
        }
        .stat-card .icon-bg {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -1px;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .wave {
            position: absolute;
            bottom: 0; right: 0;
            opacity: 0.06;
            font-size: 6rem;
            line-height: 1;
        }

        /* ── Stok Darah Cards ── */
        .blood-card {
            border-radius: 14px;
            border: 2px solid transparent;
            transition: all 0.2s;
            cursor: default;
        }
        .blood-card.tersedia  { border-color: #d1fae5; background: #f0fdf4; }
        .blood-card.kritis    { border-color: #fde68a; background: #fffbeb; }
        .blood-card.habis     { border-color: #fecaca; background: #fff5f5; }
        .blood-card .goldar   {
            font-size: 1.8rem; font-weight: 900;
            line-height: 1; letter-spacing: -1px;
        }
        .blood-card .rhesus-badge {
            font-size: 0.65rem; font-weight: 700;
            padding: 2px 7px; border-radius: 20px;
        }
        .blood-card .kantong  { font-size: 1.4rem; font-weight: 800; }

        /* ── Recent Donor Table ── */
        .hasil-layak     { color: #15803d; font-weight: 600; }
        .hasil-tidak     { color: #dc2626; font-weight: 600; }
        .hasil-ditunda   { color: #d97706; font-weight: 600; }

        /* ── Kegiatan Card ── */
        .kegiatan-card {
            border-radius: 14px;
            border: none;
            border-left: 4px solid #dc3545;
            transition: transform 0.2s;
        }
        .kegiatan-card:hover { transform: translateX(4px); }

        /* ── Progress Bar Kuota ── */
        .progress { height: 6px; border-radius: 10px; }

        /* ── Urgensi Badge ── */
        .badge-darurat  { background: #dc2626; color: white; }
        .badge-mendesak { background: #d97706; color: white; }

        /* ── Alert Stok ── */
        .stok-alert {
            border-radius: 12px;
            border: none;
            border-left: 4px solid #dc3545;
        }

        /* ── Log Item ── */
        .log-item {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.82rem;
        }
        .log-item:last-child { border-bottom: none; }

        /* ── Chart container ── */
        #chartDonor { max-height: 220px; }
    </style>
</head>
<body class="sb-nav-fixed">

<?php include 'includes/topnav.php'; ?>

<div id="layoutSidenav">
    <?php include 'includes/sidenav.php'; ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">

                <!-- ── HEADER ── -->
                <div class="d-flex justify-content-between align-items-center mt-4 mb-1">
                    <div>
                        <h1 class="h3 mb-0 fw-bold">
                            <i class="bi bi-heart-pulse-fill text-danger me-2"></i>Dashboard
                        </h1>
                        <p class="text-muted small mb-0">
                            Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong>
                            — <?= date('l, d F Y') ?>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="pendonor.php" class="btn btn-danger btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Pendonor
                        </a>
                        <a href="kegiatan_donor.php" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-calendar-plus me-1"></i>Kegiatan Baru
                        </a>
                    </div>
                </div>

                <!-- ── ALERT STOK KRITIS ── -->
                <?php if ($stok_kritis && $stok_kritis->num_rows > 0): ?>
                <div class="alert stok-alert alert-danger d-flex align-items-center gap-3 mt-3 mb-0 py-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div class="small">
                        <strong>Perhatian!</strong> Stok darah kritis/habis:
                        <?php while ($sk = $stok_kritis->fetch_assoc()): ?>
                        <span class="badge <?= $sk['status_stok']==='habis'?'bg-danger':'bg-warning text-dark' ?> ms-1">
                            <?= $sk['golongan_darah'] ?><?= $sk['rhesus']==='Positif'?'+':'-' ?>
                            <?= $sk['jumlah_kantong'] ?> kantong
                        </span>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Alert Darah Mendekati Kadaluarsa -->
                <?php if (!empty($hampir_exp)): ?>
                <div class="alert alert-warning d-flex align-items-start gap-3 mt-2 mb-0 py-2 rounded-3" style="border-left:4px solid #d97706">
                    <i class="bi bi-clock-history fs-5 text-warning mt-1"></i>
                    <div class="flex-grow-1 small">
                        <strong>Peringatan!</strong> <?= count($hampir_exp) ?> kantong darah mendekati kadaluarsa:
                        <?php foreach ($hampir_exp as $e):
                            $sym   = $e['rhesus']==='Positif'?'+':'-';
                            $warna = $e['sisa_hari'] <= 3 ? 'bg-danger' : 'bg-warning text-dark';
                        ?>
                        <span class="badge <?= $warna ?> ms-1">
                            <?= $e['golongan_darah'].$sym ?> —
                            <?= $e['jumlah_kantong'] ?> kantong —
                            exp <?= tanggal_indo($e['tanggal_kadaluarsa']) ?>
                            (<?= $e['sisa_hari'] ?> hari lagi)
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <a href="stok_darah.php" class="btn btn-sm btn-warning fw-semibold text-nowrap">
                        <i class="bi bi-arrow-right me-1"></i>Kelola Stok
                    </a>
                </div>
                <?php endif; ?>

                <!-- ── STAT CARDS ── -->
                <div class="row g-3 mt-2">

                    <!-- Pendonor Aktif -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-bg" style="background:#fef2f2;">
                                    <i class="bi bi-people-fill text-danger"></i>
                                </div>
                                <div>
                                    <div class="stat-value text-danger"><?= number_format($total_pendonor) ?></div>
                                    <div class="stat-label text-muted">Pendonor Aktif</div>
                                </div>
                            </div>
                            <div class="wave">🩸</div>
                        </div>
                    </div>

                    <!-- Donor Bulan Ini -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-bg" style="background:#eff6ff;">
                                    <i class="bi bi-droplet-fill text-primary"></i>
                                </div>
                                <div>
                                    <div class="stat-value text-primary"><?= number_format($donor_bulan_ini) ?></div>
                                    <div class="stat-label text-muted">Donor Bulan Ini</div>
                                </div>
                            </div>
                            <div class="wave">📅</div>
                        </div>
                    </div>

                    <!-- Stok Kantong -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-bg" style="background:#f0fdf4;">
                                    <i class="bi bi-bag-heart-fill text-success"></i>
                                </div>
                                <div>
                                    <div class="stat-value text-success"><?= number_format($total_stok_masuk) ?></div>
                                    <div class="stat-label text-muted">Total Stok Darah</div>
                                </div>
                            </div>
                            <div class="wave">🏥</div>
                        </div>
                    </div>

                    <!-- Permintaan Pending -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-bg" style="background:#fff7ed;">
                                    <i class="bi bi-bandaid-fill text-warning"></i>
                                </div>
                                <div>
                                    <div class="stat-value text-warning"><?= number_format($permintaan_pending) ?></div>
                                    <div class="stat-label text-muted">Permintaan Pending</div>
                                </div>
                            </div>
                            <div class="wave">⚠️</div>
                        </div>
                    </div>

                    <!-- Kegiatan Aktif -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-bg" style="background:#f5f3ff;">
                                    <i class="bi bi-calendar-event-fill text-purple" style="color:#7c3aed"></i>
                                </div>
                                <div>
                                    <div class="stat-value" style="color:#7c3aed"><?= number_format($kegiatan_aktif) ?></div>
                                    <div class="stat-label text-muted">Kegiatan Aktif</div>
                                </div>
                            </div>
                            <div class="wave">🗓️</div>
                        </div>
                    </div>

                    <!-- Pendaftaran Menunggu -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card stat-card shadow-sm h-100 p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-bg" style="background:#fdf4ff;">
                                    <i class="bi bi-person-check-fill" style="color:#db2777"></i>
                                </div>
                                <div>
                                    <div class="stat-value" style="color:#db2777"><?= number_format($pendaftaran_pending) ?></div>
                                    <div class="stat-label text-muted">Daftar Tunggu</div>
                                </div>
                            </div>
                            <div class="wave">📋</div>
                        </div>
                    </div>

                </div><!-- /stat cards -->

                <!-- ── ROW 2: GRAFIK + STOK DARAH ── -->
                <div class="row g-3 mt-1">

                    <!-- Grafik Donor 6 Bulan + Kalender Mini -->
                    <div class="col-lg-7 d-flex flex-column gap-3">
                        <!-- Chart -->
                        <div class="card border-0 shadow-sm" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">Tren Donor Darah</h6>
                                        <small class="text-muted">6 bulan terakhir (donor layak)</small>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="bi bi-graph-up me-1"></i>Live
                                    </span>
                                </div>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <canvas id="chartDonor"></canvas>
                            </div>
                        </div>

                        <!-- Kalender Mini -->
                        <div class="card border-0 shadow-sm" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0"><i class="bi bi-calendar3 text-danger me-1"></i>Kalender Kegiatan Donor</h6>
                                        <small class="text-muted"><?= date('F Y') ?> — klik tanggal merah untuk detail</small>
                                    </div>
                                    <a href="kegiatan_donor.php" class="btn btn-outline-danger btn-sm py-0">Kelola</a>
                                </div>
                            </div>
                            <div class="card-body px-3 pb-3">
                                <style>
                                .kal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
                                .kal-head {
                                    text-align:center; font-size:0.65rem; font-weight:700;
                                    color:#9ca3af; padding:6px 0 4px;
                                    text-transform:uppercase; letter-spacing:0.5px;
                                }
                                .kal-day {
                                    text-align:center; font-size:0.8rem;
                                    padding:6px 2px 4px; border-radius:10px;
                                    cursor:default; color:#374151;
                                    min-height:38px;
                                    display:flex; flex-direction:column;
                                    align-items:center; justify-content:center; gap:2px;
                                }
                                .kal-day.today {
                                    background:#fee2e2; color:#dc2626;
                                    font-weight:800;
                                    border:1.5px solid #fca5a5;
                                }
                                .kal-day.has-event {
                                    color:#dc2626; font-weight:700; cursor:pointer;
                                }
                                .kal-dot {
                                    width:5px; height:5px; border-radius:50%;
                                    background:#dc3545; flex-shrink:0;
                                }
                                .kal-day.has-event:hover {
                                    background:#fff1f2; transition:0.15s;
                                }
                                .kal-day.has-event.today .kal-dot { background:#dc2626; }
                                .kal-day.empty { min-height:38px; }
                                .kal-dot { width:5px; height:5px; background:white; border-radius:50%; margin:1px auto 0; }
                                </style>
                                <div class="kal-grid mb-1">
                                    <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h): ?>
                                    <div class="kal-head"><?= $h ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="kal-grid" id="kalenderGrid">
                                    <?php
                                    // Empty cells sebelum hari pertama
                                    for ($e = 1; $e < $hari_pertama; $e++):
                                    ?><div class="kal-day empty"></div><?php endfor; ?>

                                    <?php for ($d = 1; $d <= $total_hari; $d++):
                                        $has = isset($kalender_map[$d]);
                                        $cls = $has ? 'has-event' : '';
                                        $cls .= ($d === $hari_ini) ? ' today' : '';
                                        $title = '';
                                        $onclick = '';
                                        if ($has) {
                                            $nama_list = implode(', ', array_column($kalender_map[$d], 'nama_kegiatan'));
                                            $title = htmlspecialchars($nama_list);
                                            $onclick = "window.location='kegiatan_donor.php'";
                                        }
                                    ?>
                                    <div class="kal-day <?= $cls ?>"
                                        <?= $has ? "onclick=\"$onclick\" title=\"$title\"" : '' ?>>
                                        <span><?= $d ?></span>
                                        <?php if ($has): ?><div class="kal-dot"></div><?php endif; ?>
                                    </div>
                                    <?php endfor; ?>
                                </div>

                                <!-- Legend -->
                                <div class="d-flex gap-3 mt-3 small text-muted px-1">
                                    <div class="d-flex align-items-center gap-1">
                                        <div style="width:14px;height:14px;background:#dc3545;border-radius:4px"></div> Ada kegiatan
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <div style="width:14px;height:14px;background:#fee2e2;border:1px solid #dc3545;border-radius:4px"></div> Hari ini
                                    </div>
                                </div>

                                <?php if (!empty($kegiatan_kalender)): ?>
                                <div class="mt-3 pt-2 border-top">
                                    <div class="small fw-semibold text-muted mb-2">Kegiatan bulan ini:</div>
                                    <?php foreach ($kegiatan_kalender as $kg): ?>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-danger-subtle text-danger" style="font-size:0.7rem;min-width:32px">
                                            <?= date('d', strtotime($kg['tanggal_kegiatan'])) ?>
                                        </span>
                                        <span class="small"><?= htmlspecialchars($kg['nama_kegiatan']) ?></span>
                                        <span class="ms-auto small text-muted"><?= $kg['jumlah_terdaftar'] ?>/<?= $kg['kuota_peserta'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center mt-3 small text-muted">
                                    <i class="bi bi-calendar-x me-1"></i>Tidak ada kegiatan bulan ini
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Stok Darah per Golongan -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">Stok Darah</h6>
                                        <small class="text-muted">Per golongan & rhesus</small>
                                    </div>
                                    <a href="stok_darah.php" class="btn btn-outline-danger btn-sm py-0">
                                        <i class="bi bi-plus"></i> Tambah
                                    </a>
                                </div>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <?php if (empty($stok_goldar)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-bag-x fs-2 d-block mb-2"></i>
                                    Belum ada data stok darah
                                </div>
                                <?php else: ?>
                                <div class="row g-2">
                                    <?php foreach ($stok_goldar as $s):
                                        $rhesus_sym = $s['rhesus']==='Positif' ? '+' : '-';
                                        $status_class = match($s['status'] ?? 'tersedia') {
                                            'habis'  => 'habis',
                                            'kritis' => 'kritis',
                                            default  => 'tersedia'
                                        };
                                        $warna = match($status_class) {
                                            'habis'  => '#dc2626',
                                            'kritis' => '#d97706',
                                            default  => '#15803d'
                                        };
                                    ?>
                                    <div class="col-6">
                                        <div class="blood-card <?= $status_class ?> p-3 text-center">
                                            <div class="goldar" style="color:<?= $warna ?>">
                                                <?= $s['golongan_darah'] ?>
                                                <span class="rhesus-badge"
                                                      style="background:<?= $warna ?>20; color:<?= $warna ?>">
                                                    <?= $rhesus_sym ?>
                                                </span>
                                            </div>
                                            <div class="kantong mt-1" style="color:<?= $warna ?>">
                                                <?= number_format($s['total']) ?>
                                            </div>
                                            <div class="small text-muted">kantong</div>
                                            <div class="mt-1">
                                                <span class="badge" style="background:<?= $warna ?>20; color:<?= $warna ?>; font-size:0.65rem">
                                                    <?= ucfirst($status_class) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div><!-- /row 2 -->

                <!-- ── ROW 2b: TREN PERMINTAAN VS STOK ── -->
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">
                                            <i class="bi bi-graph-up-arrow text-danger me-2"></i>
                                            Tren Permintaan Darah vs Stok Masuk
                                        </h6>
                                        <small class="text-muted">6 bulan terakhir</small>
                                    </div>
                                    <div class="d-flex gap-3 small text-muted">
                                        <span><span style="display:inline-block;width:14px;height:3px;background:#dc3545;border-radius:2px;vertical-align:middle;margin-right:4px"></span>Permintaan</span>
                                        <span><span style="display:inline-block;width:14px;height:3px;background:#15803d;border-radius:2px;vertical-align:middle;margin-right:4px"></span>Stok Masuk</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-0">
                                <div class="col-lg-8">
                                    <div class="card-body px-4 pb-3">
                                        <canvas id="chartTrenPermintaanStok" style="max-height:200px"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4" style="border-left:1px solid #f3f4f6">
                                    <div class="card-body px-4 pb-3">
                                        <div class="small fw-semibold text-muted mb-2">Stok per Golongan</div>
                                        <canvas id="chartStokGoldar" style="max-height:200px"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /row 2b -->

                <!-- ── ROW 3: DONOR TERAKHIR + KEGIATAN MENDATANG ── -->
                <div class="row g-3 mt-1">

                    <!-- Riwayat Donor Terbaru -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">Donor Terbaru</h6>
                                        <small class="text-muted">5 riwayat donor terakhir</small>
                                    </div>
                                    <a href="riwayat_donor.php" class="btn btn-link btn-sm text-danger p-0">
                                        Lihat semua <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Pendonor</th>
                                                <th>Gol. Darah</th>
                                                <th>Volume</th>
                                                <th>Tanggal</th>
                                                <th class="pe-4">Hasil</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($donor_terakhir && $donor_terakhir->num_rows > 0):
                                            while ($d = $donor_terakhir->fetch_assoc()):
                                            $hasil_class = match($d['hasil_pemeriksaan']) {
                                                'layak'      => 'hasil-layak',
                                                'tidak_layak'=> 'hasil-tidak',
                                                'ditunda'    => 'hasil-ditunda',
                                                default      => ''
                                            };
                                            $hasil_icon = match($d['hasil_pemeriksaan']) {
                                                'layak'      => 'bi-check-circle-fill',
                                                'tidak_layak'=> 'bi-x-circle-fill',
                                                'ditunda'    => 'bi-pause-circle-fill',
                                                default      => 'bi-circle'
                                            };
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold"><?= htmlspecialchars($d['nama_lengkap']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger fw-bold">
                                                    <?= $d['golongan_darah'] ?><?= $d['rhesus']==='Positif'?'+':'-' ?>
                                                </span>
                                            </td>
                                            <td><?= $d['volume_darah_ml'] ?> ml</td>
                                            <td class="text-muted"><?= tanggal_indo($d['tanggal_donor']) ?></td>
                                            <td class="pe-4 <?= $hasil_class ?>">
                                                <i class="bi <?= $hasil_icon ?> me-1"></i>
                                                <?= ucfirst(str_replace('_',' ',$d['hasil_pemeriksaan'])) ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-droplet fs-3 d-block mb-2"></i>
                                            Belum ada riwayat donor
                                        </td></tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kegiatan Mendatang -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">Kegiatan Mendatang</h6>
                                        <small class="text-muted">Jadwal donor aktif</small>
                                    </div>
                                    <a href="kegiatan_donor.php" class="btn btn-link btn-sm text-danger p-0">
                                        Semua <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body px-4">
                                <?php if ($kegiatan_mendatang && $kegiatan_mendatang->num_rows > 0):
                                    while ($k = $kegiatan_mendatang->fetch_assoc()):
                                    $persen = $k['kuota_peserta'] > 0
                                        ? round(($k['jumlah_terdaftar'] / $k['kuota_peserta']) * 100)
                                        : 0;
                                    $bar_color = $persen >= 90 ? 'bg-danger' : ($persen >= 60 ? 'bg-warning' : 'bg-success');
                                ?>
                                <div class="kegiatan-card card mb-3 p-3 bg-light">
                                    <div class="fw-semibold mb-1" style="font-size:0.9rem">
                                        <?= htmlspecialchars($k['nama_kegiatan']) ?>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-calendar3 me-1"></i><?= tanggal_indo($k['tanggal_kegiatan']) ?>
                                        &nbsp;·&nbsp;
                                        <i class="bi bi-clock me-1"></i><?= substr($k['waktu_mulai'],0,5) ?>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($k['lokasi']) ?>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span><?= $k['jumlah_terdaftar'] ?>/<?= $k['kuota_peserta'] ?> peserta</span>
                                        <span><?= $persen ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar <?= $bar_color ?>"
                                             style="width:<?= $persen ?>%"></div>
                                    </div>
                                </div>
                                <?php endwhile; else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                    Tidak ada kegiatan mendatang
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div><!-- /row 3 -->

                <!-- ── ROW 4: PERMINTAAN DARURAT + LOG AKTIVITAS ── -->
                <div class="row g-3 mt-1 mb-4">

                    <!-- Permintaan Darurat -->
                    <div class="col-lg-<?= isSuperAdmin() ? '6' : '12' ?>">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">
                                            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                                            Permintaan Mendesak
                                        </h6>
                                        <small class="text-muted">Darurat & mendesak yang belum terpenuhi</small>
                                    </div>
                                    <a href="permintaan_darah.php" class="btn btn-link btn-sm text-danger p-0">
                                        Kelola <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Pasien</th>
                                                <th>Gol. Darah</th>
                                                <th>Jumlah</th>
                                                <th>Hb (g/dL)</th>
                                                <th>Urgensi</th>
                                                <th class="pe-4">Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($permintaan_darurat && $permintaan_darurat->num_rows > 0):
                                            while ($p = $permintaan_darurat->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-semibold"><?= htmlspecialchars($p['nama_pasien']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem">RM: <?= htmlspecialchars($p['no_rekam_medis'] ?: '-') ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger fw-bold">
                                                    <?= $p['golongan_darah'] ?><?= $p['rhesus']==='Positif'?'+':'-' ?>
                                                </span>
                                            </td>
                                            <td><?= $p['jumlah_kantong'] ?> kantong</td>
                                            <td>
                                                <?php if (!empty($p['hemoglobin'])):
                                                    $hb = (float)$p['hemoglobin'];
                                                    if ($hb < 5)      { $hb_bg='#fee2e2'; $hb_color='#991b1b'; $hb_label='Kritis'; }
                                                    elseif ($hb < 7)  { $hb_bg='#ffedd5'; $hb_color='#9a3412'; $hb_label='Berat'; }
                                                    elseif ($hb < 10) { $hb_bg='#fef9c3'; $hb_color='#713f12'; $hb_label='Sedang'; }
                                                    else              { $hb_bg='#dcfce7'; $hb_color='#166534'; $hb_label='Ringan'; }
                                                ?>
                                                <span class="badge rounded-pill px-2" style="background:<?= $hb_bg ?>;color:<?= $hb_color ?>">
                                                    <?= number_format($hb,1) ?> — <?= $hb_label ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $p['tingkat_urgensi'] ?> rounded-pill">
                                                    <?= ucfirst($p['tingkat_urgensi']) ?>
                                                </span>
                                            </td>
                                            <td class="pe-4 text-muted">
                                                <?= format_waktu_singkat($p['tanggal_permintaan']) ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                                            Tidak ada permintaan mendesak
                                        </td></tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Log Aktivitas (Super Admin Only) -->
                    <?php if (isSuperAdmin() && $log_terbaru): ?>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-0">
                                            <i class="bi bi-journal-text text-dark me-1"></i>
                                            Aktivitas Terbaru
                                        </h6>
                                        <small class="text-muted">Log sistem real-time</small>
                                    </div>
                                    <a href="audit_log.php" class="btn btn-link btn-sm text-danger p-0">
                                        Audit Log <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body px-4 py-3">
                                <?php while ($log = $log_terbaru->fetch_assoc()):
                                    $log_color = match($log['status']) {
                                        'gagal'      => 'text-danger',
                                        'peringatan' => 'text-warning',
                                        default      => 'text-success'
                                    };
                                    $log_icon = match($log['status']) {
                                        'gagal'      => 'bi-x-circle-fill text-danger',
                                        'peringatan' => 'bi-exclamation-circle-fill text-warning',
                                        default      => 'bi-check-circle-fill text-success'
                                    };
                                ?>
                                <div class="log-item d-flex align-items-start gap-2">
                                    <i class="bi <?= $log_icon ?> mt-1 flex-shrink-0"></i>
                                    <div class="flex-grow-1" style="min-width:0">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold text-truncate">
                                                <?= htmlspecialchars($log['nama_pengguna']) ?>
                                            </span>
                                            <span class="text-muted ms-2 flex-shrink-0" style="font-size:0.75rem">
                                                <?= format_waktu_singkat($log['waktu']) ?>
                                            </span>
                                        </div>
                                        <div class="text-muted text-truncate">
                                            <span class="badge bg-dark" style="font-size:0.65rem; font-family:monospace">
                                                <?= htmlspecialchars($log['aksi']) ?>
                                            </span>
                                            <?= htmlspecialchars(substr($log['detail'] ?? '', 0, 60)) ?>
                                            <?= strlen($log['detail'] ?? '') > 60 ? '...' : '' ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div><!-- /row 4 -->

            </div><!-- /container -->
        </main>

        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">SIDORAH &copy; <?= date('Y') ?> — Sistem Informasi Donor Darah</div>
                    <div class="text-muted"><?= date('H:i:s') ?> WIB</div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>

<script>
// Data grafik dari PHP
const grafikLabels = <?= json_encode(array_column($grafik_data, 'label')) ?>;
const grafikData   = <?= json_encode(array_column($grafik_data, 'n')) ?>;

// Render Chart
const ctx = document.getElementById('chartDonor').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: grafikLabels,
        datasets: [{
            label: 'Donor Layak',
            data: grafikData,
            backgroundColor: 'rgba(220, 53, 69, 0.15)',
            borderColor: 'rgba(220, 53, 69, 0.9)',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.raw} donor layak`
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    stepSize: 1,
                    font: { size: 11 }
                }
            }
        }
    }
});

// ── Chart Tren Permintaan vs Stok ─────────────────────────────
const trenData = <?= json_encode($tren_permintaan) ?>;
const stokMasuk = <?= json_encode($tren_stok_masuk) ?>;
const stokGoldar = <?= json_encode($stok_chart) ?>;

new Chart(document.getElementById('chartTrenPermintaanStok'), {
    type: 'line',
    data: {
        labels: trenData.map(d => d.label),
        datasets: [
            {
                label: 'Permintaan Darah',
                data: trenData.map(d => d.total),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220,53,69,0.1)',
                borderWidth: 2.5, tension: 0.4,
                fill: true, pointRadius: 5,
                pointBackgroundColor: '#dc3545'
            },
            {
                label: 'Stok Masuk (Donor Layak)',
                data: stokMasuk,
                borderColor: '#15803d',
                backgroundColor: 'rgba(21,128,61,0.05)',
                borderWidth: 2.5, tension: 0.4,
                fill: false, pointRadius: 5,
                pointBackgroundColor: '#15803d',
                borderDash: [5,3]
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    afterBody: function(items) {
                        const idx = items[0].dataIndex;
                        return ['Kantong diminta: ' + trenData[idx].kantong];
                    }
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 },
                 grid: { color: 'rgba(0,0,0,0.04)' } }
        }
    }
});

// ── Chart Stok per Goldar (Donut) ─────────────────────────────
new Chart(document.getElementById('chartStokGoldar'), {
    type: 'doughnut',
    data: {
        labels: stokGoldar.map(d => d.label),
        datasets: [{
            data: stokGoldar.map(d => parseInt(d.kantong)),
            backgroundColor: [
                '#dc3545','#e57373','#1565c0','#64b5f6',
                '#2e7d32','#81c784','#f57f17','#ffcc80'
            ],
            borderWidth: 2, borderColor: '#fff', hoverOffset: 6
        }]
    },
    options: {
        responsive: true, cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, font: { size: 11 }, padding: 8 }
            }
        }
    }
});
</script>
</body>
</html>