<?php
/**
 * SIDORAH - laporan_donor.php
 * Laporan rekap donor darah per periode + diagram lengkap
 */
require_once 'koneksi.php';
paksa_login();
if ($_SESSION['role'] === 'pendonor') { header('Location: portal_pendonor.php'); exit(); }

// Ambil data RS dari pengaturan
$setting_rs = [];
$res_set = $koneksi->query("SELECT kunci, nilai FROM pengaturan");
if ($res_set) {
    while ($r = $res_set->fetch_assoc()) $setting_rs[$r['kunci']] = $r['nilai'];
}
$nama_rs    = $setting_rs['nama_rs']        ?? 'RS SIDORAH';
$alamat_rs  = $setting_rs['alamat_rs']      ?? 'Makassar';
$telp_rs    = $setting_rs['telp_rs']        ?? '-';
$email_rs   = $setting_rs['email_rs']       ?? '-';

// ── FILTER ────────────────────────────────────────────────────
$dari   = bersihkan($koneksi, $_GET['dari']   ?? date('Y-m-01'));
$sampai = bersihkan($koneksi, $_GET['sampai'] ?? date('Y-m-d'));
$goldar = bersihkan($koneksi, $_GET['goldar'] ?? '');
$hasil  = bersihkan($koneksi, $_GET['hasil']  ?? '');

$where = "WHERE rd.tanggal_donor BETWEEN '$dari' AND '$sampai'";
if ($goldar) $where .= " AND p.golongan_darah='$goldar'";
if ($hasil)  $where .= " AND rd.hasil_pemeriksaan='$hasil'";

// ── STATISTIK UTAMA ───────────────────────────────────────────
$stat = $koneksi->query("
    SELECT
        COUNT(*) as total,
        SUM(rd.hasil_pemeriksaan='layak') as layak,
        SUM(rd.hasil_pemeriksaan='tidak_layak') as tidak_layak,
        SUM(rd.hasil_pemeriksaan='ditunda') as ditunda,
        COALESCE(SUM(rd.volume_darah_ml),0) as total_volume,
        COUNT(DISTINCT rd.id_pendonor) as jumlah_pendonor,
        ROUND(AVG(rd.hemoglobin),1) as avg_hb
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    $where
")->fetch_assoc();

// ── DATA RIWAYAT ──────────────────────────────────────────────
$riwayat = $koneksi->query("
    SELECT rd.*, u.nama_lengkap, p.golongan_darah, p.rhesus,
           pt.nama_lengkap as nama_petugas
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    JOIN users u ON p.id_pengguna=u.id_pengguna
    LEFT JOIN users pt ON rd.id_petugas_medis=pt.id_pengguna
    $where
    ORDER BY rd.tanggal_donor DESC
");

// ── DATA CHART 1: Trend Donor per Hari ───────────────────────
$trend_harian = $koneksi->query("
    SELECT DATE(rd.tanggal_donor) as tgl,
           COUNT(*) as total,
           SUM(rd.hasil_pemeriksaan='layak') as layak,
           SUM(rd.hasil_pemeriksaan='tidak_layak') as tidak_layak,
           SUM(rd.hasil_pemeriksaan='ditunda') as ditunda
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    $where
    GROUP BY DATE(rd.tanggal_donor)
    ORDER BY tgl ASC
")->fetch_all(MYSQLI_ASSOC);

// ── DATA CHART 2: Per Golongan Darah ─────────────────────────
$per_goldar = $koneksi->query("
    SELECT CONCAT(p.golongan_darah, IF(p.rhesus='Positif','+','-')) as label,
           COUNT(*) as total,
           SUM(rd.hasil_pemeriksaan='layak') as layak
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    $where
    GROUP BY p.golongan_darah, p.rhesus
    ORDER BY FIELD(p.golongan_darah,'A','B','AB','O'), p.rhesus DESC
")->fetch_all(MYSQLI_ASSOC);

// ── DATA CHART 3: Distribusi Hasil ───────────────────────────
$dist_hasil = [
    (int)($stat['layak'] ?? 0),
    (int)($stat['tidak_layak'] ?? 0),
    (int)($stat['ditunda'] ?? 0)
];

// ── DATA CHART 4: Trend 6 Bulan (selalu tampil) ──────────────
$trend_6bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $res = $koneksi->query("
        SELECT COUNT(*) as total,
               SUM(hasil_pemeriksaan='layak') as layak,
               DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL $i MONTH), '%b %Y') as label
        FROM riwayat_donor
        WHERE MONTH(tanggal_donor)=MONTH(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
        AND YEAR(tanggal_donor)=YEAR(DATE_SUB(CURDATE(), INTERVAL $i MONTH))
    ")->fetch_assoc();
    $trend_6bulan[] = $res;
}

// ── TOP PENDONOR ──────────────────────────────────────────────
$top_pendonor = $koneksi->query("
    SELECT u.nama_lengkap, p.golongan_darah, p.rhesus,
           COUNT(*) as jumlah_donor,
           SUM(rd.hasil_pemeriksaan='layak') as layak
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    JOIN users u ON p.id_pengguna=u.id_pengguna
    $where AND rd.hasil_pemeriksaan='layak'
    GROUP BY rd.id_pendonor
    ORDER BY jumlah_donor DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$persen_layak = $stat['total'] > 0 ? round($stat['layak']/$stat['total']*100,1) : 0;
$total_liter  = round(($stat['total_volume'] ?? 0) / 1000, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Donor — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .stat-card { border-radius:14px; border:none; }
        .chart-card { border-radius:16px; border:none; }
        .hasil-layak      { color:#065f46; font-weight:600; }
        .hasil-tidak_layak{ color:#991b1b; font-weight:600; }
        .hasil-ditunda    { color:#92400e; font-weight:600; }
        .top-rank { width:28px;height:28px;border-radius:50%;display:inline-flex;
                    align-items:center;justify-content:center;font-weight:700;font-size:0.8rem; }
        @media print {
            .no-print { display:none !important; }
            .card { box-shadow:none !important; border:1px solid #dee2e6 !important; }
            body { font-size:12px; }
            .sb-topnav, #layoutSidenav_nav { display:none !important; }
            #layoutSidenav_content { padding-left:0 !important; margin-left:0 !important; }
            #layoutSidenav { display:block !important; }
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

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                    <div>
                        <h1 class="h3 mb-0"><i class="bi bi-bar-chart-fill text-danger me-2"></i>Laporan Donor Darah</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Laporan Donor</li>
                        </ol>
                    </div>
                    <div class="d-flex gap-2 no-print">
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-excel me-1"></i> Excel
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?php
                                        $p = $_GET;
                                        $p['format'] = 'excel';
                                        $p['tipe'] = 'donor';
                                        echo 'export_laporan.php?'.http_build_query($p);
                                    ?>">
                                        <i class="bi bi-download me-2"></i>Download Excel (.xls)
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" target="_blank" href="<?php
                                        $p = $_GET;
                                        $p['format'] = 'pdf';
                                        $p['tipe'] = 'donor';
                                        echo 'export_laporan.php?'.http_build_query($p);
                                    ?>">
                                        <i class="bi bi-printer me-2"></i>Cetak / Simpan PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4 no-print">
                    <div class="card-body py-3">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                                <input type="date" name="dari" class="form-control form-control-sm" value="<?= $dari ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                                <input type="date" name="sampai" class="form-control form-control-sm" value="<?= $sampai ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Golongan Darah</label>
                                <select name="goldar" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    <?php foreach (['A','B','AB','O'] as $g): ?>
                                    <option value="<?= $g ?>" <?= $goldar===$g?'selected':'' ?>>Gol. <?= $g ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Hasil</label>
                                <select name="hasil" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    <option value="layak"       <?= $hasil==='layak'?'selected':'' ?>>Layak</option>
                                    <option value="tidak_layak" <?= $hasil==='tidak_layak'?'selected':'' ?>>Tidak Layak</option>
                                    <option value="ditunda"     <?= $hasil==='ditunda'?'selected':'' ?>>Ditunda</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="bi bi-funnel me-1"></i>Tampilkan
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="laporan_donor.php" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Periode -->
                <div class="alert alert-light border mb-4 py-2">
                    <i class="bi bi-calendar3 me-2 text-danger"></i>
                    Periode: <strong><?= tanggal_indo($dari) ?></strong> s/d <strong><?= tanggal_indo($sampai) ?></strong>
                    <?php if ($goldar): ?> &nbsp;·&nbsp; Golongan: <strong>Gol. <?= $goldar ?></strong><?php endif; ?>
                    <?php if ($hasil): ?> &nbsp;·&nbsp; Hasil: <strong><?= ucfirst(str_replace('_',' ',$hasil)) ?></strong><?php endif; ?>
                </div>

                <!-- ── STAT CARDS ── -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100">
                            <div class="h2 fw-bold text-danger mb-0"><?= number_format($stat['total']) ?></div>
                            <div class="small text-muted">Total Pemeriksaan</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #198754">
                            <div class="h2 fw-bold text-success mb-0"><?= number_format($stat['layak']) ?></div>
                            <div class="small text-muted">Layak Donor</div>
                            <div class="badge bg-success-subtle text-success mt-1"><?= $persen_layak ?>%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #dc3545">
                            <div class="h2 fw-bold text-danger mb-0"><?= number_format($stat['tidak_layak']) ?></div>
                            <div class="small text-muted">Tidak Layak</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #ffc107">
                            <div class="h2 fw-bold text-warning mb-0"><?= number_format($stat['ditunda']) ?></div>
                            <div class="small text-muted">Ditunda</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #0d6efd">
                            <div class="h2 fw-bold text-primary mb-0"><?= $total_liter ?></div>
                            <div class="small text-muted">Liter Darah</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #6f42c1">
                            <div class="h2 fw-bold mb-0" style="color:#6f42c1"><?= number_format($stat['jumlah_pendonor']) ?></div>
                            <div class="small text-muted">Pendonor Unik</div>
                            <?php if ($stat['avg_hb']): ?>
                            <div class="badge bg-light text-muted mt-1">Avg Hb: <?= $stat['avg_hb'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ── ROW CHART 1+2 ── -->
                <div class="row g-3 mb-4">

                    <!-- Chart 1: Trend Donor 6 Bulan (Kurva) -->
                    <div class="col-lg-8">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">📈 Tren Donor 6 Bulan Terakhir</h6>
                                <small class="text-muted">Perbandingan total pemeriksaan vs donor layak</small>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <canvas id="chartTrend6Bulan" style="max-height:230px"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Distribusi Hasil (Donut) -->
                    <div class="col-lg-4">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">🍩 Distribusi Hasil</h6>
                                <small class="text-muted">Periode yang dipilih</small>
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="chartHasil" style="max-height:200px;max-width:200px"></canvas>
                                <div class="mt-3 d-flex gap-3 flex-wrap justify-content-center small">
                                    <span><span style="color:#198754">●</span> Layak (<?= $stat['layak'] ?>)</span>
                                    <span><span style="color:#dc3545">●</span> Tidak Layak (<?= $stat['tidak_layak'] ?>)</span>
                                    <span><span style="color:#ffc107">●</span> Ditunda (<?= $stat['ditunda'] ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── ROW CHART 3+4 ── -->
                <div class="row g-3 mb-4">

                    <!-- Chart 3: Per Golongan Darah (Bar) -->
                    <div class="col-lg-6">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">🩸 Donor per Golongan Darah</h6>
                                <small class="text-muted">Periode yang dipilih</small>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <canvas id="chartGoldar" style="max-height:220px"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 4: Trend Harian (Line) -->
                    <div class="col-lg-6">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">📅 Aktivitas Donor Harian</h6>
                                <small class="text-muted">Dalam periode yang dipilih</small>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <?php if (empty($trend_harian)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-graph-up fs-2 d-block mb-2"></i>
                                    Tidak ada aktivitas donor pada periode ini
                                </div>
                                <?php else: ?>
                                <canvas id="chartHarian" style="max-height:220px"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── TOP PENDONOR + PER GOLDAR TABLE ── -->
                <div class="row g-3 mb-4">

                    <!-- Top 5 Pendonor -->
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">🏆 Top 5 Pendonor Aktif</h6>
                                <small class="text-muted">Berdasarkan jumlah donor layak</small>
                            </div>
                            <div class="card-body px-4">
                                <?php if (empty($top_pendonor)): ?>
                                <p class="text-muted text-center py-3">Belum ada data</p>
                                <?php else:
                                    $rank_colors = ['#FFD700','#C0C0C0','#CD7F32','#0d6efd','#6f42c1'];
                                    foreach ($top_pendonor as $i => $tp):
                                    $sym = $tp['rhesus']==='Positif'?'+':'-';
                                ?>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="top-rank text-white" style="background:<?= $rank_colors[$i] ?? '#6c757d' ?>">
                                        <?= $i+1 ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small"><?= htmlspecialchars($tp['nama_lengkap']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem">
                                            <span class="badge bg-danger"><?= $tp['golongan_darah'].$sym ?></span>
                                            <?= $tp['jumlah_donor'] ?> kali donor
                                        </div>
                                    </div>
                                    <div class="text-success fw-bold small"><?= $tp['layak'] ?>✓</div>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Per Goldar -->
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">📊 Rekap per Golongan Darah</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Golongan</th>
                                            <th>Total</th>
                                            <th>Layak</th>
                                            <th>Tidak Layak</th>
                                            <th class="pe-4">% Layak</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($per_goldar)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data</td></tr>
                                    <?php else: foreach ($per_goldar as $pg):
                                        $pct = $pg['total']>0 ? round($pg['layak']/$pg['total']*100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-danger fw-bold"><?= $pg['label'] ?></span>
                                        </td>
                                        <td class="fw-semibold"><?= $pg['total'] ?></td>
                                        <td class="text-success fw-semibold"><?= $pg['layak'] ?></td>
                                        <td class="text-danger"><?= $pg['total']-$pg['layak'] ?></td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px">
                                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                                </div>
                                                <span class="small text-muted"><?= $pct ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── TABEL DETAIL ── -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">📋 Detail Riwayat Donor</h6>
                            <small class="text-muted"><?= $riwayat->num_rows ?> data</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Pendonor</th>
                                        <th>Gol. Darah</th>
                                        <th>Tanggal</th>
                                        <th>Volume</th>
                                        <th>Hb (g/dL)</th>
                                        <th>Tekanan Darah</th>
                                        <th>Hasil</th>
                                        <th class="pe-4">Petugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($riwayat->num_rows===0): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Tidak ada data pada periode ini
                                </td></tr>
                                <?php else: $no=1; while ($r=$riwayat->fetch_assoc()):
                                    $sym=$r['rhesus']==='Positif'?'+':'-';
                                    $hb_warn = $r['hemoglobin'] && $r['hemoglobin']<12.5;
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                                    <td><span class="badge bg-danger"><?= $r['golongan_darah'].$sym ?></span></td>
                                    <td><?= tanggal_indo($r['tanggal_donor']) ?></td>
                                    <td><?= $r['volume_darah_ml'] ?> ml</td>
                                    <td class="<?= $hb_warn?'text-danger fw-semibold':'' ?>">
                                        <?= $r['hemoglobin']??'-' ?>
                                        <?= $hb_warn?'⚠️':'' ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['tekanan_darah']?:'-') ?></td>
                                    <td class="hasil-<?= $r['hasil_pemeriksaan'] ?>">
                                        <?php
                                        $icon = match($r['hasil_pemeriksaan']) {
                                            'layak'=>'✅','tidak_layak'=>'❌','ditunda'=>'⏸️',default=>''
                                        };
                                        echo $icon.' '.ucfirst(str_replace('_',' ',$r['hasil_pemeriksaan']));
                                        ?>
                                    </td>
                                    <td class="pe-4 text-muted"><?= htmlspecialchars($r['nama_petugas']??'-') ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="row small text-muted">
                    <div class="col-md-8">
                        <strong class="text-dark"><?= htmlspecialchars($nama_rs) ?></strong><br>
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($alamat_rs) ?>
                        &nbsp;|&nbsp;
                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($telp_rs) ?>
                        &nbsp;|&nbsp;
                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($email_rs) ?>
                    </div>
                    <div class="col-md-4 text-md-end">
                        SIDORAH &copy; <?= date('Y') ?><br>
                        Dicetak: <?= date('d/m/Y H:i') ?> WIB
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
// ── Data dari PHP ──────────────────────────────────────────────
const trend6 = <?= json_encode($trend_6bulan) ?>;
const goldarData = <?= json_encode($per_goldar) ?>;
const harianData = <?= json_encode($trend_harian) ?>;
const hasilData  = <?= json_encode($dist_hasil) ?>;

Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";

// ── Chart 1: Trend 6 Bulan (Line) ─────────────────────────────
new Chart(document.getElementById('chartTrend6Bulan'), {
    type: 'line',
    data: {
        labels: trend6.map(d => d.label),
        datasets: [
            {
                label: 'Total Pemeriksaan',
                data: trend6.map(d => parseInt(d.total)),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220,53,69,0.1)',
                borderWidth: 2.5, tension: 0.4, fill: true,
                pointBackgroundColor: '#dc3545', pointRadius: 5
            },
            {
                label: 'Donor Layak',
                data: trend6.map(d => parseInt(d.layak)),
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.05)',
                borderWidth: 2.5, tension: 0.4, fill: false,
                pointBackgroundColor: '#198754', pointRadius: 5,
                borderDash: [5,3]
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 },
                 grid: { color: 'rgba(0,0,0,0.05)' } }
        }
    }
});

// ── Chart 2: Distribusi Hasil (Donut) ─────────────────────────
new Chart(document.getElementById('chartHasil'), {
    type: 'doughnut',
    data: {
        labels: ['Layak', 'Tidak Layak', 'Ditunda'],
        datasets: [{
            data: hasilData,
            backgroundColor: ['#198754','#dc3545','#ffc107'],
            borderWidth: 2, borderColor: '#fff', hoverOffset: 8
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        cutout: '65%',
        plugins: { legend: { display: false } }
    }
});

// ── Chart 3: Per Golongan Darah (Bar) ─────────────────────────
const goldarColors = ['rgba(220,53,69,0.85)','rgba(220,53,69,0.65)',
    'rgba(13,110,253,0.85)','rgba(13,110,253,0.65)',
    'rgba(25,135,84,0.85)','rgba(25,135,84,0.65)',
    'rgba(255,193,7,0.85)','rgba(255,193,7,0.65)'];
new Chart(document.getElementById('chartGoldar'), {
    type: 'bar',
    data: {
        labels: goldarData.map(d => d.label),
        datasets: [
            {
                label: 'Total',
                data: goldarData.map(d => parseInt(d.total)),
                backgroundColor: goldarColors,
                borderRadius: 8, borderSkipped: false
            },
            {
                label: 'Layak',
                data: goldarData.map(d => parseInt(d.layak)),
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderRadius: 8, borderSkipped: false
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ── Chart 4: Aktivitas Harian (Area) ──────────────────────────
<?php if (!empty($trend_harian)): ?>
new Chart(document.getElementById('chartHarian'), {
    type: 'line',
    data: {
        labels: harianData.map(d => d.tgl),
        datasets: [{
            label: 'Total Donor',
            data: harianData.map(d => parseInt(d.total)),
            borderColor: '#6f42c1',
            backgroundColor: 'rgba(111,66,193,0.15)',
            borderWidth: 2.5, tension: 0.3, fill: true,
            pointBackgroundColor: '#6f42c1', pointRadius: 5
        },{
            label: 'Layak',
            data: harianData.map(d => parseInt(d.layak)),
            borderColor: '#198754',
            backgroundColor: 'transparent',
            borderWidth: 2, tension: 0.3, fill: false,
            pointRadius: 4, borderDash: [4,2]
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>