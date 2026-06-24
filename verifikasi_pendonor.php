<?php
/**
 * SIDORAH - verifikasi_pendonor.php
 * Halaman verifikasi pendonor via QR Code scan
 * Bisa diakses tanpa login (untuk petugas yang scan QR)
 */

require_once 'koneksi.php';

$id = (int)($_GET['id'] ?? 0);
$pendonor = null;
$riwayat  = [];
$error    = '';

if ($id <= 0) {
    $error = 'QR Code tidak valid.';
} else {
    $pendonor = $koneksi->query("
        SELECT p.*, u.nama_lengkap, u.email, u.no_telepon, u.status_akun
        FROM pendonor p
        JOIN users u ON p.id_pengguna = u.id_pengguna
        WHERE p.id_pendonor = $id AND p.status_aktif = 1
    ")->fetch_assoc();

    if (!$pendonor) {
        $error = 'Pendonor tidak ditemukan atau tidak aktif.';
    } else {
        // Riwayat 3 terakhir
        $riwayat = $koneksi->query("
            SELECT tanggal_donor, hasil_pemeriksaan, hemoglobin, volume_darah_ml
            FROM riwayat_donor
            WHERE id_pendonor = $id
            ORDER BY tanggal_donor DESC
            LIMIT 3
        ")->fetch_all(MYSQLI_ASSOC);
    }
}

// tanggal_indo() sudah tersedia dari koneksi.php
// Alias agar tidak perlu ganti nama di seluruh file
function tanggal_indo_v($tgl) { return tanggal_indo($tgl); }

// Cek status boleh donor (standar PMI Indonesia: jeda minimal 90 hari / 3 bulan)
$boleh_donor  = true;
$pesan_status = '';
if ($pendonor) {
    if ($pendonor['donor_terakhir']) {
        $diff      = (new DateTime())->diff(new DateTime($pendonor['donor_terakhir']));
        $hari_lalu = $diff->days;
        if ($hari_lalu < 90) {
            $boleh_donor  = false;
            $sisa         = 90 - $hari_lalu;
            $pesan_status = "Belum bisa donor. Sisa $sisa hari lagi.";
        } else {
            $pesan_status = "Sudah boleh donor kembali (jeda {$hari_lalu} hari).";
        }
    } else {
        $pesan_status = "Belum pernah donor sebelumnya.";
    }
}

$sym = $pendonor ? ($pendonor['rhesus']==='Positif'?'+':'-') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Verifikasi Pendonor — SIDORAH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }
        .topbar {
            background: #1a1a2e;
            padding: 0.8rem 1.2rem;
            display: flex; align-items: center; gap: 8px;
        }
        .topbar .brand { color: white; font-weight: 800; font-size: 1rem; text-decoration: none; }
        .topbar .brand i { color: #dc3545; }

        .status-boleh {
            background: linear-gradient(135deg, #065f46, #059669);
            color: white; border-radius: 16px; padding: 1rem 1.2rem;
        }
        .status-belum {
            background: linear-gradient(135deg, #92400e, #d97706);
            color: white; border-radius: 16px; padding: 1rem 1.2rem;
        }
        .goldar-badge {
            width: 70px; height: 70px; border-radius: 50%;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 900; color: white;
            flex-shrink: 0;
        }
        .info-row {
            display: flex; justify-content: space-between;
            align-items: center; padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }
        .info-row:last-child { border-bottom: none; }
        .card-custom { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .badge-layak      { background: #d1fae5; color: #065f46; }
        .badge-tidak_layak{ background: #fee2e2; color: #991b1b; }
        .badge-ditunda    { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <a href="login.php" class="brand">
        <i class="bi bi-heart-pulse-fill"></i> SIDORAH
    </a>
    <span class="ms-auto text-white-50 small">
        <i class="bi bi-qr-code me-1"></i>Verifikasi Pendonor
    </span>
</div>

<div class="container py-4" style="max-width: 480px;">

    <?php if ($error): ?>
    <!-- Error -->
    <div class="card-custom bg-white p-4 text-center">
        <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
        <h5 class="fw-bold text-danger"><?= $error ?></h5>
        <p class="text-muted small">QR Code mungkin rusak atau pendonor tidak terdaftar.</p>
        <a href="login.php" class="btn btn-danger mt-2">Kembali ke Login</a>
    </div>

    <?php else: ?>

    <!-- Status Donor -->
    <div class="<?= $boleh_donor ? 'status-boleh' : 'status-belum' ?> mb-3">
        <div class="d-flex align-items-center gap-3">
            <div style="font-size: 2.5rem;">
                <?= $boleh_donor ? '✅' : '⏸️' ?>
            </div>
            <div>
                <div class="fw-bold fs-6">
                    <?= $boleh_donor ? 'BOLEH DONOR' : 'BELUM BISA DONOR' ?>
                </div>
                <div style="font-size: 0.85rem; opacity: 0.9">
                    <?= $pesan_status ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Identitas Pendonor -->
    <div class="card-custom bg-white p-4 mb-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="goldar-badge">
                <?= $pendonor['golongan_darah'].$sym ?>
            </div>
            <div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($pendonor['nama_lengkap']) ?></h5>
                <div class="text-muted small"><?= htmlspecialchars($pendonor['email']) ?></div>
                <div class="text-muted small">
                    <i class="bi bi-telephone me-1"></i>
                    <?= htmlspecialchars($pendonor['no_telepon'] ?: '-') ?>
                </div>
            </div>
        </div>

        <div class="info-row">
            <span class="text-muted">ID Pendonor</span>
            <span class="fw-semibold font-monospace">SIDORAH-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="info-row">
            <span class="text-muted">Golongan Darah</span>
            <span class="fw-bold text-danger"><?= $pendonor['golongan_darah'].$sym ?></span>
        </div>
        <div class="info-row">
            <span class="text-muted">Jenis Kelamin</span>
            <span class="fw-semibold">
                <?= $pendonor['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
            </span>
        </div>
        <?php if ($pendonor['tanggal_lahir']): ?>
        <div class="info-row">
            <span class="text-muted">Tanggal Lahir</span>
            <span class="fw-semibold"><?= tanggal_indo_v($pendonor['tanggal_lahir']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($pendonor['berat_badan']): ?>
        <div class="info-row">
            <span class="text-muted">Berat Badan</span>
            <span class="fw-semibold"><?= $pendonor['berat_badan'] ?> kg</span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="text-muted">Total Donor</span>
            <span class="fw-bold text-danger">
                <i class="bi bi-droplet-fill me-1"></i><?= $pendonor['total_donor'] ?> kali
            </span>
        </div>
        <div class="info-row">
            <span class="text-muted">Donor Terakhir</span>
            <span class="fw-semibold">
                <?= $pendonor['donor_terakhir'] ? tanggal_indo_v($pendonor['donor_terakhir']) : 'Belum pernah' ?>
            </span>
        </div>
        <?php if ($pendonor['riwayat_penyakit']): ?>
        <div class="info-row">
            <span class="text-muted">Riwayat Penyakit</span>
            <span class="fw-semibold text-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= htmlspecialchars($pendonor['riwayat_penyakit']) ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Riwayat Donor -->
    <?php if (!empty($riwayat)): ?>
    <div class="card-custom bg-white p-4 mb-3">
        <h6 class="fw-bold mb-3">
            <i class="bi bi-clock-history text-danger me-2"></i>Riwayat Donor Terakhir
        </h6>
        <?php foreach ($riwayat as $r):
            $hasil_class = 'badge-'.$r['hasil_pemeriksaan'];
            $hasil_icon  = match($r['hasil_pemeriksaan']) {
                'layak'=>'✅','tidak_layak'=>'❌','ditunda'=>'⏸️',default=>''
            };
        ?>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <div>
                <div class="fw-semibold small"><?= tanggal_indo_v($r['tanggal_donor']) ?></div>
                <div class="text-muted" style="font-size:0.78rem">
                    <?= $r['volume_darah_ml'] ?> ml
                    <?= $r['hemoglobin'] ? ' · Hb '.$r['hemoglobin'].' g/dL' : '' ?>
                </div>
            </div>
            <span class="badge <?= $hasil_class ?> rounded-pill px-3">
                <?= $hasil_icon ?> <?= ucfirst(str_replace('_',' ',$r['hasil_pemeriksaan'])) ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Timestamp verifikasi -->
    <div class="text-center text-muted small">
        <i class="bi bi-clock me-1"></i>
        Diverifikasi: <?= date('d/m/Y H:i:s') ?> WIB
    </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>