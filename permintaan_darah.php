<?php
/**
 * SIDORAH - permintaan_darah.php
 * Kelola permintaan darah pasien
 */
require_once 'koneksi.php';
date_default_timezone_set('Asia/Makassar'); // WITA UTC+8
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS, ROLE_MANAJEMEN]);

// Override format_waktu_singkat dengan timezone yang benar
if (!function_exists('format_waktu_singkat')) {
    function format_waktu_singkat($datetime) {
        if (!$datetime) return '-';
        $now  = new DateTime('now', new DateTimeZone('Asia/Makassar'));
        $tgl  = new DateTime($datetime, new DateTimeZone('Asia/Makassar'));
        $diff = $now->getTimestamp() - $tgl->getTimestamp();
        if ($diff < 60)         return 'Baru saja';
        if ($diff < 3600)       return floor($diff/60) . ' menit lalu';
        if ($diff < 86400)      return floor($diff/3600) . ' jam lalu';
        if ($diff < 172800)     return 'Kemarin';
        return $tgl->format('d M Y');
    }
} else {
    // Redefine dengan timezone benar menggunakan closure
    function format_waktu_singkat_local($datetime) {
        if (!$datetime) return '-';
        $now  = new DateTime('now', new DateTimeZone('Asia/Makassar'));
        $tgl  = new DateTime($datetime, new DateTimeZone('Asia/Makassar'));
        $diff = $now->getTimestamp() - $tgl->getTimestamp();
        if ($diff < 60)         return 'Baru saja';
        if ($diff < 3600)       return floor($diff/60) . ' menit lalu';
        if ($diff < 86400)      return floor($diff/3600) . ' jam lalu';
        if ($diff < 172800)     return 'Kemarin';
        return $tgl->format('d M Y');
    }
}

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    // Petugas medis hanya boleh update_status (penuhi/tidak)
    if ($aksi === 'tambah' && !cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])) {
        $_SESSION['pesan'] = "Anda tidak memiliki akses untuk membuat permintaan darah.";
        $_SESSION['tipe']  = 'danger';
        redirect('permintaan_darah.php');
    }
    // Admin tidak boleh update_status (hanya petugas medis)
    if ($aksi === 'update_status' && !cekRole([ROLE_PETUGAS_MEDIS, ROLE_SUPER_ADMIN])) {
        $_SESSION['pesan'] = "Konfirmasi pemenuhan hanya bisa dilakukan oleh Petugas Medis.";
        $_SESSION['tipe']  = 'danger';
        redirect('permintaan_darah.php');
    }

    if ($aksi === 'tambah') {
        $goldar   = bersihkan($koneksi, $_POST['golongan_darah']);
        $rhesus   = bersihkan($koneksi, $_POST['rhesus']);
        $kantong  = (int)$_POST['jumlah_kantong'];
        $pasien   = bersihkan($koneksi, $_POST['nama_pasien']);
        $rm       = bersihkan($koneksi, $_POST['no_rekam_medis']);
        $urgensi  = bersihkan($koneksi, $_POST['tingkat_urgensi']);
        $hb       = !empty($_POST['hemoglobin']) ? (float)$_POST['hemoglobin'] : null;
        $jenis_darah = bersihkan($koneksi, $_POST['jenis_darah'] ?? 'WB');
        // Urgensi otomatis dari Hb
        if ($hb === null)    $urgensi = 'normal';
        elseif ($hb < 5)     $urgensi = 'darurat';
        elseif ($hb < 10)    $urgensi = 'mendesak';
        else                 $urgensi = 'normal';
        $status   = 'menunggu';
        $id_petugas = $_SESSION['id_pengguna'];

        $stmt = $koneksi->prepare("INSERT INTO permintaan_darah (golongan_darah,rhesus,jumlah_kantong,nama_pasien,no_rekam_medis,tingkat_urgensi,status_permintaan,id_petugas,hemoglobin,jenis_darah) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssissssids', $goldar,$rhesus,$kantong,$pasien,$rm,$urgensi,$status,$id_petugas,$hb,$jenis_darah);
        if ($stmt->execute()) {
            catat_log($koneksi,'TAMBAH_PERMINTAAN','permintaan_darah',"Permintaan $kantong kantong $goldar $rhesus untuk $pasien ($urgensi)");
            $_SESSION['pesan'] = "Permintaan darah untuk <strong>$pasien</strong> berhasil dicatat.";
            $_SESSION['tipe']  = 'success';
        }
        $stmt->close();
        redirect('permintaan_darah.php');
    }

    if ($aksi === 'update_status') {
        $id      = (int)$_POST['id_permintaan'];
        $status  = bersihkan($koneksi, $_POST['status_baru']);
        $perm    = $koneksi->query("SELECT * FROM permintaan_darah WHERE id_permintaan=$id")->fetch_assoc();

        $koneksi->query("UPDATE permintaan_darah SET status_permintaan='$status' WHERE id_permintaan=$id");

        // Jika terpenuhi, kurangi stok
        if ($status === 'terpenuhi') {
            $koneksi->query("
                UPDATE stok_darah SET
                    jumlah_kantong = GREATEST(0, jumlah_kantong - {$perm['jumlah_kantong']}),
                    status_stok = CASE
                        WHEN jumlah_kantong - {$perm['jumlah_kantong']} <= 0 THEN 'habis'
                        WHEN jumlah_kantong - {$perm['jumlah_kantong']} <= 5 THEN 'kritis'
                        ELSE 'tersedia'
                    END
                WHERE golongan_darah='{$perm['golongan_darah']}'
                AND rhesus='{$perm['rhesus']}'
                AND status_stok='tersedia'
                ORDER BY tanggal_kadaluarsa ASC LIMIT 1
            ");
        }

        catat_log($koneksi,'UPDATE_PERMINTAAN','permintaan_darah',"{$perm['nama_pasien']}: {$perm['status_permintaan']} → $status",['status'=>$perm['status_permintaan']],['status'=>$status]);
        $_SESSION['pesan'] = "Status permintaan diperbarui menjadi <strong>$status</strong>.";
        $_SESSION['tipe']  = 'success';
        redirect('permintaan_darah.php');
    }
}

// ── DATA ──────────────────────────────────────────────────────
$filter_status  = bersihkan($koneksi, $_GET['status']  ?? '');
$filter_urgensi = bersihkan($koneksi, $_GET['urgensi'] ?? '');
$filter_goldar  = bersihkan($koneksi, $_GET['goldar']  ?? '');
$cari           = bersihkan($koneksi, $_GET['cari']    ?? '');

$where = "WHERE 1=1";
if ($filter_status)  $where .= " AND status_permintaan='$filter_status'";
if ($filter_urgensi) $where .= " AND tingkat_urgensi='$filter_urgensi'";
if ($filter_goldar)  $where .= " AND golongan_darah='$filter_goldar'";
if ($cari)           $where .= " AND (nama_pasien LIKE '%$cari%' OR no_rekam_medis LIKE '%$cari%')";

// Tab aktif/arsip
$tab_aktif = $_GET['tab'] ?? 'aktif';
$bulan_ini = date('Y-m');

// WHERE aktif = bulan ini + semua yang masih menunggu
$where_aktif  = $where . " AND (DATE_FORMAT(tanggal_permintaan,'%Y-%m') = '$bulan_ini' OR status_permintaan = 'menunggu')";
// WHERE arsip = bulan sebelumnya & sudah selesai (bukan menunggu)
$where_arsip  = $where . " AND DATE_FORMAT(tanggal_permintaan,'%Y-%m') < '$bulan_ini' AND status_permintaan != 'menunggu'";

$where_query  = $tab_aktif === 'arsip' ? $where_arsip : $where_aktif;

$per_hal = 15;
$hal     = max(1,(int)($_GET['hal'] ?? 1));
$offset  = ($hal-1)*$per_hal;

$total     = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah $where_query")->fetch_assoc()['n'];
$total_hal = ceil($total/$per_hal);
$total_arsip = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah $where_arsip")->fetch_assoc()['n'];
$total_aktif = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah $where_aktif")->fetch_assoc()['n'];

$permintaan = $koneksi->query("
    SELECT pm.*, u.nama_lengkap as nama_petugas
    FROM permintaan_darah pm
    LEFT JOIN users u ON pm.id_petugas=u.id_pengguna
    $where_query
    ORDER BY
        pm.tanggal_permintaan DESC,
        FIELD(pm.tingkat_urgensi,'darurat','mendesak','normal'),
        FIELD(pm.status_permintaan,'menunggu','terpenuhi','tidak_terpenuhi')
    LIMIT $per_hal OFFSET $offset
");

// Stat
$stat_menunggu = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE status_permintaan='menunggu'")->fetch_assoc()['n'];
$stat_darurat  = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE tingkat_urgensi='darurat' AND status_permintaan='menunggu'")->fetch_assoc()['n'];
$stat_terpenuhi= $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE status_permintaan='terpenuhi'")->fetch_assoc()['n'];
$stat_total    = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah")->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permintaan Darah — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .urgensi-darurat  { background:#fee2e2; color:#991b1b; font-weight:700; }
        .urgensi-mendesak { background:#fef3c7; color:#92400e; font-weight:600; }
        .urgensi-normal   { background:#dbeafe; color:#1e40af; }
        .status-menunggu       { background:#fef3c7; color:#92400e; }
        .status-terpenuhi      { background:#d1fae5; color:#065f46; }
        .status-tidak_terpenuhi{ background:#fee2e2; color:#991b1b; }
        tr.darurat-row { background:#fff5f5 !important; }
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
                        <h1 class="h3 mb-0"><i class="bi bi-bandaid-fill text-danger me-2"></i>Permintaan Darah</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Permintaan Darah</li>
                        </ol>
                    </div>
                    <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-1"></i> Buat Permintaan
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show">
                    <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Alert darurat -->
                <?php if ($stat_darurat > 0): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <strong><?= $stat_darurat ?> permintaan DARURAT</strong> menunggu pemenuhan segera!
                </div>
                <?php endif; ?>

                <!-- Stat -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-warning mb-0"><?= $stat_menunggu ?></div>
                            <div class="small text-muted">Menunggu</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-danger mb-0"><?= $stat_darurat ?></div>
                            <div class="small text-muted">Darurat</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-success mb-0"><?= $stat_terpenuhi ?></div>
                            <div class="small text-muted">Terpenuhi</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-primary mb-0"><?= $stat_total ?></div>
                            <div class="small text-muted">Total</div>
                        </div>
                    </div>
                </div>

                <!-- Tab Aktif / Arsip -->
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <ul class="nav nav-tabs card-header-tabs" id="tabPermintaan">
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold <?= $tab_aktif==='aktif'?'active':'' ?>"
                                       href="permintaan_darah.php?tab=aktif">
                                        <i class="bi bi-clock-fill me-1 text-danger"></i> Aktif
                                        <span class="badge bg-danger ms-1" style="font-size:0.68rem"><?= $total_aktif ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold <?= $tab_aktif==='arsip'?'active':'' ?>"
                                       href="permintaan_darah.php?tab=arsip">
                                        <i class="bi bi-archive-fill me-1 text-secondary"></i> Arsip
                                        <span class="badge bg-secondary ms-1" style="font-size:0.68rem"><?= $total_arsip ?></span>
                                    </a>
                                </li>
                            </ul>
                            <?php if ($tab_aktif === 'arsip'): ?>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Permintaan bulan-bulan sebelumnya yang sudah selesai</small>
                            <?php else: ?>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Bulan ini + semua yang masih menunggu</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Filter -->
                    <div class="card-body py-3 border-bottom">
                        <form method="GET" class="row g-2 align-items-center">
                            <input type="hidden" name="tab" value="<?= $tab_aktif ?>">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="cari" class="form-control"
                                           placeholder="Nama pasien / no. RM..."
                                           value="<?= htmlspecialchars($cari) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="urgensi" class="form-select">
                                    <option value="">Semua Urgensi</option>
                                    <option value="darurat"  <?= $filter_urgensi==='darurat'?'selected':'' ?>>🚨 Darurat</option>
                                    <option value="mendesak" <?= $filter_urgensi==='mendesak'?'selected':'' ?>>⚠️ Mendesak</option>
                                    <option value="normal"   <?= $filter_urgensi==='normal'?'selected':'' ?>>✅ Normal</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="goldar" class="form-select">
                                    <option value="">Semua Goldar</option>
                                    <?php foreach (['A','B','AB','O'] as $g): ?>
                                    <option value="<?= $g ?>" <?= $filter_goldar===$g?'selected':'' ?>>Gol. <?= $g ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu"         <?= $filter_status==='menunggu'?'selected':'' ?>>Menunggu</option>
                                    <option value="terpenuhi"        <?= $filter_status==='terpenuhi'?'selected':'' ?>>Terpenuhi</option>
                                    <option value="tidak_terpenuhi"  <?= $filter_status==='tidak_terpenuhi'?'selected':'' ?>>Tidak Terpenuhi</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="permintaan_darah.php?tab=<?= $tab_aktif ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>

                    <!-- Tabel -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Pasien</th>
                                        <th>Golongan Darah</th>
                                        <th>Jumlah</th>
                                        <th>Jenis Darah</th>
                                        <th>Hb (g/dL)</th>
                                        <th>Urgensi</th>
                                        <th>Status</th>
                                        <th>Petugas</th>
                                        <th>Waktu</th>
                                        <?php if (cekRole([ROLE_SUPER_ADMIN,ROLE_ADMIN,ROLE_PETUGAS_MEDIS])): ?>
                                        <th class="text-center pe-4">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($permintaan->num_rows === 0): ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-check fs-2 d-block mb-2"></i>Tidak ada permintaan darah
                                </td></tr>
                                <?php else:
                                    while ($p = $permintaan->fetch_assoc()):
                                    $sym = $p['rhesus']==='Positif'?'+':'-';
                                    $is_darurat = $p['tingkat_urgensi'] === 'darurat' && $p['status_permintaan'] === 'menunggu';
                                ?>
                                <tr class="<?= $is_darurat ? 'darurat-row' : '' ?>">
                                    <td class="ps-4">
                                        <div class="fw-semibold"><?= htmlspecialchars($p['nama_pasien']) ?></div>
                                        <?php if ($p['no_rekam_medis']): ?>
                                        <div class="text-muted" style="font-size:0.75rem">RM: <?= htmlspecialchars($p['no_rekam_medis']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fw-bold fs-6">
                                            <?= $p['golongan_darah'] ?><?= $sym ?>
                                        </span>
                                    </td>
                                    <td><strong><?= $p['jumlah_kantong'] ?></strong> kantong</td>
                                    <td>
                                        <?php
                                        $jd = $p['jenis_darah'] ?? 'WB';
                                        if ($jd === 'PRC') {
                                            echo '<span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af">💉 PRC (250mL)</span>';
                                        } else {
                                            echo '<span class="badge rounded-pill" style="background:#fce7f3;color:#9d174d">🩸 WB (450mL)</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['hemoglobin'])): 
                                            $hb = (float)$p['hemoglobin'];
                                            if ($hb < 5)       { $hb_bg='#fee2e2'; $hb_color='#991b1b'; $hb_label='Kritis'; }
                                            elseif ($hb < 7)   { $hb_bg='#ffedd5'; $hb_color='#9a3412'; $hb_label='Berat'; }
                                            elseif ($hb < 10)  { $hb_bg='#fef9c3'; $hb_color='#713f12'; $hb_label='Sedang'; }
                                            else               { $hb_bg='#dcfce7'; $hb_color='#166534'; $hb_label='Ringan'; }
                                        ?>
                                        <span class="badge rounded-pill px-2" style="background:<?= $hb_bg ?>;color:<?= $hb_color ?>">
                                            <?= number_format($hb,1) ?> — <?= $hb_label ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge urgensi-<?= $p['tingkat_urgensi'] ?> rounded-pill px-3">
                                            <?php if ($p['tingkat_urgensi']==='darurat') echo '🚨 ';
                                            elseif ($p['tingkat_urgensi']==='mendesak') echo '⚠️ '; ?>
                                            <?= ucfirst($p['tingkat_urgensi']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-<?= $p['status_permintaan'] ?> rounded-pill px-3">
                                            <?= ucfirst(str_replace('_',' ',$p['status_permintaan'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($p['nama_petugas'] ?? '-') ?></td>
                                    <td class="text-muted"><?= function_exists('format_waktu_singkat_local') ? format_waktu_singkat_local($p['tanggal_permintaan']) : format_waktu_singkat($p['tanggal_permintaan']) ?></td>
                                    <?php if (cekRole([ROLE_SUPER_ADMIN,ROLE_ADMIN,ROLE_PETUGAS_MEDIS])): ?>
                                    <td class="text-center pe-4">
                                        <?php if ($p['status_permintaan'] === 'menunggu'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?php if (cekRole([ROLE_PETUGAS_MEDIS])): ?>
                                            <!-- Petugas Medis: hanya bisa konfirmasi penuhi/tidak -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="aksi_form" value="update_status">
                                                <input type="hidden" name="id_permintaan" value="<?= $p['id_permintaan'] ?>">
                                                <input type="hidden" name="status_baru" value="terpenuhi">
                                                <button type="submit" class="btn btn-success btn-sm"
                                                    title="Konfirmasi stok tersedia & terpenuhi">
                                                    <i class="bi bi-check-lg"></i> Penuhi
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="aksi_form" value="update_status">
                                                <input type="hidden" name="id_permintaan" value="<?= $p['id_permintaan'] ?>">
                                                <input type="hidden" name="status_baru" value="tidak_terpenuhi">
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    title="Stok tidak tersedia — lapor ke admin"
                                                    onclick="return confirm('Konfirmasi: stok darah tidak tersedia? Admin akan diberitahu.')">
                                                    <i class="bi bi-x-lg"></i> Tidak Tersedia
                                                </button>
                                            </form>
                                            <?php elseif (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                                            <!-- Admin: hanya bisa lihat, konfirmasi oleh petugas medis -->
                                            <span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af;font-size:0.72rem">
                                                <i class="bi bi-clock me-1"></i>Menunggu konfirmasi petugas
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_hal > 1): ?>
                        <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                            <div class="small text-muted">Halaman <?= $hal ?> dari <?= $total_hal ?></div>
                            <nav><ul class="pagination pagination-sm mb-0">
                                <?php $q=$_GET; unset($q['hal']); $qs=http_build_query($q);
                                for ($i=max(1,$hal-3); $i<=min($total_hal,$hal+3); $i++): ?>
                                <li class="page-item <?= $i===$hal?'active':'' ?>">
                                    <a class="page-link" href="?<?= $qs ?>&hal=<?= $i ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul></nav>
                        </div>
                        <?php endif; ?>
                    </div><!-- /card-body -->
                </div><!-- /card tab -->

            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="text-muted small">SIDORAH &copy; <?= date('Y') ?></div>
            </div>
        </footer>
    </div>
</div>

<!-- Modal Tambah Permintaan -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-bandaid-fill me-2"></i>Buat Permintaan Darah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Pasien <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pasien" class="form-control" required placeholder="Nama lengkap pasien">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Rekam Medis</label>
                            <input type="text" name="no_rekam_medis" class="form-control" placeholder="RM-XXXX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Golongan Darah <span class="text-danger">*</span></label>
                            <select name="golongan_darah" class="form-select" required>
                                <?php foreach (['A','B','AB','O'] as $g): ?>
                                <option value="<?= $g ?>"><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rhesus</label>
                            <select name="rhesus" class="form-select">
                                <option value="Positif">Positif (+)</option>
                                <option value="Negatif">Negatif (-)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jumlah Kantong</label>
                            <input type="number" name="jumlah_kantong" class="form-control" min="1" value="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Jenis Darah <span class="text-danger">*</span></label>
                            <select name="jenis_darah" class="form-select" required>
                                <option value="WB">🩸 Darah Utuh / Whole Blood (WB) — 1 kantong = 450 mL</option>
                                <option value="PRC">💉 Darah Pekat / Packed Red Cells (PRC) — 1 kantong = 250 mL</option>
                            </select>
                        </div>
                        <input type="hidden" name="tingkat_urgensi" value="normal">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hemoglobin (Hb) <span class="text-muted small">g/dL</span></label>
                            <input type="number" name="hemoglobin" id="inputHb" class="form-control"
                                   step="0.1" min="0" max="20" placeholder="Contoh: 8.5"
                                   oninput="updateKeteranganHb(this.value)">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div id="keteranganHb" class="w-100 p-2 rounded text-center fw-semibold" style="min-height:38px;display:none!important"></div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function updateKeteranganHb(val) {
    const box = document.getElementById('keteranganHb');
    const hb  = parseFloat(val);
    if (isNaN(hb) || val === '') {
        box.style.display = 'none';
        return;
    }
    box.style.display = 'block';
    let text, bg, color;
    if (hb < 5) {
        text = '🔴 Kritis — Hb sangat rendah, transfusi segera!';
        bg = '#fee2e2'; color = '#991b1b';
    } else if (hb < 7) {
        text = '🟠 Berat — Hb rendah, perlu transfusi';
        bg = '#ffedd5'; color = '#9a3412';
    } else if (hb < 10) {
        text = '🟡 Sedang — Hb 7–9.9 g/dL';
        bg = '#fef9c3'; color = '#713f12';
    } else {
        text = '🟢 Ringan — Hb ≥ 10 g/dL';
        bg = '#dcfce7'; color = '#166534';
    }
    box.textContent = text;
    box.style.background = bg;
    box.style.color = color;
}
</script>
</html>