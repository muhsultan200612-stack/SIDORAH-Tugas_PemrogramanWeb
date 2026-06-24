<?php
/**
 * SIDORAH - pendaftaran.php
 * Kelola pendaftaran peserta kegiatan donor
 */
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

// ── PROSES ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    // TAMBAH PENDAFTARAN MANUAL
    if ($aksi === 'tambah') {
        $id_pendonor  = (int)$_POST['id_pendonor'];
        $id_kegiatan  = (int)$_POST['id_kegiatan'];
        $status       = 'menunggu';

        // Cek duplikat
        $cek = $koneksi->query("SELECT id_pendaftaran FROM pendaftaran WHERE id_pendonor=$id_pendonor AND id_kegiatan=$id_kegiatan LIMIT 1");
        if ($cek->num_rows > 0) {
            $_SESSION['pesan'] = 'Pendonor sudah terdaftar di kegiatan ini.';
            $_SESSION['tipe']  = 'warning';
        } else {
            $stmt = $koneksi->prepare("INSERT INTO pendaftaran (id_pendonor,id_kegiatan,status_pendaftaran) VALUES (?,?,?)");
            $stmt->bind_param('iis', $id_pendonor,$id_kegiatan,$status);
            if ($stmt->execute()) {
                // Update jumlah terdaftar
                $koneksi->query("UPDATE kegiatan_donor SET jumlah_terdaftar=jumlah_terdaftar+1 WHERE id_kegiatan=$id_kegiatan");
                $info = $koneksi->query("SELECT u.nama_lengkap, k.nama_kegiatan FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna JOIN kegiatan_donor k ON k.id_kegiatan=$id_kegiatan WHERE p.id_pendonor=$id_pendonor")->fetch_assoc();
                catat_log($koneksi,'TAMBAH_PENDAFTARAN','pendaftaran',"Daftarkan {$info['nama_lengkap']} ke {$info['nama_kegiatan']}");
                $_SESSION['pesan'] = "Pendonor berhasil didaftarkan.";
                $_SESSION['tipe']  = 'success';
            }
            $stmt->close();
        }
        redirect('pendaftaran.php');
    }

    // VERIFIKASI (SETUJUI / TOLAK)
    if ($aksi === 'verifikasi') {
        $id          = (int)$_POST['id_pendaftaran'];
        $status_baru = bersihkan($koneksi, $_POST['status_baru']);
        $catatan     = bersihkan($koneksi, $_POST['catatan_admin'] ?? '');
        $id_admin    = $_SESSION['id_pengguna'];
        $now         = date('Y-m-d H:i:s');

        $pend = $koneksi->query("
            SELECT pend.*, u.nama_lengkap, k.nama_kegiatan, k.id_kegiatan
            FROM pendaftaran pend
            JOIN pendonor p ON pend.id_pendonor=p.id_pendonor
            JOIN users u ON p.id_pengguna=u.id_pengguna
            JOIN kegiatan_donor k ON pend.id_kegiatan=k.id_kegiatan
            WHERE pend.id_pendaftaran=$id
        ")->fetch_assoc();

        $stmt = $koneksi->prepare("UPDATE pendaftaran SET status_pendaftaran=?,catatan_admin=?,tanggal_verifikasi=?,id_admin_verifikasi=? WHERE id_pendaftaran=?");
        $stmt->bind_param('sssii', $status_baru,$catatan,$now,$id_admin,$id);
        $stmt->execute();
        $stmt->close();

        // Jika ditolak/batal, kurangi jumlah terdaftar
        if (in_array($status_baru, ['ditolak','batal'])) {
            $koneksi->query("UPDATE kegiatan_donor SET jumlah_terdaftar=GREATEST(0,jumlah_terdaftar-1) WHERE id_kegiatan={$pend['id_kegiatan']}");
        }

        $label = match($status_baru) {
            'disetujui' => 'disetujui',
            'ditolak'   => 'ditolak',
            'batal'     => 'dibatalkan',
            default     => $status_baru
        };
        catat_log($koneksi,'VERIFIKASI_PENDAFTARAN','pendaftaran',"{$pend['nama_lengkap']} → {$pend['nama_kegiatan']}: $label");
        $_SESSION['pesan'] = "Pendaftaran <strong>{$pend['nama_lengkap']}</strong> berhasil $label.";
        $_SESSION['tipe']  = $status_baru === 'disetujui' ? 'success' : 'info';
        redirect('pendaftaran.php');
    }
}

// ── DATA ──────────────────────────────────────────────────────
$filter_status   = bersihkan($koneksi, $_GET['status']   ?? '');
$filter_kegiatan = (int)($_GET['kegiatan'] ?? 0);
$cari            = bersihkan($koneksi, $_GET['cari']     ?? '');

$where = "WHERE 1=1";
if ($filter_status)   $where .= " AND pend.status_pendaftaran='$filter_status'";
if ($filter_kegiatan) $where .= " AND pend.id_kegiatan=$filter_kegiatan";
if ($cari)            $where .= " AND u.nama_lengkap LIKE '%$cari%'";

$per_hal = 15;
$hal     = max(1,(int)($_GET['hal'] ?? 1));
$offset  = ($hal-1)*$per_hal;

$total = $koneksi->query("
    SELECT COUNT(*) as n FROM pendaftaran pend
    JOIN pendonor p ON pend.id_pendonor=p.id_pendonor
    JOIN users u ON p.id_pengguna=u.id_pengguna
    $where
")->fetch_assoc()['n'];
$total_hal = ceil($total/$per_hal);

$pendaftaran = $koneksi->query("
    SELECT pend.*,
           u.nama_lengkap, u.email,
           p.golongan_darah, p.rhesus, p.total_donor,
           k.nama_kegiatan, k.tanggal_kegiatan,
           adm.nama_lengkap as nama_admin
    FROM pendaftaran pend
    JOIN pendonor p ON pend.id_pendonor=p.id_pendonor
    JOIN users u ON p.id_pengguna=u.id_pengguna
    JOIN kegiatan_donor k ON pend.id_kegiatan=k.id_kegiatan
    LEFT JOIN users adm ON pend.id_admin_verifikasi=adm.id_pengguna
    $where
    ORDER BY
        FIELD(pend.status_pendaftaran,'menunggu','disetujui','ditolak','batal'),
        pend.tanggal_daftar DESC
    LIMIT $per_hal OFFSET $offset
");

// Stat
$stat_menunggu  = $koneksi->query("SELECT COUNT(*) as n FROM pendaftaran WHERE status_pendaftaran='menunggu'")->fetch_assoc()['n'];
$stat_disetujui = $koneksi->query("SELECT COUNT(*) as n FROM pendaftaran WHERE status_pendaftaran='disetujui'")->fetch_assoc()['n'];
$stat_ditolak   = $koneksi->query("SELECT COUNT(*) as n FROM pendaftaran WHERE status_pendaftaran='ditolak'")->fetch_assoc()['n'];
$stat_total     = $koneksi->query("SELECT COUNT(*) as n FROM pendaftaran")->fetch_assoc()['n'];

// Dropdown kegiatan aktif
$kegiatan_list = $koneksi->query("SELECT id_kegiatan, nama_kegiatan, tanggal_kegiatan FROM kegiatan_donor WHERE status_kegiatan='aktif' ORDER BY tanggal_kegiatan DESC")->fetch_all(MYSQLI_ASSOC);

// Dropdown pendonor aktif
$pendonor_list = $koneksi->query("
    SELECT p.id_pendonor, u.nama_lengkap, p.golongan_darah, p.rhesus
    FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna
    WHERE p.status_aktif=1
    ORDER BY u.nama_lengkap ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .status-menunggu  { background:#fef3c7; color:#92400e; }
        .status-disetujui { background:#d1fae5; color:#065f46; }
        .status-ditolak   { background:#fee2e2; color:#991b1b; }
        .status-batal     { background:#f3f4f6; color:#6b7280; }
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
                        <h1 class="h3 mb-0"><i class="bi bi-person-check-fill text-danger me-2"></i>Pendaftaran Donor</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Pendaftaran</li>
                        </ol>
                    </div>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-1"></i> Daftarkan Pendonor
                    </button>
                </div>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show">
                    <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Stat -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-warning mb-0"><?= $stat_menunggu ?></div>
                            <div class="small text-muted">Menunggu Verifikasi</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-success mb-0"><?= $stat_disetujui ?></div>
                            <div class="small text-muted">Disetujui</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-danger mb-0"><?= $stat_ditolak ?></div>
                            <div class="small text-muted">Ditolak</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-primary mb-0"><?= $stat_total ?></div>
                            <div class="small text-muted">Total Pendaftaran</div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <form method="GET" class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="cari" class="form-control"
                                           placeholder="Cari nama pendonor..."
                                           value="<?= htmlspecialchars($cari) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="kegiatan" class="form-select">
                                    <option value="">Semua Kegiatan</option>
                                    <?php foreach ($kegiatan_list as $k): ?>
                                    <option value="<?= $k['id_kegiatan'] ?>" <?= $filter_kegiatan==$k['id_kegiatan']?'selected':'' ?>>
                                        <?= htmlspecialchars($k['nama_kegiatan']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <?php foreach (['menunggu','disetujui','ditolak','batal'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="pendaftaran.php" class="btn btn-outline-secondary">Reset</a>
                            </div>
                            <div class="col-auto ms-auto text-muted small"><?= number_format($total) ?> data</div>
                        </form>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Pendonor</th>
                                        <th>Gol. Darah</th>
                                        <th>Kegiatan</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Status</th>
                                        <th>Diverifikasi</th>
                                        <th class="text-center pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($pendaftaran->num_rows === 0): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x fs-2 d-block mb-2"></i>Tidak ada data pendaftaran
                                </td></tr>
                                <?php else:
                                    while ($pend = $pendaftaran->fetch_assoc()):
                                    $sym = $pend['rhesus']==='Positif'?'+':'-';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:34px;height:34px;border-radius:50%;background:#dc3545;
                                                display:flex;align-items:center;justify-content:center;
                                                color:white;font-weight:700;font-size:0.8rem;flex-shrink:0">
                                                <?= strtoupper(substr($pend['nama_lengkap'],0,1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($pend['nama_lengkap']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars($pend['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fw-bold">
                                            <?= $pend['golongan_darah'] ?><?= $sym ?>
                                        </span>
                                        <div class="text-muted" style="font-size:0.72rem"><?= $pend['total_donor'] ?>x donor</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                            <?= htmlspecialchars($pend['nama_kegiatan']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size:0.72rem"><?= tanggal_indo($pend['tanggal_kegiatan']) ?></div>
                                    </td>
                                    <td class="text-muted"><?= format_waktu_singkat($pend['tanggal_daftar']) ?></td>
                                    <td>
                                        <span class="badge status-<?= $pend['status_pendaftaran'] ?> rounded-pill px-3">
                                            <?= ucfirst($pend['status_pendaftaran']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?php if ($pend['nama_admin']): ?>
                                            <?= htmlspecialchars($pend['nama_admin']) ?><br>
                                            <span style="font-size:0.72rem"><?= $pend['tanggal_verifikasi'] ? format_waktu_singkat($pend['tanggal_verifikasi']) : '' ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <?php if ($pend['status_pendaftaran'] === 'menunggu'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-success btn-sm"
                                                onclick="verifikasi(<?= $pend['id_pendaftaran'] ?>,'disetujui','<?= addslashes($pend['nama_lengkap']) ?>')">
                                                <i class="bi bi-check-lg"></i> Setuju
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm"
                                                onclick="verifikasi(<?= $pend['id_pendaftaran'] ?>,'ditolak','<?= addslashes($pend['nama_lengkap']) ?>')">
                                                <i class="bi bi-x-lg"></i> Tolak
                                            </button>
                                        </div>
                                        <?php elseif ($pend['status_pendaftaran'] === 'disetujui'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="aksi_form" value="verifikasi">
                                            <input type="hidden" name="id_pendaftaran" value="<?= $pend['id_pendaftaran'] ?>">
                                            <input type="hidden" name="status_baru" value="batal">
                                            <input type="hidden" name="catatan_admin" value="Dibatalkan oleh admin">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-x-circle"></i> Batalkan
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
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

<!-- Modal Tambah Pendaftaran -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Daftarkan Pendonor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Pendonor <span class="text-danger">*</span></label>
                        <select name="id_pendonor" class="form-select" required>
                            <option value="">-- Pilih Pendonor --</option>
                            <?php foreach ($pendonor_list as $p): ?>
                            <option value="<?= $p['id_pendonor'] ?>">
                                <?= htmlspecialchars($p['nama_lengkap']) ?>
                                (<?= $p['golongan_darah'] ?><?= $p['rhesus']==='Positif'?'+':'-' ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Kegiatan <span class="text-danger">*</span></label>
                        <select name="id_kegiatan" class="form-select" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            <?php foreach ($kegiatan_list as $k): ?>
                            <option value="<?= $k['id_kegiatan'] ?>">
                                <?= htmlspecialchars($k['nama_kegiatan']) ?>
                                (<?= tanggal_indo($k['tanggal_kegiatan']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-save me-1"></i>Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div class="modal fade" id="modalVerifikasi" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" id="vHeader">
                <h5 class="modal-title" id="vTitle">Verifikasi Pendaftaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="verifikasi">
                <input type="hidden" name="id_pendaftaran" id="v_id">
                <input type="hidden" name="status_baru" id="v_status">
                <div class="modal-body">
                    <p id="v_pesan" class="mb-3"></p>
                    <label class="form-label fw-semibold">Catatan (opsional)</label>
                    <textarea name="catatan_admin" class="form-control" rows="2"
                              placeholder="Alasan penolakan, catatan medis, dll"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" id="v_btn">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function verifikasi(id, status, nama) {
    document.getElementById('v_id').value     = id;
    document.getElementById('v_status').value = status;
    const isSetuju = status === 'disetujui';
    document.getElementById('vHeader').className = 'modal-header ' + (isSetuju ? 'bg-success text-white' : 'bg-danger text-white');
    document.getElementById('vTitle').textContent = isSetuju ? 'Setujui Pendaftaran' : 'Tolak Pendaftaran';
    document.getElementById('v_pesan').textContent = `${isSetuju ? 'Setujui' : 'Tolak'} pendaftaran ${nama}?`;
    const btn = document.getElementById('v_btn');
    btn.className = 'btn ' + (isSetuju ? 'btn-success' : 'btn-danger');
    btn.textContent = isSetuju ? 'Setujui' : 'Tolak';
    new bootstrap.Modal(document.getElementById('modalVerifikasi')).show();
}
</script>
</body>
</html>