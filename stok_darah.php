<?php
/**
 * SIDORAH - stok_darah.php
 * Kelola stok darah
 */
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS, ROLE_MANAJEMEN]);

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'tambah') {
        $goldar  = bersihkan($koneksi, $_POST['golongan_darah']);
        $rhesus  = bersihkan($koneksi, $_POST['rhesus']);
        $kantong = (int)$_POST['jumlah_kantong'];
        $masuk   = bersihkan($koneksi, $_POST['tanggal_masuk']);
        $jenis_darah = bersihkan($koneksi, $_POST['jenis_darah'] ?? 'WB');
        $sumber_stok = bersihkan($koneksi, $_POST['sumber_stok'] ?? 'manual');
        // Auto-hitung kadaluarsa: WB & PRC = +35 hari
        if (!empty($_POST['tanggal_kadaluarsa'])) {
            $kadaluarsa = bersihkan($koneksi, $_POST['tanggal_kadaluarsa']);
        } else {
            $kadaluarsa = date('Y-m-d', strtotime($masuk . ' +35 days'));
        }
        $status  = 'tersedia';
        if ($kantong <= 0)  $status = 'habis';
        elseif ($kantong <= 5) $status = 'kritis';

        $stmt = $koneksi->prepare("INSERT INTO stok_darah (golongan_darah,rhesus,jumlah_kantong,tanggal_masuk,tanggal_kadaluarsa,status_stok,jenis_darah,sumber_stok) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssisssss', $goldar,$rhesus,$kantong,$masuk,$kadaluarsa,$status,$jenis_darah,$sumber_stok);
        if ($stmt->execute()) {
            catat_log($koneksi,'TAMBAH_STOK','stok_darah',"Tambah stok $goldar $rhesus: $kantong kantong",null,['goldar'=>$goldar,'rhesus'=>$rhesus,'kantong'=>$kantong]);
            $_SESSION['pesan'] = "Stok darah <strong>$goldar $rhesus</strong> berhasil ditambahkan ($kantong kantong).";
            $_SESSION['tipe']  = 'success';

            // Hitung total kantong semua entri aktif goldar+rhesus yang sama
            $total_res = $koneksi->query("
                SELECT COALESCE(SUM(jumlah_kantong),0) as total
                FROM stok_darah
                WHERE golongan_darah='$goldar' AND rhesus='$rhesus'
                AND status_stok != 'expired'
            ")->fetch_assoc();
            $total_all = (int)$total_res['total'];

            // Update status semua entri berdasarkan total gabungan
            if ($total_all <= 0) {
                $new_status = 'habis';
            } elseif ($total_all <= 5) {
                $new_status = 'kritis';
            } else {
                $new_status = 'tersedia';
            }
            $koneksi->query("
                UPDATE stok_darah SET status_stok='$new_status'
                WHERE golongan_darah='$goldar' AND rhesus='$rhesus'
                AND status_stok != 'expired'
            ");
        }
        $stmt->close();
        redirect('stok_darah.php');
    }

    if ($aksi === 'edit') {
        $id      = (int)$_POST['id_stok'];
        $kantong = (int)$_POST['jumlah_kantong'];
        $kadaluarsa = bersihkan($koneksi, $_POST['tanggal_kadaluarsa']);
        $status  = bersihkan($koneksi, $_POST['status_stok']);

        $lama = $koneksi->query("SELECT * FROM stok_darah WHERE id_stok=$id")->fetch_assoc();
        $koneksi->query("UPDATE stok_darah SET jumlah_kantong=$kantong, tanggal_kadaluarsa='$kadaluarsa', status_stok='$status' WHERE id_stok=$id");
        catat_log($koneksi,'EDIT_STOK','stok_darah',"Edit stok ID $id: $kantong kantong, status: $status",$lama,['kantong'=>$kantong,'status'=>$status]);
        $_SESSION['pesan'] = "Stok darah berhasil diperbarui.";
        $_SESSION['tipe']  = 'success';
        redirect('stok_darah.php');
    }

    if ($aksi === 'hapus') {
        $id   = (int)$_POST['id_stok'];
        $lama = $koneksi->query("SELECT * FROM stok_darah WHERE id_stok=$id")->fetch_assoc();
        $koneksi->query("DELETE FROM stok_darah WHERE id_stok=$id");
        catat_log($koneksi,'HAPUS_STOK','stok_darah',"Hapus stok {$lama['golongan_darah']} {$lama['rhesus']}: {$lama['jumlah_kantong']} kantong",$lama,null);
        $_SESSION['pesan'] = "Stok darah berhasil dihapus.";
        $_SESSION['tipe']  = 'success';
        redirect('stok_darah.php');
    }
}

// Auto update status kadaluarsa
$koneksi->query("UPDATE stok_darah SET status_stok='expired' WHERE tanggal_kadaluarsa < CURDATE() AND status_stok != 'expired'");

// Auto fix: sinkronkan status berdasarkan TOTAL per goldar+rhesus (bukan per baris)
$koneksi->query("
    UPDATE stok_darah sd
    JOIN (
        SELECT golongan_darah, rhesus,
               SUM(CASE WHEN status_stok != 'expired' THEN jumlah_kantong ELSE 0 END) as total_aktif
        FROM stok_darah
        GROUP BY golongan_darah, rhesus
    ) t ON sd.golongan_darah = t.golongan_darah AND sd.rhesus = t.rhesus
    SET sd.status_stok = CASE
        WHEN sd.status_stok = 'expired' THEN 'expired'
        WHEN sd.jumlah_kantong <= 0     THEN 'habis'
        WHEN t.total_aktif <= 5         THEN 'kritis'
        ELSE 'tersedia'
    END
    WHERE sd.status_stok != 'expired'
");

// Data stok
$filter_goldar  = bersihkan($koneksi, $_GET['goldar']  ?? '');
$filter_status  = bersihkan($koneksi, $_GET['status']  ?? '');
$filter_jenis   = bersihkan($koneksi, $_GET['jenis']   ?? '');
$where = "WHERE 1=1";
if ($filter_goldar) $where .= " AND golongan_darah='$filter_goldar'";
if ($filter_status) $where .= " AND status_stok='$filter_status'";
if ($filter_jenis)  $where .= " AND jenis_darah='$filter_jenis'";

$stok_list = $koneksi->query("SELECT * FROM stok_darah $where ORDER BY FIELD(status_stok,'habis','kritis','tersedia','expired'), golongan_darah, rhesus, tanggal_kadaluarsa ASC");

// Ringkasan per goldar
$ringkasan = $koneksi->query("
    SELECT golongan_darah, rhesus,
           SUM(jumlah_kantong) as total,
           MIN(tanggal_kadaluarsa) as terdekat,
           CASE
               WHEN SUM(jumlah_kantong) <= 0  THEN 'habis'
               WHEN SUM(jumlah_kantong) <= 5  THEN 'kritis'
               ELSE 'tersedia'
           END as status
    FROM stok_darah WHERE status_stok != 'expired'
    GROUP BY golongan_darah, rhesus
    ORDER BY FIELD(golongan_darah,'A','B','AB','O'), rhesus DESC
")->fetch_all(MYSQLI_ASSOC);

$total_tersedia    = $koneksi->query("SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah WHERE status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0")->fetch_assoc()['n'];
$total_kritis      = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='kritis'")->fetch_assoc()['n'];
$total_habis       = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='habis'")->fetch_assoc()['n'];
$total_expired     = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok='expired'")->fetch_assoc()['n'];
$total_semua       = $koneksi->query("SELECT COALESCE(SUM(jumlah_kantong),0) as n FROM stok_darah WHERE status_stok != 'expired'")->fetch_assoc()['n'];
// Total stok telah digunakan (dari transfusi selesai berdasarkan jumlah kantong)
$total_digunakan = $koneksi->query("
    SELECT COALESCE(
        (SELECT SUM(
            CASE
                WHEN jenis_darah='PRC' THEN CEIL(volume_ml/250)
                ELSE CEIL(volume_ml/450)
            END
        ) FROM transfusi_darah WHERE status='selesai'),
    0) as n")->fetch_assoc()['n'];
$total_digunakan = (int)$total_digunakan;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stok Darah — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .stok-badge-tersedia { background:#d1fae5; color:#065f46; }
        .stok-badge-kritis   { background:#fef3c7; color:#92400e; }
        .stok-badge-habis    { background:#fee2e2; color:#991b1b; }
        .stok-badge-expired  { background:#f3f4f6; color:#6b7280; }
        .goldar-chip {
            width:50px; height:50px; border-radius:50%;
            background:#dc3545; color:white;
            display:flex; align-items:center; justify-content:center;
            font-weight:900; font-size:1rem; flex-shrink:0;
        }
        /* Card goldar baru */
        .goldar-card {
            border-radius: 18px;
            padding: 1.25rem 1rem;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: default;
            border: none;
            position: relative;
            overflow: hidden;
        }
        .goldar-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.13) !important; }
        .goldar-card.status-tersedia { background: linear-gradient(135deg,#f0fdf4,#dcfce7); border-left: 4px solid #22c55e !important; }
        .goldar-card.status-kritis   { background: linear-gradient(135deg,#fffbeb,#fef3c7); border-left: 4px solid #f59e0b !important; }
        .goldar-card.status-habis    { background: linear-gradient(135deg,#fff1f2,#fee2e2); border-left: 4px solid #ef4444 !important; }
        .goldar-big-chip {
            width: 52px; height: 52px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 1rem; color: white;
            margin: 0 auto 0.6rem;
        }
        .goldar-card.status-tersedia .goldar-big-chip { background: #16a34a; }
        .goldar-card.status-kritis   .goldar-big-chip { background: #d97706; }
        .goldar-card.status-habis    .goldar-big-chip { background: #dc2626; }
        /* Progress bar stok */
        .stok-progress {
            height: 8px;
            border-radius: 99px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .stok-progress-bar {
            height: 100%;
            border-radius: 99px;
            transition: width 0.5s ease;
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

                <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                    <div>
                        <h1 class="h3 mb-0"><i class="bi bi-bag-heart-fill text-danger me-2"></i>Stok Darah</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Stok Darah</li>
                        </ol>
                    </div>
                    <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS])): ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Stok
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show">
                    <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Stat -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3 col-xl">
                        <div class="card border-0 shadow-sm text-center py-3" style="border-left:3px solid #dc3545 !important">
                            <div class="h3 fw-bold text-danger mb-0"><?= number_format($total_semua) ?></div>
                            <div class="small text-muted">Total Stok Masuk</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-success mb-0"><?= number_format($total_tersedia) ?></div>
                            <div class="small text-muted">Kantong Tersedia</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-warning mb-0"><?= $total_kritis ?></div>
                            <div class="small text-muted">Stok Kritis</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="card border-0 shadow-sm text-center py-3" style="border-left:3px solid #6b7280 !important">
                            <div class="h3 fw-bold text-secondary mb-0"><?= $total_digunakan ?></div>
                            <div class="small text-muted">Stok Digunakan</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-xl">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-secondary mb-0"><?= $total_expired ?></div>
                            <div class="small text-muted">Expired</div>
                        </div>
                    </div>
                </div>

                <!-- Tab View -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <ul class="nav nav-tabs card-header-tabs" id="tabStok">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tabRingkasan">
                                    <i class="bi bi-grid-3x3-gap me-1"></i> Ringkasan
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabDetail">
                                    <i class="bi bi-list-ul me-1"></i> Detail Batch
                                    <span class="badge bg-secondary ms-1" style="font-size:0.7rem"><?= $stok_list->num_rows ?></span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- TAB 1: RINGKASAN -->
                        <div class="tab-pane fade show active p-4" id="tabRingkasan">
                            <?php
                            $ringkasan_tab = $koneksi->query("
                                SELECT golongan_darah, rhesus, jenis_darah,
                                       SUM(jumlah_kantong) as total,
                                       MIN(tanggal_kadaluarsa) as exp_terdekat,
                                       CASE
                                           WHEN SUM(jumlah_kantong) <= 0 THEN 'habis'
                                           WHEN SUM(jumlah_kantong) <= 5 THEN 'kritis'
                                           ELSE 'tersedia'
                                       END as status
                                FROM stok_darah
                                WHERE status_stok != 'expired'
                                GROUP BY golongan_darah, rhesus, jenis_darah
                                ORDER BY FIELD(golongan_darah,'A','B','AB','O'), rhesus DESC, jenis_darah
                            ")->fetch_all(MYSQLI_ASSOC);
                            // Max total untuk progress bar
                            $max_total = 0;
                            foreach ($ringkasan_tab as $r) if ($r['total'] > $max_total) $max_total = $r['total'];
                            $max_total = max($max_total, 20); // minimum skala 20
                            ?>
                            <?php if (empty($ringkasan_tab)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-bag-x fs-2 d-block mb-2"></i>Tidak ada data stok
                            </div>
                            <?php else: ?>

                            <!-- BAGIAN ATAS: Card Grid per Goldar -->
                            <div class="mb-4">
                                <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:1px">
                                    <i class="bi bi-grid me-1"></i>Overview Stok per Golongan Darah
                                </div>
                                <div class="row g-3">
                                <?php
                                // Kelompokkan per goldar+rhesus (gabungkan semua jenis)
                                $card_data = [];
                                foreach ($ringkasan_tab as $r) {
                                    $key = $r['golongan_darah'].'_'.$r['rhesus'];
                                    if (!isset($card_data[$key])) {
                                        $card_data[$key] = [
                                            'golongan_darah' => $r['golongan_darah'],
                                            'rhesus' => $r['rhesus'],
                                            'total' => 0,
                                            'total_wb' => 0,
                                            'total_prc' => 0,
                                            'exp_terdekat' => $r['exp_terdekat'],
                                            'status' => $r['status'],
                                        ];
                                    }
                                    $card_data[$key]['total'] += $r['total'];
                                    if ($r['jenis_darah'] === 'WB')  $card_data[$key]['total_wb']  += $r['total'];
                                    if ($r['jenis_darah'] === 'PRC') $card_data[$key]['total_prc'] += $r['total'];
                                    // ambil status berdasarkan TOTAL semua jenis (bukan per jenis)
                                    $total_gabung = $card_data[$key]['total'];
                                    if ($total_gabung <= 0)     $card_data[$key]['status'] = 'habis';
                                    elseif ($total_gabung <= 5) $card_data[$key]['status'] = 'kritis';
                                    else                        $card_data[$key]['status'] = 'tersedia';
                                }
                                foreach ($card_data as $c):
                                    $sym = $c['rhesus']==='Positif'?'+':'-';
                                    $st = $c['status'];
                                    $icon_st = match($st) { 'habis'=>'🔴', 'kritis'=>'🟡', default=>'🟢' };
                                    $is_near = $c['exp_terdekat'] && (strtotime($c['exp_terdekat']) - time()) < 7*24*3600;
                                ?>
                                <div class="col-6 col-md-3">
                                    <div class="goldar-card shadow-sm status-<?= $st ?>">
                                        <div class="goldar-big-chip"><?= $c['golongan_darah'] ?><?= $sym ?></div>
                                        <div class="fw-black mb-0" style="font-size:2rem;line-height:1"><?= number_format($c['total']) ?></div>
                                        <div class="text-muted small mb-2">kantong tersedia</div>
                                        <?php if ($c['total_wb'] > 0 || $c['total_prc'] > 0): ?>
                                        <div class="d-flex justify-content-center gap-2 mb-2" style="font-size:0.7rem">
                                            <?php if ($c['total_wb'] > 0): ?>
                                            <span class="badge" style="background:#fee2e2;color:#991b1b">
                                                🩸 WB <?= $c['total_wb'] ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if ($c['total_prc'] > 0): ?>
                                            <span class="badge" style="background:#dbeafe;color:#1e40af">
                                                💉 PRC <?= $c['total_prc'] ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <span class="badge rounded-pill stok-badge-<?= $st ?>" style="font-size:0.75rem">
                                            <?= $icon_st ?> <?= ucfirst($st) ?>
                                        </span>
                                        <?php if ($c['exp_terdekat']): ?>
                                        <div class="mt-2 small <?= $is_near?'text-danger fw-semibold':'' ?>" style="font-size:0.72rem">
                                            <?= $is_near ? '⚠️ ' : '' ?>Exp: <?= tanggal_indo($c['exp_terdekat']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- BAGIAN BAWAH: Tabel dengan Progress Bar -->
                            <div>
                                <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:1px">
                                    <i class="bi bi-bar-chart me-1"></i>Detail Stok per Jenis Darah
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" style="border-collapse:separate;border-spacing:0 6px">
                                        <thead>
                                            <tr class="small text-muted">
                                                <th class="ps-2 border-0">Golongan Darah</th>
                                                <th class="border-0">Jenis</th>
                                                <th class="border-0" style="min-width:200px">Stok & Progress</th>
                                                <th class="border-0">Exp. Terdekat</th>
                                                <th class="border-0">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($ringkasan_tab as $r):
                                            $sym = $r['rhesus']==='Positif'?'+':'-';
                                            $st  = $r['status'];
                                            $pct = $max_total > 0 ? min(100, round($r['total']/$max_total*100)) : 0;
                                            $bar_color = match($st) {
                                                'habis'  => '#ef4444',
                                                'kritis' => '#f59e0b',
                                                default  => '#22c55e'
                                            };
                                            $chip_bg = match($st) {
                                                'habis'  => '#9ca3af',
                                                'kritis' => '#d97706',
                                                default  => '#dc3545'
                                            };
                                            $is_near = $r['exp_terdekat'] && (strtotime($r['exp_terdekat']) - time()) < 7*24*3600;
                                        ?>
                                        <tr style="background:white;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.05)">
                                            <td class="ps-3" style="border-radius:12px 0 0 12px">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="goldar-chip" style="width:36px;height:36px;font-size:0.85rem;background:<?= $chip_bg ?>">
                                                        <?= $r['golongan_darah'] ?><?= $sym ?>
                                                    </div>
                                                    <span class="fw-semibold small">Gol. <?= $r['golongan_darah'] ?> <?= $r['rhesus'] ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (($r['jenis_darah']??'WB')==='PRC'): ?>
                                                <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af">💉 PRC</span>
                                                <?php else: ?>
                                                <span class="badge rounded-pill" style="background:#fce7f3;color:#9d174d">🩸 WB</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="fw-bold" style="min-width:28px;color:<?= $bar_color ?>"><?= $r['total'] ?></div>
                                                    <div class="stok-progress flex-grow-1">
                                                        <div class="stok-progress-bar" style="width:<?= $pct ?>%;background:<?= $bar_color ?>"></div>
                                                    </div>
                                                    <div class="small text-muted" style="min-width:40px"><?= $pct ?>%</div>
                                                </div>
                                            </td>
                                            <td class="small <?= $is_near?'text-danger fw-semibold':'' ?>">
                                                <?= $r['exp_terdekat'] ? tanggal_indo($r['exp_terdekat']) : '-' ?>
                                                <?= $is_near ? ' ⚠️' : '' ?>
                                            </td>
                                            <td style="border-radius:0 12px 12px 0">
                                                <span class="badge stok-badge-<?= $st ?> rounded-pill px-3">
                                                    <?= ucfirst($st) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- TAB 2: DETAIL BATCH -->
                        <div class="tab-pane fade" id="tabDetail">
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="toggleHabis" onchange="toggleBarisTersembunyi()">
                                        <label class="form-check-label small text-muted" for="toggleHabis">
                                            Tampilkan stok Habis & Expired
                                        </label>
                                    </div>
                                </div>
                                <form method="GET" class="row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <select name="goldar" class="form-select form-select-sm">
                                            <option value="">Semua Golongan</option>
                                            <?php foreach (['A','B','AB','O'] as $g): ?>
                                            <option value="<?= $g ?>" <?= $filter_goldar===$g?'selected':'' ?>>Gol. <?= $g ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">Semua Status</option>
                                            <?php foreach (['tersedia','kritis','habis','expired'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="jenis" class="form-select form-select-sm">
                                            <option value="">Semua Jenis Darah</option>
                                            <option value="WB"  <?= $filter_jenis==='WB' ?'selected':'' ?>>🩸 Darah Utuh (WB)</option>
                                            <option value="PRC" <?= $filter_jenis==='PRC'?'selected':'' ?>>💉 Darah Pekat (PRC)</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="document.getElementById('tabDetail').click()">Filter</button>
                                        <a href="stok_darah.php#tabDetail" class="btn btn-outline-secondary btn-sm">Reset</a>
                                    </div>
                                </form>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Golongan Darah</th>
                                            <th>Jumlah Kantong</th>
                                            <th>Jenis Darah</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Kadaluarsa</th>
                                            <th>Sumber</th>
                                            <th>Status</th>
                                            <?php if (cekRole([ROLE_SUPER_ADMIN,ROLE_ADMIN,ROLE_PETUGAS_MEDIS])): ?>
                                            <th class="text-center pe-4">Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if ($stok_list->num_rows === 0): ?>
                                    <tr><td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-bag-x fs-2 d-block mb-2"></i>Tidak ada data stok
                                    </td></tr>
                                    <?php else:
                                        // Info baris tersembunyi (diisi oleh JS)
                                    ?>
                                    <tr id="row-info-tersembunyi" style="display:none">
                                        <td colspan="8" class="text-center py-2">
                                            <small id="info-baris-tersembunyi" class="text-muted" style="display:none"></small>
                                        </td>
                                    </tr>
                                    <?php
                                        // Reset pointer result set
                                        $stok_list->data_seek(0);
                                        while ($s = $stok_list->fetch_assoc()):
                                        $sym = $s['rhesus']==='Positif'?'+':'-';
                                        $is_expired = $s['status_stok'] === 'expired';
                                        $is_near_exp = !$is_expired && $s['tanggal_kadaluarsa'] &&
                                            (strtotime($s['tanggal_kadaluarsa']) - time()) < 7*24*3600;
                                    ?>
                                    <tr class="<?= $is_expired ? 'table-secondary' : '' ?> baris-stok" data-status="<?= $s['status_stok'] ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="goldar-chip" style="width:36px;height:36px;font-size:0.85rem;
                                                    <?= $is_expired?'background:#9ca3af':'background:#dc3545' ?>">
                                                    <?= $s['golongan_darah'] ?><?= $sym ?>
                                                </div>
                                                <div class="fw-semibold">Gol. <?= $s['golongan_darah'] ?> <?= $s['rhesus'] ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold <?= $s['jumlah_kantong']<=5?'text-danger':'text-success' ?>">
                                                <?= $s['jumlah_kantong'] ?>
                                            </span> kantong
                                        </td>
                                        <td>
                                            <?php $jd = $s['jenis_darah'] ?? 'WB'; ?>
                                            <?php if ($jd === 'PRC'): ?>
                                            <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af">💉 PRC (250mL)</span>
                                            <?php else: ?>
                                            <span class="badge rounded-pill" style="background:#fce7f3;color:#9d174d">🩸 WB (450mL)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $s['tanggal_masuk'] ? tanggal_indo($s['tanggal_masuk']) : '-' ?></td>
                                        <td class="<?= $is_near_exp?'text-warning fw-semibold':'' ?>">
                                            <?= $s['tanggal_kadaluarsa'] ? tanggal_indo($s['tanggal_kadaluarsa']) : '-' ?>
                                            <?= $is_near_exp ? '<i class="bi bi-exclamation-triangle-fill text-warning ms-1"></i>' : '' ?>
                                        </td>
                                        <td>
                                            <?php
                                            $sumber = $s['sumber_stok'] ?? 'manual';
                                            $sumber_map = [
                                                'donor'       => ['🩸 Donor',      '#fce7f3','#9d174d'],
                                                'pmi'         => ['🏥 PMI',         '#dbeafe','#1e40af'],
                                                'transfer_rs' => ['🔄 Transfer RS', '#d1fae5','#065f46'],
                                                'manual'      => ['✏️ Manual',      '#f3f4f6','#374151'],
                                            ];
                                            $sm = $sumber_map[$sumber] ?? $sumber_map['manual'];
                                            ?>
                                            <span class="badge rounded-pill" style="background:<?= $sm[1] ?>;color:<?= $sm[2] ?>">
                                                <?= $sm[0] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge stok-badge-<?= $s['status_stok'] ?> rounded-pill">
                                                <?= ucfirst($s['status_stok']) ?>
                                            </span>
                                        </td>
                                        <?php if (cekRole([ROLE_SUPER_ADMIN,ROLE_ADMIN,ROLE_PETUGAS_MEDIS])): ?>
                                        <td class="text-center pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                    onclick='bukaEdit(<?= json_encode($s) ?>)'>
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="konfirmasiHapus(<?= $s['id_stok'] ?>)">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Stok Darah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Golongan Darah</label>
                            <select name="golongan_darah" class="form-select" required>
                                <?php foreach (['A','B','AB','O'] as $g): ?>
                                <option value="<?= $g ?>"><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Rhesus</label>
                            <select name="rhesus" class="form-select">
                                <option value="Positif">Positif (+)</option>
                                <option value="Negatif">Negatif (-)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Jumlah Kantong</label>
                            <input type="number" name="jumlah_kantong" class="form-control" min="0" required placeholder="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" id="tgl_masuk" class="form-control" value="<?= date('Y-m-d') ?>" onchange="hitungKadaluarsa()" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Jenis Darah</label>
                            <select name="jenis_darah" id="jenis_darah_select" class="form-select" onchange="hitungKadaluarsa()">
                                <option value="WB">🩸 Darah Utuh / Whole Blood (WB) — 450 mL/kantong</option>
                                <option value="PRC">💉 Darah Pekat / Packed Red Cells (PRC) — 250 mL/kantong</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Sumber Stok</label>
                            <select name="sumber_stok" class="form-select">
                                <option value="pmi">🏥 Kiriman PMI</option>
                                <option value="transfer_rs">🔄 Transfer Rumah Sakit</option>
                            </select>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Stok dari <strong>Donor Langsung</strong> otomatis tercatat via menu <a href="riwayat_donor.php">Riwayat Donor</a>.
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tanggal Kadaluarsa</label>
                            <input type="date" name="tanggal_kadaluarsa" id="tgl_kadaluarsa" class="form-control" required>
                            <div class="form-text text-muted" id="info_kadaluarsa">
                                <i class="bi bi-info-circle me-1"></i>
                                Dihitung otomatis: WB & PRC = 35 hari dari tanggal masuk
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Stok Darah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="edit">
                <input type="hidden" name="id_stok" id="es_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Golongan Darah</label>
                            <input type="text" id="es_goldar_label" class="form-control" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Jumlah Kantong</label>
                            <input type="number" name="jumlah_kantong" id="es_kantong" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status_stok" id="es_status" class="form-select">
                                <?php foreach (['tersedia','kritis','habis','expired'] as $s): ?>
                                <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tanggal Kadaluarsa</label>
                            <input type="date" name="tanggal_kadaluarsa" id="es_exp" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Hapus Stok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="hapus">
                <input type="hidden" name="id_stok" id="hapus_id">
                <div class="modal-body">Yakin ingin menghapus data stok ini?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function bukaEdit(s) {
    document.getElementById('es_id').value          = s.id_stok;
    document.getElementById('es_goldar_label').value= s.golongan_darah + ' ' + s.rhesus;
    document.getElementById('es_kantong').value     = s.jumlah_kantong;
    document.getElementById('es_status').value      = s.status_stok;
    document.getElementById('es_exp').value         = s.tanggal_kadaluarsa || '';
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
function konfirmasiHapus(id) {
    document.getElementById('hapus_id').value = id;
    new bootstrap.Modal(document.getElementById('modalHapus')).show();
}

// Auto-hitung kadaluarsa
function hitungKadaluarsa() {
    const tglMasuk = document.getElementById('tgl_masuk').value;
    const jenis = document.getElementById('jenis_darah_select').value;
    if (!tglMasuk) return;
    // WB & PRC = +35 hari
    const hari = 35;
    const tgl = new Date(tglMasuk);
    tgl.setDate(tgl.getDate() + hari);
    const y = tgl.getFullYear();
    const m = String(tgl.getMonth()+1).padStart(2,'0');
    const d = String(tgl.getDate()).padStart(2,'0');
    document.getElementById('tgl_kadaluarsa').value = `${y}-${m}-${d}`;
    document.getElementById('info_kadaluarsa').innerHTML =
        `<i class="bi bi-check-circle-fill text-success me-1"></i>Auto: ${jenis} = Tanggal Masuk + ${hari} hari`;
}

// Toggle tampilkan/sembunyikan baris Habis & Expired
function toggleBarisTersembunyi() {
    const show = document.getElementById('toggleHabis').checked;
    document.querySelectorAll('.baris-stok').forEach(row => {
        const status = row.getAttribute('data-status');
        if (status === 'habis' || status === 'expired') {
            row.style.display = show ? '' : 'none';
        }
    });
    const rowInfo = document.getElementById('row-info-tersembunyi');
    if (rowInfo) rowInfo.style.display = show ? 'none' : '';
}

// Jalankan saat halaman load - sembunyikan habis & expired by default
document.addEventListener('DOMContentLoaded', function() {
    hitungKadaluarsa();
    // Sembunyikan baris habis & expired by default
    let tersembunyi = 0;
    document.querySelectorAll('.baris-stok').forEach(row => {
        const status = row.getAttribute('data-status');
        if (status === 'habis' || status === 'expired') {
            row.style.display = 'none';
            tersembunyi++;
        }
    });
    // Tampilkan info jumlah tersembunyi
    if (tersembunyi > 0) {
        const info = document.getElementById('info-baris-tersembunyi');
        const rowInfo = document.getElementById('row-info-tersembunyi');
        if (info && rowInfo) {
            info.textContent = `${tersembunyi} baris (Habis/Expired) disembunyikan. Aktifkan toggle di atas untuk melihat.`;
            info.style.display = '';
            rowInfo.style.display = '';
        }
    }
});
</script>
</body>
</html>