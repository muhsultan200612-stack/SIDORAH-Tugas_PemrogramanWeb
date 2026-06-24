<?php
/**
 * SIDORAH - laporan_stok.php
 * Laporan stok darah lengkap + diagram
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

// Auto update expired
$koneksi->query("UPDATE stok_darah SET status_stok='expired' WHERE tanggal_kadaluarsa < CURDATE() AND status_stok != 'expired'");
$koneksi->query("UPDATE stok_darah SET status_stok=CASE WHEN jumlah_kantong<=0 THEN 'habis' WHEN jumlah_kantong<=5 THEN 'kritis' ELSE 'tersedia' END WHERE status_stok!='expired'");

// ── DATA STOK ──────────────────────────────────────────────────
$stok_detail = $koneksi->query("SELECT * FROM stok_darah ORDER BY FIELD(status_stok,'habis','kritis','tersedia','expired'), golongan_darah, rhesus, tanggal_kadaluarsa ASC")->fetch_all(MYSQLI_ASSOC);

// Ringkasan per goldar
$stok_summary = $koneksi->query("
    SELECT golongan_darah, rhesus,
           COALESCE(SUM(CASE WHEN jumlah_kantong > 0 THEN jumlah_kantong ELSE 0 END),0) as tersedia,
           COALESCE(SUM(CASE WHEN status_stok='kritis' THEN jumlah_kantong ELSE 0 END),0) as kritis,
           COALESCE(SUM(CASE WHEN status_stok='habis' THEN 1 ELSE 0 END),0) as habis,
           COALESCE(SUM(jumlah_kantong),0) as total,
           MIN(CASE WHEN status_stok NOT IN ('expired','habis') THEN tanggal_kadaluarsa END) as exp_terdekat
    FROM stok_darah WHERE status_stok != 'expired'
    GROUP BY golongan_darah, rhesus
    ORDER BY FIELD(golongan_darah,'A','B','AB','O'), rhesus DESC
")->fetch_all(MYSQLI_ASSOC);

// Permintaan per goldar
$req_stat = $koneksi->query("
    SELECT golongan_darah, rhesus,
           COUNT(*) as total,
           SUM(jumlah_kantong) as total_kantong,
           SUM(status_permintaan='terpenuhi') as terpenuhi,
           SUM(status_permintaan='menunggu') as menunggu,
           SUM(status_permintaan='tidak_terpenuhi') as tidak_terpenuhi
    FROM permintaan_darah
    GROUP BY golongan_darah, rhesus
    ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);

// Riwayat masuk stok (12 bulan)
$riwayat_masuk = $koneksi->query("
    SELECT DATE_FORMAT(tanggal_masuk,'%b %Y') as label,
           SUM(jumlah_kantong) as masuk,
           MONTH(tanggal_masuk) as bln,
           YEAR(tanggal_masuk) as thn
    FROM stok_darah
    WHERE tanggal_masuk >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY YEAR(tanggal_masuk), MONTH(tanggal_masuk)
    ORDER BY thn, bln
")->fetch_all(MYSQLI_ASSOC);

// Statistik utama
// Kantong tersedia = total semua kantong aktif (tersedia + kritis, bukan habis/expired)
$total_tersedia = $koneksi->query("SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah WHERE status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0")->fetch_assoc()['n'];
$total_kritis   = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='kritis'")->fetch_assoc()['n'];
$total_habis    = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='habis'")->fetch_assoc()['n'];
$total_expired  = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='expired'")->fetch_assoc()['n'];
$total_pending  = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE status_permintaan='menunggu'")->fetch_assoc()['n'];
$total_darurat  = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE tingkat_urgensi='darurat' AND status_permintaan='menunggu'")->fetch_assoc()['n'];

// Data chart stok per goldar
$chart_goldar_labels = [];
$chart_tersedia = [];
$chart_kritis   = [];
foreach ($stok_summary as $s) {
    $sym = $s['rhesus']==='Positif'?'+':'-';
    $chart_goldar_labels[] = $s['golongan_darah'].$sym;
    $chart_tersedia[] = (int)$s['tersedia'];
    $chart_kritis[]   = (int)$s['kritis'];
}

// Data chart distribusi status (pakai jumlah entri, bukan jumlah kantong untuk habis)
$dist_status = [
    (int)$koneksi->query("SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah WHERE status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0")->fetch_assoc()['n'],
    (int)$koneksi->query("SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah WHERE status_stok='kritis'")->fetch_assoc()['n'],
    (int)$koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='habis'")->fetch_assoc()['n'],
];

// Permintaan terpenuhi vs tidak
$req_chart = $koneksi->query("
    SELECT DATE_FORMAT(tanggal_permintaan,'%b %Y') as label,
           SUM(status_permintaan='terpenuhi') as terpenuhi,
           SUM(status_permintaan='tidak_terpenuhi') as tidak,
           SUM(status_permintaan='menunggu') as menunggu
    FROM permintaan_darah
    WHERE tanggal_permintaan >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(tanggal_permintaan), MONTH(tanggal_permintaan)
    ORDER BY tanggal_permintaan ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Stok Darah — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .stat-card { border-radius:14px; border:none; }
        .chart-card { border-radius:16px; border:none; }
        .stok-meter { height:12px; border-radius:20px; background:#e9ecef; overflow:hidden; }
        .stok-meter-fill { height:100%; border-radius:20px; transition:width 0.6s; }
        .goldar-chip { width:44px;height:44px;border-radius:50%;background:#dc3545;color:white;
                       display:flex;align-items:center;justify-content:center;font-weight:800;
                       font-size:0.85rem;flex-shrink:0; }
        @media print {
            .no-print { display:none !important; }
            .card { box-shadow:none !important; border:1px solid #dee2e6 !important; }
            .sb-topnav, #layoutSidenav_nav { display:none !important; }
            #layoutSidenav_content { padding-left:0 !important; margin-left:0 !important; }
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
                        <h1 class="h3 mb-0"><i class="bi bi-bag-heart-fill text-danger me-2"></i>Laporan Stok Darah</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Laporan Stok</li>
                        </ol>
                    </div>
                    <div class="d-flex gap-2 align-items-center no-print">
                        <span class="text-muted small">Update: <?= date('d/m/Y H:i') ?></span>
                        <div class="d-flex gap-2">
                            <a href="export_laporan.php?tipe=stok&format=excel"
                               class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </a>
                            <a href="export_laporan.php?tipe=stok&format=pdf"
                               target="_blank" class="btn btn-danger btn-sm">
                                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alert darurat -->
                <?php if ($total_darurat > 0): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 no-print">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <strong><?= $total_darurat ?> permintaan DARURAT</strong> menunggu pemenuhan!
                    <a href="permintaan_darah.php" class="ms-auto btn btn-sm btn-danger">Lihat Sekarang</a>
                </div>
                <?php endif; ?>

                <!-- ── STAT CARDS ── -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #198754">
                            <div class="h2 fw-bold text-success mb-0"><?= number_format($total_tersedia) ?></div>
                            <div class="small text-muted">Kantong Tersedia</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #ffc107">
                            <div class="h2 fw-bold text-warning mb-0"><?= $total_kritis ?></div>
                            <div class="small text-muted">Stok Kritis</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #dc3545">
                            <div class="h2 fw-bold text-danger mb-0"><?= $total_habis ?></div>
                            <div class="small text-muted">Stok Habis</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #6c757d">
                            <div class="h2 fw-bold text-secondary mb-0"><?= $total_expired ?></div>
                            <div class="small text-muted">Expired</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #0d6efd">
                            <div class="h2 fw-bold text-primary mb-0"><?= $total_pending ?></div>
                            <div class="small text-muted">Permintaan Pending</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card stat-card shadow-sm text-center py-3 h-100" style="border-top:3px solid #dc3545">
                            <div class="h2 fw-bold text-danger mb-0"><?= $total_darurat ?></div>
                            <div class="small text-muted">Permintaan Darurat</div>
                        </div>
                    </div>
                </div>

                <!-- ── ROW CHART 1+2 ── -->
                <div class="row g-3 mb-4">

                    <!-- Chart 1: Stok per Goldar (Grouped Bar) -->
                    <div class="col-lg-7">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">🩸 Stok Darah per Golongan</h6>
                                <small class="text-muted">Tersedia vs Kritis saat ini</small>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <?php if (empty($chart_goldar_labels)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-bag-x fs-2 d-block mb-2"></i>Belum ada data stok
                                </div>
                                <?php else: ?>
                                <canvas id="chartStokGoldar" style="max-height:230px"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Distribusi Status (Donut) -->
                    <div class="col-lg-5">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">🍩 Distribusi Status Stok</h6>
                                <small class="text-muted">Persentase kondisi kantong darah</small>
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="chartDistStatus" style="max-height:180px;max-width:180px"></canvas>
                                <div class="mt-3 d-flex gap-3 flex-wrap justify-content-center small">
                                    <span><span style="color:#198754">●</span> Tersedia (<?= $dist_status[0] ?>)</span>
                                    <span><span style="color:#ffc107">●</span> Kritis (<?= $dist_status[1] ?>)</span>
                                    <span><span style="color:#dc3545">●</span> Habis (<?= $dist_status[2] ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── ROW CHART 3+4 ── -->
                <div class="row g-3 mb-4">

                    <!-- Chart 3: Riwayat Masuk Stok (Bar) -->
                    <div class="col-lg-6">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">📦 Riwayat Masuk Stok (12 Bulan)</h6>
                                <small class="text-muted">Total kantong yang masuk per bulan</small>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <?php if (empty($riwayat_masuk)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada data
                                </div>
                                <?php else: ?>
                                <canvas id="chartMasuk" style="max-height:220px"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 4: Permintaan 6 Bulan (Stacked Bar) -->
                    <div class="col-lg-6">
                        <div class="card chart-card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                                <h6 class="fw-bold mb-0">📋 Permintaan Darah 6 Bulan</h6>
                                <small class="text-muted">Status pemenuhan per bulan</small>
                            </div>
                            <div class="card-body px-4 pb-3">
                                <?php if (empty($req_chart)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-clipboard-x fs-2 d-block mb-2"></i>Belum ada permintaan
                                </div>
                                <?php else: ?>
                                <canvas id="chartPermintaan" style="max-height:220px"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── RINGKASAN STOK PER GOLDAR ── -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-2">📊 Ringkasan Stok per Golongan Darah</h6>
                    </div>
                    <div class="card-body px-4">
                        <?php if (empty($stok_summary)): ?>
                        <p class="text-muted text-center py-3">Belum ada data stok</p>
                        <?php else:
                        $max_stok = max(array_map(fn($s) => (int)$s['tersedia'], $stok_summary) ?: [1]);
                        foreach ($stok_summary as $s):
                            $sym = $s['rhesus']==='Positif'?'+':'-';
                            $pct = $max_stok > 0 ? round($s['tersedia']/$max_stok*100) : 0;
                            $status = $s['total']<=0 ? 'habis' : ($s['total']<=5 ? 'kritis' : 'tersedia');
                            $warna  = match($status) {'habis'=>'#dc3545','kritis'=>'#ffc107',default=>'#198754'};
                            $is_exp_soon = $s['exp_terdekat'] && (strtotime($s['exp_terdekat'])-time()) < 7*24*3600;
                        ?>
                        <div class="row align-items-center mb-3 py-2 border-bottom">
                            <div class="col-auto">
                                <div class="goldar-chip" style="background:<?= $warna ?>">
                                    <?= $s['golongan_darah'].$sym ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="fw-bold" style="color:<?= $warna ?>"><?= number_format($s['tersedia']) ?> kantong</div>
                                <div class="small text-muted">tersedia</div>
                            </div>
                            <div class="col-md-4">
                                <div class="stok-meter">
                                    <div class="stok-meter-fill" style="width:<?= $pct ?>%;background:<?= $warna ?>"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span>Kritis: <?= $s['kritis'] ?></span>
                                    <span>Total: <?= $s['total'] ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 small">
                                <?php if ($s['exp_terdekat']): ?>
                                <div class="<?= $is_exp_soon?'text-warning fw-semibold':'' ?>">
                                    <i class="bi bi-calendar-x me-1"></i>
                                    Exp: <?= tanggal_indo($s['exp_terdekat']) ?>
                                    <?= $is_exp_soon?'⚠️':'' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <span class="badge rounded-pill px-3"
                                      style="background:<?= $warna ?>20;color:<?= $warna ?>;font-weight:600">
                                    <?= ucfirst($status) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- ── PERMINTAAN PER GOLDAR ── -->
                <?php if (!empty($req_stat)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <h6 class="fw-bold mb-2">🏥 Statistik Permintaan Darah per Golongan</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Golongan</th>
                                        <th>Total Permintaan</th>
                                        <th>Kantong Diminta</th>
                                        <th class="text-success">Terpenuhi</th>
                                        <th class="text-warning">Menunggu</th>
                                        <th class="pe-4 text-danger">Tidak Terpenuhi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($req_stat as $r):
                                    $sym = $r['rhesus']==='Positif'?'+':'-';
                                    $pct_t = $r['total']>0 ? round($r['terpenuhi']/$r['total']*100) : 0;
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-danger fw-bold"><?= $r['golongan_darah'].$sym ?></span>
                                    </td>
                                    <td><?= $r['total'] ?></td>
                                    <td><?= $r['total_kantong'] ?> kantong</td>
                                    <td class="text-success fw-semibold"><?= $r['terpenuhi'] ?> (<?= $pct_t ?>%)</td>
                                    <td class="text-warning fw-semibold"><?= $r['menunggu'] ?></td>
                                    <td class="pe-4 text-danger"><?= $r['tidak_terpenuhi'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── DETAIL STOK ── -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold mb-2">📦 Detail Seluruh Stok Darah</h6>
                            <small class="text-muted"><?= count($stok_detail) ?> entri</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Golongan Darah</th>
                                        <th>Jumlah Kantong</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Kadaluarsa</th>
                                        <th class="pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($stok_detail)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-bag-x fs-2 d-block mb-2"></i>Belum ada data stok
                                </td></tr>
                                <?php else: $no=1; foreach ($stok_detail as $s):
                                    $sym = $s['rhesus']==='Positif'?'+':'-';
                                    $is_exp = $s['status_stok']==='expired';
                                    $is_near = !$is_exp && $s['tanggal_kadaluarsa'] &&
                                               (strtotime($s['tanggal_kadaluarsa'])-time()) < 7*24*3600;
                                    $status_colors = ['tersedia'=>'#d1fae5,#065f46','kritis'=>'#fef3c7,#92400e',
                                                      'habis'=>'#fee2e2,#991b1b','expired'=>'#f3f4f6,#6b7280'];
                                    [$bg,$fg] = explode(',',$status_colors[$s['status_stok']]??'#f3f4f6,#6b7280');
                                ?>
                                <tr class="<?= $is_exp?'table-secondary':'' ?>">
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:34px;height:34px;border-radius:50%;
                                                background:<?= $is_exp?'#9ca3af':'#dc3545' ?>;color:white;
                                                display:flex;align-items:center;justify-content:center;
                                                font-weight:800;font-size:0.78rem">
                                                <?= $s['golongan_darah'].$sym ?>
                                            </div>
                                            <span>Gol. <?= $s['golongan_darah'] ?> (<?= $s['rhesus'] ?>)</span>
                                        </div>
                                    </td>
                                    <td class="fw-bold <?= $s['jumlah_kantong']<=5?'text-danger':'text-success' ?>">
                                        <?= $s['jumlah_kantong'] ?> kantong
                                    </td>
                                    <td><?= $s['tanggal_masuk']?tanggal_indo($s['tanggal_masuk']):'-' ?></td>
                                    <td class="<?= $is_near?'text-warning fw-semibold':'' ?>">
                                        <?= $s['tanggal_kadaluarsa']?tanggal_indo($s['tanggal_kadaluarsa']):'-' ?>
                                        <?= $is_near?'⚠️':'' ?>
                                    </td>
                                    <td class="pe-4">
                                        <span class="badge rounded-pill px-3"
                                              style="background:<?= $bg ?>;color:<?= $fg ?>">
                                            <?= ucfirst($s['status_stok']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
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
Chart.defaults.font.family = "system-ui, sans-serif";

const goldarLabels = <?= json_encode($chart_goldar_labels) ?>;
const dataTersedia = <?= json_encode($chart_tersedia) ?>;
const dataKritis   = <?= json_encode($chart_kritis) ?>;
const distStatus   = <?= json_encode($dist_status) ?>;
const masukData    = <?= json_encode($riwayat_masuk) ?>;
const reqData      = <?= json_encode($req_chart) ?>;

// ── Chart 1: Stok per Goldar (Grouped Bar) ────────────────────
<?php if (!empty($chart_goldar_labels)): ?>
new Chart(document.getElementById('chartStokGoldar'), {
    type: 'bar',
    data: {
        labels: goldarLabels,
        datasets: [
            {
                label: 'Tersedia',
                data: dataTersedia,
                backgroundColor: 'rgba(25,135,84,0.8)',
                borderRadius: 8, borderSkipped: false
            },
            {
                label: 'Kritis',
                data: dataKritis,
                backgroundColor: 'rgba(255,193,7,0.8)',
                borderRadius: 8, borderSkipped: false
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
<?php endif; ?>

// ── Chart 2: Distribusi Status (Donut) ────────────────────────
new Chart(document.getElementById('chartDistStatus'), {
    type: 'doughnut',
    data: {
        labels: ['Tersedia', 'Kritis', 'Habis'],
        datasets: [{
            data: distStatus,
            backgroundColor: ['#198754','#ffc107','#dc3545'],
            borderWidth: 2, borderColor: '#fff', hoverOffset: 8
        }]
    },
    options: {
        responsive: true, cutout: '65%',
        plugins: { legend: { display: false } }
    }
});

// ── Chart 3: Masuk Stok 12 Bulan (Bar) ────────────────────────
<?php if (!empty($riwayat_masuk)): ?>
new Chart(document.getElementById('chartMasuk'), {
    type: 'bar',
    data: {
        labels: masukData.map(d => d.label),
        datasets: [{
            label: 'Kantong Masuk',
            data: masukData.map(d => parseInt(d.masuk)),
            backgroundColor: 'rgba(13,110,253,0.75)',
            borderColor: '#0d6efd',
            borderWidth: 1,
            borderRadius: 8, borderSkipped: false
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
<?php endif; ?>

// ── Chart 4: Permintaan 6 Bulan (Stacked Bar) ─────────────────
<?php if (!empty($req_chart)): ?>
new Chart(document.getElementById('chartPermintaan'), {
    type: 'bar',
    data: {
        labels: reqData.map(d => d.label),
        datasets: [
            {
                label: 'Terpenuhi',
                data: reqData.map(d => parseInt(d.terpenuhi)),
                backgroundColor: 'rgba(25,135,84,0.8)',
                borderRadius: 4
            },
            {
                label: 'Menunggu',
                data: reqData.map(d => parseInt(d.menunggu)),
                backgroundColor: 'rgba(255,193,7,0.8)',
                borderRadius: 4
            },
            {
                label: 'Tidak Terpenuhi',
                data: reqData.map(d => parseInt(d.tidak)),
                backgroundColor: 'rgba(220,53,69,0.8)',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true } }
        },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>