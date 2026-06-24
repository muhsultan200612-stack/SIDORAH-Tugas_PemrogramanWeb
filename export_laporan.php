<?php
/**
 * SIDORAH - export_laporan.php
 * Export laporan donor & stok ke Excel atau PDF
 */
require_once 'koneksi.php';
paksa_login();
if ($_SESSION['role'] === 'pendonor') { header('Location: portal_pendonor.php'); exit(); }

$tipe    = $_GET['tipe']   ?? 'donor'; // donor atau stok
$format  = $_GET['format'] ?? 'excel'; // excel atau pdf
$dari    = bersihkan($koneksi, $_GET['dari']   ?? date('Y-m-01'));
$sampai  = bersihkan($koneksi, $_GET['sampai'] ?? date('Y-m-d'));
$goldar  = bersihkan($koneksi, $_GET['goldar'] ?? '');

// Ambil info RS
$setting_rs = [];
$res_set = $koneksi->query("SELECT kunci, nilai FROM pengaturan");
if ($res_set) while ($r = $res_set->fetch_assoc()) $setting_rs[$r['kunci']] = $r['nilai'];
$nama_rs   = $setting_rs['nama_rs']   ?? 'RS SIDORAH';
$alamat_rs = $setting_rs['alamat_rs'] ?? 'Makassar';
$telp_rs   = $setting_rs['telp_rs']   ?? '-';

// Tanggal cetak — tersedia di semua bagian export
$tgl_cetak = date('d/m/Y H:i');

// ── AMBIL DATA ────────────────────────────────────────────────
if ($tipe === 'transfusi') {
    $dari_t   = bersihkan($koneksi, $_GET['dari']   ?? date('Y-m-01'));
    $sampai_t = bersihkan($koneksi, $_GET['sampai'] ?? date('Y-m-d'));
    $filter_status = bersihkan($koneksi, $_GET['status'] ?? '');

    $where_t = "WHERE DATE(t.tanggal_transfusi) BETWEEN '$dari_t' AND '$sampai_t'";
    if ($filter_status) $where_t .= " AND t.status='$filter_status'";

    $data = $koneksi->query("
        SELECT t.*, u.nama_lengkap as petugas_nama
        FROM transfusi_darah t
        LEFT JOIN users u ON t.id_petugas=u.id_pengguna
        $where_t ORDER BY t.tanggal_transfusi DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $stat = $koneksi->query("
        SELECT COUNT(*) as total,
               SUM(t.status='selesai') as selesai,
               SUM(t.status='proses') as proses,
               SUM(t.status='dibatalkan') as dibatalkan,
               SUM(t.reaksi_transfusi != 'tidak_ada') as ada_reaksi,
               SUM(t.reaksi_transfusi = 'berat') as reaksi_berat,
               COALESCE(SUM(CASE WHEN t.status='selesai' THEN t.volume_ml ELSE 0 END),0) as total_volume
        FROM transfusi_darah t
        $where_t
    ")->fetch_assoc();

    $judul_laporan = 'Laporan Transfusi Darah';

    if ($format === 'excel') {
        $filename = "Laporan_Transfusi_{$dari_t}_sd_{$sampai_t}.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        ob_start();
        echo "<html><head><meta charset='utf-8'></head><body><table border='0'>";
        echo "<tr><td colspan='10'><b style='font-size:14pt'>$judul_laporan</b></td></tr>";
        echo "<tr><td colspan='10'>$nama_rs | $alamat_rs | Telp: $telp_rs</td></tr>";
        echo "<tr><td colspan='10'>Periode: " . tanggal_indo($dari_t) . " s/d " . tanggal_indo($sampai_t) . "</td></tr>";
        echo "<tr><td colspan='10'>Dicetak: $tgl_cetak WIB</td></tr><tr></tr>";
        echo "<tr><td><b>Total Transfusi</b></td><td>{$stat['total']}</td></tr>";
        echo "<tr><td><b>Selesai</b></td><td>{$stat['selesai']}</td></tr>";
        echo "<tr><td><b>Proses</b></td><td>{$stat['proses']}</td></tr>";
        echo "<tr><td><b>Dibatalkan</b></td><td>{$stat['dibatalkan']}</td></tr>";
        echo "<tr><td><b>Ada Reaksi</b></td><td>{$stat['ada_reaksi']}</td></tr>";
        echo "<tr><td><b>Reaksi Berat</b></td><td>{$stat['reaksi_berat']}</td></tr>";
        echo "<tr><td><b>Total Volume</b></td><td>" . number_format($stat['total_volume']) . " ml</td></tr><tr></tr>";
        echo "<tr bgcolor='#dc3545' style='color:white'>
            <th>No</th><th>No. Transfusi</th><th>Nama Pasien</th><th>No. RM</th>
            <th>Gol. Darah</th><th>Volume (ml)</th><th>Tanggal</th>
            <th>Dokter</th><th>Reaksi</th><th>Status</th><th>Petugas</th>
        </tr>";
        $no = 1;
        foreach ($data as $r) {
            $sym = $r['rhesus']==='Positif'?'+':'-';
            $bg = match($r['status']) {
                'selesai'    => '#d1fae5',
                'dibatalkan' => '#fee2e2',
                default      => '#dbeafe'
            };
            $reaksi = match($r['reaksi_transfusi']) {
                'tidak_ada' => 'Tidak Ada',
                'ringan'    => 'Ringan',
                'sedang'    => 'Sedang',
                'berat'     => 'BERAT ⚠',
                default     => '-'
            };
            echo "<tr bgcolor='$bg'>
                <td>{$no}</td>
                <td>{$r['no_transfusi']}</td>
                <td>{$r['nama_pasien']}</td>
                <td>{$r['no_rekam_medis']}</td>
                <td>{$r['golongan_darah']}{$sym}</td>
                <td>{$r['volume_ml']}</td>
                <td>" . tanggal_indo($r['tanggal_transfusi']) . "</td>
                <td>" . htmlspecialchars($r['nama_dokter'] ?: '-') . "</td>
                <td>{$reaksi}</td>
                <td>" . ucfirst($r['status']) . "</td>
                <td>{$r['petugas_nama']}</td>
            </tr>";
            $no++;
        }
        echo "</table></body></html>";
        echo ob_get_clean();
        exit();
    }

    if ($format === 'pdf') {
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul_laporan ?> — <?= $nama_rs ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #1a1a1a; }
        .header { text-align:center; padding:20px 0 10px; border-bottom:2px solid #dc3545; margin-bottom:16px; }
        .header h1 { font-size:16pt; color:#dc3545; margin-bottom:4px; }
        .header p { font-size:10pt; color:#555; }
        .ringkasan { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
        .stat-box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; text-align:center; }
        .stat-box .angka { font-size:18pt; font-weight:bold; color:#dc3545; }
        .stat-box .label { font-size:9pt; color:#666; }
        table { width:100%; border-collapse:collapse; font-size:9.5pt; }
        th { background:#dc3545; color:white; padding:7px 5px; text-align:left; font-size:9pt; }
        td { padding:5px; border-bottom:1px solid #f3f4f6; }
        tr:nth-child(even) td { background:#f9fafb; }
        .badge-selesai    { background:#d1fae5; color:#065f46; padding:2px 7px; border-radius:10px; font-size:8.5pt; }
        .badge-proses     { background:#dbeafe; color:#1e40af; padding:2px 7px; border-radius:10px; font-size:8.5pt; }
        .badge-dibatalkan { background:#fee2e2; color:#991b1b; padding:2px 7px; border-radius:10px; font-size:8.5pt; }
        .badge-berat      { background:#fee2e2; color:#991b1b; padding:2px 7px; border-radius:10px; font-size:8.5pt; font-weight:bold; }
        .badge-normal     { background:#f3f4f6; color:#6b7280; padding:2px 7px; border-radius:10px; font-size:8.5pt; }
        .footer { text-align:center; font-size:9pt; color:#888; border-top:1px solid #e5e7eb; padding-top:10px; margin-top:16px; }
        @media print {
            .no-print { display:none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            th { background:#dc3545 !important; color:white !important; }
            @page { margin:1.5cm; }
        }
    </style>
</head>
<body>
<div id="toolbar" class="no-print" style="background:#f8f9fa;padding:10px 20px;display:flex;justify-content:flex-end;gap:8px;border-bottom:1px solid #e5e7eb;margin-bottom:20px">
    <button onclick="cetakPDF()" style="background:#dc3545;color:white;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:bold">🖨️ Cetak / Simpan PDF</button>
    <button onclick="window.close()" style="background:#6c757d;color:white;border:none;padding:8px 16px;border-radius:8px;cursor:pointer">✕ Tutup</button>
</div>
<div class="header">
    <h1><?= $judul_laporan ?></h1>
    <p><strong><?= htmlspecialchars($nama_rs) ?></strong></p>
    <p><?= htmlspecialchars($alamat_rs) ?> | Telp: <?= htmlspecialchars($telp_rs) ?></p>
    <p>Periode: <?= tanggal_indo($dari_t) ?> s/d <?= tanggal_indo($sampai_t) ?></p>
    <p style="font-size:9pt;color:#888">Dicetak: <?= $tgl_cetak ?> WIB</p>
</div>

<div class="ringkasan">
    <div class="stat-box">
        <div class="angka"><?= $stat['total'] ?></div>
        <div class="label">Total Transfusi</div>
    </div>
    <div class="stat-box" style="border-color:#bbf7d0">
        <div class="angka" style="color:#15803d"><?= $stat['selesai'] ?></div>
        <div class="label">Selesai</div>
    </div>
    <div class="stat-box" style="border-color:#fecaca">
        <div class="angka" style="color:#b91c1c"><?= $stat['ada_reaksi'] ?></div>
        <div class="label">Ada Reaksi</div>
    </div>
    <div class="stat-box" style="border-color:#e5e7eb">
        <div class="angka" style="color:#374151"><?= number_format($stat['total_volume']) ?></div>
        <div class="label">ml Ditransfusi</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>No</th><th>No. Transfusi</th><th>Nama Pasien</th><th>No. RM</th>
            <th>Gol. Darah</th><th>Volume</th><th>Tanggal</th>
            <th>Dokter</th><th>Reaksi</th><th>Status</th><th>Petugas</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($data)): ?>
    <tr><td colspan="10" style="text-align:center;padding:20px;color:#888">Tidak ada data pada periode ini</td></tr>
    <?php else: $no=1; foreach ($data as $r):
        $sym = $r['rhesus']==='Positif'?'+':'-';
        $reaksi_label = match($r['reaksi_transfusi']) {
            'tidak_ada' => '<span class="badge-normal">Tidak Ada</span>',
            'ringan'    => '<span class="badge-normal">Ringan</span>',
            'sedang'    => '<span class="badge-berat">Sedang</span>',
            'berat'     => '<span class="badge-berat">⚠ Berat</span>',
            default     => '-'
        };
        $status_label = match($r['status']) {
            'selesai'    => '<span class="badge-selesai">Selesai</span>',
            'proses'     => '<span class="badge-proses">Proses</span>',
            'dibatalkan' => '<span class="badge-dibatalkan">Dibatalkan</span>',
            default      => $r['status']
        };
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td style="font-family:monospace;font-size:8.5pt;color:#dc3545"><?= htmlspecialchars($r['no_transfusi']) ?></td>
        <td><?= htmlspecialchars($r['nama_pasien']) ?></td>
        <td style="font-size:8.5pt"><?= htmlspecialchars($r['no_rekam_medis'] ?: '-') ?></td>
        <td style="font-weight:bold;color:#dc3545"><?= $r['golongan_darah'].$sym ?></td>
        <td><?= number_format($r['volume_ml']) ?> ml</td>
        <td><?= tanggal_indo($r['tanggal_transfusi']) ?></td>
        <td style="font-size:9pt"><?= htmlspecialchars($r['nama_dokter'] ?: '-') ?></td>
        <td><?= $reaksi_label ?></td>
        <td><?= $status_label ?></td>
        <td style="font-size:9pt"><?= htmlspecialchars($r['petugas_nama'] ?? '-') ?></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
<div class="footer"><?= htmlspecialchars($nama_rs) ?> &copy; <?= date('Y') ?> — Dokumen ini digenerate otomatis oleh sistem SIDORAH</div>
<script>
function cetakPDF() {
    document.getElementById('toolbar').style.display = 'none';
    setTimeout(() => {
        window.print();
        setTimeout(() => { document.getElementById('toolbar').style.display = 'flex'; }, 1000);
    }, 100);
}
window.onload = function() { setTimeout(() => cetakPDF(), 800); };
</script>
</body>
</html>
        <?php
        exit();
    }
}

if ($tipe === 'suhu') {
    $dari_suhu   = bersihkan($koneksi, $_GET['dari']   ?? date('Y-m-d'));
    $sampai_suhu = bersihkan($koneksi, $_GET['sampai'] ?? date('Y-m-d'));

    $data = $koneksi->query("
        SELECT created_at, suhu, kelembaban, device_id, lokasi, status
        FROM sensor_suhu
        WHERE DATE(created_at) BETWEEN '$dari_suhu' AND '$sampai_suhu'
        ORDER BY created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $stat = $koneksi->query("
        SELECT COUNT(*) as total,
               ROUND(AVG(suhu),1) as avg_suhu,
               MIN(suhu) as min_suhu,
               MAX(suhu) as max_suhu,
               SUM(status='normal') as normal,
               SUM(status='warning') as warning,
               SUM(status='kritis') as kritis
        FROM sensor_suhu
        WHERE DATE(created_at) BETWEEN '$dari_suhu' AND '$sampai_suhu'
    ")->fetch_assoc();

    $judul_laporan = 'Laporan Monitoring Suhu Darah';

    if ($format === 'excel') {
        $filename = "Laporan_Suhu_{$dari_suhu}_sd_{$sampai_suhu}.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        ob_start();
        echo "<html><head><meta charset='utf-8'></head><body><table border='0'>";
        echo "<tr><td colspan='6'><b style='font-size:14pt'>$judul_laporan</b></td></tr>";
        echo "<tr><td colspan='6'>$nama_rs | $alamat_rs | Telp: $telp_rs</td></tr>";
        echo "<tr><td colspan='6'>Periode: " . tanggal_indo($dari_suhu) . " s/d " . tanggal_indo($sampai_suhu) . "</td></tr>";
        echo "<tr><td colspan='6'>Dicetak: $tgl_cetak WIB</td></tr><tr></tr>";
        echo "<tr><td><b>Total Data</b></td><td>{$stat['total']}</td></tr>";
        echo "<tr><td><b>Rata-rata Suhu</b></td><td>{$stat['avg_suhu']}°C</td></tr>";
        echo "<tr><td><b>Suhu Min</b></td><td>{$stat['min_suhu']}°C</td></tr>";
        echo "<tr><td><b>Suhu Max</b></td><td>{$stat['max_suhu']}°C</td></tr>";
        echo "<tr><td><b>Normal</b></td><td>{$stat['normal']}</td></tr>";
        echo "<tr><td><b>Warning</b></td><td>{$stat['warning']}</td></tr>";
        echo "<tr><td><b>Kritis</b></td><td>{$stat['kritis']}</td></tr><tr></tr>";
        echo "<tr bgcolor='#dc3545' style='color:white'>
            <th>No</th><th>Waktu</th><th>Suhu (°C)</th>
            <th>Kelembaban (%)</th><th>Lokasi</th><th>Status</th>
        </tr>";
        $no = 1;
        foreach ($data as $r) {
            $bg = match($r['status']) {
                'kritis'  => '#fee2e2',
                'warning' => '#fef3c7',
                default   => '#f0fdf4'
            };
            echo "<tr bgcolor='$bg'>
                <td>{$no}</td>
                <td>" . date('d/m/Y H:i:s', strtotime($r['created_at'])) . "</td>
                <td>{$r['suhu']}</td>
                <td>" . ($r['kelembaban'] ?? '-') . "</td>
                <td>{$r['lokasi']}</td>
                <td>" . ucfirst($r['status']) . "</td>
            </tr>";
            $no++;
        }
        echo "</table></body></html>";
        echo ob_get_clean();
        exit();
    }

    if ($format === 'pdf') {
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul_laporan ?> — <?= $nama_rs ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #1a1a1a; }
        .header { text-align:center; padding:20px 0 10px; border-bottom:2px solid #dc3545; margin-bottom:16px; }
        .header h1 { font-size:16pt; color:#dc3545; margin-bottom:4px; }
        .header p { font-size:10pt; color:#555; }
        .ringkasan { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
        .stat-box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; text-align:center; }
        .stat-box .angka { font-size:18pt; font-weight:bold; color:#dc3545; }
        .stat-box .label { font-size:9pt; color:#666; }
        table { width:100%; border-collapse:collapse; font-size:10pt; }
        th { background:#dc3545; color:white; padding:7px 6px; text-align:left; }
        td { padding:6px; border-bottom:1px solid #f3f4f6; }
        .badge-normal  { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-warning { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-kritis  { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .footer { text-align:center; font-size:9pt; color:#888; border-top:1px solid #e5e7eb; padding-top:10px; margin-top:16px; }
        @media print { .no-print { display:none !important; visibility:hidden !important; height:0 !important; } @page { margin:1.5cm; } }
    </style>
</head>
<body>
<div class="no-print" style="background:#f8f9fa;padding:10px 20px;display:flex;justify-content:flex-end;gap:8px;border-bottom:1px solid #e5e7eb;margin-bottom:20px">
    <button onclick="window.print()" style="background:#dc3545;color:white;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:bold">🖨️ Cetak / Simpan PDF</button>
    <button onclick="window.close()" style="background:#6c757d;color:white;border:none;padding:8px 16px;border-radius:8px;cursor:pointer">✕ Tutup</button>
</div>
<div class="header">
    <h1><?= $judul_laporan ?></h1>
    <p><strong><?= htmlspecialchars($nama_rs) ?></strong></p>
    <p><?= htmlspecialchars($alamat_rs) ?> | Telp: <?= htmlspecialchars($telp_rs) ?></p>
    <p>Periode: <?= tanggal_indo($dari_suhu) ?> s/d <?= tanggal_indo($sampai_suhu) ?></p>
    <p style="font-size:9pt;color:#888">Dicetak: <?= $tgl_cetak ?> WIB</p>
</div>
<div class="ringkasan">
    <div class="stat-box"><div class="angka"><?= $stat['avg_suhu'] ?>°</div><div class="label">Rata-rata Suhu</div></div>
    <div class="stat-box" style="border-color:#bbf7d0"><div class="angka" style="color:#15803d"><?= $stat['normal'] ?></div><div class="label">Normal</div></div>
    <div class="stat-box" style="border-color:#fde68a"><div class="angka" style="color:#a16207"><?= $stat['warning'] ?></div><div class="label">Warning</div></div>
    <div class="stat-box" style="border-color:#fecaca"><div class="angka" style="color:#b91c1c"><?= $stat['kritis'] ?></div><div class="label">Kritis</div></div>
</div>
<table>
    <thead>
        <tr><th>No</th><th>Waktu</th><th>Suhu (°C)</th><th>Kelembaban (%)</th><th>Lokasi</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php if (empty($data)): ?>
    <tr><td colspan="6" style="text-align:center;padding:20px;color:#888">Belum ada data sensor pada periode ini</td></tr>
    <?php else: $no=1; foreach ($data as $r): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= date('d/m/Y H:i:s', strtotime($r['created_at'])) ?></td>
        <td><strong><?= $r['suhu'] ?>°C</strong></td>
        <td><?= $r['kelembaban'] ?? '-' ?></td>
        <td><?= htmlspecialchars($r['lokasi']) ?></td>
        <td><span class="badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
<div class="footer"><?= htmlspecialchars($nama_rs) ?> &copy; <?= date('Y') ?> — Dokumen ini digenerate otomatis oleh sistem SIDORAH</div>
<script>
function cetakPDF() {
    document.getElementById('toolbar').style.display = 'none';
    setTimeout(() => {
        window.print();
        setTimeout(() => {
            document.getElementById('toolbar').style.display = 'flex';
        }, 1000);
    }, 100);
}
window.onload = function() { setTimeout(() => cetakPDF(), 800); };
</script>
</body>
</html>
        <?php
        exit();
    }
}

if ($tipe === 'donor') {
    $where = "WHERE rd.tanggal_donor BETWEEN '$dari' AND '$sampai'";
    if ($goldar) $where .= " AND p.golongan_darah='$goldar'";

    $data = $koneksi->query("
        SELECT rd.tanggal_donor, u.nama_lengkap,
               p.golongan_darah, p.rhesus,
               rd.volume_darah_ml, rd.hemoglobin,
               rd.tekanan_darah, rd.hasil_pemeriksaan,
               rd.catatan_medis, pt.nama_lengkap as petugas
        FROM riwayat_donor rd
        JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
        JOIN users u ON p.id_pengguna=u.id_pengguna
        LEFT JOIN users pt ON rd.id_petugas_medis=pt.id_pengguna
        $where
        ORDER BY rd.tanggal_donor DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $stat = $koneksi->query("
        SELECT COUNT(*) as total,
               SUM(hasil_pemeriksaan='layak') as layak,
               SUM(hasil_pemeriksaan='tidak_layak') as tidak_layak,
               SUM(hasil_pemeriksaan='ditunda') as ditunda,
               COALESCE(SUM(volume_darah_ml),0) as total_volume
        FROM riwayat_donor rd
        JOIN pendonor p ON rd.id_pendonor=p.id_pendonor
        $where
    ")->fetch_assoc();

} else {
    // Stok
    $data = $koneksi->query("
        SELECT golongan_darah, rhesus, jumlah_kantong,
               COALESCE(jenis_darah,'WB') as jenis_darah,
               tanggal_masuk, tanggal_kadaluarsa, status_stok
        FROM stok_darah
        ORDER BY FIELD(status_stok,'habis','kritis','tersedia','expired'),
                 golongan_darah, rhesus
    ")->fetch_all(MYSQLI_ASSOC);

    $stat = $koneksi->query("
        SELECT COALESCE(SUM(CASE WHEN status_stok='tersedia' THEN jumlah_kantong ELSE 0 END),0) as tersedia,
               COALESCE(SUM(CASE WHEN status_stok='kritis' THEN jumlah_kantong ELSE 0 END),0) as kritis,
               COALESCE(SUM(CASE WHEN status_stok='habis' THEN jumlah_kantong ELSE 0 END),0) as habis,
               COUNT(*) as total_entri
        FROM stok_darah WHERE status_stok != 'expired'
    ")->fetch_assoc();
}

$judul_laporan = $tipe === 'donor' ? 'Laporan Donor Darah' : 'Laporan Stok Darah';

// ── EXPORT EXCEL ──────────────────────────────────────────────
if ($format === 'excel') {
    $filename = $tipe === 'donor'
        ? "Laporan_Donor_{$dari}_sd_{$sampai}.xls"
        : "Laporan_Stok_" . date('Ymd') . ".xls";

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    ob_start();

    echo "<html><head><meta charset='utf-8'></head><body>";
    echo "<table border='0'>";
    echo "<tr><td colspan='9'><b style='font-size:14pt'>$judul_laporan</b></td></tr>";
    echo "<tr><td colspan='9'>$nama_rs | $alamat_rs | Telp: $telp_rs</td></tr>";

    if ($tipe === 'donor') {
        echo "<tr><td colspan='9'>Periode: " . tanggal_indo($dari) . " s/d " . tanggal_indo($sampai) . "</td></tr>";
        echo "<tr><td colspan='9'>Dicetak: $tgl_cetak WIB</td></tr>";
        echo "<tr><td></td></tr>";

        // Ringkasan
        echo "<tr><td colspan='4'><b>Ringkasan</b></td></tr>";
        echo "<tr><td>Total Pemeriksaan</td><td><b>{$stat['total']}</b></td></tr>";
        echo "<tr><td>Layak Donor</td><td><b>{$stat['layak']}</b></td></tr>";
        echo "<tr><td>Tidak Layak</td><td><b>{$stat['tidak_layak']}</b></td></tr>";
        echo "<tr><td>Ditunda</td><td><b>{$stat['ditunda']}</b></td></tr>";
        echo "<tr><td>Total Volume</td><td><b>" . number_format($stat['total_volume']) . " ml</b></td></tr>";
        echo "<tr><td></td></tr>";

        // Header tabel
        echo "<tr bgcolor='#dc3545' style='color:white'>
            <th>No</th><th>Nama Pendonor</th><th>Gol. Darah</th>
            <th>Tanggal Donor</th><th>Volume (ml)</th><th>Hb (g/dL)</th>
            <th>Tekanan Darah</th><th>Hasil</th><th>Petugas</th>
        </tr>";

        $no = 1;
        foreach ($data as $r) {
            $sym = $r['rhesus']==='Positif'?'+':'-';
            $hasil = ucfirst(str_replace('_',' ',$r['hasil_pemeriksaan']));
            $bg = match($r['hasil_pemeriksaan']) {
                'layak'       => '#d1fae5',
                'tidak_layak' => '#fee2e2',
                'ditunda'     => '#fef3c7',
                default       => '#ffffff'
            };
            echo "<tr bgcolor='$bg'>
                <td>{$no}</td>
                <td>{$r['nama_lengkap']}</td>
                <td>{$r['golongan_darah']}{$sym}</td>
                <td>" . tanggal_indo($r['tanggal_donor']) . "</td>
                <td>{$r['volume_darah_ml']}</td>
                <td>{$r['hemoglobin']}</td>
                <td>{$r['tekanan_darah']}</td>
                <td>{$hasil}</td>
                <td>{$r['petugas']}</td>
            </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='6'>Tanggal Cetak: $tgl_cetak WIB</td></tr>";
        echo "<tr><td></td></tr>";

        // Ringkasan
        echo "<tr><td colspan='3'><b>Ringkasan Stok</b></td></tr>";
        echo "<tr><td>Total Kantong Tersedia</td><td><b>{$stat['tersedia']}</b></td></tr>";
        echo "<tr><td>Stok Kritis</td><td><b>{$stat['kritis']}</b></td></tr>";
        echo "<tr><td>Stok Habis</td><td><b>{$stat['habis']}</b></td></tr>";
        echo "<tr><td></td></tr>";

        // Header tabel
        echo "<tr bgcolor='#dc3545' style='color:white'>
            <th>No</th><th>Golongan Darah</th><th>Rhesus</th>
            <th>Jenis Darah</th><th>Jumlah Kantong</th><th>Tanggal Masuk</th>
            <th>Kadaluarsa</th><th>Status</th>
        </tr>";

        $no = 1;
        foreach ($data as $s) {
            $bg = match($s['status_stok']) {
                'tersedia' => '#d1fae5',
                'kritis'   => '#fef3c7',
                'habis'    => '#fee2e2',
                default    => '#f3f4f6'
            };
            $jd = ($s['jenis_darah'] === 'PRC') ? 'Darah Pekat (PRC) 250mL' : 'Darah Utuh (WB) 450mL';
            echo "<tr bgcolor='$bg'>
                <td>{$no}</td>
                <td>{$s['golongan_darah']}</td>
                <td>{$s['rhesus']}</td>
                <td>{$jd}</td>
                <td>{$s['jumlah_kantong']}</td>
                <td>" . tanggal_indo($s['tanggal_masuk'] ?? '') . "</td>
                <td>" . tanggal_indo($s['tanggal_kadaluarsa'] ?? '') . "</td>
                <td>" . ucfirst($s['status_stok']) . "</td>
            </tr>";
            $no++;
        }
    }

    echo "</table></body></html>";
    echo ob_get_clean();
    exit();
}

// ── EXPORT PDF (via print browser) ───────────────────────────
if ($format === 'pdf') {
    // Generate HTML yang dioptimalkan untuk cetak/PDF
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul_laporan ?> — <?= $nama_rs ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #1a1a1a; }
        .header { text-align:center; padding:20px 0 10px; border-bottom:2px solid #dc3545; margin-bottom:16px; }
        .header h1 { font-size:16pt; color:#dc3545; margin-bottom:4px; }
        .header p { font-size:10pt; color:#555; }
        .ringkasan { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
        .stat-box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; text-align:center; }
        .stat-box .angka { font-size:18pt; font-weight:bold; color:#dc3545; }
        .stat-box .label { font-size:9pt; color:#666; }
        table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:10pt; }
        th { background:#dc3545; color:white; padding:7px 6px; text-align:left; }
        td { padding:6px; border-bottom:1px solid #f3f4f6; }
        tr:nth-child(even) td { background:#f9fafb; }
        .badge-layak      { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-tidak_layak{ background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-ditunda    { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-tersedia   { background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-kritis     { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-habis      { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .badge-expired    { background:#f3f4f6; color:#6b7280; padding:2px 8px; border-radius:10px; font-size:9pt; }
        .footer { text-align:center; font-size:9pt; color:#888; border-top:1px solid #e5e7eb; padding-top:10px; margin-top:10px; }
        @media print {
            body { font-size:10pt; }
            .no-print { display:none !important; visibility:hidden !important; height:0 !important; }
            @page { margin:1.5cm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
            th { background:#dc3545 !important; color:white !important; }
            .stat-box .angka { color: inherit !important; }
        }
    </style>
</head>
<body>

<!-- Tombol Print -->
<div class="no-print" style="position:fixed;top:10px;right:10px;z-index:999;display:flex;gap:8px">
    <button onclick="window.print()"
        style="background:#dc3545;color:white;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:bold">
        🖨️ Cetak / Simpan PDF
    </button>
    <button onclick="window.close()"
        style="background:#6c757d;color:white;border:none;padding:8px 16px;border-radius:8px;cursor:pointer">
        ✕ Tutup
    </button>
</div>

<!-- Header -->
<div class="header">
    <h1><?= $judul_laporan ?></h1>
    <p><strong><?= htmlspecialchars($nama_rs) ?></strong></p>
    <p><?= htmlspecialchars($alamat_rs) ?> | Telp: <?= htmlspecialchars($telp_rs) ?></p>
    <?php if ($tipe === 'donor'): ?>
    <p>Periode: <?= tanggal_indo($dari) ?> s/d <?= tanggal_indo($sampai) ?></p>
    <?php endif; ?>
    <p style="font-size:9pt;color:#888">Dicetak: <?= $tgl_cetak ?> WIB</p>
</div>

<?php if ($tipe === 'donor'): ?>
<!-- Ringkasan Donor -->
<div class="ringkasan">
    <div class="stat-box">
        <div class="angka"><?= $stat['total'] ?></div>
        <div class="label">Total Pemeriksaan</div>
    </div>
    <div class="stat-box" style="border-color:#bbf7d0">
        <div class="angka" style="color:#15803d"><?= $stat['layak'] ?></div>
        <div class="label">Layak Donor</div>
    </div>
    <div class="stat-box" style="border-color:#fecaca">
        <div class="angka" style="color:#b91c1c"><?= $stat['tidak_layak'] ?></div>
        <div class="label">Tidak Layak</div>
    </div>
    <div class="stat-box" style="border-color:#fde68a">
        <div class="angka" style="color:#a16207"><?= $stat['ditunda'] ?></div>
        <div class="label">Ditunda</div>
    </div>
</div>

<!-- Tabel Donor -->
<table>
    <thead>
        <tr>
            <th>No</th><th>Nama Pendonor</th><th>Gol. Darah</th>
            <th>Tanggal Donor</th><th>Volume</th><th>Hb</th>
            <th>Tekanan</th><th>Hasil</th><th>Petugas</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($data)): ?>
    <tr><td colspan="9" style="text-align:center;color:#888;padding:20px">
        Tidak ada data pada periode ini
    </td></tr>
    <?php else: $no=1; foreach ($data as $r):
        $sym = $r['rhesus']==='Positif'?'+':'-';
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
        <td><strong><?= $r['golongan_darah'].$sym ?></strong></td>
        <td><?= tanggal_indo($r['tanggal_donor']) ?></td>
        <td><?= $r['volume_darah_ml'] ?> ml</td>
        <td><?= $r['hemoglobin'] ?? '-' ?></td>
        <td><?= htmlspecialchars($r['tekanan_darah'] ?: '-') ?></td>
        <td><span class="badge-<?= $r['hasil_pemeriksaan'] ?>">
            <?= ucfirst(str_replace('_',' ',$r['hasil_pemeriksaan'])) ?>
        </span></td>
        <td><?= htmlspecialchars($r['petugas'] ?? '-') ?></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<?php else: ?>
<!-- Ringkasan Stok -->
<div class="ringkasan" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-box" style="border-color:#bbf7d0">
        <div class="angka" style="color:#15803d"><?= $stat['tersedia'] ?></div>
        <div class="label">Kantong Tersedia</div>
    </div>
    <div class="stat-box" style="border-color:#fde68a">
        <div class="angka" style="color:#a16207"><?= $stat['kritis'] ?></div>
        <div class="label">Kantong Kritis</div>
    </div>
    <div class="stat-box" style="border-color:#fecaca">
        <div class="angka" style="color:#b91c1c"><?= $stat['habis'] ?></div>
        <div class="label">Kantong Habis</div>
    </div>
</div>

<!-- Tabel Stok -->
<table>
    <thead>
        <tr>
            <th>No</th><th>Golongan Darah</th><th>Rhesus</th>
            <th>Jenis Darah</th><th>Jumlah Kantong</th><th>Tanggal Masuk</th>
            <th>Kadaluarsa</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($data)): ?>
    <tr><td colspan="8" style="text-align:center;color:#888;padding:20px">
        Belum ada data stok
    </td></tr>
    <?php else: $no=1; foreach ($data as $s): 
        $jd = ($s['jenis_darah'] === 'PRC') ? '💉 PRC (250mL)' : '🩸 WB (450mL)';
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><strong><?= $s['golongan_darah'] ?></strong></td>
        <td><?= $s['rhesus'] ?></td>
        <td><?= $jd ?></td>
        <td><strong><?= $s['jumlah_kantong'] ?></strong> kantong</td>
        <td><?= tanggal_indo($s['tanggal_masuk'] ?? '') ?></td>
        <td><?= tanggal_indo($s['tanggal_kadaluarsa'] ?? '') ?></td>
        <td><span class="badge-<?= $s['status_stok'] ?>">
            <?= ucfirst($s['status_stok']) ?>
        </span></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="footer">
    <?= htmlspecialchars($nama_rs) ?> &copy; <?= date('Y') ?> —
    Dokumen ini digenerate otomatis oleh sistem SIDORAH
</div>

<script>
// Auto buka dialog print setelah halaman load
window.onload = function() {
    setTimeout(() => window.print(), 800);
};
</script>
</body>
</html>
    <?php
    exit();
}
?>