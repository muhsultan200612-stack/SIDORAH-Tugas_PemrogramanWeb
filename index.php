<?php
// Koneksi ke database untuk ambil data pengaturan
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dbsidorah');
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$koneksi->set_charset('utf8mb4');

// Ambil pengaturan RS
$setting = [];
$res = $koneksi->query("SELECT kunci, nilai FROM pengaturan");
if ($res) while ($r = $res->fetch_assoc()) $setting[$r['kunci']] = $r['nilai'];

$nama_rs   = $setting['nama_rs']        ?? 'RS SIDORAH';
$alamat_rs = $setting['alamat_rs']      ?? 'Jl. Kesehatan No.1, Makassar';
$telp_rs   = $setting['telp_rs']        ?? '0411-123-456';
$email_rs  = $setting['email_rs']       ?? 'info@sidorah.id';
$jam_ops   = $setting['jam_operasional'] ?? '24 Jam / 7 Hari';
$telp_darurat = $setting['telp_darurat'] ?? '119';

// Ambil stok darah untuk ditampilkan
$stok = [];
$res_stok = $koneksi->query("
    SELECT golongan_darah, rhesus,
           COALESCE(SUM(jumlah_kantong),0) as tersedia,
           CASE WHEN COALESCE(SUM(jumlah_kantong),0) <= 0 THEN 'habis'
                WHEN COALESCE(SUM(jumlah_kantong),0) <= 5 THEN 'kritis'
                ELSE 'tersedia' END as status
    FROM stok_darah WHERE status_stok != 'expired'
    GROUP BY golongan_darah, rhesus
    ORDER BY FIELD(golongan_darah,'A','B','AB','O'), rhesus DESC
");
if ($res_stok) while ($r = $res_stok->fetch_assoc()) {
    $sym = $r['rhesus']==='Positif'?'+':'-';
    $stok[$r['golongan_darah'].$sym] = $r;
}

// Ambil kegiatan donor mendatang
$kegiatan = $koneksi->query("
    SELECT nama_kegiatan, tanggal_kegiatan, waktu_mulai, waktu_selesai, lokasi,
           kuota_peserta, jumlah_terdaftar
    FROM kegiatan_donor
    WHERE status_kegiatan='aktif' AND tanggal_kegiatan >= CURDATE()
    ORDER BY tanggal_kegiatan ASC LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

function tgl_indo($tgl) {
    if (!$tgl) return '-';
    $b=['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
        '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
    $t=explode('-',$tgl);
    return (int)$t[2].' '.($b[$t[1]]??'').' '.$t[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Donor Darah | <?= htmlspecialchars($nama_rs) ?> - Selamatkan Nyawa</title>
    
    <!-- Font Awesome 6 (free) & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background-color: #fefaf9;
        }

        /* modern navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            padding: 0.8rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #C62828, #E53935);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent !important;
        }

        .navbar-brand i {
            background: none;
            -webkit-background-clip: unset;
            color: #E53935;
            margin-right: 6px;
        }

        .nav-link {
            font-weight: 600;
            color: #2d2f36 !important;
            margin: 0 0.3rem;
            transition: 0.2s;
        }

        .nav-link:hover {
            color: #e03a3a !important;
            transform: translateY(-2px);
        }

        .btn-donor-primary {
            background: linear-gradient(95deg, #e52d27, #b31217);
            border: none;
            border-radius: 40px;
            padding: 8px 28px;
            font-weight: 700;
            transition: all 0.3s;
            color: white;
            box-shadow: 0 8px 18px rgba(229, 45, 39, 0.25);
        }

        .btn-donor-primary:hover {
            transform: scale(1.03);
            background: linear-gradient(95deg, #c62828, #9a1c1c);
            box-shadow: 0 10px 22px rgba(197, 36, 36, 0.35);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid #e03a3a;
            background: transparent;
            border-radius: 40px;
            font-weight: 600;
            color: #e03a3a;
            transition: 0.25s;
        }

        .btn-outline-custom:hover {
            background: #e03a3a;
            color: white;
            transform: translateY(-2px);
        }

        /* Hero section with glass morphism */
        .hero-modern {
            min-height: 100vh;
            background: linear-gradient(120deg, #fef1ee 0%, #ffe6e2 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .hero-modern .blob {
            position: absolute;
            width: 55%;
            right: -10%;
            top: 20%;
            opacity: 0.15;
            pointer-events: none;
        }

        .hero-title {
            font-size: 4.2rem;
            font-weight: 800;
            line-height: 1.2;
            background: linear-gradient(to right, #b71c1c, #d32f2f, #ef5350);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-badge {
            background: rgba(211, 47, 47, 0.12);
            display: inline-block;
            padding: 6px 20px;
            border-radius: 60px;
            font-weight: 600;
            color: #b71c1c;
            margin-bottom: 1.2rem;
            backdrop-filter: blur(2px);
        }

        /* card feature modern */
        .feature-glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 40px;
            padding: 2rem 1.8rem;
            transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 35px -15px rgba(0,0,0,0.05);
        }

        .feature-glass-card:hover {
            transform: translateY(-12px);
            background: white;
            box-shadow: 0 30px 45px -18px rgba(220, 53, 69, 0.25);
            border-color: #ffccc7;
        }

        .icon-red-glow {
            width: 75px;
            height: 75px;
            background: linear-gradient(145deg, #fff0ee, #ffe0dc);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 30px;
            margin-bottom: 1.5rem;
            color: #c62828;
            font-size: 2.3rem;
            transition: 0.2s;
        }

        .section-subhead {
            color: #b91c1c;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
        }

        .stats-modern {
            background: #0f172a;
            color: white;
            border-radius: 60px;
            margin: 2rem 0;
        }

        .schedule-table th {
            background: #fceae8;
            border-bottom: 2px solid #e3342f;
        }

        .schedule-table td, .schedule-table th {
            padding: 1rem;
            vertical-align: middle;
        }

        .gallery-card img {
            border-radius: 32px;
            transition: all 0.4s;
            height: 280px;
            object-fit: cover;
            box-shadow: 0 15px 25px -12px rgba(0,0,0,0.15);
        }

        .gallery-card img:hover {
            transform: scale(1.02);
            box-shadow: 0 25px 30px -12px rgba(199, 44, 44, 0.3);
        }

        .footer-modern {
            background: #0c0e14;
            border-radius: 40px 40px 0 0;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            .navbar-brand {
                font-size: 1.4rem;
            }
            .stats-modern {
                border-radius: 30px;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR redesigned -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bolder" href="#">
            <i class="fas fa-hand-holding-heart"></i> <?= htmlspecialchars($nama_rs) ?>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-1">
                <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="#stok">Stok Darah</a></li>
                    <li class="nav-item"><a class="nav-link" href="#jadwal">Jadwal</a></li>
                <li class="nav-item"><a class="nav-link" href="#layanan">Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="#edukasi">Edukasi</a></li>
                <li class="nav-item"><a class="nav-link" href="#galeri">Galeri</a></li>
                <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <button class="btn btn-donor-primary" data-bs-toggle="modal" data-bs-target="#modalDonorSekarang"><i class="fas fa-tint me-1"></i> Donor Sekarang</button>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a class="btn btn-outline-custom" href="login.php"><i class="fas fa-user-circle"></i> Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-modern" id="home">
    <div class="container position-relative z-1 py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7" data-aos="fade-up" data-aos-duration="800">
                <div class="hero-badge">
                    <i class="fas fa-droplet me-1"></i> GERAKAN KEMANUSIAAN
                </div>
                <h1 class="hero-title mb-4">Setetes Darah Anda<br>Menyelamatkan <span style="color:#b71c1c;">Jiwa</span></h1>
                <p class="lead text-secondary mb-4">Menjadi pahlawan tanpa jubah dengan donor darah rutin. Kami hadir untuk memastikan ketersediaan darah bagi pasien yang membutuhkan di seluruh Indonesia.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#jadwal" class="btn btn-donor-primary px-4 py-3"><i class="fas fa-calendar-alt me-2"></i>Lihat Jadwal</a>
                    <a href="#kontak" class="btn btn-outline-custom px-4 py-3"><i class="fas fa-headset me-2"></i>Konsultasi</a>
                </div>
                <div class="mt-5 d-flex gap-4 flex-wrap">
                    <div><i class="fas fa-check-circle text-danger me-1"></i> <span class="fw-semibold">Terakreditasi</span></div>
                    <div><i class="fas fa-shield-alt text-danger me-1"></i> <span class="fw-semibold">100% Steril & Aman</span></div>
                    <div><i class="fas fa-clock text-danger me-1"></i> <span class="fw-semibold">Respon Cepat</span></div>
                </div>
            </div>
            <div class="col-lg-5 text-center" data-aos="fade-left" data-aos-duration="900">
                <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=500&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" alt="Donor Darah" class="img-fluid rounded-5 shadow-lg" style="max-width: 95%; border-radius: 3rem;">
            </div>
        </div>
    </div>
    <div class="blob">
        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="#E63946" d="M44.7,-76.4C58.9,-69.6,71.9,-56.7,80.8,-40.2C89.7,-23.7,94.4,-3.5,88.9,14.9C83.3,33.3,67.5,49.8,51.2,61.4C34.9,73,18.0,79.6,-0.5,80.5C-19,81.4,-38.1,76.5,-54.2,65.6C-70.3,54.7,-83.4,37.8,-86.4,18.9C-89.5,0,-82.5,-21,-71.3,-37.3C-60.1,-53.6,-44.8,-65.2,-28.8,-71.9C-12.8,-78.6,3.9,-80.3,20.3,-77.1C36.7,-73.9,44.7,-76.4,44.7,-76.4Z" transform="translate(100 100)" />
        </svg>
    </div>
</section>

<!-- Tentang Section reimagined -->
<section id="tentang" class="py-5">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1615461066841-6116e61058f4?w=600&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" alt="Tim Medis" class="img-fluid rounded-4 shadow-lg" style="border-radius: 3rem !important;">
                    <div class="bg-white p-3 shadow rounded-4 position-absolute bottom-0 start-0 translate-middle-y ms-3 d-none d-md-block" style="background: rgba(255,255,240,0.9); backdrop-filter: blur(4px);">
                        <i class="fas fa-heartbeat text-danger fs-3"></i>
                        <p class="mb-0 fw-bold">22+ Tahun Pelayanan</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="section-subhead"><i class="far fa-heart"></i> TENTANG KAMI</span>
                <h2 class="display-5 fw-bold mt-2 mb-4">Unit Donor Darah Terpercaya & Modern</h2>
                <p class="text-secondary">Rumah Sakit Kami berkomitmen penuh untuk menyediakan darah yang aman dan berkualitas tinggi. Didukung tenaga profesional, peralatan canggih, dan protokol kesehatan ketat.</p>
                <div class="row mt-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="fas fa-users fa-2x text-danger"></i>
                            <div><h3 class="fw-bold mb-0">7.200+</h3><small>Pendonor Aktif</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="fas fa-database fa-2x text-danger"></i>
                            <div><h3 class="fw-bold mb-0">2.150+</h3><small>Kantong Darah tersalur</small></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress mb-3" style="height: 10px; border-radius: 10px;">
                        <div class="progress-bar bg-danger" style="width: 86%" role="progressbar"></div>
                    </div>
                    <p class="fst-italic">"Melayani dengan hati, menyelamatkan dengan darah."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATISTIK DARAH - redesigned modern card -->
<section class="container my-4">
    <div class="stats-modern p-4 text-center">
        <div class="row py-3">
            <div class="col-md-3 col-6 mb-3"><h2 class="fw-bold display-6">A+</h2><p class="opacity-75">Tersedia</p></div>
            <div class="col-md-3 col-6 mb-3"><h2 class="fw-bold display-6">B+</h2><p class="opacity-75">Tersedia</p></div>
            <div class="col-md-3 col-6 mb-3"><h2 class="fw-bold display-6">O+</h2><p class="opacity-75">Stok Prioritas</p></div>
            <div class="col-md-3 col-6 mb-3"><h2 class="fw-bold display-6">AB+</h2><p class="opacity-75">Tersedia</p></div>
        </div>
    </div>
</section>

<!-- Layanan Premium -->
<section id="layanan" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-subhead"><i class="fas fa-hand-holding-medical"></i> LAYANAN UNGGULAN</span>
            <h2 class="display-5 fw-bold">Kami Hadir Untuk Kemanusiaan</h2>
            <p class="text-secondary w-75 mx-auto">Fasilitas terbaik dengan lingkungan nyaman untuk donor darah reguler & darurat.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="feature-glass-card text-center h-100">
                    <div class="icon-red-glow mx-auto"><i class="fas fa-syringe"></i></div>
                    <h4 class="fw-bold">Donor Darah Rutin</h4>
                    <p class="text-secondary">Pelayanan donor dengan metode terkini, cepat, dan minim rasa sakit.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="feature-glass-card text-center h-100">
                    <div class="icon-red-glow mx-auto"><i class="fas fa-chart-line"></i></div>
                    <h4 class="fw-bold">Cek Stok Darah 24/7</h4>
                    <p class="text-secondary">Informasi stok darah real-time untuk golongan A, B, O, AB melalui hotline kami.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="feature-glass-card text-center h-100">
                    <div class="icon-red-glow mx-auto"><i class="fas fa-mobile-alt"></i></div>
                    <h4 class="fw-bold">Mobile Donor Unit</h4>
                    <p class="text-secondary">Layanan donor keliling hingga ke perusahaan, kampus, atau komunitas Anda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JADWAL DONOR -->
<!-- Stok Darah Real-time -->
<section id="stok" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-subhead"><i class="fas fa-tint"></i> STOK DARAH</span>
            <h2 class="display-5 fw-bold">Ketersediaan Darah Real-Time</h2>
            <p class="text-muted">Data diperbarui otomatis setiap saat</p>
        </div>
        <div class="row g-3 justify-content-center" data-aos="fade-up">
            <?php
            $semua_goldar = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
            foreach ($semua_goldar as $gd):
                $s = $stok[$gd] ?? null;
                $kantong = $s ? (int)$s['tersedia'] : 0;
                $status  = $s ? $s['status'] : 'tidak-ada';
                $warna   = match($status) {
                    'tersedia'  => ['#d1fae5','#065f46','#bbf7d0'],
                    'kritis'    => ['#fef3c7','#92400e','#fde68a'],
                    'habis'     => ['#fee2e2','#991b1b','#fecaca'],
                    default     => ['#f3f4f6','#9ca3af','#e5e7eb']
                };
                $label = match($status) {
                    'tersedia'=>'Tersedia','kritis'=>'Kritis','habis'=>'Habis',default=>'–'
                };
            ?>
            <div class="col-6 col-md-3">
                <div class="card border-0 rounded-4 text-center p-3 h-100"
                     style="background:<?= $warna[0] ?>;border:2px solid <?= $warna[2] ?> !important">
                    <div style="font-size:2.2rem;font-weight:900;color:<?= $warna[1] ?>;line-height:1">
                        <?php
                        if (strlen($gd) > 2) {
                            echo substr($gd,0,2).'<span style="font-size:1.2rem">'.substr($gd,2).'</span>';
                        } else {
                            echo $gd[0].'<span style="font-size:1.2rem">'.substr($gd,1).'</span>';
                        }
                        ?>
                    </div>
                    <div style="font-size:1.5rem;font-weight:800;color:<?= $warna[1] ?>;margin:6px 0">
                        <?= $kantong ?>
                    </div>
                    <div style="font-size:0.75rem;color:<?= $warna[1] ?>">kantong</div>
                    <span class="badge mt-2 rounded-pill"
                          style="background:<?= $warna[2] ?>;color:<?= $warna[1] ?>;font-size:0.7rem">
                        <?= $label ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 text-muted small">
            <i class="fas fa-sync-alt text-danger me-1"></i>
            Update terakhir: <?= date('d/m/Y H:i') ?> WIB
            &nbsp;·&nbsp;
            <i class="fas fa-phone-alt text-danger me-1"></i>
            Butuh darah darurat? Hubungi: <strong><?= htmlspecialchars($telp_darurat) ?></strong>
        </div>
    </div>
</section>

<section id="jadwal" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-subhead"><i class="far fa-calendar-alt"></i> JADWAL DONOR</span>
            <h2 class="display-5 fw-bold">Kegiatan Donor Darah Mendatang</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <?php if (empty($kegiatan)): ?>
            <div class="col-12 text-center py-4 text-muted" data-aos="fade-up">
                <i class="fas fa-calendar-times fa-3x mb-3 text-danger opacity-50"></i>
                <p>Belum ada kegiatan donor darah yang dijadwalkan.</p>
                <a href="login.php" class="btn btn-danger rounded-pill mt-2">Daftar sebagai Pendonor</a>
            </div>
            <?php else: foreach ($kegiatan as $k):
                $sisa = $k['kuota_peserta'] - $k['jumlah_terdaftar'];
                $pct  = $k['kuota_peserta'] > 0 ? round($k['jumlah_terdaftar']/$k['kuota_peserta']*100) : 0;
                $badge_class = $sisa <= 0 ? 'bg-danger' : ($sisa <= 10 ? 'bg-warning text-dark' : 'bg-success');
                $badge_text  = $sisa <= 0 ? 'Penuh' : "$sisa Sisa Kuota";
            ?>
            <div class="col-md-4" data-aos="flip-up">
                <div class="card border-0 shadow-lg rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3">
                                <i class="fas fa-calendar-check me-1"></i><?= tgl_indo($k['tanggal_kegiatan']) ?>
                            </span>
                            <span class="badge <?= $badge_class ?> rounded-pill"><?= $badge_text ?></span>
                        </div>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($k['nama_kegiatan']) ?></h5>
                        <p class="text-muted small mb-1">
                            <i class="fas fa-clock text-danger me-1"></i>
                            <?= substr($k['waktu_mulai'],0,5) ?> – <?= substr($k['waktu_selesai'],0,5) ?> WIB
                        </p>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                            <?= htmlspecialchars($k['lokasi']) ?>
                        </p>
                        <div class="progress mb-2" style="height:6px;border-radius:10px">
                            <div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-3">
                            <span><?= $k['jumlah_terdaftar'] ?>/<?= $k['kuota_peserta'] ?> peserta</span>
                            <span><?= $pct ?>% terisi</span>
                        </div>
                        <?php if ($sisa > 0): ?>
                        <a href="login.php" class="btn btn-danger w-100 rounded-3 fw-semibold">
                            <i class="fas fa-user-plus me-1"></i> Daftar Sekarang
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary w-100 rounded-3" disabled>Kuota Penuh</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="mt-4 text-center">
            <i class="fas fa-info-circle text-danger"></i>
            <small class="text-muted"> Login terlebih dahulu untuk mendaftar kegiatan donor darah.</small>
        </div>
    </div>
</section>

<!-- Edukasi Section Modern cards -->
<section id="edukasi" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-subhead"><i class="fas fa-graduation-cap"></i> EDUKASI</span>
            <h2 class="display-5 fw-bold">Tanya Seputar Donor Darah</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-right">
                <div class="card h-100 border-0 shadow rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1516549655169-df83a0774514?w=500&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" class="card-img-top" style="height: 220px; object-fit: cover;">
                    <div class="card-body p-4">
                        <div class="badge bg-danger mb-2">Syarat & Ketentuan</div>
                        <h5 class="fw-bold">Syarat Menjadi Pendonor</h5>
                        <p class="text-secondary">Usia 17-65 tahun, berat badan minimal 45kg, sehat jasmani & rohani, tidak dalam kondisi anemia.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up">
                <div class="card h-100 border-0 shadow rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=500&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" class="card-img-top" style="height: 220px; object-fit: cover;">
                    <div class="card-body p-4">
                        <div class="badge bg-danger mb-2">Tips Sehat</div>
                        <h5 class="fw-bold">Persiapan Sebelum Donor</h5>
                        <p class="text-secondary">Istirahat cukup 5 jam, konsumsi makanan bergizi, perbanyak air putih hindari makanan berlemak.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-left">
                <div class="card h-100 border-0 shadow rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1584515933487-779824d29309?w=500&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" class="card-img-top" style="height: 220px; object-fit: cover;">
                    <div class="card-body p-4">
                        <div class="badge bg-danger mb-2">Manfaat</div>
                        <h5 class="fw-bold">Keajaiban Donor Darah</h5>
                        <p class="text-secondary">Menurunkan resiko penyakit jantung, meningkatkan produksi sel darah baru, dan pahala sosial besar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery premium -->
<section id="galeri" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="section-subhead"><i class="fas fa-images"></i> DOKUMENTASI</span>
            <h2 class="display-5 fw-bold">Momen Terbaru Donor Darah</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6 gallery-card" data-aos="flip-left"><img src="https://images.unsplash.com/photo-1579684453423-f84349ef60b0?w=600&auto=format&fit=crop" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" class="img-fluid w-100" onerror="this.src='https://placehold.co/600x400/dc3545/white?text=Donor+Darah'"></div>
            <div class="col-lg-4 col-md-6 gallery-card" data-aos="flip-left" data-aos-delay="100"><img src="https://images.unsplash.com/photo-1584982751601-97dcc096659c?w=600&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" class="img-fluid w-100"></div>
            <div class="col-lg-4 col-md-6 gallery-card" data-aos="flip-left" data-aos-delay="200"><img src="https://images.unsplash.com/photo-1628348070889-cb656235b4eb?w=600&auto=format" onerror="this.src=\'https://placehold.co/600x400/dc3545/white?text=SIDORAH\'" class="img-fluid w-100"></div>
        </div>
    </div>
</section>

<!-- Kontak redesigned -->
<section id="kontak" class="py-5">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <span class="section-subhead"><i class="fas fa-phone-alt"></i> HUBUNGI KAMI</span>
                <h2 class="display-5 fw-bold mb-4">Siap Membantu Anda</h2>
                <ul class="list-unstyled">
                    <li class="mb-4 d-flex align-items-center gap-3"><i class="fas fa-map-marker-alt fa-2x text-danger"></i> <span><?= htmlspecialchars($alamat_rs) ?></span></li>
                    <li class="mb-4 d-flex align-items-center gap-3"><i class="fas fa-phone-alt fa-2x text-danger"></i> <span><?= htmlspecialchars($telp_rs) ?> (<?= htmlspecialchars($jam_ops) ?>)</span></li>
                    <li class="mb-4 d-flex align-items-center gap-3"><i class="fas fa-envelope fa-2x text-danger"></i> <span><?= htmlspecialchars($email_rs) ?></span></li>
                </ul>
                <div class="mt-4 d-flex gap-3">
                    <a href="#" class="btn btn-outline-danger rounded-pill px-4"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="#" class="btn btn-outline-danger rounded-pill px-4"><i class="fab fa-instagram"></i> Instagram</a>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="card border-0 shadow-xl rounded-5 p-4">
                    <form>
                        <div class="mb-3"><input type="text" class="form-control form-control-lg rounded-4" placeholder="Nama Lengkap"></div>
                        <div class="mb-3"><input type="email" class="form-control form-control-lg rounded-4" placeholder="Email / No. HP"></div>
                        <div class="mb-3"><textarea rows="4" class="form-control rounded-4" placeholder="Pertanyaan atau permintaan jadwal donor..."></textarea></div>
                        <button class="btn btn-danger w-100 py-3 rounded-4 fw-bold"><i class="fas fa-paper-plane"></i> Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer Modern -->
<footer class="footer-modern pt-5 pb-4 mt-4">
    <div class="container">
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-6">
                <h4 class="fw-bold text-white"><i class="fas fa-hand-holding-heart text-danger me-2"></i><?= htmlspecialchars($nama_rs) ?></h4>
                <p class="text-white-50">Darah yang anda donorkan adalah harapan bagi mereka yang membutuhkan.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-white-50">© <?= date("Y") ?> <?= htmlspecialchars($nama_rs) ?> – Peduli Setetes Darah</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 900,
        once: true,
        offset: 50
    });
    
    // smooth scroll & navbar effect
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e){
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 30) navbar.style.boxShadow = "0 15px 35px rgba(0,0,0,0.08)";
        else navbar.style.boxShadow = "0 8px 30px rgba(0, 0, 0, 0.05)";
    });
</script>

<!-- Modal Donor Sekarang -->
<div class="modal fade" id="modalDonorSekarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">

            <!-- Header merah -->
            <div class="modal-header border-0 text-white py-4"
                 style="background:linear-gradient(135deg,#7b0d1e,#c0392b)">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.2);
                                display:flex;align-items:center;justify-content:center;font-size:1.4rem">
                        🩸
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Ayo Donor Darah!</h5>
                        <div style="font-size:0.85rem;opacity:0.85">Satu donor, tiga nyawa terselamatkan</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Info RS -->
                <div class="rounded-3 p-3 mb-4 text-center"
                     style="background:#fef2f2;border:1px solid #fecaca">
                    <div class="fw-bold text-danger fs-6 mb-1">
                        <?= htmlspecialchars($nama_rs) ?>
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <?= htmlspecialchars($alamat_rs) ?>
                    </div>
                    <div class="small text-muted mt-1">
                        <i class="fas fa-phone-alt text-danger me-1"></i>
                        <?= htmlspecialchars($telp_rs) ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-clock text-danger me-1"></i>
                        <?= htmlspecialchars($jam_ops) ?>
                    </div>
                </div>

                <!-- Himbauan -->
                <p class="text-center text-muted mb-4" style="font-size:0.95rem;line-height:1.6">
                    Untuk melakukan donor darah, silakan <strong>daftar langsung</strong>
                    ke <?= htmlspecialchars($nama_rs) ?> atau hubungi kami untuk informasi lebih lanjut.
                </p>

                <!-- Syarat singkat -->
                <div class="rounded-3 p-3 mb-4" style="background:#f8f9fa">
                    <div class="fw-semibold small mb-2 text-danger">
                        <i class="fas fa-clipboard-check me-1"></i> Syarat Umum Donor:
                    </div>
                    <div class="row g-1 small text-muted">
                        <div class="col-6">✅ Usia 17 – 65 tahun</div>
                        <div class="col-6">✅ BB minimal 45 kg</div>
                        <div class="col-6">✅ Hb minimal 12,5 g/dL</div>
                        <div class="col-6">✅ Sehat jasmani & rohani</div>
                        <div class="col-6">✅ Jeda minimal 3 bulan</div>
                        <div class="col-6">✅ Tekanan darah normal</div>
                    </div>
                </div>

                <!-- Tombol aksi -->
                <div class="d-flex gap-2">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $telp_rs) ?>"
                       class="btn btn-outline-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                        <i class="fas fa-phone-alt me-1"></i> Hubungi Kami
                    </a>
                    <a href="login.php"
                       class="btn btn-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                        <i class="fas fa-user-plus me-1"></i> Daftar Online
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>