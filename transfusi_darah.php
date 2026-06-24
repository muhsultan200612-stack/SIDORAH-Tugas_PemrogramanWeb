<?php
/**
 * SIDORAH - transfusi_darah.php
 * Manajemen Transfusi Darah
 */
require_once 'koneksi.php';
paksa_login();
if (!cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS, ROLE_MANAJEMEN])) {
    redirect('dashboard.php');
}
$hanya_lihat = cekRole([ROLE_MANAJEMEN]) || cekRole([ROLE_ADMIN]); // Manajemen & Admin hanya bisa lihat

// ── PROSES ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // Hanya petugas medis & super admin yang bisa tambah/hapus
    if (in_array($aksi, ['tambah','hapus']) && !cekRole([ROLE_PETUGAS_MEDIS, ROLE_SUPER_ADMIN])) {
        $_SESSION['pesan'] = "Aksi ini hanya bisa dilakukan oleh Petugas Medis.";
        $_SESSION['tipe']  = 'danger';
        redirect('transfusi_darah.php');
    }

    if ($aksi === 'tambah') {
        // Generate nomor transfusi
        $thn = date('Y'); $bln = date('m');
        $res_no = $koneksi->query("SELECT COUNT(*) as n FROM transfusi_darah WHERE YEAR(created_at)=$thn AND MONTH(created_at)=$bln")->fetch_assoc();
        $no_urut = str_pad(($res_no['n'] + 1), 4, '0', STR_PAD_LEFT);
        $no_transfusi = "TRF-{$thn}{$bln}-{$no_urut}";

        $nama       = bersihkan($koneksi, $_POST['nama_pasien']);
        $no_rm      = bersihkan($koneksi, $_POST['no_rekam_medis']);
        $usia       = (int)$_POST['usia_pasien'];
        $jk         = bersihkan($koneksi, $_POST['jenis_kelamin']);
        $goldar     = bersihkan($koneksi, $_POST['golongan_darah']);
        $rhesus     = bersihkan($koneksi, $_POST['rhesus']);
        $id_stok    = (int)$_POST['id_stok'];
        $jenis_darah= bersihkan($koneksi, $_POST['jenis_darah'] ?? 'WB');
        $kantong_input = max(1, (int)$_POST['jumlah_kantong']);

        // Hitung TOTAL stok tersedia goldar+rhesus+jenis (semua batch aktif)
        $stok_info = $koneksi->query("SELECT tanggal_kadaluarsa, jumlah_kantong FROM stok_darah WHERE id_stok=$id_stok")->fetch_assoc();
        $tgl = date('Y-m-d');

        $total_stok_res = $koneksi->query("
            SELECT COALESCE(SUM(jumlah_kantong),0) as total
            FROM stok_darah
            WHERE golongan_darah='$goldar' AND rhesus='$rhesus'
            AND COALESCE(jenis_darah,'WB')='$jenis_darah'
            AND status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0
        ")->fetch_assoc();
        $total_stok_tersedia = (int)$total_stok_res['total'];

        // Validasi: kantong diminta tidak boleh melebihi stok tersedia
        if ($kantong_input > $total_stok_tersedia) {
            $sym = $rhesus === 'Positif' ? '+' : '-';
            $_SESSION['pesan'] = "Stok darah <strong>$goldar$sym</strong> tidak mencukupi! 
                Diminta: <strong>$kantong_input kantong</strong>, 
                tersedia: <strong>$total_stok_tersedia kantong</strong>.";
            $_SESSION['tipe']  = 'danger';
            redirect('transfusi_darah.php');
        }

        // Hitung volume otomatis dari jumlah kantong
        $ml_per_kantong = ($jenis_darah === 'PRC') ? 250 : 450;
        $volume = $kantong_input * $ml_per_kantong;
        $jam_mulai  = bersihkan($koneksi, $_POST['jam_mulai']);
        $ruangan    = bersihkan($koneksi, $_POST['ruangan']);
        $nama_dokter= bersihkan($koneksi, $_POST['nama_dokter'] ?? '');

        if (empty(trim($nama_dokter))) {
            $_SESSION['pesan'] = "Nama dokter penanggung jawab wajib diisi!";
            $_SESSION['tipe']  = 'danger';
            redirect('transfusi_darah.php');
        }
        $diagnosa   = bersihkan($koneksi, $_POST['diagnosa']);
        $indikasi   = bersihkan($koneksi, $_POST['indikasi']);
        $id_petugas = $_SESSION['id_pengguna'];

        $stmt = $koneksi->prepare("INSERT INTO transfusi_darah
            (no_transfusi,nama_pasien,no_rekam_medis,usia_pasien,jenis_kelamin,
             golongan_darah,rhesus,id_stok,volume_ml,tanggal_transfusi,jam_mulai,
             ruangan,nama_dokter,diagnosa,indikasi,jenis_darah,status,id_petugas)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'proses',?)");
        $stmt->bind_param('sssississsssssssi',
            $no_transfusi,$nama,$no_rm,$usia,$jk,$goldar,$rhesus,
            $id_stok,$volume,$tgl,$jam_mulai,$ruangan,$nama_dokter,$diagnosa,$indikasi,$jenis_darah,$id_petugas);

        if ($stmt->execute()) {
            // Kurangi stok darah FIFO multi-batch
            if ($kantong_input > 0) {
                $sisa_kurang = $kantong_input;

                // Ambil semua batch goldar+rhesus+jenis yang aktif, urut expired terdekat (FIFO)
                $batches = $koneksi->query("
                    SELECT id_stok, jumlah_kantong
                    FROM stok_darah
                    WHERE golongan_darah='$goldar' AND rhesus='$rhesus'
                    AND COALESCE(jenis_darah,'WB')='$jenis_darah'
                    AND status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0
                    ORDER BY tanggal_kadaluarsa ASC
                ")->fetch_all(MYSQLI_ASSOC);

                foreach ($batches as $batch) {
                    if ($sisa_kurang <= 0) break;
                    $bid = $batch['id_stok'];
                    $bkantong = (int)$batch['jumlah_kantong'];
                    $dikurangi = min($sisa_kurang, $bkantong);
                    $koneksi->query("UPDATE stok_darah SET jumlah_kantong = jumlah_kantong - $dikurangi WHERE id_stok=$bid");
                    $sisa_kurang -= $dikurangi;
                }

                // Update status semua batch goldar+rhesus
                $koneksi->query("UPDATE stok_darah SET status_stok = CASE
                    WHEN jumlah_kantong <= 0 THEN 'habis'
                    WHEN jumlah_kantong <= 5 THEN 'kritis'
                    ELSE 'tersedia' END
                    WHERE golongan_darah='$goldar' AND rhesus='$rhesus' AND status_stok != 'expired'");

                // Pastikan 0 kantong = habis
                $koneksi->query("UPDATE stok_darah SET status_stok='habis' WHERE jumlah_kantong <= 0 AND status_stok != 'expired'");
            }
            catat_log($koneksi, 'TAMBAH_TRANSFUSI', 'transfusi_darah', "Transfusi $no_transfusi untuk $nama");
            $_SESSION['pesan'] = "Transfusi <strong>$no_transfusi</strong> berhasil dicatat!";
            $_SESSION['tipe']  = 'success';
        }
        redirect('transfusi_darah.php');
    }

    if ($aksi === 'update_status') {
        $id       = (int)$_POST['id_transfusi'];
        $status   = bersihkan($koneksi, $_POST['status']);
        $reaksi   = bersihkan($koneksi, $_POST['reaksi_transfusi']);
        $ket_reaksi = bersihkan($koneksi, $_POST['keterangan_reaksi']);
        $jam_selesai = bersihkan($koneksi, $_POST['jam_selesai']);
        $catatan  = bersihkan($koneksi, $_POST['catatan']);

        $koneksi->query("UPDATE transfusi_darah SET
            status='$status', reaksi_transfusi='$reaksi',
            keterangan_reaksi='$ket_reaksi', jam_selesai='$jam_selesai',
            catatan='$catatan' WHERE id_transfusi=$id");

        catat_log($koneksi, 'UPDATE_TRANSFUSI', 'transfusi_darah', "Update status transfusi ID $id → $status");
        $_SESSION['pesan'] = 'Status transfusi berhasil diperbarui!';
        $_SESSION['tipe']  = 'success';
        redirect('transfusi_darah.php');
    }

    if ($aksi === 'hapus') {
        $id = (int)$_POST['id_transfusi'];
        $t  = $koneksi->query("SELECT no_transfusi FROM transfusi_darah WHERE id_transfusi=$id")->fetch_assoc();
        $koneksi->query("DELETE FROM transfusi_darah WHERE id_transfusi=$id");
        catat_log($koneksi, 'HAPUS_TRANSFUSI', 'transfusi_darah', "Hapus transfusi {$t['no_transfusi']}");
        $_SESSION['pesan'] = 'Data transfusi berhasil dihapus.';
        $_SESSION['tipe']  = 'warning';
        redirect('transfusi_darah.php');
    }
}

// ── AMBIL DATA ────────────────────────────────────────────────
$cari   = bersihkan($koneksi, $_GET['cari'] ?? '');
$filter = bersihkan($koneksi, $_GET['status'] ?? '');
$where  = "WHERE 1=1";
if ($cari)   $where .= " AND (t.nama_pasien LIKE '%$cari%' OR t.no_transfusi LIKE '%$cari%' OR t.no_rekam_medis LIKE '%$cari%')";
if ($filter) $where .= " AND t.status='$filter'";

$transfusi = $koneksi->query("
    SELECT t.*, u.nama_lengkap as petugas_nama
    FROM transfusi_darah t
    LEFT JOIN users u ON t.id_petugas=u.id_pengguna
    $where ORDER BY t.created_at DESC LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

// Statistik
$stat = $koneksi->query("SELECT
    COUNT(*) as total,
    SUM(status='proses') as proses,
    SUM(status='selesai') as selesai,
    SUM(status='dibatalkan') as dibatalkan,
    SUM(reaksi_transfusi != 'tidak_ada') as ada_reaksi,
    COALESCE(SUM(CASE WHEN status='selesai' THEN volume_ml ELSE 0 END),0) as total_volume
    FROM transfusi_darah")->fetch_assoc();

// Stok tersedia untuk dropdown — digabung per goldar+rhesus+jenis (FIFO: exp terdekat)
$stok_tersedia = $koneksi->query("
    SELECT golongan_darah, rhesus, COALESCE(jenis_darah,'WB') as jenis_darah,
           SUM(jumlah_kantong) as total_kantong,
           MIN(tanggal_kadaluarsa) as exp_terdekat,
           MIN(id_stok) as id_stok_fifo
    FROM stok_darah WHERE status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0
    GROUP BY golongan_darah, rhesus, jenis_darah
    ORDER BY golongan_darah, rhesus DESC, jenis_darah
")->fetch_all(MYSQLI_ASSOC);

// Untuk backend: ambil id_stok terdekat expired per goldar+rhesus+jenis (FIFO)
$stok_fifo = $koneksi->query("
    SELECT golongan_darah, rhesus, COALESCE(jenis_darah,'WB') as jenis_darah,
           id_stok
    FROM stok_darah WHERE status_stok IN ('tersedia','kritis') AND jumlah_kantong > 0
    ORDER BY golongan_darah, rhesus, jenis_darah, tanggal_kadaluarsa ASC
")->fetch_all(MYSQLI_ASSOC);
// Index by goldar+rhesus+jenis → ambil yang pertama (FIFO)
$fifo_map = [];
foreach ($stok_fifo as $sf) {
    $key = $sf['golongan_darah'].'_'.$sf['rhesus'].'_'.$sf['jenis_darah'];
    if (!isset($fifo_map[$key])) $fifo_map[$key] = $sf['id_stok'];
}

$halaman_aktif = 'transfusi_darah.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transfusi Darah — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .card-stat { border-radius:14px; border:none; }
        .badge-proses     { background:#dbeafe; color:#1e40af; }
        .badge-selesai    { background:#d1fae5; color:#065f46; }
        .badge-dibatalkan { background:#fee2e2; color:#991b1b; }
        .badge-reaksi     { background:#fef3c7; color:#92400e; }
        .tbl-row:hover { background:#fef2f2 !important; cursor:pointer; }
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
                        <h1 class="h3 mb-0">
                            <i class="bi bi-droplet-half text-danger me-2"></i>Transfusi Darah
                            <?php if ($hanya_lihat): ?>
                            <span class="badge bg-warning text-dark ms-2 small">
                                <i class="bi bi-eye me-1"></i>View Only
                            </span>
                            <?php endif; ?>
                        </h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Transfusi Darah</li>
                        </ol>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if (!$hanya_lihat): ?>
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="export_laporan.php?tipe=transfusi&format=excel&dari=<?= date('Y-m-01') ?>&sampai=<?= date('Y-m-d') ?>">
                                        <i class="bi bi-download me-2"></i>Bulan Ini
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="export_laporan.php?tipe=transfusi&format=excel&dari=<?= date('Y-01-01') ?>&sampai=<?= date('Y-m-d') ?>">
                                        <i class="bi bi-download me-2"></i>Tahun Ini
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="export_laporan.php?tipe=transfusi&format=pdf&dari=<?= date('Y-m-01') ?>&sampai=<?= date('Y-m-d') ?>">
                                        <i class="bi bi-printer me-2"></i>Bulan Ini
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="export_laporan.php?tipe=transfusi&format=pdf&dari=<?= date('Y-01-01') ?>&sampai=<?= date('Y-m-d') ?>">
                                        <i class="bi bi-printer me-2"></i>Tahun Ini
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php if (cekRole([ROLE_PETUGAS_MEDIS, ROLE_SUPER_ADMIN])): ?>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="bi bi-plus-circle me-2"></i>Catat Transfusi
                        </button>
                        <?php endif; ?>
                        <?php endif; // end !$hanya_lihat ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['pesan'])): ?>
                <div class="alert alert-<?= $_SESSION['tipe'] ?> alert-dismissible fade show rounded-3">
                    <i class="bi bi-check-circle me-2"></i><?= $_SESSION['pesan'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan'], $_SESSION['tipe']); endif; ?>

                <!-- Statistik -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-2">
                        <div class="card card-stat shadow-sm p-3 text-center">
                            <div class="fw-bold fs-4 text-danger"><?= $stat['total'] ?></div>
                            <div class="small text-muted">Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card card-stat shadow-sm p-3 text-center" style="border-left:3px solid #1e40af">
                            <div class="fw-bold fs-4" style="color:#1e40af"><?= $stat['proses'] ?></div>
                            <div class="small text-muted">Proses</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card card-stat shadow-sm p-3 text-center" style="border-left:3px solid #15803d">
                            <div class="fw-bold fs-4 text-success"><?= $stat['selesai'] ?></div>
                            <div class="small text-muted">Selesai</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card card-stat shadow-sm p-3 text-center" style="border-left:3px solid #dc3545">
                            <div class="fw-bold fs-4 text-danger"><?= $stat['dibatalkan'] ?></div>
                            <div class="small text-muted">Dibatalkan</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card card-stat shadow-sm p-3 text-center" style="border-left:3px solid #d97706">
                            <div class="fw-bold fs-4" style="color:#d97706"><?= $stat['ada_reaksi'] ?></div>
                            <div class="small text-muted">Ada Reaksi</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="card card-stat shadow-sm p-3 text-center" style="border-left:3px solid #6b7280">
                            <div class="fw-bold fs-4 text-secondary"><?= number_format($stat['total_volume']) ?></div>
                            <div class="small text-muted">ml Ditransfusi</div>
                        </div>
                    </div>
                </div>

                <!-- Filter & Cari -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 p-3">
                    <form method="GET" class="d-flex gap-2 flex-wrap">
                        <input type="text" name="cari" class="form-control" style="max-width:260px"
                               placeholder="Cari nama pasien / no. transfusi..."
                               value="<?= htmlspecialchars($cari) ?>">
                        <select name="status" class="form-select" style="max-width:160px">
                            <option value="">Semua Status</option>
                            <option value="proses"     <?= $filter==='proses'?'selected':'' ?>>Proses</option>
                            <option value="selesai"    <?= $filter==='selesai'?'selected':'' ?>>Selesai</option>
                            <option value="dibatalkan" <?= $filter==='dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                        </select>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                        <?php if ($cari || $filter): ?>
                        <a href="transfusi_darah.php" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Tabel -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background:#fef2f2">
                                    <tr>
                                        <th class="ps-3">No. Transfusi</th>
                                        <th>Pasien</th>
                                        <th>Gol. Darah</th>
                                        <th>Volume</th>
                                        <th>Tanggal</th>
                                        <th>Reaksi</th>
                                        <th>Status</th>
                                        <th>Dokter</th>
                                        <th>Petugas</th>
                                        <?php if (!$hanya_lihat): ?>
                                        <th class="text-center">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($transfusi)): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-droplet fs-2 d-block mb-2 text-danger opacity-50"></i>
                                    Belum ada data transfusi
                                </td></tr>
                                <?php else: foreach ($transfusi as $t):
                                    $sym = $t['rhesus']==='Positif'?'+':'-';
                                    $reaksi_badge = match($t['reaksi_transfusi']) {
                                        'tidak_ada' => '<span class="badge bg-light text-muted">Tidak Ada</span>',
                                        'ringan'    => '<span class="badge" style="background:#fef3c7;color:#92400e">Ringan</span>',
                                        'sedang'    => '<span class="badge" style="background:#fed7aa;color:#9a3412">Sedang</span>',
                                        'berat'     => '<span class="badge bg-danger">Berat</span>',
                                        default     => '-'
                                    };
                                    $status_badge = match($t['status']) {
                                        'proses'     => '<span class="badge badge-proses rounded-pill px-3">● Proses</span>',
                                        'selesai'    => '<span class="badge badge-selesai rounded-pill px-3">✓ Selesai</span>',
                                        'dibatalkan' => '<span class="badge badge-dibatalkan rounded-pill px-3">✕ Dibatalkan</span>',
                                        default      => '-'
                                    };
                                ?>
                                <tr class="tbl-row">
                                    <td class="ps-3">
                                        <div class="fw-semibold small font-monospace text-danger">
                                            <?= htmlspecialchars($t['no_transfusi']) ?>
                                        </div>
                                        <?php if ($t['no_rekam_medis']): ?>
                                        <div class="text-muted" style="font-size:0.72rem">RM: <?= htmlspecialchars($t['no_rekam_medis']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small"><?= htmlspecialchars($t['nama_pasien']) ?></div>
                                        <div class="text-muted" style="font-size:0.72rem">
                                            <?= $t['jenis_kelamin']==='L'?'Laki-laki':'Perempuan' ?>
                                            <?= $t['usia_pasien'] ? '· '.$t['usia_pasien'].' th' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger"><?= $t['golongan_darah'].$sym ?></span>
                                    </td>
                                    <td class="small"><?= number_format($t['volume_ml']) ?> ml</td>
                                    <td class="small"><?= tanggal_indo($t['tanggal_transfusi']) ?></td>
                                    <td><?= $reaksi_badge ?></td>
                                    <td><?= $status_badge ?></td>
                                    <td class="small"><?= htmlspecialchars($t['nama_dokter'] ?: '-') ?></td>
                                    <td class="small"><?= htmlspecialchars($t['petugas_nama'] ?? '-') ?></td>
                                    <?php if (!$hanya_lihat): ?>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="bukaUpdate(<?= htmlspecialchars(json_encode($t)) ?>)"
                                                title="Update Status">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <?php if (isSuperAdmin() || isAdmin()): ?>
                                        <form method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus transfusi ini?')">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_transfusi" value="<?= $t['id_transfusi'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
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
                <div class="text-muted small">SIDORAH &copy; <?= date('Y') ?></div>
            </div>
        </footer>
    </div>
</div>

<!-- Modal Tambah Transfusi -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Catat Transfusi Darah
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Data Pasien -->
                        <div class="col-12">
                            <div class="fw-semibold text-danger small mb-1">
                                <i class="bi bi-person-fill me-1"></i>DATA PASIEN
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Pasien <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pasien" class="form-control" required placeholder="Nama lengkap pasien">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Rekam Medis</label>
                            <input type="text" name="no_rekam_medis" class="form-control" placeholder="RM-XXXXX">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Usia (tahun)</label>
                            <input type="number" name="usia_pasien" class="form-control" min="0" max="150" placeholder="25">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Golongan Darah <span class="text-danger">*</span></label>
                            <select name="golongan_darah" class="form-select" required onchange="filterStok()">
                                <option value="">-- Pilih --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="O">O</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Rhesus <span class="text-danger">*</span></label>
                            <select name="rhesus" class="form-select" required onchange="filterStok()">
                                <option value="Positif">Positif (+)</option>
                                <option value="Negatif">Negatif (-)</option>
                            </select>
                        </div>

                        <!-- Data Transfusi -->
                        <div class="col-12 mt-2">
                            <div class="fw-semibold text-danger small mb-1">
                                <i class="bi bi-droplet-fill me-1"></i>DATA TRANSFUSI
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kantong Darah <span class="text-danger">*</span></label>
                            <select name="id_stok" id="selectStok" class="form-select" required onchange="updateInfoStok(this)">
                                <option value="">-- Pilih golongan darah --</option>
                                <?php foreach ($stok_tersedia as $s):
                                    $sym = $s['rhesus']==='Positif'?'+':'-';
                                    $key = $s['golongan_darah'].'_'.$s['rhesus'].'_'.$s['jenis_darah'];
                                    $id_fifo = $fifo_map[$key] ?? 0;
                                    $jenis_label = $s['jenis_darah']==='PRC' ? 'PRC' : 'WB';
                                    $status_cls = $s['total_kantong'] <= 5 ? 'color:#d97706' : 'color:#16a34a';
                                ?>
                                <option value="<?= $id_fifo ?>"
                                        data-goldar="<?= $s['golongan_darah'] ?>"
                                        data-rhesus="<?= $s['rhesus'] ?>"
                                        data-jenis="<?= $s['jenis_darah'] ?>"
                                        data-total="<?= $s['total_kantong'] ?>"
                                        data-exp="<?= $s['exp_terdekat'] ?>">
                                    <?= $s['golongan_darah'].$sym ?> (<?= $jenis_label ?>) — <?= $s['total_kantong'] ?> kantong tersedia
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Info stok real-time -->
                            <div id="infoStokPilih" class="mt-2" style="display:none"></div>
                            <div id="warningKantong" class="small mt-1" style="display:none;color:#dc2626"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Darah <span class="text-danger">*</span></label>
                            <select name="jenis_darah" id="selectJenisDarah" class="form-select" required onchange="updateVolume(this.value)">
                                <option value="WB">🩸 Darah Utuh / WB — 450 mL/kantong</option>
                                <option value="PRC">💉 Darah Pekat / PRC — 250 mL/kantong</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jumlah Kantong <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_kantong" id="inputJumlahKantong" class="form-control" value="1" min="1" required oninput="validasiKantong(this)">
                            <div class="small text-muted mt-1" id="infoVolume">Volume: 450 mL</div>
                            <div id="warningKantong" class="small mt-1" style="display:none;color:#dc2626"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ruangan</label>
                            <input type="text" name="ruangan" class="form-control" placeholder="ICU / Bangsal A / dll">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Dokter Penanggung Jawab <span class="text-danger">*</span></label>
                            <input type="text" name="nama_dokter" class="form-control" placeholder="dr. Nama Dokter, Sp.PD" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Indikasi</label>
                            <input type="text" name="indikasi" class="form-control" placeholder="Anemia berat, trauma, dll">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Diagnosa</label>
                            <textarea name="diagnosa" class="form-control" rows="2" placeholder="Diagnosa medis pasien"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-save me-2"></i>Simpan Transfusi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalUpdate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="update_status">
                <input type="hidden" name="id_transfusi" id="u_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-fill me-2"></i>Update Status Transfusi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="u_info" class="alert alert-light py-2 small mb-3"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="u_status" class="form-select">
                                <option value="proses">Proses</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="u_jam_selesai" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reaksi Transfusi</label>
                            <select name="reaksi_transfusi" id="u_reaksi" class="form-select">
                                <option value="tidak_ada">Tidak Ada</option>
                                <option value="ringan">Ringan</option>
                                <option value="sedang">Sedang</option>
                                <option value="berat">Berat ⚠️</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Keterangan Reaksi</label>
                            <input type="text" name="keterangan_reaksi" id="u_ket_reaksi" class="form-control"
                                   placeholder="Contoh: demam ringan">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan Petugas</label>
                            <textarea name="catatan" id="u_catatan" class="form-control" rows="2"
                                      placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
// Info stok real-time saat pilih goldar
function filterStok() {
    const goldar = document.querySelector('select[name="golongan_darah"]').value;
    const rhesus = document.querySelector('select[name="rhesus"]').value;
    const sel    = document.getElementById('selectStok');
    const info   = document.getElementById('infoStokPilih');
    const inputKantong = document.getElementById('inputJumlahKantong');

    // Reset
    sel.value = '';
    if (info) info.style.display = 'none';
    if (inputKantong) { inputKantong.removeAttribute('max'); inputKantong.style.borderColor = ''; }

    let found = 0;
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) { opt.style.display = ''; return; } // placeholder
        const cocokGoldar = !goldar || opt.dataset.goldar === goldar;
        const cocokRhesus = !rhesus || opt.dataset.rhesus === rhesus;
        if (cocokGoldar && cocokRhesus) {
            opt.style.display = '';
            found++;
        } else {
            opt.style.display = 'none';
        }
    });

    // Kalau hanya 1 hasil, auto-pilih
    if (found === 1) {
        const visOpt = Array.from(sel.options).find(o => o.value && o.style.display !== 'none');
        if (visOpt) { sel.value = visOpt.value; updateInfoStok(sel); }
    }

    // Warning kalau tidak ada stok
    const warnKantong = document.getElementById('warningKantong');
    if (found === 0 && goldar) {
        const sym = rhesus === 'Positif' ? '+' : (rhesus === 'Negatif' ? '-' : '');
        if (warnKantong) {
            warnKantong.innerHTML = `🔴 Stok <strong>${goldar}${sym}</strong> tidak tersedia saat ini.`;
            warnKantong.style.display = 'block';
        }
    } else {
        if (warnKantong) warnKantong.style.display = 'none';
    }
}

function updateInfoStok(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('infoStokPilih');
    const inputKantong = document.getElementById('inputJumlahKantong');
    if (!info) return;

    if (!opt || !opt.value) {
        info.style.display = 'none';
        if (inputKantong) inputKantong.removeAttribute('max');
        return;
    }

    const total = parseInt(opt.dataset.total);
    const exp   = opt.dataset.exp;
    const jenis = opt.dataset.jenis || 'WB';

    // Set max kantong sesuai stok tersedia
    if (inputKantong) {
        inputKantong.max = total;
        // Reset warning
        const warn = document.getElementById('warningKantong');
        if (warn) warn.style.display = 'none';
    }

    // Auto-sync jenis darah
    const jenisSel = document.getElementById('selectJenisDarah');
    if (jenisSel) { jenisSel.value = jenis; hitungVolume(); }

    const warna = total <= 5
        ? {bg:'#fff7ed',border:'#f97316',text:'#9a3412',icon:'🟡'}
        : {bg:'#f0fdf4',border:'#22c55e',text:'#166534',icon:'🟢'};

    const expStr = exp ? new Date(exp).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : '-';

    info.innerHTML = `<div class="p-2 rounded-3 small" style="background:${warna.bg};border:1px solid ${warna.border};color:${warna.text}">
        ${warna.icon} <strong>${total} kantong</strong> tersedia &nbsp;|&nbsp;
        Exp terdekat: <strong>${expStr}</strong>
        <div class="mt-1 text-muted" style="font-size:0.75rem">
            ⚡ Sistem otomatis ambil dari batch yang paling dekat expired (FIFO)
        </div>
    </div>`;
    info.style.display = 'block';
}

// Validasi jumlah kantong tidak melebihi stok
function validasiKantong(input) {
    hitungVolume();
    const max = parseInt(input.max);
    const val = parseInt(input.value);
    const warn = document.getElementById('warningKantong');
    const btnSimpan = document.querySelector('#modalTambah .btn-danger[type="submit"]');
    if (!warn || isNaN(max)) return;

    if (val > max) {
        warn.innerHTML = `🔴 Stok tidak cukup! Maksimal <strong>${max} kantong</strong> tersedia.`;
        warn.style.display = 'block';
        input.style.borderColor = '#dc2626';
        if (btnSimpan) btnSimpan.disabled = true;
    } else {
        warn.style.display = 'none';
        input.style.borderColor = '';
        if (btnSimpan) btnSimpan.disabled = false;
    }
}

function updateVolume(jenis) { hitungVolume(); }

function hitungVolume() {
    const jenis = document.getElementById('selectJenisDarah')?.value || 'WB';
    const kantong = parseInt(document.getElementById('inputJumlahKantong')?.value || 1);
    const mlPerKantong = jenis === 'PRC' ? 250 : 450;
    const totalMl = kantong * mlPerKantong;
    const info = document.getElementById('infoVolume');
    if (info) info.textContent = `Volume: ${totalMl.toLocaleString()} mL (${kantong} × ${mlPerKantong} mL)`;
}

document.addEventListener('DOMContentLoaded', () => {
    const inputKantong = document.getElementById('inputJumlahKantong');
    if (inputKantong) inputKantong.addEventListener('input', hitungVolume);
});

function bukaUpdate(t) {
    document.getElementById('u_id').value         = t.id_transfusi;
    document.getElementById('u_status').value     = t.status;
    document.getElementById('u_reaksi').value     = t.reaksi_transfusi;
    document.getElementById('u_ket_reaksi').value = t.keterangan_reaksi || '';
    document.getElementById('u_jam_selesai').value= t.jam_selesai || '';
    document.getElementById('u_catatan').value    = t.catatan || '';
    document.getElementById('u_info').innerHTML   =
        '<strong>' + t.no_transfusi + '</strong> — ' + t.nama_pasien +
        ' · ' + t.golongan_darah + (t.rhesus==='Positif'?'+':'-') +
        ' · ' + t.volume_ml + ' ml';
    new bootstrap.Modal(document.getElementById('modalUpdate')).show();
}
</script>
</body>
</html>