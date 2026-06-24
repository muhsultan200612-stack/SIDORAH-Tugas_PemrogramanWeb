<?php
require_once 'koneksi.php';

if (empty($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'pendonor') {
    header('Location: dashboard.php');
    exit();
}// Ambil data pendonor yang login
$id_user = $_SESSION['id_pengguna'];
$pendonor = $koneksi->query("
    SELECT p.*, u.nama_lengkap, u.email, u.no_telepon
    FROM pendonor p
    JOIN users u ON p.id_pengguna = u.id_pengguna
    WHERE p.id_pengguna = $id_user
")->fetch_assoc();

if (!$pendonor) {
    // Data pendonor belum ada - tampilkan pesan, jangan redirect loop
    echo '<div style="font-family:sans-serif;padding:40px;text-align:center">';
    echo '<h2 style="color:#dc3545">⚠️ Data Pendonor Belum Lengkap</h2>';
    echo '<p>Akun kamu terdaftar sebagai pendonor, tapi data detail pendonor belum ada.</p>';
    echo '<p>Hubungi admin untuk melengkapi data.</p>';
    echo '<p><a href="logout.php">Logout</a></p>';
    echo '</div>';
    exit();
}

$id_pendonor = $pendonor['id_pendonor'];

$pesan = $_SESSION['pesan'] ?? null;
$tipe  = $_SESSION['tipe']  ?? null;
unset($_SESSION['pesan'], $_SESSION['tipe']);

// ── PROSES PENDAFTARAN KEGIATAN ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi_form'] ?? '';

    if ($aksi === 'daftar_kegiatan') {
        $id_kegiatan = (int)$_POST['id_kegiatan'];

        // Validasi: cek sudah daftar belum
        $cek = $koneksi->query("
            SELECT id_pendaftaran FROM pendaftaran
            WHERE id_pendonor=$id_pendonor AND id_kegiatan=$id_kegiatan
            LIMIT 1
        ");
        if ($cek->num_rows > 0) {
            $_SESSION['pesan'] = 'Kamu sudah terdaftar di kegiatan ini.';
            $_SESSION['tipe']  = 'warning';
        } else {
            // Cek kuota
            $kegiatan = $koneksi->query("
                SELECT nama_kegiatan, kuota_peserta, jumlah_terdaftar
                FROM kegiatan_donor WHERE id_kegiatan=$id_kegiatan
            ")->fetch_assoc();

            if ($kegiatan['jumlah_terdaftar'] >= $kegiatan['kuota_peserta']) {
                $_SESSION['pesan'] = 'Maaf, kuota kegiatan ini sudah penuh.';
                $_SESSION['tipe']  = 'danger';
            } else {
                $stmt = $koneksi->prepare("INSERT INTO pendaftaran (id_pendonor,id_kegiatan,status_pendaftaran) VALUES (?,?,'menunggu')");
                $stmt->bind_param('ii', $id_pendonor, $id_kegiatan);
                if ($stmt->execute()) {
                    $koneksi->query("UPDATE kegiatan_donor SET jumlah_terdaftar=jumlah_terdaftar+1 WHERE id_kegiatan=$id_kegiatan");
                    catat_log($koneksi,'DAFTAR_KEGIATAN','pendaftaran',"Pendonor {$pendonor['nama_lengkap']} daftar ke: {$kegiatan['nama_kegiatan']}");
                    $_SESSION['pesan'] = "Berhasil mendaftar ke kegiatan <strong>{$kegiatan['nama_kegiatan']}</strong>! Menunggu verifikasi admin.";
                    $_SESSION['tipe']  = 'success';
                }
                $stmt->close();
            }
        }
        header('Location: portal_pendonor.php');
        exit();
    }

    if ($aksi === 'batal_pendaftaran') {
        $id_pendaftaran = (int)$_POST['id_pendaftaran'];
        $id_kegiatan    = (int)$_POST['id_kegiatan'];
        $pend = $koneksi->query("SELECT status_pendaftaran FROM pendaftaran WHERE id_pendaftaran=$id_pendaftaran AND id_pendonor=$id_pendonor")->fetch_assoc();

        if ($pend && $pend['status_pendaftaran'] === 'menunggu') {
            $koneksi->query("UPDATE pendaftaran SET status_pendaftaran='batal' WHERE id_pendaftaran=$id_pendaftaran");
            $koneksi->query("UPDATE kegiatan_donor SET jumlah_terdaftar=GREATEST(0,jumlah_terdaftar-1) WHERE id_kegiatan=$id_kegiatan");
            catat_log($koneksi,'BATAL_PENDAFTARAN','pendaftaran',"Pendonor {$pendonor['nama_lengkap']} membatalkan pendaftaran");
            $_SESSION['pesan'] = 'Pendaftaran berhasil dibatalkan.';
            $_SESSION['tipe']  = 'info';
        }
        header('Location: portal_pendonor.php');
        exit();
    }
}

// ── NOTIFIKASI DARURAT ───────────────────────────────────────
$goldar_user = $pendonor['golongan_darah'];
$rhesus_user = $pendonor['rhesus'];
$notif_darurat = $koneksi->query("
    SELECT * FROM notifikasi_darurat
    WHERE status='aktif'
    AND (expired_at IS NULL OR expired_at > NOW())
    AND (golongan_darah='Semua' OR golongan_darah='$goldar_user' OR golongan_darah='')
    AND (rhesus='Semua' OR rhesus='$rhesus_user' OR rhesus='')
    ORDER BY FIELD(tingkat,'darurat','warning','info'), created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// ── DATA ──────────────────────────────────────────────────────
// Kegiatan aktif mendatang + status pendaftaran pendonor ini
$kegiatan_tersedia = $koneksi->query("
    SELECT k.*,
           pend.id_pendaftaran,
           pend.status_pendaftaran,
           (k.kuota_peserta - k.jumlah_terdaftar) as sisa_kuota
    FROM kegiatan_donor k
    LEFT JOIN pendaftaran pend ON k.id_kegiatan=pend.id_kegiatan
        AND pend.id_pendonor=$id_pendonor
    WHERE k.status_kegiatan='aktif'
    AND k.tanggal_kegiatan >= CURDATE()
    ORDER BY k.tanggal_kegiatan ASC
");

// Riwayat donor pribadi (5 terakhir)
$riwayat_pribadi = $koneksi->query("
    SELECT rd.*, k.nama_kegiatan
    FROM riwayat_donor rd
    LEFT JOIN pendaftaran pend ON rd.id_pendaftaran=pend.id_pendaftaran
    LEFT JOIN kegiatan_donor k ON pend.id_kegiatan=k.id_kegiatan
    WHERE rd.id_pendonor=$id_pendonor
    ORDER BY rd.tanggal_donor DESC
    LIMIT 5
");

// Pendaftaran aktif pendonor ini
$pendaftaran_aktif = $koneksi->query("
    SELECT pend.*, k.nama_kegiatan, k.tanggal_kegiatan, k.lokasi, k.waktu_mulai
    FROM pendaftaran pend
    JOIN kegiatan_donor k ON pend.id_kegiatan=k.id_kegiatan
    WHERE pend.id_pendonor=$id_pendonor
    AND pend.status_pendaftaran IN ('menunggu','disetujui')
    ORDER BY pend.tanggal_daftar DESC
");

// Total donor & info
$sym = $pendonor['rhesus']==='Positif'?'+':'-';
$hari_sejak_terakhir = null;
if ($pendonor['donor_terakhir']) {
    $diff = (new DateTime())->diff(new DateTime($pendonor['donor_terakhir']));
    $hari_sejak_terakhir = $diff->days;
}
$boleh_donor = $hari_sejak_terakhir === null || $hari_sejak_terakhir >= 90;
$sisa_hari   = $hari_sejak_terakhir !== null ? max(0, 90 - $hari_sejak_terakhir) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Pendonor — SIDORAH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --merah: #dc3545;
            --merah-muda: #fef2f2;
            --gelap: #1a1a2e;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fa;
        }
        /* Topnav */
        .topbar {
            background: var(--gelap);
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .brand { color: white; font-weight: 800; font-size: 1.2rem; text-decoration: none; }
        .topbar .brand i { color: var(--merah); }

        /* Hero Card */
        .hero-card {
            background: linear-gradient(135deg, #7b0d1e 0%, #c0392b 60%, #e74c3c 100%);
            border-radius: 20px;
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero-card::after {
            content: '🩸';
            position: absolute;
            right: 1.5rem; bottom: -10px;
            font-size: 5rem;
            opacity: 0.15;
        }
        .hero-card .goldar-big {
            font-size: 1.5rem; font-weight: 900;
            background: rgba(255,255,255,0.15);
            border-radius: 16px;
            width: 80px; height: 80px;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid rgba(255,255,255,0.3);
            letter-spacing: -0.5px;
        }
        .hero-card .stat-item { text-align: center; }
        .hero-card .stat-item .angka { font-size: 1.8rem; font-weight: 800; }
        .hero-card .stat-item .label { font-size: 0.75rem; opacity: 0.75; }

        /* Status donor -->*/
        .donor-status {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .donor-status.boleh   { background: #d1fae5; color: #065f46; }
        .donor-status.belum   { background: #fef3c7; color: #92400e; }
        .donor-status.pertama { background: #dbeafe; color: #1e40af; }

        /* Kegiatan Card */
        .kegiatan-item {
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding: 1.2rem;
            transition: all 0.2s;
            background: white;
        }
        .kegiatan-item:hover { border-color: var(--merah); box-shadow: 0 4px 16px rgba(220,53,69,0.1); }
        .kegiatan-item.sudah-daftar { border-color: #a7f3d0; background: #f0fdf4; }
        .kegiatan-item.penuh { opacity: 0.6; }

        /* Badge status pendaftaran */
        .badge-menunggu  { background:#fef3c7; color:#92400e; }
        .badge-disetujui { background:#d1fae5; color:#065f46; }
        .badge-ditolak   { background:#fee2e2; color:#991b1b; }
        .badge-batal     { background:#f3f4f6; color:#6b7280; }

        /* Progress bar */
        .progress { height: 6px; border-radius: 10px; }

        /* Riwayat */
        .riwayat-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .riwayat-item:last-child { border-bottom: none; }

        .hasil-layak      { color: #065f46; font-weight: 600; }
        .hasil-tidak_layak{ color: #991b1b; font-weight: 600; }
        .hasil-ditunda    { color: #92400e; font-weight: 600; }
    </style>
</head>
<body>

<!-- ── POPUP NOTIFIKASI DARURAT ── -->
<?php if (!empty($notif_darurat)): ?>
<div id="popupNotifDarurat" style="
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6); z-index:9999;
    display:flex; align-items:center; justify-content:center;
    padding:1rem; animation:fadeIn 0.3s ease;">
    <div style="
        background:white; border-radius:20px;
        max-width:520px; width:100%;
        box-shadow:0 20px 60px rgba(0,0,0,0.4);
        overflow:hidden; animation:slideUp 0.4s ease;">

        <!-- Header -->
        <div style="
            background:linear-gradient(135deg,#7b0d1e,#dc3545);
            padding:1.2rem 1.5rem;
            display:flex; align-items:center; gap:12px; color:white">
            <div style="font-size:2rem; animation:pulse 1s infinite">🚨</div>
            <div>
                <div style="font-weight:800;font-size:1rem">NOTIFIKASI DARURAT SIDORAH</div>
                <div style="font-size:0.8rem;opacity:0.85"><?= count($notif_darurat) ?> pengumuman penting untukmu</div>
            </div>
            <button onclick="tutupPopup()" style="
                margin-left:auto; background:rgba(255,255,255,0.2);
                border:none; color:white; width:30px; height:30px;
                border-radius:50%; cursor:pointer; font-size:1rem;
                display:flex; align-items:center; justify-content:center">
                ✕
            </button>
        </div>

        <!-- Isi Notifikasi -->
        <div style="max-height:60vh; overflow-y:auto; padding:1.2rem 1.5rem;">
            <?php foreach ($notif_darurat as $i => $nd):
                $warna_bg = match($nd['tingkat']) {
                    'darurat' => '#fef2f2','warning' => '#fffbeb',default => '#eff6ff'
                };
                $warna_border = match($nd['tingkat']) {
                    'darurat' => '#dc3545','warning' => '#d97706',default => '#3b82f6'
                };
                $icon = match($nd['tingkat']) {
                    'darurat'=>'🚨','warning'=>'⚠️',default=>'ℹ️'
                };
            ?>
            <div style="
                background:<?= $warna_bg ?>;
                border-left:4px solid <?= $warna_border ?>;
                border-radius:10px; padding:1rem;
                margin-bottom:<?= $i<count($notif_darurat)-1?'1rem':'0' ?>">
                <div style="font-weight:700;color:<?= $warna_border ?>;margin-bottom:4px">
                    <?= $icon ?> <?= htmlspecialchars($nd['judul']) ?>
                </div>
                <div style="color:#374151;font-size:0.9rem;line-height:1.5">
                    <?= htmlspecialchars($nd['pesan']) ?>
                </div>
                <?php if ($nd['golongan_darah'] !== 'Semua'): ?>
                <div style="margin-top:8px;font-size:0.78rem;color:<?= $warna_border ?>">
                    🩸 Golongan darah yang dibutuhkan:
                    <strong><?= $nd['golongan_darah'] ?><?= $nd['rhesus']==='Semua'?'±':($nd['rhesus']==='Positif'?'+':'-') ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($nd['id_kegiatan']): ?>
                <div style="margin-top:8px">
                    <a href="portal_pendonor.php#kegiatan" onclick="tutupPopup()"
                       style="background:<?= $warna_border ?>;color:white;
                              text-decoration:none;padding:4px 12px;
                              border-radius:20px;font-size:0.8rem;font-weight:600">
                        📅 Lihat Kegiatan Donor
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div style="padding:1rem 1.5rem;border-top:1px solid #f3f4f6;
                    display:flex;gap:10px;justify-content:flex-end">
            <button onclick="tutupPopup()" style="
                background:#f3f4f6;border:none;border-radius:10px;
                padding:8px 20px;font-weight:600;cursor:pointer;color:#374151">
                Nanti saja
            </button>
            <button onclick="tutupPopup()" style="
                background:#dc3545;border:none;border-radius:10px;
                padding:8px 20px;font-weight:600;cursor:pointer;color:white">
                Siap, saya mengerti!
            </button>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
@keyframes slideUp { from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }
@keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.2)} }
</style>
<script>
function tutupPopup() {
    document.getElementById('popupNotifDarurat').style.animation = 'fadeIn 0.2s ease reverse';
    setTimeout(() => document.getElementById('popupNotifDarurat').style.display = 'none', 200);
}
</script>
<?php endif; ?>

<!-- Topbar -->
<div class="topbar">
    <a href="portal_pendonor.php" class="brand">
        <i class="bi bi-heart-pulse-fill"></i> SIDORAH
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-white-50 small d-none d-md-inline">
            <?= date('l, d F Y') ?>
        </span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2"
                    data-bs-toggle="dropdown">
                <div style="width:28px;height:28px;border-radius:50%;background:var(--merah);
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:0.75rem;color:white">
                    <?= strtoupper(substr($pendonor['nama_lengkap'],0,1)) ?>
                </div>
                <span class="d-none d-md-inline"><?= htmlspecialchars(explode(' ',$pendonor['nama_lengkap'])[0]) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header"><?= htmlspecialchars($pendonor['nama_lengkap']) ?></h6></li>
                <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($pendonor['email']) ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger fw-semibold" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container py-4" style="max-width:900px">

    <!-- Alert -->
    <?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe ?> alert-dismissible fade show rounded-3">
        <?= $pesan ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Hero Card -->
    <div class="hero-card mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="goldar-big">
                <?= $pendonor['golongan_darah'] ?><?= $sym ?>
            </div>
            <div>
                <div class="fw-bold fs-5">Halo, <?= htmlspecialchars(explode(' ',$pendonor['nama_lengkap'])[0]) ?>! 👋</div>
                <div style="opacity:0.8;font-size:0.9rem">Terima kasih sudah menjadi pendonor darah</div>
                <div style="opacity:0.7;font-size:0.8rem"><?= htmlspecialchars($pendonor['email']) ?></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="hero-card stat-item" style="background:rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem">
                    <div class="angka"><?= $pendonor['total_donor'] ?></div>
                    <div class="label">Total Donor</div>
                </div>
            </div>
            <div class="col-4">
                <div class="hero-card stat-item" style="background:rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem">
                    <div class="angka" style="font-size:1rem;padding-top:6px"><?= $pendonor['donor_terakhir'] ? tanggal_indo($pendonor['donor_terakhir']) : 'Belum pernah' ?></div>
                    <div class="label">Donor Terakhir</div>
                </div>
            </div>
            <div class="col-4">
                <div class="hero-card stat-item" style="background:rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem">
                    <div class="angka"><?= $pendonor['berat_badan'] ? $pendonor['berat_badan'].' kg' : '-' ?></div>
                    <div class="label">Berat Badan</div>
                </div>
            </div>
        </div>

        <!-- Tombol Edit Profil -->
        <a href="profil_pendonor.php" class="btn btn-sm mt-2"
           style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);border-radius:20px">
            <i class="bi bi-person-gear me-1"></i> Edit Profil & Ganti Password
        </a>

        <!-- Status boleh donor -->
        <?php if ($pendonor['donor_terakhir']): ?>
        <div class="donor-status <?= $boleh_donor ? 'boleh' : 'belum' ?>">
            <?php if ($boleh_donor): ?>
            <i class="bi bi-check-circle-fill me-1"></i>
            Kamu <strong>sudah boleh</strong> donor lagi! Donor terakhir <?= $hari_sejak_terakhir ?> hari lalu.
            <?php else: ?>
            <i class="bi bi-clock-fill me-1"></i>
            Belum bisa donor. Minimal 90 hari antar donor. Sisa <?= $sisa_hari ?> hari lagi.
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="donor-status pertama">
            <i class="bi bi-info-circle-fill me-1"></i>
            Kamu belum pernah donor. Ayo daftar ke kegiatan donor di bawah!
        </div>
        <?php endif; ?>
    </div>

    <!-- Kartu Pendonor Digital + QR Code -->
    <?php
    // Ambil IP dari pengaturan
    $res_ip    = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='ip_server' LIMIT 1");
    $ip_setting = ($res_ip && $res_ip->num_rows > 0) ? trim($res_ip->fetch_assoc()['nilai']) : '';

    if (!empty($ip_setting) && $ip_setting !== '::1' && $ip_setting !== '127.0.0.1') {
        // Pakai IP dari pengaturan
        $server_ip = $ip_setting;
    } else {
        // Fallback: ambil dari HTTP_HOST (sudah berisi IP benar saat diakses via IP)
        $http_host = $_SERVER['HTTP_HOST'] ?? '';
        $host_ip   = explode(':', $http_host)[0]; // hapus port jika ada
        if (!empty($host_ip) && $host_ip !== 'localhost' && $host_ip !== '::1') {
            $server_ip = $host_ip;
        } else {
            $server_ip = '10.99.133.126'; // fallback hardcode
        }
    }
    $server_port = $_SERVER['SERVER_PORT'] ?? '80';
    $port_str    = ($server_port == '80') ? '' : ":$server_port";
    $qr_url      = "http://{$server_ip}{$port_str}/sidorah/verifikasi_pendonor.php?id={$id_pendonor}";
    $qr_id_pad   = str_pad($id_pendonor, 4, '0', STR_PAD_LEFT);
    $qr_img_url  = "https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=".urlencode($qr_url);
    ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:linear-gradient(135deg,#1a1a2e,#2d2d44);color:white;overflow:hidden">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">

                <!-- QR Code -->
                <div class="col-auto">
                    <div style="background:white;padding:6px;border-radius:12px;width:90px;height:90px;
                                display:flex;align-items:center;justify-content:center;position:relative">
                        <img id="qr_img_<?= $id_pendonor ?>"
                             src="<?= $qr_img_url ?>"
                             alt="QR Code" width="78" height="78" style="border-radius:4px"
                             onerror="this.style.display='none';document.getElementById('qr_off_<?= $id_pendonor ?>').style.display='flex'">
                        <div id="qr_off_<?= $id_pendonor ?>" style="display:none;flex-direction:column;
                             align-items:center;justify-content:center;width:78px;height:78px;text-align:center">
                            <i class="bi bi-qr-code" style="font-size:2rem;color:#374151"></i>
                            <div style="font-size:7px;color:#6b7280;font-family:monospace;margin-top:2px">
                                <?= $qr_id_pad ?>
                            </div>
                            <div style="font-size:6px;color:#9ca3af">Offline</div>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="col">
                    <div style="font-size:0.72rem;opacity:0.6;letter-spacing:1px;margin-bottom:3px">
                        KARTU PENDONOR DIGITAL
                    </div>
                    <div class="fw-bold fs-6"><?= htmlspecialchars($pendonor['nama_lengkap']) ?></div>
                    <div style="font-size:0.85rem;opacity:0.8">
                        Gol. Darah: <strong><?= $pendonor['golongan_darah'].$sym ?></strong>
                        &nbsp;·&nbsp;
                        <?= $pendonor['total_donor'] ?> kali donor
                    </div>
                    <div style="font-family:monospace;font-size:0.8rem;opacity:0.6;margin-top:4px">
                        SIDORAH-<?= $qr_id_pad ?>
                    </div>
                </div>

                <!-- 2 Cara Verifikasi -->
                <div class="col-12">
                    <div style="background:rgba(255,255,255,0.08);border-radius:12px;padding:10px 14px">
                        <div style="font-size:0.72rem;opacity:0.7;margin-bottom:8px;font-weight:600">
                            Verifikasi ke Petugas — 2 Cara:
                        </div>
                        <div class="row g-2">
                            <!-- Cara 1: Scan QR -->
                            <div class="col-6">
                                <div style="background:rgba(255,255,255,0.1);border-radius:10px;padding:8px 10px">
                                    <div style="font-size:0.75rem;font-weight:600;margin-bottom:3px">
                                        📷 Cara 1 — Scan QR
                                    </div>
                                    <div style="font-size:0.72rem;opacity:0.75">
                                        Petugas arahkan kamera HP ke gambar QR di atas
                                    </div>

                                </div>
                            </div>
                            <!-- Cara 2: Tap Link -->
                            <div class="col-6">
                                <div style="background:rgba(255,255,255,0.1);border-radius:10px;padding:8px 10px">
                                    <div style="font-size:0.75rem;font-weight:600;margin-bottom:3px">
                                        🔗 Cara 2 — Tap Link
                                    </div>
                                    <a href="<?= $qr_url ?>" target="_blank"
                                       style="color:#93c5fd;font-size:0.68rem;
                                              word-break:break-all;text-decoration:underline">
                                        <?= $qr_url ?>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Pendaftaran Aktif -->
    <?php if ($pendaftaran_aktif && $pendaftaran_aktif->num_rows > 0): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-clipboard-check text-danger me-2"></i>Pendaftaranmu</h6>
            <small class="text-muted">Status pendaftaran yang sedang aktif</small>
        </div>
        <div class="card-body px-4 py-3">
            <?php while ($pend = $pendaftaran_aktif->fetch_assoc()): ?>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div>
                    <div class="fw-semibold"><?= htmlspecialchars($pend['nama_kegiatan']) ?></div>
                    <div class="small text-muted">
                        <i class="bi bi-calendar3 me-1"></i><?= tanggal_indo($pend['tanggal_kegiatan']) ?>
                        &nbsp;·&nbsp;
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($pend['lokasi']) ?>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-<?= $pend['status_pendaftaran'] ?> rounded-pill px-3">
                        <?php
                        $icon = match($pend['status_pendaftaran']) {
                            'menunggu'  => '⏳',
                            'disetujui' => '✅',
                            default     => '—'
                        };
                        echo $icon . ' ' . ucfirst($pend['status_pendaftaran']);
                        ?>
                    </span>
                    <?php if ($pend['status_pendaftaran'] === 'menunggu'): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="aksi_form" value="batal_pendaftaran">
                        <input type="hidden" name="id_pendaftaran" value="<?= $pend['id_pendaftaran'] ?>">
                        <input type="hidden" name="id_kegiatan" value="<?= $pend['id_kegiatan'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"
                            onclick="return confirm('Batalkan pendaftaran ini?')">
                            Batal
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Kegiatan Donor Tersedia -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-calendar-event-fill text-danger me-2"></i>Kegiatan Donor Tersedia</h6>
            <small class="text-muted">Daftar sekarang untuk ikut kegiatan donor darah</small>
        </div>
        <div class="card-body px-4 py-3">
            <?php if ($kegiatan_tersedia->num_rows === 0): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                Belum ada kegiatan donor yang tersedia saat ini.
            </div>
            <?php else:
                while ($k = $kegiatan_tersedia->fetch_assoc()):
                $persen = $k['kuota_peserta'] > 0 ? round(($k['jumlah_terdaftar']/$k['kuota_peserta'])*100) : 0;
                $bar = $persen >= 90 ? 'bg-danger' : ($persen >= 60 ? 'bg-warning' : 'bg-success');
                $sudah_daftar = !empty($k['id_pendaftaran']) && !in_array($k['status_pendaftaran'],['batal','ditolak']);
                $penuh = $k['sisa_kuota'] <= 0;
            ?>
            <div class="kegiatan-item mb-3 <?= $sudah_daftar?'sudah-daftar':'' ?> <?= $penuh&&!$sudah_daftar?'penuh':'' ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($k['nama_kegiatan']) ?></div>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-calendar3 me-1 text-danger"></i>
                            <?= tanggal_indo($k['tanggal_kegiatan']) ?>
                            &nbsp;·&nbsp;
                            <i class="bi bi-clock me-1 text-danger"></i>
                            <?= substr($k['waktu_mulai'],0,5) ?>–<?= substr($k['waktu_selesai'],0,5) ?>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>
                            <?= htmlspecialchars($k['lokasi']) ?>
                        </div>
                    </div>
                    <div class="ms-3 flex-shrink-0">
                        <?php if ($sudah_daftar): ?>
                            <span class="badge badge-<?= $k['status_pendaftaran'] ?> rounded-pill px-3">
                                <?= $k['status_pendaftaran']==='menunggu'?'⏳ Menunggu':'✅ Terdaftar' ?>
                            </span>
                        <?php elseif ($penuh): ?>
                            <span class="badge bg-secondary rounded-pill px-3">Penuh</span>
                        <?php elseif (!$boleh_donor && $pendonor['donor_terakhir']): ?>
                            <span class="badge bg-warning text-dark rounded-pill px-3">Belum bisa donor</span>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="aksi_form" value="daftar_kegiatan">
                                <input type="hidden" name="id_kegiatan" value="<?= $k['id_kegiatan'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                    <i class="bi bi-plus-circle me-1"></i>Daftar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kuota progress -->
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span><?= $k['jumlah_terdaftar'] ?>/<?= $k['kuota_peserta'] ?> peserta</span>
                    <span><?= $k['sisa_kuota'] > 0 ? $k['sisa_kuota'].' tempat tersisa' : 'Penuh' ?></span>
                </div>
                <div class="progress">
                    <div class="progress-bar <?= $bar ?>" style="width:<?= $persen ?>%"></div>
                </div>

                <!-- Persyaratan -->
                <?php if ($k['persyaratan']): ?>
                <div class="mt-2 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    <?= htmlspecialchars(substr($k['persyaratan'],0,120)) ?>
                    <?= strlen($k['persyaratan'])>120?'...':'' ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>

    <!-- Riwayat Donor Pribadi -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-danger me-2"></i>Riwayat Donorku</h6>
            <small class="text-muted">5 riwayat donor terakhir</small>
        </div>
        <div class="card-body px-4 py-3">
            <?php if ($riwayat_pribadi->num_rows === 0): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-droplet fs-2 d-block mb-2"></i>
                Belum ada riwayat donor. Yuk daftar ke kegiatan donor!
            </div>
            <?php else:
                while ($r = $riwayat_pribadi->fetch_assoc()):
                $hasil_icon = match($r['hasil_pemeriksaan']) {
                    'layak'       => '<i class="bi bi-check-circle-fill text-success"></i>',
                    'tidak_layak' => '<i class="bi bi-x-circle-fill text-danger"></i>',
                    'ditunda'     => '<i class="bi bi-pause-circle-fill text-warning"></i>',
                    default       => ''
                };
            ?>
            <div class="riwayat-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <?= $hasil_icon ?>
                    <div>
                        <div class="fw-semibold small"><?= tanggal_indo($r['tanggal_donor']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem">
                            <?= $r['volume_darah_ml'] ?> ml
                            <?php if ($r['hemoglobin']): ?> · Hb <?= $r['hemoglobin'] ?> g/dL<?php endif; ?>
                            <?php if ($r['nama_kegiatan']): ?> · <?= htmlspecialchars($r['nama_kegiatan']) ?><?php endif; ?>
                        </div>
                    </div>
                </div>
                <span class="hasil-<?= $r['hasil_pemeriksaan'] ?> small">
                    <?= ucfirst(str_replace('_',' ',$r['hasil_pemeriksaan'])) ?>
                </span>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>

    <!-- Info Syarat Donor -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:linear-gradient(135deg,#fff5f5,#fef2f2);">
        <div class="card-body px-4 py-3">
            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-shield-check me-2"></i>Syarat Donor Darah</h6>
            <div class="row g-2 small">
                <?php
                $syarat = [
                    ['bi-calendar-check','Usia 17–65 tahun'],
                    ['bi-person-fill','Berat badan minimal 45 kg'],
                    ['bi-heart-pulse-fill','Tekanan darah normal (100-160/70-100 mmHg)'],
                    ['bi-droplet-fill','Kadar Hb minimal 12,5 g/dL'],
                    ['bi-clock-history','Jeda minimal 3 bulan antar donor'],
                    ['bi-emoji-smile-fill','Sehat jasmani dan rohani'],
                ];
                foreach ($syarat as $s): ?>
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <i class="bi <?= $s[0] ?> text-danger flex-shrink-0"></i>
                    <span><?= $s[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>