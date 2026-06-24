<?php
/**
 * SIDORAH - kelola_notifikasi.php
 * Admin/Petugas buat notifikasi darurat untuk pendonor
 */
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'tambah') {
        $judul   = bersihkan($koneksi, $_POST['judul']);
        $isi     = bersihkan($koneksi, $_POST['pesan']);
        $goldar  = bersihkan($koneksi, $_POST['golongan_darah']);
        $rhesus  = bersihkan($koneksi, $_POST['rhesus']);
        $tingkat = bersihkan($koneksi, $_POST['tingkat']);
        $id_keg  = !empty($_POST['id_kegiatan']) ? (int)$_POST['id_kegiatan'] : null;
        $expired = !empty($_POST['expired_at']) ? bersihkan($koneksi, $_POST['expired_at']) : null;
        $id_user = $_SESSION['id_pengguna'];

        $stmt = $koneksi->prepare("INSERT INTO notifikasi_darurat (judul,pesan,golongan_darah,rhesus,tingkat,id_kegiatan,id_pembuat,expired_at) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssiss', $judul,$isi,$goldar,$rhesus,$tingkat,$id_keg,$id_user,$expired);
        if ($stmt->execute()) {
            catat_log($koneksi,'BUAT_NOTIF_DARURAT','notifikasi_darurat',"Buat notifikasi: $judul (tingkat: $tingkat, goldar: $goldar)");
            $_SESSION['pesan'] = "Notifikasi <strong>$judul</strong> berhasil dikirim ke pendonor!";
            $_SESSION['tipe']  = 'success';
        }
        $stmt->close();
        redirect('kelola_notifikasi.php');
    }

    if ($aksi === 'toggle') {
        $id     = (int)$_POST['id_notif'];
        $notif  = $koneksi->query("SELECT status, judul FROM notifikasi_darurat WHERE id_notif=$id")->fetch_assoc();
        $baru   = $notif['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $koneksi->query("UPDATE notifikasi_darurat SET status='$baru' WHERE id_notif=$id");
        catat_log($koneksi,'TOGGLE_NOTIF','notifikasi_darurat',"{$notif['judul']} → $baru");
        $_SESSION['pesan'] = "Notifikasi berhasil di-$baru.";
        $_SESSION['tipe']  = 'info';
        redirect('kelola_notifikasi.php');
    }

    if ($aksi === 'hapus') {
        $id = (int)$_POST['id_notif'];
        $notif = $koneksi->query("SELECT judul FROM notifikasi_darurat WHERE id_notif=$id")->fetch_assoc();
        $koneksi->query("DELETE FROM notifikasi_darurat WHERE id_notif=$id");
        catat_log($koneksi,'HAPUS_NOTIF','notifikasi_darurat',"Hapus notifikasi: {$notif['judul']}");
        $_SESSION['pesan'] = "Notifikasi berhasil dihapus.";
        $_SESSION['tipe']  = 'success';
        redirect('kelola_notifikasi.php');
    }
}

// Auto nonaktifkan yang expired (pakai timezone WITA)
$koneksi->query("UPDATE notifikasi_darurat SET status='nonaktif' WHERE expired_at IS NOT NULL AND expired_at < CONVERT_TZ(NOW(), '+00:00', '+08:00') AND status='aktif'");

// Data
$notifikasi = $koneksi->query("
    SELECT n.*, u.nama_lengkap as nama_pembuat,
           k.nama_kegiatan
    FROM notifikasi_darurat n
    LEFT JOIN users u ON n.id_pembuat=u.id_pengguna
    LEFT JOIN kegiatan_donor k ON n.id_kegiatan=k.id_kegiatan
    ORDER BY n.status='aktif' DESC, n.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$stat_aktif = count(array_filter($notifikasi, fn($n) => $n['status']==='aktif'));

// Kegiatan aktif untuk dropdown
$kegiatan_list = $koneksi->query("SELECT id_kegiatan, nama_kegiatan, tanggal_kegiatan FROM kegiatan_donor WHERE status_kegiatan='aktif' AND tanggal_kegiatan >= CURDATE() ORDER BY tanggal_kegiatan ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Notifikasi Darurat — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .notif-card { border-radius:14px; transition:transform .2s; }
        .notif-card:hover { transform:translateY(-2px); }
        .badge-darurat { background:#dc3545; color:white; }
        .badge-warning  { background:#ffc107; color:#212529; }
        .badge-info     { background:#0dcaf0; color:#212529; }
        .border-darurat { border-left:5px solid #dc3545 !important; }
        .border-warning  { border-left:5px solid #ffc107 !important; }
        .border-info     { border-left:5px solid #0dcaf0 !important; }
        .preview-popup {
            border-radius:16px;
            background:linear-gradient(135deg,#7b0d1e,#dc3545);
            color:white; padding:1.2rem 1.5rem;
            display:flex; align-items:flex-start; gap:12px;
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
                        <h1 class="h3 mb-0"><i class="bi bi-bell-fill text-danger me-2"></i>Notifikasi Darurat</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notifikasi Darurat</li>
                        </ol>
                    </div>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-megaphone-fill me-1"></i> Kirim Notifikasi
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
                            <div class="h3 fw-bold text-danger mb-0"><?= $stat_aktif ?></div>
                            <div class="small text-muted">Notifikasi Aktif</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-secondary mb-0"><?= count($notifikasi) - $stat_aktif ?></div>
                            <div class="small text-muted">Nonaktif / Expired</div>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Notifikasi aktif akan <strong>muncul otomatis</strong> di portal pendonor sebagai popup darurat.
                    Pendonor akan melihatnya saat membuka halaman portal.
                </div>

                <!-- Preview tampilan di portal pendonor -->
                <?php
                $notif_aktif_preview = array_filter($notifikasi, fn($n) => $n['status']==='aktif');
                if (!empty($notif_aktif_preview)):
                    $np = array_values($notif_aktif_preview)[0];
                    $warna_preview = match($np['tingkat']) {
                        'darurat' => 'linear-gradient(135deg,#7b0d1e,#dc3545)',
                        'warning' => 'linear-gradient(135deg,#92400e,#d97706)',
                        default   => 'linear-gradient(135deg,#1e40af,#3b82f6)'
                    };
                ?>
                <div class="mb-4">
                    <div class="small fw-semibold text-muted mb-2">
                        <i class="bi bi-eye me-1"></i>Preview tampilan di portal pendonor:
                    </div>
                    <div class="preview-popup" style="background:<?= $warna_preview ?>;max-width:600px">
                        <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($np['judul']) ?></div>
                            <div style="font-size:0.9rem;opacity:0.9"><?= nl2br(htmlspecialchars($np['pesan'])) ?></div>
                            <?php if ($np['golongan_darah'] !== 'Semua'): ?>
                            <div class="mt-1" style="font-size:0.8rem;opacity:0.8">
                                Golongan: <?= $np['golongan_darah'] ?><?= $np['rhesus']==='Semua'?'':($np['rhesus']==='Positif'?'+':'-') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Daftar Notifikasi -->
                <?php if (empty($notifikasi)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-bell-slash fs-2 d-block mb-3"></i>
                        <p>Belum ada notifikasi. Klik "Kirim Notifikasi" untuk membuat.</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($notifikasi as $n):
                        $warna = match($n['tingkat']) {
                            'darurat' => '#dc3545',
                            'warning' => '#d97706',
                            default   => '#0d6efd'
                        };
                        $is_expired = $n['expired_at'] && strtotime($n['expired_at']) < time();
                    ?>
                    <div class="col-md-6">
                        <div class="card notif-card border-0 shadow-sm h-100 border-<?= $n['tingkat'] ?>"
                             style="border-left:5px solid <?= $warna ?> !important">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge rounded-pill px-3"
                                          style="background:<?= $warna ?>20;color:<?= $warna ?>;font-weight:700">
                                        <?php
                                        $icon = match($n['tingkat']) {
                                            'darurat'=>'🚨','warning'=>'⚠️',default=>'ℹ️'
                                        };
                                        echo $icon.' '.ucfirst($n['tingkat']);
                                        ?>
                                    </span>
                                    <span class="badge <?= $n['status']==='aktif'?'bg-success':'bg-secondary' ?> rounded-pill">
                                        <?= $n['status']==='aktif'?'● Aktif':'○ Nonaktif' ?>
                                    </span>
                                </div>

                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($n['judul']) ?></h6>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($n['pesan']) ?></p>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if ($n['golongan_darah'] !== 'Semua'): ?>
                                    <span class="badge bg-danger-subtle text-danger">
                                        Gol. <?= $n['golongan_darah'] ?>
                                        <?= $n['rhesus']==='Semua'?'':($n['rhesus']==='Positif'?'+':'-') ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Semua Golongan</span>
                                    <?php endif; ?>

                                    <?php if ($n['nama_kegiatan']): ?>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($n['nama_kegiatan']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <div class="text-muted" style="font-size:0.75rem">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($n['nama_pembuat'] ?? '-') ?>
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-clock me-1"></i><?= format_waktu_singkat($n['created_at']) ?>
                                    <?php if ($n['expired_at']): ?>
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-hourglass me-1 <?= $is_expired?'text-danger':'' ?>"></i>
                                    <?= $is_expired?'<span class="text-danger">Expired</span>':('Exp: '.tanggal_indo(substr($n['expired_at'],0,10))) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-4">
                                <div class="d-flex gap-2">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="aksi_form" value="toggle">
                                        <input type="hidden" name="id_notif" value="<?= $n['id_notif'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $n['status']==='aktif'?'secondary':'success' ?>">
                                            <i class="bi bi-<?= $n['status']==='aktif'?'pause-circle':'play-circle' ?>-fill me-1"></i>
                                            <?= $n['status']==='aktif'?'Nonaktifkan':'Aktifkan' ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline ms-auto">
                                        <input type="hidden" name="aksi_form" value="hapus">
                                        <input type="hidden" name="id_notif" value="<?= $n['id_notif'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus notifikasi ini?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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

<!-- Modal Tambah Notifikasi -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Kirim Notifikasi Darurat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Judul Notifikasi <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required
                                   placeholder="Contoh: BUTUH DONOR DARAH SEGERA - Golongan O+">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Pesan <span class="text-danger">*</span></label>
                            <textarea name="pesan" class="form-control" rows="3" required
                                placeholder="Contoh: RS SIDORAH membutuhkan donor darah golongan O+ segera untuk pasien darurat. Hubungi 08xxx atau datang langsung ke PMI."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tingkat Urgensi</label>
                            <select name="tingkat" class="form-select" id="selectTingkat" onchange="updatePreview()">
                                <option value="darurat">🚨 Darurat</option>
                                <option value="warning">⚠️ Peringatan</option>
                                <option value="info">ℹ️ Informasi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Golongan Darah</label>
                            <select name="golongan_darah" class="form-select">
                                <option value="Semua">Semua Golongan</option>
                                <?php foreach (['A','B','AB','O'] as $g): ?>
                                <option value="<?= $g ?>">Golongan <?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rhesus</label>
                            <select name="rhesus" class="form-select">
                                <option value="Semua">Semua</option>
                                <option value="Positif">Positif (+)</option>
                                <option value="Negatif">Negatif (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tautkan ke Kegiatan (opsional)</label>
                            <select name="id_kegiatan" class="form-select">
                                <option value="">— Tidak ada —</option>
                                <?php foreach ($kegiatan_list as $k): ?>
                                <option value="<?= $k['id_kegiatan'] ?>">
                                    <?= htmlspecialchars($k['nama_kegiatan']) ?>
                                    (<?= tanggal_indo($k['tanggal_kegiatan']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Berlaku Sampai (opsional)</label>
                            <input type="datetime-local" name="expired_at" class="form-control"
                                   min="<?= date('Y-m-d\TH:i') ?>">
                            <div class="form-text">Kosongkan = aktif sampai dinonaktifkan manual</div>
                        </div>

                        <!-- Preview -->
                        <div class="col-12">
                            <div class="small fw-semibold text-muted mb-2">Preview di portal pendonor:</div>
                            <div id="previewNotif" style="border-radius:12px;background:linear-gradient(135deg,#7b0d1e,#dc3545);color:white;padding:1rem 1.2rem;display:flex;gap:10px;align-items:flex-start">
                                <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                                <div>
                                    <div class="fw-bold" id="prevJudul">Judul notifikasi...</div>
                                    <div style="font-size:0.875rem;opacity:0.9" id="prevPesan">Isi pesan akan muncul di sini</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-megaphone-fill me-1"></i>Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
// Live preview
document.querySelector('[name="judul"]')?.addEventListener('input', updatePreview);
document.querySelector('[name="pesan"]')?.addEventListener('input', updatePreview);

function updatePreview() {
    const judul  = document.querySelector('[name="judul"]')?.value || 'Judul notifikasi...';
    const pesan  = document.querySelector('[name="pesan"]')?.value || 'Isi pesan akan muncul di sini';
    const tingkat = document.getElementById('selectTingkat')?.value;

    const warna = {
        'darurat': 'linear-gradient(135deg,#7b0d1e,#dc3545)',
        'warning': 'linear-gradient(135deg,#92400e,#d97706)',
        'info':    'linear-gradient(135deg,#1e40af,#3b82f6)'
    };

    document.getElementById('prevJudul').textContent = judul;
    document.getElementById('prevPesan').textContent = pesan;
    document.getElementById('previewNotif').style.background = warna[tingkat] || warna['darurat'];
}
</script>
</body>
</html>