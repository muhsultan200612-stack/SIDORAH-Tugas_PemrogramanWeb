<?php
/**
 * SIDORAH - riwayat_donor.php
 * Riwayat & rekam medis donor
 */
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS]);

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

// Filter pendonor spesifik dari halaman pendonor
$filter_pendonor = (int)($_GET['id'] ?? 0);
$info_pendonor   = null;
if ($filter_pendonor) {
    $info_pendonor = $koneksi->query("
        SELECT u.nama_lengkap, p.golongan_darah, p.rhesus, p.total_donor
        FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna
        WHERE p.id_pendonor=$filter_pendonor
    ")->fetch_assoc();
}

// ── PROSES ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hanya petugas medis yang boleh catat donor
    if (!cekRole([ROLE_PETUGAS_MEDIS, ROLE_SUPER_ADMIN])) {
        $_SESSION['pesan'] = "Pencatatan donor hanya bisa dilakukan oleh Petugas Medis.";
        $_SESSION['tipe']  = 'danger';
        redirect('riwayat_donor.php');
    }
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'tambah') {
        $id_pendonor  = (int)$_POST['id_pendonor'];
        $id_pendaft   = $_POST['id_pendaftaran'] ? (int)$_POST['id_pendaftaran'] : null;
        $tgl_donor    = bersihkan($koneksi, $_POST['tanggal_donor']);
        $volume       = (int)($_POST['volume_darah_ml'] ?? 450);
        $hasil        = bersihkan($koneksi, $_POST['hasil_pemeriksaan']);
        $tekanan      = bersihkan($koneksi, $_POST['tekanan_darah']);
        $hb           = (float)$_POST['hemoglobin'];
        $catatan      = bersihkan($koneksi, $_POST['catatan_medis']);
        $id_petugas   = $_SESSION['id_pengguna'];

        $stmt = $koneksi->prepare("INSERT INTO riwayat_donor (id_pendaftaran,id_pendonor,tanggal_donor,volume_darah_ml,hasil_pemeriksaan,tekanan_darah,hemoglobin,catatan_medis,id_petugas_medis) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iisissdsi', $id_pendaft,$id_pendonor,$tgl_donor,$volume,$hasil,$tekanan,$hb,$catatan,$id_petugas);

        if ($stmt->execute()) {
            // Update total_donor dan donor_terakhir jika layak
            if ($hasil === 'layak') {
                $koneksi->query("UPDATE pendonor SET total_donor=total_donor+1, donor_terakhir='$tgl_donor' WHERE id_pendonor=$id_pendonor");
            }
            // Buat batch stok baru jika hasil layak (Opsi B)
            // Setiap donor = 1 batch baru, kadaluarsa WB = 35 hari
            if ($hasil === 'layak') {
                $p        = $koneksi->query("SELECT golongan_darah, rhesus FROM pendonor WHERE id_pendonor=$id_pendonor")->fetch_assoc();
                $goldar   = $p['golongan_darah'];
                $rhesus_p = $p['rhesus'];
                $tgl_exp  = date('Y-m-d', strtotime($tgl_donor . ' +35 days'));
                $stmt_stok = $koneksi->prepare("INSERT INTO stok_darah (golongan_darah, rhesus, jumlah_kantong, jenis_darah, sumber_stok, tanggal_masuk, tanggal_kadaluarsa, status_stok) VALUES (?, ?, 1, 'WB', 'donor', ?, ?, 'tersedia')");
                $stmt_stok->bind_param('ssss', $goldar, $rhesus_p, $tgl_donor, $tgl_exp);
                $stmt_stok->execute();
                $stmt_stok->close();
            }
            $nama_p = $koneksi->query("SELECT u.nama_lengkap FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna WHERE p.id_pendonor=$id_pendonor")->fetch_assoc()['nama_lengkap'];
            catat_log($koneksi,'TAMBAH_RIWAYAT_DONOR','riwayat_donor',"Rekam donor $nama_p: $hasil, Hb=$hb, Tekanan=$tekanan");
            $_SESSION['pesan'] = "Riwayat donor berhasil dicatat.";
            $_SESSION['tipe']  = 'success';
        }
        $stmt->close();
        redirect('riwayat_donor.php' . ($filter_pendonor ? "?id=$filter_pendonor" : ''));
    }
}

// ── DATA ──────────────────────────────────────────────────────
$filter_hasil = bersihkan($koneksi, $_GET['hasil'] ?? '');
$cari         = bersihkan($koneksi, $_GET['cari']  ?? '');

$where = "WHERE 1=1";
if ($filter_pendonor) $where .= " AND rd.id_pendonor=$filter_pendonor";
if ($filter_hasil)    $where .= " AND rd.hasil_pemeriksaan='$filter_hasil'";
if ($cari)            $where .= " AND u.nama_lengkap LIKE '%$cari%'";

$per_hal = 15;
$hal     = max(1,(int)($_GET['hal'] ?? 1));
$offset  = ($hal-1)*$per_hal;

$total = $koneksi->query("
    SELECT COUNT(*) as n FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    JOIN users u ON p.id_pengguna=u.id_pengguna
    $where
")->fetch_assoc()['n'];
$total_hal = ceil($total/$per_hal);

$riwayat = $koneksi->query("
    SELECT rd.*,
           u.nama_lengkap,
           p.golongan_darah, p.rhesus,
           pt.nama_lengkap as nama_petugas
    FROM riwayat_donor rd
    JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
    JOIN users u ON p.id_pengguna=u.id_pengguna
    LEFT JOIN users pt ON rd.id_petugas_medis=pt.id_pengguna
    $where
    ORDER BY rd.tanggal_donor DESC, rd.id_riwayat DESC
    LIMIT $per_hal OFFSET $offset
");

// Stat
$stat_layak    = $koneksi->query("SELECT COUNT(*) as n FROM riwayat_donor WHERE hasil_pemeriksaan='layak'")->fetch_assoc()['n'];
$stat_tidak    = $koneksi->query("SELECT COUNT(*) as n FROM riwayat_donor WHERE hasil_pemeriksaan='tidak_layak'")->fetch_assoc()['n'];
$stat_ditunda  = $koneksi->query("SELECT COUNT(*) as n FROM riwayat_donor WHERE hasil_pemeriksaan='ditunda'")->fetch_assoc()['n'];
$stat_bulan    = $koneksi->query("SELECT COUNT(*) as n FROM riwayat_donor WHERE MONTH(tanggal_donor)=MONTH(CURDATE()) AND YEAR(tanggal_donor)=YEAR(CURDATE()) AND hasil_pemeriksaan='layak'")->fetch_assoc()['n'];

// Dropdown pendonor & pendaftaran untuk form tambah
$pendonor_list = $koneksi->query("
    SELECT p.id_pendonor, u.nama_lengkap, p.golongan_darah, p.rhesus,
           p.donor_terakhir,
           DATEDIFF(CURDATE(), p.donor_terakhir) as hari_sejak_donor,
           DATE_ADD(p.donor_terakhir, INTERVAL 90 DAY) as boleh_donor_lagi
    FROM pendonor p JOIN users u ON p.id_pengguna=u.id_pengguna
    WHERE p.status_aktif=1 ORDER BY u.nama_lengkap ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Donor — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .hasil-layak      { color:#065f46; font-weight:600; }
        .hasil-tidak_layak{ color:#991b1b; font-weight:600; }
        .hasil-ditunda    { color:#92400e; font-weight:600; }
        .hb-normal { color:#065f46; }
        .hb-low    { color:#991b1b; }
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
                        <h1 class="h3 mb-0"><i class="bi bi-clock-history text-danger me-2"></i>Riwayat Donor</h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <?php if ($filter_pendonor): ?>
                            <li class="breadcrumb-item"><a href="pendonor.php">Pendonor</a></li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active">Riwayat Donor</li>
                        </ol>
                    </div>
                    <?php if (cekRole([ROLE_PETUGAS_MEDIS, ROLE_SUPER_ADMIN])): ?>
                    <?php if ($filter_pendonor): ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-1"></i> Catat Donor
                    </button>
                    <?php else: ?>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-1"></i> Catat Donor
                    </button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Info pendonor spesifik -->
                <?php if ($info_pendonor): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;border-radius:50%;background:#dc3545;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:white;flex-shrink:0;">
                            <?= strtoupper(substr($info_pendonor['nama_lengkap'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fw-bold fs-5"><?= htmlspecialchars($info_pendonor['nama_lengkap']) ?></div>
                            <div class="text-muted small">
                                Gol. <strong><?= $info_pendonor['golongan_darah'] ?><?= $info_pendonor['rhesus']==='Positif'?'+':'-' ?></strong>
                                &nbsp;·&nbsp; Total donor: <strong><?= $info_pendonor['total_donor'] ?> kali</strong>
                            </div>
                        </div>
                        <a href="riwayat_donor.php" class="ms-auto btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Semua Pendonor
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe ?> alert-dismissible fade show">
                    <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Stat -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-danger mb-0"><?= number_format($stat_bulan) ?></div>
                            <div class="small text-muted">Donor Layak Bulan Ini</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-success mb-0"><?= number_format($stat_layak) ?></div>
                            <div class="small text-muted">Total Layak</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-warning mb-0"><?= number_format($stat_ditunda) ?></div>
                            <div class="small text-muted">Ditunda</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center py-3">
                            <div class="h3 fw-bold text-secondary mb-0"><?= number_format($stat_tidak) ?></div>
                            <div class="small text-muted">Tidak Layak</div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <form method="GET" class="row g-2 align-items-center">
                            <?php if ($filter_pendonor): ?>
                            <input type="hidden" name="id" value="<?= $filter_pendonor ?>">
                            <?php endif; ?>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="cari" class="form-control"
                                           placeholder="Cari nama pendonor..."
                                           value="<?= htmlspecialchars($cari) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="hasil" class="form-select">
                                    <option value="">Semua Hasil</option>
                                    <option value="layak"       <?= $filter_hasil==='layak'?'selected':'' ?>>Layak</option>
                                    <option value="tidak_layak" <?= $filter_hasil==='tidak_layak'?'selected':'' ?>>Tidak Layak</option>
                                    <option value="ditunda"     <?= $filter_hasil==='ditunda'?'selected':'' ?>>Ditunda</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="riwayat_donor.php<?= $filter_pendonor?"?id=$filter_pendonor":'' ?>" class="btn btn-outline-secondary">Reset</a>
                            </div>
                            <div class="col-auto ms-auto text-muted small"><?= number_format($total) ?> riwayat</div>
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
                                        <th>Tanggal Donor</th>
                                        <th>Volume</th>
                                        <th>Hb (g/dL)</th>
                                        <th>Tekanan Darah</th>
                                        <th>Hasil</th>
                                        <th>Petugas</th>
                                        <th class="pe-4">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($riwayat->num_rows === 0): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-data fs-2 d-block mb-2"></i>Belum ada riwayat donor
                                </td></tr>
                                <?php else:
                                    while ($r = $riwayat->fetch_assoc()):
                                    $sym = $r['rhesus']==='Positif'?'+':'-';
                                    $hb_class = $r['hemoglobin'] && $r['hemoglobin'] < 12.5 ? 'hb-low' : 'hb-normal';
                                    $hasil_icon = match($r['hasil_pemeriksaan']) {
                                        'layak'       => '✅',
                                        'tidak_layak' => '❌',
                                        'ditunda'     => '⏸️',
                                        default       => '—'
                                    };
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold"><?= htmlspecialchars($r['nama_lengkap']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fw-bold">
                                            <?= $r['golongan_darah'] ?><?= $sym ?>
                                        </span>
                                    </td>
                                    <td><?= tanggal_indo($r['tanggal_donor']) ?></td>
                                    <td><?= $r['volume_darah_ml'] ?> ml</td>
                                    <td class="<?= $hb_class ?> fw-semibold">
                                        <?= $r['hemoglobin'] ?? '-' ?>
                                        <?php if ($r['hemoglobin'] && $r['hemoglobin'] < 12.5): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Hb rendah"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['tekanan_darah'] ?: '-') ?></td>
                                    <td>
                                        <span class="hasil-<?= $r['hasil_pemeriksaan'] ?>">
                                            <?= $hasil_icon ?> <?= ucfirst(str_replace('_',' ',$r['hasil_pemeriksaan'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($r['nama_petugas'] ?? '-') ?></td>
                                    <td class="pe-4 text-muted" style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                                        title="<?= htmlspecialchars($r['catatan_medis'] ?? '') ?>">
                                        <?= htmlspecialchars($r['catatan_medis'] ?: '-') ?>
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

<!-- Modal Catat Donor -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-heart-pulse-fill me-2"></i>Catat Hasil Pemeriksaan Donor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi_form" value="tambah">
                <input type="hidden" name="id_pendaftaran" value="">
                <input type="hidden" name="volume_darah_ml" value="450">
                <?php if ($filter_pendonor): ?>
                <input type="hidden" name="id_pendonor" value="<?= $filter_pendonor ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <?php if (!$filter_pendonor): ?>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Pendonor <span class="text-danger">*</span></label>
                            <input type="text" id="cariPendonor" class="form-control mb-1"
                                   placeholder="Ketik nama pendonor..." autocomplete="off">
                            <select name="id_pendonor" id="selectPendonor" class="form-select" required size="4"
                                    style="border-radius:10px;" onchange="cekJedaDonor(this)">
                                <?php foreach ($pendonor_list as $p): ?>
                                <option value="<?= $p['id_pendonor'] ?>"
                                        data-nama="<?= strtolower(htmlspecialchars($p['nama_lengkap'])) ?>"
                                        data-donor-terakhir="<?= $p['donor_terakhir'] ?? '' ?>"
                                        data-hari="<?= $p['hari_sejak_donor'] ?? '' ?>"
                                        data-boleh="<?= $p['boleh_donor_lagi'] ?? '' ?>">
                                    <?= htmlspecialchars($p['nama_lengkap']) ?>
                                    (<?= $p['golongan_darah'] ?><?= $p['rhesus']==='Positif'?'+':'-' ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Warning jeda donor -->
                            <div id="warningJeda" class="mt-2" style="display:none"></div>
                            <input type="hidden" name="catatan_medis" value="">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tanggal Donor</label>
                            <input type="date" name="tanggal_donor" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <?php else: ?>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tanggal Donor</label>
                            <input type="date" name="tanggal_donor" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hasil Pemeriksaan <span class="text-danger">*</span></label>
                            <select name="hasil_pemeriksaan" id="selectHasil" class="form-select" required>
                                <option value="layak">✅ Layak</option>
                                <option value="tidak_layak">❌ Tidak Layak</option>
                                <option value="ditunda">⏸️ Ditunda</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hemoglobin (g/dL)</label>
                            <input type="number" name="hemoglobin" id="inputHb" class="form-control" step="0.1" placeholder="12.5" oninput="cekHb(this.value)">
                            <div id="warningHb" class="form-text" style="display:none"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tekanan Darah (mmHg)</label>
                            <input type="text" name="tekanan_darah" id="inputTekanan" class="form-control"
                                placeholder="120/80" pattern="\d{2,3}\/\d{2,3}"
                                oninput="cekTekanan(this.value)">
                            <div id="warningTekanan" class="form-text" style="display:none"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Catatan Medis</label>
                            <input type="text" name="catatan_medis" class="form-control" placeholder="Opsional...">
                        </div>
                        <div class="col-12">
                            <div id="infoHasil" class="small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Jika hasil <strong>Layak</strong>, stok darah otomatis +1 kantong.
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inp = document.getElementById('cariPendonor');
    const sel = document.getElementById('selectPendonor');
    if (!inp || !sel) return;

    // Filter list saat ketik
    inp.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        sel.style.display = '';
        Array.from(sel.options).forEach(opt => {
            opt.style.display = (opt.dataset.nama || '').includes(q) ? '' : 'none';
        });
    });

    // Setelah klik nama → tampilkan di input, sembunyikan list
    sel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            inp.value = opt.text;
            sel.style.display = 'none';
        }
    });

    // Tampilkan list saat klik input lagi
    inp.addEventListener('focus', function() {
        sel.style.display = '';
        Array.from(sel.options).forEach(opt => opt.style.display = '');
    });

    // Gabungkan sistolik/diastolik ke hidden input sebelum submit — tidak perlu lagi (single field)

    // Reset form saat modal ditutup
    document.getElementById('modalTambah')?.addEventListener('hidden.bs.modal', () => {
        const hb = document.getElementById('inputHb');
        const td = document.getElementById('inputTekanan');
        const wHb = document.getElementById('warningHb');
        const wTd = document.getElementById('warningTekanan');
        const wJeda = document.getElementById('warningJeda');
        const btnSimpan = document.querySelector('#modalTambah .btn-danger[type="submit"]');
        if (hb) { hb.value = ''; hb.style.borderColor = ''; }
        if (td) { td.value = ''; td.style.borderColor = ''; }
        if (wHb) wHb.style.display = 'none';
        if (wTd) wTd.style.display = 'none';
        if (wJeda) wJeda.style.display = 'none';
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.title = ''; }
    });
});

// Cek jeda 90 hari donor (standar PMI Indonesia)
function cekJedaDonor(sel) {
    const opt = sel.options[sel.selectedIndex];
    const warn = document.getElementById('warningJeda');
    const btnSimpan = document.querySelector('#modalTambah .btn-danger[type="submit"]');
    if (!warn || !opt) return;

    const donorTerakhir = opt.dataset.donorTerakhir;
    const hari = parseInt(opt.dataset.hari);
    const boleh = opt.dataset.boleh;

    if (!donorTerakhir) {
        // Belum pernah donor
        warn.innerHTML = `<div class="p-2 rounded-3 small" style="background:#d1fae5;color:#065f46">
            🟢 Pendonor <strong>belum pernah donor</strong> — boleh langsung donor.</div>`;
        warn.style.display = 'block';
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.title = ''; }
        return;
    }

    const tglBoleh = new Date(boleh);
    const tglBolehStr = tglBoleh.toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'});
    const sisaHari = 90 - hari;

    if (hari < 90) {
        // Belum boleh donor
        warn.innerHTML = `<div class="p-2 rounded-3 small" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
            🔴 <strong>Belum boleh donor!</strong><br>
            Donor terakhir: <strong>${new Date(donorTerakhir).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})}</strong>
            (${hari} hari lalu)<br>
            Baru boleh donor lagi: <strong>${tglBolehStr}</strong><br>
            <span style="color:#b91c1c">Sisa menunggu: <strong>${sisaHari} hari lagi</strong></span>
        </div>`;
        warn.style.display = 'block';
        if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.title = `Belum boleh donor, sisa ${sisaHari} hari`; }
    } else {
        // Sudah boleh donor
        warn.innerHTML = `<div class="p-2 rounded-3 small" style="background:#d1fae5;color:#065f46;border:1px solid #86efac">
            🟢 <strong>Boleh donor!</strong><br>
            Donor terakhir: <strong>${new Date(donorTerakhir).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})}</strong>
            (${hari} hari lalu — sudah melewati 90 hari)
        </div>`;
        warn.style.display = 'block';
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.title = ''; }
    }
}

// Smart warning Hemoglobin
function cekHb(val) {
    const hb = parseFloat(val);
    const warn = document.getElementById('warningHb');
    const selectHasil = document.getElementById('selectHasil');
    const input = document.getElementById('inputHb');
    if (!warn || isNaN(hb)) { warn.style.display='none'; return; }

    if (hb < 12) {
        warn.innerHTML = '🔴 Hb rendah (&lt;12 g/dL) — disarankan <strong>Tidak Layak</strong>';
        warn.style.cssText = 'display:block;color:#dc2626';
        input.style.borderColor = '#dc2626';
        if (selectHasil) selectHasil.value = 'tidak_layak';
    } else if (hb >= 12 && hb <= 17) {
        warn.innerHTML = '🟢 Hb normal — disarankan <strong>Layak</strong>';
        warn.style.cssText = 'display:block;color:#16a34a';
        input.style.borderColor = '#16a34a';
        if (selectHasil) selectHasil.value = 'layak';
    } else if (hb > 17) {
        warn.innerHTML = '🟡 Hb terlalu tinggi (&gt;17 g/dL) — pertimbangkan <strong>Ditunda</strong>';
        warn.style.cssText = 'display:block;color:#d97706';
        input.style.borderColor = '#d97706';
        if (selectHasil) selectHasil.value = 'ditunda';
    } else {
        warn.style.display = 'none';
        input.style.borderColor = '';
    }
}

// Smart warning Tekanan Darah
function cekTekanan(val) {
    const warn = document.getElementById('warningTekanan');
    const input = document.getElementById('inputTekanan');
    if (!warn) return;

    const match = val.match(/^(\d+)\/(\d+)$/);
    if (!match) {
        if (val.length > 0) {
            warn.innerHTML = '⚠️ Format: <strong>120/80</strong> (sistolik/diastolik)';
            warn.style.cssText = 'display:block;color:#6b7280';
        } else {
            warn.style.display = 'none';
        }
        input.style.borderColor = '';
        return;
    }

    const sis = parseInt(match[1]);
    const dia = parseInt(match[2]);

    if (sis > 180 || dia > 100) {
        warn.innerHTML = '🔴 Tekanan darah tinggi — pertimbangkan <strong>Ditunda</strong>';
        warn.style.cssText = 'display:block;color:#dc2626';
        input.style.borderColor = '#dc2626';
        const selectHasil = document.getElementById('selectHasil');
        if (selectHasil && selectHasil.value === 'layak') selectHasil.value = 'ditunda';
    } else if (sis < 90 || dia < 60) {
        warn.innerHTML = '🟡 Tekanan darah rendah — pertimbangkan <strong>Ditunda</strong>';
        warn.style.cssText = 'display:block;color:#d97706';
        input.style.borderColor = '#d97706';
    } else {
        warn.innerHTML = '🟢 Tekanan darah normal';
        warn.style.cssText = 'display:block;color:#16a34a';
        input.style.borderColor = '#16a34a';
    }
}
</script>
</body>
</html>