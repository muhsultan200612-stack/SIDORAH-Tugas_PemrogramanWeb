<?php
/**
 * SIDORAH - Sistem Informasi Donor Darah
 * File: kelola_akun.php
 * Akses: Super Admin only
 */
require_once 'koneksi.php';
paksa_role(ROLE_SUPER_ADMIN, 'dashboard.php');

$pesan   = $_SESSION['pesan']   ?? null;
$tipe    = $_SESSION['tipe']    ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

// ── PROSES TAMBAH / EDIT / HAPUS / TOGGLE ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi_form = $_POST['aksi_form'] ?? '';

    // ── TAMBAH AKUN ──
    if ($aksi_form === 'tambah') {
        $nama    = bersihkan($koneksi, $_POST['nama_lengkap']);
        $email   = bersihkan($koneksi, $_POST['email']);
        $telp    = bersihkan($koneksi, $_POST['no_telepon']);
        $role    = bersihkan($koneksi, $_POST['role']);
        $pw      = trim($_POST['password']);
        $status  = 'aktif';

        // Validasi
        $cek = $koneksi->query("SELECT id_pengguna FROM users WHERE email='$email' LIMIT 1");
        if ($cek->num_rows > 0) {
            $_SESSION['pesan'] = 'Email sudah terdaftar.';
            $_SESSION['tipe']  = 'danger';
        } elseif (strlen($pw) < 6) {
            $_SESSION['pesan'] = 'Password minimal 6 karakter.';
            $_SESSION['tipe']  = 'danger';
        } else {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO users (nama_lengkap,email,password,no_telepon,role,status_akun) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('ssssss', $nama,$email,$hash,$telp,$role,$status);
            if ($stmt->execute()) {
                $id_baru = $koneksi->insert_id;
                catat_log($koneksi,'TAMBAH_AKUN','users',
                    "Super admin menambah akun baru: $nama ($email) role: $role",
                    null, ['id'=>$id_baru,'nama'=>$nama,'email'=>$email,'role'=>$role]);
                $_SESSION['pesan'] = "Akun <strong>$nama</strong> berhasil dibuat.";
                $_SESSION['tipe']  = 'success';
            } else {
                $_SESSION['pesan'] = 'Gagal membuat akun: ' . $stmt->error;
                $_SESSION['tipe']  = 'danger';
            }
            $stmt->close();
        }
        redirect('kelola_akun.php');
    }

    // ── EDIT AKUN ──
    if ($aksi_form === 'edit') {
        $id      = (int)$_POST['id_pengguna'];
        $nama    = bersihkan($koneksi, $_POST['nama_lengkap']);
        $email   = bersihkan($koneksi, $_POST['email']);
        $telp    = bersihkan($koneksi, $_POST['no_telepon']);
        $role    = bersihkan($koneksi, $_POST['role']);
        $pw      = trim($_POST['password']);

        // Ambil data lama untuk audit
        $lama = $koneksi->query("SELECT * FROM users WHERE id_pengguna=$id")->fetch_assoc();

        if (!empty($pw) && strlen($pw) < 6) {
            $_SESSION['pesan'] = 'Password minimal 6 karakter.';
            $_SESSION['tipe']  = 'danger';
        } else {
            if (!empty($pw)) {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $stmt = $koneksi->prepare("UPDATE users SET nama_lengkap=?,email=?,no_telepon=?,role=?,password=? WHERE id_pengguna=?");
                $stmt->bind_param('sssssi',$nama,$email,$telp,$role,$hash,$id);
            } else {
                $stmt = $koneksi->prepare("UPDATE users SET nama_lengkap=?,email=?,no_telepon=?,role=? WHERE id_pengguna=?");
                $stmt->bind_param('ssssi',$nama,$email,$telp,$role,$id);
            }
            if ($stmt->execute()) {
                catat_log($koneksi,'EDIT_AKUN','users',
                    "Super admin mengedit akun ID $id: $nama ($email)",
                    ['nama'=>$lama['nama_lengkap'],'email'=>$lama['email'],'role'=>$lama['role']],
                    ['nama'=>$nama,'email'=>$email,'role'=>$role]);
                $_SESSION['pesan'] = "Akun <strong>$nama</strong> berhasil diperbarui.";
                $_SESSION['tipe']  = 'success';
            } else {
                $_SESSION['pesan'] = 'Gagal edit akun.';
                $_SESSION['tipe']  = 'danger';
            }
            $stmt->close();
        }
        redirect('kelola_akun.php');
    }

    // ── TOGGLE STATUS AKUN ──
    if ($aksi_form === 'toggle_status') {
        $id = (int)$_POST['id_pengguna'];
        $user = $koneksi->query("SELECT * FROM users WHERE id_pengguna=$id")->fetch_assoc();

        // Super admin tidak bisa menonaktifkan diri sendiri
        if ($id === (int)$_SESSION['id_pengguna']) {
            $_SESSION['pesan'] = 'Tidak dapat mengubah status akun sendiri.';
            $_SESSION['tipe']  = 'warning';
            redirect('kelola_akun.php');
        }

        $status_baru = $user['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
        $koneksi->query("UPDATE users SET status_akun='$status_baru' WHERE id_pengguna=$id");
        catat_log($koneksi,'TOGGLE_STATUS_AKUN','users',
            "Status akun {$user['nama_lengkap']} diubah: {$user['status_akun']} → $status_baru",
            ['status'=>$user['status_akun']], ['status'=>$status_baru]);
        $_SESSION['pesan'] = "Status akun <strong>{$user['nama_lengkap']}</strong> diubah menjadi $status_baru.";
        $_SESSION['tipe']  = 'info';
        redirect('kelola_akun.php');
    }

    // ── RESET PASSWORD ──
    if ($aksi_form === 'reset_password') {
        $id      = (int)$_POST['id_pengguna'];
        $pw_baru = trim($_POST['password_baru']);
        if (strlen($pw_baru) < 6) {
            $_SESSION['pesan'] = 'Password minimal 6 karakter.';
            $_SESSION['tipe']  = 'danger';
        } else {
            $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
            $user = $koneksi->query("SELECT nama_lengkap FROM users WHERE id_pengguna=$id")->fetch_assoc();
            $koneksi->query("UPDATE users SET password='$hash' WHERE id_pengguna=$id");
            catat_log($koneksi,'RESET_PASSWORD','users',
                "Super admin mereset password akun: {$user['nama_lengkap']} (ID: $id)");
            $_SESSION['pesan'] = "Password akun <strong>{$user['nama_lengkap']}</strong> berhasil direset.";
            $_SESSION['tipe']  = 'success';
        }
        redirect('kelola_akun.php');
    }

    // ── HAPUS AKUN ──
    if ($aksi_form === 'hapus') {
        $id = (int)$_POST['id_pengguna'];
        if ($id === (int)$_SESSION['id_pengguna']) {
            $_SESSION['pesan'] = 'Tidak dapat menghapus akun sendiri.';
            $_SESSION['tipe']  = 'danger';
            redirect('kelola_akun.php');
        }
        $user = $koneksi->query("SELECT * FROM users WHERE id_pengguna=$id")->fetch_assoc();
        // Cegah hapus super admin lain (opsional — bisa diaktifkan)
        if ($user['role'] === 'super_admin') {
            $_SESSION['pesan'] = 'Akun Super Admin tidak dapat dihapus.';
            $_SESSION['tipe']  = 'danger';
            redirect('kelola_akun.php');
        }
        $koneksi->query("DELETE FROM users WHERE id_pengguna=$id");
        catat_log($koneksi,'HAPUS_AKUN','users',
            "Super admin menghapus akun: {$user['nama_lengkap']} ({$user['email']}) role: {$user['role']}",
            $user, null);
        $_SESSION['pesan'] = "Akun <strong>{$user['nama_lengkap']}</strong> berhasil dihapus.";
        $_SESSION['tipe']  = 'success';
        redirect('kelola_akun.php');
    }
}

// ── AMBIL DATA ────────────────────────────────────────────────
$search = bersihkan($koneksi, $_GET['cari'] ?? '');
$filter_role = bersihkan($koneksi, $_GET['role'] ?? '');

$where = "WHERE 1=1";
if ($search)      $where .= " AND (nama_lengkap LIKE '%$search%' OR email LIKE '%$search%')";
if ($filter_role) $where .= " AND role='$filter_role'";

$users = $koneksi->query("SELECT * FROM users $where ORDER BY role, nama_lengkap ASC");
$total = $koneksi->query("SELECT COUNT(*) as n FROM users $where")->fetch_assoc()['n'];

// Statistik per role
$stat = $koneksi->query("SELECT role, COUNT(*) as jumlah FROM users GROUP BY role")->fetch_all(MYSQLI_ASSOC);
$stat_map = array_column($stat, 'jumlah', 'role');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kelola Akun — SIDORAH</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .role-super_admin { border-left: 4px solid #212529; }
        .role-admin        { border-left: 4px solid #dc3545; }
        .role-petugas_medis{ border-left: 4px solid #0d6efd; }
        .role-manajemen    { border-left: 4px solid #ffc107; }
        .avatar-circle {
            width:38px; height:38px; border-radius:50%;
            display:inline-flex; align-items:center; justify-content:center;
            font-weight:700; font-size:0.85rem; color:white; flex-shrink:0;
        }
        .bg-superadmin { background: #212529; }
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
                        <h1 class="h3 mb-0"><i class="bi bi-people-fill text-danger me-2"></i>Kelola Akun Pengguna</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Kelola Akun</li>
                        </ol>
                    </div>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Akun
                    </button>
                </div>

                <!-- Alert -->
                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show">
                    <i class="bi bi-info-circle me-1"></i> <?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <?php
                    $roles_info = [
                        'super_admin'   => ['Super Admin',    'bg-dark',              'bi-stars'],
                        'admin'         => ['Admin',          'bg-danger',            'bi-shield-fill'],
                        'petugas_medis' => ['Petugas Medis',  'bg-primary',           'bi-heart-pulse-fill'],
                        'manajemen'     => ['Manajemen',      'bg-warning text-dark', 'bi-briefcase-fill'],
                        'pendonor'      => ['Pendonor',       'bg-success',           'bi-droplet-fill'],
                    ];
                    foreach ($roles_info as $r => $info): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card text-center h-100 border-0 shadow-sm">
                            <div class="card-body py-3">
                                <div class="badge <?= $info[1] ?> fs-5 rounded-circle p-2 mb-2">
                                    <i class="bi <?= $info[2] ?>"></i>
                                </div>
                                <div class="h4 mb-0 fw-bold"><?= $stat_map[$r] ?? 0 ?></div>
                                <div class="small text-muted"><?= $info[0] ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filter & Search -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <form method="GET" class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="cari" class="form-control"
                                           placeholder="Cari nama atau email..."
                                           value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-select">
                                    <option value="">Semua Role</option>
                                    <?php foreach ($roles_info as $r => $info): ?>
                                    <option value="<?= $r ?>" <?= $filter_role===$r?'selected':'' ?>><?= $info[0] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="kelola_akun.php" class="btn btn-outline-secondary">Reset</a>
                            </div>
                            <div class="col-auto ms-auto text-muted small">
                                <i class="bi bi-people"></i> <?= $total ?> akun ditemukan
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
                                        <th class="ps-4">Pengguna</th>
                                        <th>Role</th>
                                        <th>No. Telepon</th>
                                        <th>Status</th>
                                        <th>Terakhir Login</th>
                                        <th>Dibuat</th>
                                        <th class="text-center pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($users->num_rows === 0): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-person-x fs-2 d-block mb-2"></i>Tidak ada akun ditemukan.
                                    </td></tr>
                                <?php else:
                                    while ($u = $users->fetch_assoc()):
                                    $initial  = strtoupper(substr($u['nama_lengkap'], 0, 1));
                                    $bg_color = match($u['role']) {
                                        'super_admin'   => 'bg-superadmin',
                                        'admin'         => 'bg-danger',
                                        'petugas_medis' => 'bg-primary',
                                        'manajemen'     => 'bg-warning',
                                        default         => 'bg-success'
                                    };
                                    $is_self = $u['id_pengguna'] == $_SESSION['id_pengguna'];
                                ?>
                                    <tr class="role-<?= $u['role'] ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-circle <?= $bg_color ?>"><?= $initial ?></div>
                                                <div>
                                                    <div class="fw-semibold">
                                                        <?= htmlspecialchars($u['nama_lengkap']) ?>
                                                        <?php if ($is_self): ?>
                                                        <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Anda</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="small text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= badge_role($u['role']) ?></td>
                                        <td><?= htmlspecialchars($u['no_telepon'] ?: '-') ?></td>
                                        <td><?= badge_status_akun($u['status_akun']) ?></td>
                                        <td class="small text-muted">
                                            <?= isset($u['last_login']) && $u['last_login'] ? format_waktu_singkat($u['last_login']) : '<span class="text-muted">Belum pernah</span>' ?>
                                        </td>
                                        <td class="small text-muted"><?= tanggal_indo(substr($u['created_at'],0,10)) ?></td>
                                        <td class="text-center pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Edit -->
                                                <button class="btn btn-outline-primary" title="Edit"
                                                    onclick="bukaEdit(<?= htmlspecialchars(json_encode($u)) ?>)">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <!-- Reset Password -->
                                                <button class="btn btn-outline-warning" title="Reset Password"
                                                    onclick="bukaResetPw(<?= $u['id_pengguna'] ?>, '<?= addslashes($u['nama_lengkap']) ?>')">
                                                    <i class="bi bi-key-fill"></i>
                                                </button>
                                                <?php if (!$is_self): ?>
                                                <!-- Toggle Status -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="aksi_form" value="toggle_status">
                                                    <input type="hidden" name="id_pengguna" value="<?= $u['id_pengguna'] ?>">
                                                    <button type="submit" class="btn btn-outline-<?= $u['status_akun']==='aktif'?'secondary':'success' ?>"
                                                        title="<?= $u['status_akun']==='aktif'?'Nonaktifkan':'Aktifkan' ?>">
                                                        <i class="bi bi-<?= $u['status_akun']==='aktif'?'toggle-on':'toggle-off' ?>"></i>
                                                    </button>
                                                </form>
                                                <!-- Hapus -->
                                                <?php if ($u['role'] !== 'super_admin'): ?>
                                                <button class="btn btn-outline-danger" title="Hapus"
                                                    onclick="konfirmasiHapus(<?= $u['id_pengguna'] ?>, '<?= addslashes($u['nama_lengkap']) ?>')">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
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
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">SIDORAH &copy; <?= date('Y') ?></div>
                    <div class="text-muted">Logged in as: <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- ── Modal Tambah Akun ── -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Tambah Akun Baru</h5>
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
                            <input type="email" name="email" class="form-control" required placeholder="email@sidorah.id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="no_telepon" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="petugas_medis">Petugas Medis</option>
                                <option value="manajemen">Manajemen</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Untuk menambah <strong>Pendonor</strong>, gunakan menu
                                <a href="pendonor.php" class="fw-bold">Data Pendonor → Tambah Pendonor</a>
                                agar data medis (golongan darah, dll) ikut tersimpan.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min. 6 karakter">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-save me-1"></i>Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Edit Akun ── -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="edit">
                <input type="hidden" name="id_pengguna" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="no_telepon" id="edit_telp" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="petugas_medis">Petugas Medis</option>
                                <option value="manajemen">Manajemen</option>
                                <option value="pendonor">Pendonor (hanya edit, tambah via Data Pendonor)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                            <input type="password" name="password" class="form-control" minlength="6" placeholder="Kosongkan jika tidak diubah">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Reset Password ── -->
<div class="modal fade" id="modalResetPw" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-key-fill me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="reset_password">
                <input type="hidden" name="id_pengguna" id="reset_id">
                <div class="modal-body">
                    <p>Reset password untuk akun: <strong id="reset_nama"></strong></p>
                    <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="password_baru" class="form-control" required minlength="6" placeholder="Min. 6 karakter">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Hapus ── -->
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Hapus Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="hapus">
                <input type="hidden" name="id_pengguna" id="hapus_id">
                <div class="modal-body">
                    Yakin ingin menghapus akun <strong id="hapus_nama"></strong>? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function bukaEdit(u) {
    document.getElementById('edit_id').value    = u.id_pengguna;
    document.getElementById('edit_nama').value  = u.nama_lengkap;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_telp').value  = u.no_telepon || '';
    document.getElementById('edit_role').value  = u.role;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
function bukaResetPw(id, nama) {
    document.getElementById('reset_id').value   = id;
    document.getElementById('reset_nama').textContent = nama;
    new bootstrap.Modal(document.getElementById('modalResetPw')).show();
}
function konfirmasiHapus(id, nama) {
    document.getElementById('hapus_id').value   = id;
    document.getElementById('hapus_nama').textContent = nama;
    new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
</script>
</body>
</html>