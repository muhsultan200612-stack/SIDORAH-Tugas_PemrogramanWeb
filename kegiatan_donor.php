<?php
/**
 * SIDORAH - kegiatan_donor.php
 */
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS]);

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hanya admin & super admin yang boleh POST
    if (!cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])) {
        $_SESSION['pesan'] = "Anda tidak memiliki akses untuk melakukan aksi ini.";
        $_SESSION['tipe']  = 'danger';
        redirect('kegiatan_donor.php');
    }
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'tambah' || $aksi === 'edit') {
        $nama      = bersihkan($koneksi, $_POST['nama_kegiatan']);
        $tgl       = bersihkan($koneksi, $_POST['tanggal_kegiatan']);
        $mulai     = bersihkan($koneksi, $_POST['waktu_mulai']);
        $selesai   = bersihkan($koneksi, $_POST['waktu_selesai']);
        $lokasi    = bersihkan($koneksi, $_POST['lokasi']);
        $kuota     = (int)$_POST['kuota_peserta'];
        $syarat    = bersihkan($koneksi, $_POST['persyaratan']);
        $status    = bersihkan($koneksi, $_POST['status_kegiatan']);
        $id_admin  = $_SESSION['id_pengguna'];

        if ($aksi === 'tambah') {
            $stmt = $koneksi->prepare("INSERT INTO kegiatan_donor (nama_kegiatan,tanggal_kegiatan,waktu_mulai,waktu_selesai,lokasi,kuota_peserta,persyaratan,status_kegiatan,id_admin) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssissi', $nama,$tgl,$mulai,$selesai,$lokasi,$kuota,$syarat,$status,$id_admin);
            $stmt->execute();
            catat_log($koneksi,'TAMBAH_KEGIATAN','kegiatan_donor',"Tambah kegiatan: $nama ($tgl)");
            $_SESSION['pesan'] = "Kegiatan <strong>$nama</strong> berhasil dibuat.";
            $_SESSION['tipe']  = 'success';
            $stmt->close();
        } else {
            $id = (int)$_POST['id_kegiatan'];
            $stmt = $koneksi->prepare("UPDATE kegiatan_donor SET nama_kegiatan=?,tanggal_kegiatan=?,waktu_mulai=?,waktu_selesai=?,lokasi=?,kuota_peserta=?,persyaratan=?,status_kegiatan=? WHERE id_kegiatan=?");
            $stmt->bind_param('sssssissi', $nama,$tgl,$mulai,$selesai,$lokasi,$kuota,$syarat,$status,$id);
            $stmt->execute();
            catat_log($koneksi,'EDIT_KEGIATAN','kegiatan_donor',"Edit kegiatan ID $id: $nama");
            $_SESSION['pesan'] = "Kegiatan berhasil diperbarui.";
            $_SESSION['tipe']  = 'success';
            $stmt->close();
        }
        redirect('kegiatan_donor.php');
    }

    if ($aksi === 'ubah_status') {
        $id     = (int)$_POST['id_kegiatan'];
        $status = bersihkan($koneksi, $_POST['status_baru']);
        $k      = $koneksi->query("SELECT nama_kegiatan FROM kegiatan_donor WHERE id_kegiatan=$id")->fetch_assoc();
        $koneksi->query("UPDATE kegiatan_donor SET status_kegiatan='$status' WHERE id_kegiatan=$id");
        catat_log($koneksi,'UBAH_STATUS_KEGIATAN','kegiatan_donor',"{$k['nama_kegiatan']} → $status");
        $_SESSION['pesan'] = "Status kegiatan diubah menjadi <strong>$status</strong>.";
        $_SESSION['tipe']  = 'info';
        redirect('kegiatan_donor.php');
    }
}

// Data
$filter_status = bersihkan($koneksi, $_GET['status'] ?? '');
$cari          = bersihkan($koneksi, $_GET['cari']   ?? '');
$tab_aktif     = $_GET['tab'] ?? 'aktif';
$bulan_ini     = date('Y-m');

$where = "WHERE 1=1";
if ($filter_status) $where .= " AND k.status_kegiatan='$filter_status'";
if ($cari)          $where .= " AND (k.nama_kegiatan LIKE '%$cari%' OR k.lokasi LIKE '%$cari%')";

// Tab aktif = bulan ini + yang akan datang + yang masih aktif
$where_aktif = $where . " AND (DATE_FORMAT(k.tanggal_kegiatan,'%Y-%m') >= '$bulan_ini')";
// Tab arsip = bulan sebelumnya
$where_arsip = $where . " AND DATE_FORMAT(k.tanggal_kegiatan,'%Y-%m') < '$bulan_ini'";

$where_query = $tab_aktif === 'arsip' ? $where_arsip : $where_aktif;

$kegiatan = $koneksi->query("
    SELECT k.*, u.nama_lengkap as nama_admin,
           (k.kuota_peserta - k.jumlah_terdaftar) as sisa_kuota
    FROM kegiatan_donor k
    LEFT JOIN users u ON k.id_admin=u.id_pengguna
    $where_query
    ORDER BY k.tanggal_kegiatan DESC
");

$total_aktif_tab = $koneksi->query("
    SELECT COUNT(*) as n FROM kegiatan_donor k $where_aktif
")->fetch_assoc()['n'];
$total_arsip_tab = $koneksi->query("
    SELECT COUNT(*) as n FROM kegiatan_donor k $where_arsip
")->fetch_assoc()['n'];

$stat_aktif   = $koneksi->query("SELECT COUNT(*) as n FROM kegiatan_donor WHERE status_kegiatan='aktif' AND tanggal_kegiatan >= CURDATE()")->fetch_assoc()['n'];
$stat_selesai = $koneksi->query("SELECT COUNT(*) as n FROM kegiatan_donor WHERE status_kegiatan='selesai'")->fetch_assoc()['n'];
$stat_total_donor = $koneksi->query("SELECT COALESCE(SUM(jumlah_terdaftar),0) as n FROM kegiatan_donor")->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kegiatan Donor — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .kegiatan-card { border-radius:14px; border:none; transition:transform .2s,box-shadow .2s; }
        .kegiatan-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.1)!important; }
        .status-aktif    { background:#d1fae5; color:#065f46; }
        .status-selesai  { background:#dbeafe; color:#1e40af; }
        .status-dibatalkan { background:#fee2e2; color:#991b1b; }
        .progress { height:8px; border-radius:10px; }
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
                        <h1 class="h3 mb-0"><i class="bi bi-calendar-event-fill text-danger me-2"></i>Kegiatan Donor</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Kegiatan</li>
                        </ol>
                    </div>
                    <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-calendar-plus me-1"></i> Buat Kegiatan
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
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-success mb-0"><?= $stat_aktif ?></div>
                            <div class="small text-muted">Kegiatan Aktif</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-primary mb-0"><?= $stat_selesai ?></div>
                            <div class="small text-muted">Sudah Selesai</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-danger mb-0"><?= number_format($stat_total_donor) ?></div>
                            <div class="small text-muted">Total Terdaftar</div>
                        </div>
                    </div>
                </div>

                <!-- Tab Aktif / Arsip + Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold <?= $tab_aktif==='aktif'?'active':'' ?>"
                                       href="kegiatan_donor.php?tab=aktif">
                                        <i class="bi bi-calendar-check me-1 text-danger"></i> Aktif & Mendatang
                                        <span class="badge bg-danger ms-1" style="font-size:0.68rem"><?= $total_aktif_tab ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold <?= $tab_aktif==='arsip'?'active':'' ?>"
                                       href="kegiatan_donor.php?tab=arsip">
                                        <i class="bi bi-archive-fill me-1 text-secondary"></i> Arsip
                                        <span class="badge bg-secondary ms-1" style="font-size:0.68rem"><?= $total_arsip_tab ?></span>
                                    </a>
                                </li>
                            </ul>
                            <?php if ($tab_aktif === 'arsip'): ?>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Kegiatan bulan-bulan sebelumnya</small>
                            <?php else: ?>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Bulan ini & mendatang</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body py-3 border-bottom">
                        <form method="GET" class="row g-2 align-items-center">
                            <input type="hidden" name="tab" value="<?= $tab_aktif ?>">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="cari" class="form-control"
                                           placeholder="Cari nama kegiatan / lokasi..."
                                           value="<?= htmlspecialchars($cari) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <?php foreach (['aktif','selesai','dibatalkan'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="kegiatan_donor.php?tab=<?= $tab_aktif ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Grid Kegiatan -->
                <?php if ($kegiatan->num_rows === 0): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                    <p>Belum ada kegiatan donor</p>
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php while ($k = $kegiatan->fetch_assoc()):
                        $persen = $k['kuota_peserta'] > 0
                            ? round(($k['jumlah_terdaftar'] / $k['kuota_peserta']) * 100) : 0;
                        $bar    = $persen >= 90 ? 'bg-danger' : ($persen >= 60 ? 'bg-warning' : 'bg-success');
                        $sudah_lewat = strtotime($k['tanggal_kegiatan']) < strtotime('today');
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card kegiatan-card shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge status-<?= $k['status_kegiatan'] ?> rounded-pill px-3">
                                        <?= ucfirst($k['status_kegiatan']) ?>
                                    </span>
                                    <?php if ($sudah_lewat && $k['status_kegiatan']==='aktif'): ?>
                                    <span class="badge bg-warning-subtle text-warning rounded-pill">Perlu diupdate</span>
                                    <?php endif; ?>
                                </div>

                                <h6 class="fw-bold mb-2"><?= htmlspecialchars($k['nama_kegiatan']) ?></h6>

                                <div class="text-muted small mb-1">
                                    <i class="bi bi-calendar3 me-1 text-danger"></i>
                                    <?= tanggal_indo($k['tanggal_kegiatan']) ?>
                                </div>
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-clock me-1 text-danger"></i>
                                    <?= substr($k['waktu_mulai'],0,5) ?> – <?= substr($k['waktu_selesai'],0,5) ?>
                                </div>
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i>
                                    <?= htmlspecialchars($k['lokasi']) ?>
                                </div>

                                <!-- Progress kuota -->
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Peserta</span>
                                    <span class="fw-semibold"><?= $k['jumlah_terdaftar'] ?>/<?= $k['kuota_peserta'] ?></span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar <?= $bar ?>" style="width:<?= $persen ?>%"></div>
                                </div>

                                <div class="text-muted" style="font-size:0.75rem">
                                    <i class="bi bi-person-fill me-1"></i>
                                    Dibuat oleh: <?= htmlspecialchars($k['nama_admin'] ?? 'Sistem') ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-4">
                                <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm flex-grow-1"
                                        onclick='bukaEdit(<?= json_encode($k) ?>)'>
                                        <i class="bi bi-pencil-fill me-1"></i>Edit
                                    </button>
                                    <?php if ($k['status_kegiatan'] === 'aktif'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="aksi_form" value="ubah_status">
                                        <input type="hidden" name="id_kegiatan" value="<?= $k['id_kegiatan'] ?>">
                                        <input type="hidden" name="status_baru" value="selesai">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-check-lg"></i> Selesai
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="aksi_form" value="ubah_status">
                                        <input type="hidden" name="id_kegiatan" value="<?= $k['id_kegiatan'] ?>">
                                        <input type="hidden" name="status_baru" value="dibatalkan">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="small text-muted text-center py-1">
                                    <i class="bi bi-eye me-1"></i>Hanya bisa dilihat
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Buat Kegiatan Donor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" class="form-control" required placeholder="Contoh: Donor Darah HUT RS SIDORAH 2026">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal_kegiatan" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" class="form-control" value="08:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" class="form-control" value="12:00">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control" placeholder="Aula Rumah Sakit SIDORAH">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kuota Peserta</label>
                            <input type="number" name="kuota_peserta" class="form-control" min="1" value="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status_kegiatan" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Persyaratan</label>
                            <textarea name="persyaratan" class="form-control" rows="3"
                                placeholder="Usia 17-65 tahun, berat badan min 45 kg, sehat jasmani dan rohani..."></textarea>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Kegiatan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="edit">
                <input type="hidden" name="id_kegiatan" id="ek_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Kegiatan</label>
                            <input type="text" name="nama_kegiatan" id="ek_nama" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal_kegiatan" id="ek_tgl" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" id="ek_mulai" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" id="ek_selesai" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" name="lokasi" id="ek_lokasi" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kuota Peserta</label>
                            <input type="number" name="kuota_peserta" id="ek_kuota" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status_kegiatan" id="ek_status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Persyaratan</label>
                            <textarea name="persyaratan" id="ek_syarat" class="form-control" rows="3"></textarea>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function bukaEdit(k) {
    document.getElementById('ek_id').value     = k.id_kegiatan;
    document.getElementById('ek_nama').value   = k.nama_kegiatan;
    document.getElementById('ek_tgl').value    = k.tanggal_kegiatan;
    document.getElementById('ek_mulai').value  = k.waktu_mulai ? k.waktu_mulai.substr(0,5) : '';
    document.getElementById('ek_selesai').value= k.waktu_selesai ? k.waktu_selesai.substr(0,5) : '';
    document.getElementById('ek_lokasi').value = k.lokasi || '';
    document.getElementById('ek_kuota').value  = k.kuota_peserta;
    document.getElementById('ek_status').value = k.status_kegiatan;
    document.getElementById('ek_syarat').value = k.persyaratan || '';
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>