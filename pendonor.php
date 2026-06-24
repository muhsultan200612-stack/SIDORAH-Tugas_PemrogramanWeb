<?php
/**
 * SIDORAH - pendonor.php
 * Kelola data pendonor
 */
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS]);

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

// Ambil password default dari pengaturan
$res_pw     = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='password_default_pendonor' LIMIT 1");
$pw_default = ($res_pw && $res_pw->num_rows > 0) ? $res_pw->fetch_assoc()['nilai'] : 'Sidorah@2026';

// ── PROSES ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hanya admin & super admin yang boleh POST
    if (!cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])) {
        $_SESSION['pesan'] = "Anda tidak memiliki akses untuk melakukan aksi ini.";
        $_SESSION['tipe']  = 'danger';
        redirect('pendonor.php');
    }
    $aksi = $_POST['aksi_form'] ?? '';

    // TAMBAH PENDONOR
    if ($aksi === 'tambah') {
        $nama     = bersihkan($koneksi, $_POST['nama_lengkap']);
        $email    = bersihkan($koneksi, $_POST['email']);
        $telp     = bersihkan($koneksi, $_POST['no_telepon']);
        $nik      = bersihkan($koneksi, $_POST['nik'] ?? '');
        $tgl_lahir= bersihkan($koneksi, $_POST['tanggal_lahir']);
        $tmp_lahir= bersihkan($koneksi, $_POST['tempat_lahir'] ?? '');
        $jk       = bersihkan($koneksi, $_POST['jenis_kelamin']);
        $pekerjaan= bersihkan($koneksi, $_POST['pekerjaan'] ?? '');
        $goldar   = bersihkan($koneksi, $_POST['golongan_darah']);
        $rhesus   = bersihkan($koneksi, $_POST['rhesus']);
        $alamat   = bersihkan($koneksi, $_POST['alamat']);
        $bb       = (float)$_POST['berat_badan'];
        $riwayat  = bersihkan($koneksi, $_POST['riwayat_penyakit']);

        // Validasi
        if (empty($tgl_lahir)) {
            $_SESSION['pesan'] = 'Tanggal lahir wajib diisi.';
            $_SESSION['tipe']  = 'danger';
            redirect('pendonor.php');
        }
        if ($bb < 45) {
            $_SESSION['pesan'] = 'Berat badan minimal 45 kg untuk bisa donor.';
            $_SESSION['tipe']  = 'danger';
            redirect('pendonor.php');
        }
        // Validasi usia minimal 17 tahun
        $usia = (new DateTime($tgl_lahir))->diff(new DateTime())->y;
        if ($usia < 17 || $usia > 65) {
            $_SESSION['pesan'] = 'Usia pendonor harus antara 17-65 tahun.';
            $_SESSION['tipe']  = 'danger';
            redirect('pendonor.php');
        }
        $pw       = password_hash($pw_default, PASSWORD_DEFAULT);

        // Cek email duplikat
        $cek = $koneksi->query("SELECT id_pengguna FROM users WHERE email='$email' LIMIT 1");
        if ($cek->num_rows > 0) {
            $_SESSION['pesan'] = 'Email sudah terdaftar.';
            $_SESSION['tipe']  = 'danger';
        } else {
            $koneksi->begin_transaction();
            try {
                // Insert ke users
                $stmt = $koneksi->prepare("INSERT INTO users (nama_lengkap,email,password,no_telepon,role,status_akun) VALUES (?,?,?,?,'pendonor','aktif')");
                $stmt->bind_param('ssss', $nama, $email, $pw, $telp);
                $stmt->execute();
                $id_user = $koneksi->insert_id;
                $stmt->close();

                // Insert ke pendonor
                $stmt2 = $koneksi->prepare("INSERT INTO pendonor (id_pengguna,nik,tanggal_lahir,tempat_lahir,jenis_kelamin,pekerjaan,golongan_darah,rhesus,alamat,berat_badan,riwayat_penyakit,status_aktif) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)");
                $stmt2->bind_param('issssssssds', $id_user,$nik,$tgl_lahir,$tmp_lahir,$jk,$pekerjaan,$goldar,$rhesus,$alamat,$bb,$riwayat);
                $stmt2->execute();
                $stmt2->close();

                $koneksi->commit();
                catat_log($koneksi,'TAMBAH_PENDONOR','pendonor',"Menambah pendonor baru: $nama ($email)",null,['nama'=>$nama,'email'=>$email,'goldar'=>"$goldar $rhesus"]);
                $_SESSION['pesan'] = "Pendonor <strong>$nama</strong> berhasil ditambahkan. Password default: <code>$pw_default</code>";
                $_SESSION['tipe']  = 'success';
            } catch (Exception $e) {
                $koneksi->rollback();
                $_SESSION['pesan'] = 'Gagal menambah pendonor: ' . $e->getMessage();
                $_SESSION['tipe']  = 'danger';
            }
        }
        redirect('pendonor.php');
    }

    // EDIT PENDONOR
    if ($aksi === 'edit') {
        $id_pendonor = (int)$_POST['id_pendonor'];
        $id_pengguna = (int)$_POST['id_pengguna'];
        $nama      = bersihkan($koneksi, $_POST['nama_lengkap']);
        $telp      = bersihkan($koneksi, $_POST['no_telepon']);
        $nik       = bersihkan($koneksi, $_POST['nik'] ?? '');
        $tgl       = bersihkan($koneksi, $_POST['tanggal_lahir']);
        $tmp_lahir = bersihkan($koneksi, $_POST['tempat_lahir'] ?? '');
        $jk        = bersihkan($koneksi, $_POST['jenis_kelamin']);
        $pekerjaan = bersihkan($koneksi, $_POST['pekerjaan'] ?? '');
        $goldar    = bersihkan($koneksi, $_POST['golongan_darah']);
        $rhesus    = bersihkan($koneksi, $_POST['rhesus']);
        $alamat    = bersihkan($koneksi, $_POST['alamat']);
        $bb        = (float)$_POST['berat_badan'];
        $riwayat   = bersihkan($koneksi, $_POST['riwayat_penyakit']);
        $status    = (int)$_POST['status_aktif'];

        $lama = $koneksi->query("SELECT u.nama_lengkap, p.golongan_darah FROM users u JOIN pendonor p ON u.id_pengguna=p.id_pengguna WHERE p.id_pendonor=$id_pendonor")->fetch_assoc();

        $koneksi->query("UPDATE users SET nama_lengkap='$nama', no_telepon='$telp' WHERE id_pengguna=$id_pengguna");
        $stmt = $koneksi->prepare("UPDATE pendonor SET nik=?,tanggal_lahir=?,tempat_lahir=?,jenis_kelamin=?,pekerjaan=?,golongan_darah=?,rhesus=?,alamat=?,berat_badan=?,riwayat_penyakit=?,status_aktif=? WHERE id_pendonor=?");
        $stmt->bind_param('ssssssssdsii', $nik,$tgl,$tmp_lahir,$jk,$pekerjaan,$goldar,$rhesus,$alamat,$bb,$riwayat,$status,$id_pendonor);
        $stmt->execute();
        $stmt->close();

        catat_log($koneksi,'EDIT_PENDONOR','pendonor',"Edit data pendonor: $nama (ID: $id_pendonor)",['nama'=>$lama['nama_lengkap']],['nama'=>$nama,'goldar'=>"$goldar $rhesus"]);
        $_SESSION['pesan'] = "Data pendonor <strong>$nama</strong> berhasil diperbarui.";
        $_SESSION['tipe']  = 'success';
        redirect('pendonor.php');
    }

    // TOGGLE STATUS
    if ($aksi === 'toggle') {
        $id = (int)$_POST['id_pendonor'];
        $p  = $koneksi->query("SELECT p.status_aktif, u.nama_lengkap FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna WHERE p.id_pendonor=$id")->fetch_assoc();
        $baru = $p['status_aktif'] ? 0 : 1;
        $koneksi->query("UPDATE pendonor SET status_aktif=$baru WHERE id_pendonor=$id");
        $label = $baru ? 'diaktifkan' : 'dinonaktifkan';
        catat_log($koneksi,'TOGGLE_PENDONOR','pendonor',"{$p['nama_lengkap']} $label");
        $_SESSION['pesan'] = "Pendonor <strong>{$p['nama_lengkap']}</strong> berhasil $label.";
        $_SESSION['tipe']  = 'info';
        redirect('pendonor.php');
    }
}

// ── AMBIL DATA ────────────────────────────────────────────────
$cari        = bersihkan($koneksi, $_GET['cari']   ?? '');
$filter_goldar = bersihkan($koneksi, $_GET['goldar'] ?? '');
$filter_status = bersihkan($koneksi, $_GET['status'] ?? '');

$where = "WHERE 1=1";
if ($cari)          $where .= " AND (u.nama_lengkap LIKE '%$cari%' OR u.email LIKE '%$cari%')";
if ($filter_goldar) $where .= " AND p.golongan_darah='$filter_goldar'";
if ($filter_status !== '') $where .= " AND p.status_aktif=$filter_status";

$per_hal = 15;
$hal     = max(1, (int)($_GET['hal'] ?? 1));
$offset  = ($hal - 1) * $per_hal;

$total = $koneksi->query("SELECT COUNT(*) as n FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna $where")->fetch_assoc()['n'];
$total_hal = ceil($total / $per_hal);

$pendonor = $koneksi->query("
    SELECT p.*, u.nama_lengkap, u.email, u.no_telepon, u.status_akun, u.id_pengguna
    FROM pendonor p
    JOIN users u ON p.id_pengguna = u.id_pengguna
    $where
    ORDER BY p.id_pendonor DESC
    LIMIT $per_hal OFFSET $offset
");

// Ambil password default dari pengaturan
$res_pw = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='password_default_pendonor' LIMIT 1");
$pw_default = ($res_pw && $res_pw->num_rows > 0) ? $res_pw->fetch_assoc()['nilai'] : 'Sidorah@2026';

// Stat ringkasan
$stat_goldar = $koneksi->query("SELECT golongan_darah, COUNT(*) as n FROM pendonor WHERE status_aktif=1 GROUP BY golongan_darah")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pendonor — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0"><i class="bi bi-droplet-fill text-danger me-2"></i>Data Pendonor</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Pendonor</li>
                        </ol>
                    </div>
                    <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pendonor
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Alert -->
                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show">
                    <?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Stat goldar -->
                <div class="row g-3 mb-4">
                    <?php foreach (['A','B','AB','O'] as $g):
                        $jml = 0;
                        foreach ($stat_goldar as $sg) if ($sg['golongan_darah']===$g) $jml=$sg['n'];
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h2 fw-black text-danger mb-0"><?= $g ?></div>
                            <div class="h4 fw-bold mb-0"><?= $jml ?></div>
                            <div class="small text-muted">pendonor aktif</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <form method="GET" class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="cari" class="form-control"
                                           placeholder="Cari nama atau email..."
                                           value="<?= htmlspecialchars($cari) ?>">
                                </div>
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
                                    <option value="1" <?= $filter_status==='1'?'selected':'' ?>>Aktif</option>
                                    <option value="0" <?= $filter_status==='0'?'selected':'' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="pendonor.php" class="btn btn-outline-secondary">Reset</a>
                            </div>
                            <div class="col-auto ms-auto text-muted small">
                                <?= number_format($total) ?> pendonor
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Pendonor</th>
                                        <th>Gol. Darah</th>
                                        <th>Usia</th>
                                        <th>Berat</th>
                                        <th>Total Donor</th>
                                        <th>Donor Terakhir</th>
                                        <th>Status</th>
                                        <th class="text-center pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($pendonor->num_rows === 0): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-2 d-block mb-2"></i>Tidak ada data pendonor
                                </td></tr>
                                <?php else: $no = $offset + 1;
                                    while ($p = $pendonor->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 text-muted small"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:36px;height:36px;border-radius:50%;background:#dc3545;
                                                        display:flex;align-items:center;justify-content:center;
                                                        color:white;font-weight:700;font-size:0.85rem;flex-shrink:0">
                                                <?= strtoupper(substr($p['nama_lengkap'],0,1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($p['nama_lengkap']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($p['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fw-bold fs-6">
                                            <?= $p['golongan_darah'] ?><?= $p['rhesus']==='Positif'?'+':'-' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!$p['tanggal_lahir']): ?>
                                        <span class="badge bg-warning text-dark">Belum diisi</span>
                                        <?php else:
                                            $usia_th = (new DateTime($p['tanggal_lahir']))->diff(new DateTime())->y;
                                        ?>
                                        <?= $usia_th ?> tahun
                                        <?php if ($usia_th < 17 || $usia_th > 65): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Usia di luar batas donor (17-65 tahun)"></i>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$p['berat_badan'] || $p['berat_badan'] == 0): ?>
                                        <span class="badge bg-warning text-dark">Belum diisi</span>
                                        <?php else: ?>
                                        <?= number_format($p['berat_badan'], 1) ?> kg
                                        <?php if ($p['berat_badan'] < 45): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Berat di bawah minimum 45kg"></i>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">
                                            <?= $p['total_donor'] ?> kali
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= $p['donor_terakhir'] ? tanggal_indo($p['donor_terakhir']) : 'Belum pernah' ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p['status_aktif']?'bg-success':'bg-secondary' ?>">
                                            <?= $p['status_aktif']?'Aktif':'Nonaktif' ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                                            <button class="btn btn-outline-primary"
                                                onclick='bukaEdit(<?= json_encode($p) ?>)'>
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <?php endif; ?>
                                            <a href="riwayat_donor.php?id=<?= $p['id_pendonor'] ?>"
                                               class="btn btn-outline-info" title="Riwayat">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                            <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="aksi_form" value="toggle">
                                                <input type="hidden" name="id_pendonor" value="<?= $p['id_pendonor'] ?>">
                                                <button type="submit"
                                                    class="btn btn-outline-<?= $p['status_aktif']?'secondary':'success' ?>"
                                                    title="<?= $p['status_aktif']?'Nonaktifkan':'Aktifkan' ?>">
                                                    <i class="bi bi-<?= $p['status_aktif']?'toggle-on':'toggle-off' ?>"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_hal > 1): ?>
                        <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                            <div class="small text-muted">Halaman <?= $hal ?> dari <?= $total_hal ?></div>
                            <nav><ul class="pagination pagination-sm mb-0">
                                <?php $q = $_GET; unset($q['hal']); $qs = http_build_query($q);
                                for ($i = max(1,$hal-3); $i <= min($total_hal,$hal+3); $i++): ?>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Tambah Pendonor Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required placeholder="Nama lengkap">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="email@gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="no_telepon" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK</label>
                            <input type="text" name="nik" class="form-control" placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" placeholder="Contoh: Makassar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_lahir" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
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
                            <label class="form-label fw-semibold">Berat Badan (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="berat_badan" class="form-control" placeholder="Min. 45 kg" step="0.1" min="45" max="150" required>
                            <div class="form-text text-muted">Minimal 45 kg untuk syarat donor</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control" placeholder="Contoh: Mahasiswa, PNS">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Riwayat Penyakit</label>
                            <textarea name="riwayat_penyakit" class="form-control" rows="2" placeholder="Kosongkan jika tidak ada"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Password default pendonor: <strong><?= htmlspecialchars($pw_default) ?></strong>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Data Pendonor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="edit">
                <input type="hidden" name="id_pendonor" id="e_id">
                <input type="hidden" name="id_pengguna" id="e_uid">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="e_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="no_telepon" id="e_telp" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK</label>
                            <input type="text" name="nik" id="e_nik" class="form-control" placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="e_tmp_lahir" class="form-control" placeholder="Contoh: Makassar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="e_tgl" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Berat Badan (kg)</label>
                            <input type="number" name="berat_badan" id="e_bb" class="form-control" step="0.1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pekerjaan</label>
                            <input type="text" name="pekerjaan" id="e_pekerjaan" class="form-control" placeholder="Contoh: Mahasiswa, PNS">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="e_jk" class="form-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Golongan Darah</label>
                            <select name="golongan_darah" id="e_goldar" class="form-select">
                                <?php foreach (['A','B','AB','O'] as $g): ?>
                                <option value="<?= $g ?>"><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rhesus</label>
                            <select name="rhesus" id="e_rhesus" class="form-select">
                                <option value="Positif">Positif (+)</option>
                                <option value="Negatif">Negatif (-)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status_aktif" id="e_status" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" id="e_alamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Riwayat Penyakit</label>
                            <textarea name="riwayat_penyakit" id="e_riwayat" class="form-control" rows="2"></textarea>
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
function bukaEdit(p) {
    document.getElementById('e_id').value          = p.id_pendonor;
    document.getElementById('e_uid').value         = p.id_pengguna;
    document.getElementById('e_nama').value        = p.nama_lengkap;
    document.getElementById('e_telp').value        = p.no_telepon || '';
    document.getElementById('e_nik').value         = p.nik || '';
    document.getElementById('e_tmp_lahir').value   = p.tempat_lahir || '';
    document.getElementById('e_tgl').value         = p.tanggal_lahir || '';
    document.getElementById('e_bb').value          = p.berat_badan || '';
    document.getElementById('e_pekerjaan').value   = p.pekerjaan || '';
    document.getElementById('e_jk').value          = p.jenis_kelamin || 'L';
    document.getElementById('e_goldar').value      = p.golongan_darah || 'A';
    document.getElementById('e_rhesus').value      = p.rhesus || 'Positif';
    document.getElementById('e_status').value      = p.status_aktif;
    document.getElementById('e_alamat').value      = p.alamat || '';
    document.getElementById('e_riwayat').value     = p.riwayat_penyakit || '';
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>