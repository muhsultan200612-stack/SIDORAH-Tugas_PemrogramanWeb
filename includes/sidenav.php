<?php
/**
 * SIDORAH - includes/sidenav.php
 * Sidebar navigasi — menu tampil sesuai role pengguna
 */
$halaman_aktif = basename($_SERVER['PHP_SELF']);
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <!-- ── MENU UTAMA ── -->
                <div class="sb-sidenav-menu-heading">Utama</div>

                <a class="nav-link <?= $halaman_aktif==='dashboard.php'?'active':'' ?>"
                   href="dashboard.php">
                    <div class="sb-nav-link-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    Dashboard
                </a>

                <!-- ── DONOR ── -->
                <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS])): ?>
                <div class="sb-sidenav-menu-heading">Donor Darah</div>

                <a class="nav-link <?= $halaman_aktif==='pendonor.php'?'active':'' ?>"
                   href="pendonor.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-droplet-fill"></i></div>
                    Data Pendonor
                </a>

                <a class="nav-link <?= $halaman_aktif==='kegiatan_donor.php'?'active':'' ?>"
                   href="kegiatan_donor.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-calendar-event-fill"></i></div>
                    Kegiatan Donor
                </a>

                <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                <a class="nav-link <?= $halaman_aktif==='kelola_notifikasi.php'?'active':'' ?>"
                   href="kelola_notifikasi.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-megaphone-fill"></i></div>
                    Notifikasi Darurat
                    <?php
                    $notif_aktif = $koneksi->query("SELECT COUNT(*) as n FROM notifikasi_darurat WHERE status='aktif' AND (expired_at IS NULL OR expired_at > NOW())")->fetch_assoc()['n'];
                    if ($notif_aktif > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $notif_aktif ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN])): ?>
                <a class="nav-link <?= $halaman_aktif==='pendaftaran.php'?'active':'' ?>"
                   href="pendaftaran.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-person-check-fill"></i></div>
                    Pendaftaran
                </a>
                <?php endif; ?>

                <a class="nav-link <?= $halaman_aktif==='riwayat_donor.php'?'active':'' ?>"
                   href="riwayat_donor.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-clock-history"></i></div>
                    Riwayat Donor
                </a>
                <?php endif; ?>

                <!-- ── STOK & PERMINTAAN ── -->
                <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS, ROLE_MANAJEMEN])): ?>
                <div class="sb-sidenav-menu-heading">Darah</div>

                <a class="nav-link <?= $halaman_aktif==='transfusi_darah.php'?'active':'' ?>"
                   href="transfusi_darah.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-droplet-half"></i></div>
                    Transfusi Darah
                </a>

                <a class="nav-link <?= $halaman_aktif==='stok_darah.php'?'active':'' ?>"
                   href="stok_darah.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-bag-heart-fill"></i></div>
                    Stok Darah
                    <?php
                    $kritis   = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok IN ('kritis','habis')")->fetch_assoc()['n'];
                    $exp_soon = $koneksi->query("SELECT COUNT(*) as n FROM stok_darah WHERE status_stok != 'expired' AND jumlah_kantong > 0 AND tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['n'];
                    if ($kritis > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $kritis ?></span>
                    <?php elseif ($exp_soon > 0): ?>
                    <span class="badge bg-warning text-dark ms-auto" title="<?= $exp_soon ?> kantong mendekati kadaluarsa">
                        ⏰ <?= $exp_soon ?>
                    </span>
                    <?php endif; ?>
                </a>

                <a class="nav-link <?= $halaman_aktif==='permintaan_darah.php'?'active':'' ?>"
                   href="permintaan_darah.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-bandaid-fill"></i></div>
                    Permintaan Darah
                </a>


                <?php endif; ?>



                <!-- ── LAPORAN ── -->
                <?php if (cekRole([ROLE_SUPER_ADMIN, ROLE_MANAJEMEN, ROLE_ADMIN])): ?>
                <div class="sb-sidenav-menu-heading">Laporan</div>

                <a class="nav-link collapsed" href="#"
                   data-bs-toggle="collapse" data-bs-target="#collapseLaporan"
                   aria-expanded="false">
                    <div class="sb-nav-link-icon"><i class="bi bi-bar-chart-fill"></i></div>
                    Laporan
                    <div class="sb-sidenav-collapse-arrow">
                        <i class="bi bi-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse" id="collapseLaporan" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?= $halaman_aktif==='laporan_donor.php'?'active':'' ?>"
                           href="laporan_donor.php">Laporan Donor</a>
                        <a class="nav-link <?= $halaman_aktif==='laporan_stok.php'?'active':'' ?>"
                           href="laporan_stok.php">Laporan Stok</a>
                    </nav>
                </div>
                <?php endif; ?>

                <!-- ── SUPER ADMIN ONLY ── -->
                <?php if (isSuperAdmin()): ?>
                <div class="sb-sidenav-menu-heading" style="color:#ffc107 !important">
                    <i class="bi bi-stars me-1"></i> Super Admin
                </div>

                <a class="nav-link <?= $halaman_aktif==='kelola_akun.php'?'active':'' ?>"
                   href="kelola_akun.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-people-fill"></i></div>
                    Kelola Akun
                </a>

                <a class="nav-link <?= $halaman_aktif==='audit_log.php'?'active':'' ?>"
                   href="audit_log.php">
                    <div class="sb-nav-link-icon"><i class="bi bi-journal-text"></i></div>
                    Audit Log
                    <?php
                    // Badge aktivitas gagal hari ini
                    $gagal_hari = $koneksi->query("SELECT COUNT(*) as n FROM audit_log WHERE status='gagal' AND DATE(waktu)=CURDATE()")->fetch_assoc()['n'];
                    if ($gagal_hari > 0):
                    ?>
                    <span class="badge bg-warning text-dark ms-auto"><?= $gagal_hari ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

            </div>
        </div>

        <!-- Footer Sidebar -->
        <div class="sb-sidenav-footer">
            <div class="small text-muted mb-1">Login sebagai:</div>
            <div class="d-flex align-items-center gap-2">
                <div style="width:28px;height:28px;border-radius:50%;background:#dc3545;
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:0.75rem;color:white;flex-shrink:0;">
                    <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'A', 0, 1)) ?>
                </div>
                <div style="overflow:hidden">
                    <div class="small fw-semibold text-white text-truncate">
                        <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?>
                    </div>
                    <div style="font-size:0.7rem">
                        <?= badge_role($_SESSION['role'] ?? '') ?>
                    </div>
                </div>
            </div>
        </div>

    </nav>
</div>
