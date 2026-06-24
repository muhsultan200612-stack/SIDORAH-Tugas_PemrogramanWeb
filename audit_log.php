<?php
/**
 * SIDORAH - Sistem Informasi Donor Darah
 * File: audit_log.php
 * Akses: Super Admin only
 * Deskripsi: Lihat semua rekam jejak aktivitas pengguna
 */
require_once 'koneksi.php';
paksa_role(ROLE_SUPER_ADMIN, 'dashboard.php');

// ── FILTER ───────────────────────────────────────────────────
$cari        = bersihkan($koneksi, $_GET['cari']   ?? '');
$filter_aksi = bersihkan($koneksi, $_GET['aksi']   ?? '');
$filter_modul= bersihkan($koneksi, $_GET['modul']  ?? '');
$filter_status=bersihkan($koneksi, $_GET['status'] ?? '');
$filter_user = bersihkan($koneksi, $_GET['user']   ?? '');
$dari        = bersihkan($koneksi, $_GET['dari']   ?? '');
$sampai      = bersihkan($koneksi, $_GET['sampai'] ?? '');
$per_hal     = 50;
$hal         = max(1, (int)($_GET['hal'] ?? 1));
$offset      = ($hal - 1) * $per_hal;

$where = "WHERE 1=1";
if ($cari)         $where .= " AND (al.nama_pengguna LIKE '%$cari%' OR al.detail LIKE '%$cari%' OR al.aksi LIKE '%$cari%')";
if ($filter_aksi)  $where .= " AND al.aksi='$filter_aksi'";
if ($filter_modul) $where .= " AND al.modul='$filter_modul'";
if ($filter_status)$where .= " AND al.status='$filter_status'";
if ($filter_user)  $where .= " AND al.id_pengguna='$filter_user'";
if ($dari)         $where .= " AND DATE(al.waktu) >= '$dari'";
if ($sampai)       $where .= " AND DATE(al.waktu) <= '$sampai'";

$total_log = $koneksi->query("SELECT COUNT(*) as n FROM audit_log al $where")->fetch_assoc()['n'];
$total_hal  = ceil($total_log / $per_hal);

$logs = $koneksi->query("
    SELECT al.*, u.email
    FROM audit_log al
    LEFT JOIN users u ON al.id_pengguna = u.id_pengguna
    $where
    ORDER BY al.waktu DESC
    LIMIT $per_hal OFFSET $offset
");

// Opsi filter dropdown
$aksi_list  = $koneksi->query("SELECT DISTINCT aksi FROM audit_log ORDER BY aksi")->fetch_all(MYSQLI_ASSOC);
$modul_list = $koneksi->query("SELECT DISTINCT modul FROM audit_log WHERE modul IS NOT NULL ORDER BY modul")->fetch_all(MYSQLI_ASSOC);
$user_list  = $koneksi->query("SELECT DISTINCT id_pengguna, nama_pengguna FROM audit_log ORDER BY nama_pengguna")->fetch_all(MYSQLI_ASSOC);

// Stat ringkasan hari ini
$today_stat = $koneksi->query("
    SELECT
        COUNT(*) as total,
        SUM(status='sukses') as sukses,
        SUM(status='gagal') as gagal,
        SUM(status='peringatan') as peringatan
    FROM audit_log
    WHERE DATE(waktu) = CURDATE()
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Log — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .log-row-gagal     { background: #fff5f5; }
        .log-row-peringatan{ background: #fffbeb; }
        .aksi-badge { font-family: monospace; font-size: 0.75rem; }
        .detail-cell { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        pre.json-view { background:#f8f9fa; border-radius:8px; padding:0.75rem; font-size:0.78rem; max-height:300px; overflow-y:auto; }
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
                <div class="mt-4 mb-3">
                    <h1 class="h3 mb-0"><i class="bi bi-journal-text text-danger me-2"></i>Audit Log Aktivitas</h1>
                    <ol class="breadcrumb mb-0 mt-1">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Audit Log</li>
                    </ol>
                </div>

                <!-- Stat hari ini -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <div class="h3 fw-bold text-dark mb-0"><?= number_format($today_stat['total']) ?></div>
                                <div class="small text-muted">Aktivitas Hari Ini</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <div class="h3 fw-bold text-success mb-0"><?= $today_stat['sukses'] ?></div>
                                <div class="small text-muted">Sukses</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <div class="h3 fw-bold text-danger mb-0"><?= $today_stat['gagal'] ?></div>
                                <div class="small text-muted">Gagal</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body py-3">
                                <div class="h3 fw-bold text-warning mb-0"><?= $today_stat['peringatan'] ?></div>
                                <div class="small text-muted">Peringatan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-2">
                            <div class="col-md-3">
                                <input type="text" name="cari" class="form-control form-control-sm"
                                       placeholder="Cari nama, aksi, detail..."
                                       value="<?= htmlspecialchars($cari) ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="user" class="form-select form-select-sm">
                                    <option value="">Semua User</option>
                                    <?php foreach ($user_list as $ul): ?>
                                    <option value="<?= $ul['id_pengguna'] ?>" <?= $filter_user==$ul['id_pengguna']?'selected':'' ?>>
                                        <?= htmlspecialchars($ul['nama_pengguna']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="modul" class="form-select form-select-sm">
                                    <option value="">Semua Modul</option>
                                    <?php foreach ($modul_list as $ml): ?>
                                    <option value="<?= $ml['modul'] ?>" <?= $filter_modul===$ml['modul']?'selected':'' ?>>
                                        <?= htmlspecialchars($ml['modul']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">Status</option>
                                    <option value="sukses"     <?= $filter_status==='sukses'?'selected':'' ?>>Sukses</option>
                                    <option value="gagal"      <?= $filter_status==='gagal'?'selected':'' ?>>Gagal</option>
                                    <option value="peringatan" <?= $filter_status==='peringatan'?'selected':'' ?>>Peringatan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="dari" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($dari) ?>" placeholder="Dari tanggal">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="sampai" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($sampai) ?>" placeholder="Sampai tanggal">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-funnel me-1"></i>Filter
                                </button>
                                <a href="audit_log.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                                <span class="ms-auto text-muted small align-self-center">
                                    <i class="bi bi-list-ul"></i> <?= number_format($total_log) ?> entri log ditemukan
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Log -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="ps-3" style="width:160px">Waktu</th>
                                        <th>Pengguna</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                        <th>Modul</th>
                                        <th>Detail</th>
                                        <th>IP Address</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center pe-3">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($logs->num_rows === 0): ?>
                                    <tr><td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>Tidak ada log ditemukan.
                                    </td></tr>
                                <?php else:
                                    while ($log = $logs->fetch_assoc()):
                                    $row_class = match($log['status']) {
                                        'gagal'      => 'log-row-gagal',
                                        'peringatan' => 'log-row-peringatan',
                                        default      => ''
                                    };
                                    $badge_status = match($log['status']) {
                                        'sukses'     => 'bg-success',
                                        'gagal'      => 'bg-danger',
                                        'peringatan' => 'bg-warning text-dark',
                                        default      => 'bg-secondary'
                                    };
                                ?>
                                    <tr class="<?= $row_class ?>">
                                        <td class="ps-3 text-muted">
                                            <?= date('d/m/Y H:i:s', strtotime($log['waktu'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($log['nama_pengguna']) ?></div>
                                            <div class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars($log['email'] ?? '') ?></div>
                                        </td>
                                        <td><?= badge_role($log['role_pengguna']) ?></td>
                                        <td><span class="badge bg-dark aksi-badge"><?= htmlspecialchars($log['aksi']) ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($log['modul'] ?? '-') ?></span></td>
                                        <td class="detail-cell" title="<?= htmlspecialchars($log['detail'] ?? '') ?>">
                                            <?= htmlspecialchars($log['detail'] ?? '-') ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $badge_status ?>"><?= ucfirst($log['status']) ?></span>
                                        </td>
                                        <td class="text-center pe-3">
                                            <?php if ($log['data_sebelum'] || $log['data_sesudah']): ?>
                                            <button class="btn btn-outline-secondary btn-sm py-0 px-2"
                                                onclick='lihatData(<?= htmlspecialchars(json_encode([
                                                    "sebelum" => $log["data_sebelum"],
                                                    "sesudah" => $log["data_sesudah"],
                                                    "aksi"    => $log["aksi"]
                                                ])) ?>)'>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_hal > 1): ?>
                        <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                            <div class="small text-muted">
                                Halaman <?= $hal ?> dari <?= $total_hal ?> (<?= number_format($total_log) ?> total entri)
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <?php
                                    $q = $_GET; unset($q['hal']);
                                    $qs = http_build_query($q);
                                    for ($i = max(1,$hal-3); $i <= min($total_hal,$hal+3); $i++): ?>
                                    <li class="page-item <?= $i===$hal?'active':'' ?>">
                                        <a class="page-link" href="?<?= $qs ?>&hal=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">SIDORAH &copy; <?= date('Y') ?></div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Modal Detail Data -->
<div class="modal fade" id="modalData" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-database me-2"></i>Detail Perubahan Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="dataSebelumWrap" class="mb-3">
                    <div class="fw-semibold text-danger mb-1"><i class="bi bi-arrow-left-circle me-1"></i>Data Sebelum</div>
                    <pre class="json-view" id="dataSebelum">—</pre>
                </div>
                <div id="dataSesudahWrap">
                    <div class="fw-semibold text-success mb-1"><i class="bi bi-arrow-right-circle me-1"></i>Data Sesudah</div>
                    <pre class="json-view" id="dataSesudah">—</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
function lihatData(obj) {
    const fmt = (json) => {
        try { return JSON.stringify(JSON.parse(json), null, 2); }
        catch { return json || '—'; }
    };
    document.getElementById('dataSebelum').textContent = fmt(obj.sebelum);
    document.getElementById('dataSesudah').textContent = fmt(obj.sesudah);
    new bootstrap.Modal(document.getElementById('modalData')).show();
}
</script>
</body>
</html>