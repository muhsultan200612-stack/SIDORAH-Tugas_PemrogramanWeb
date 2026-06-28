<?php
/**
 * SIDORAH - includes/topnav.php
 * Navbar atas — digunakan di semua halaman admin
 */

// Hitung notifikasi belum dibaca
$notif_count = 0;
if (sudahLogin()) {
    $res = $koneksi->query("SELECT COUNT(*) as n FROM notifikasi WHERE id_penerima=" . intval($_SESSION['id_pengguna']) . " AND status_baca=0");
    if ($res) $notif_count = $res->fetch_assoc()['n'];
}

// Ambil nama RS dari pengaturan
$nama_rs_nav = 'SIDORAH';
$res_nama = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='nama_rs' LIMIT 1");
if ($res_nama && $res_nama->num_rows > 0) {
    $nama_rs_nav = $res_nama->fetch_assoc()['nilai'] ?: 'SIDORAH';
}
?>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">

    <!-- Brand -->
    <a class="navbar-brand ps-3 fw-bold" href="dashboard.php">
        <i class="bi bi-heart-pulse-fill text-danger me-1"></i>
        <?= htmlspecialchars($nama_rs_nav) ?>
    </a>

    <!-- Sidebar Toggle -->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white"
            id="sidebarToggle" href="#!">
        <i class="bi bi-list fs-5"></i>
    </button>

    <!-- Search (desktop) -->
    <form class="d-none d-md-inline-block ms-auto me-0 me-md-3 my-2 my-md-0"
          action="cari.php" method="GET">
        <div class="input-group">
            <input class="form-control" type="text" name="q"
                   placeholder="Cari pendonor, kegiatan..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                   style="width:250px; border-radius:20px 0 0 20px;"
                   autocomplete="off">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>

    <!-- Right Nav -->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4 align-items-center">

        <!-- Notifikasi -->
        <li class="nav-item dropdown me-2">
            <a class="nav-link position-relative" href="#"
               role="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell-fill fs-5"></i>
                <?php if ($notif_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size:0.6rem">
                    <?= $notif_count > 9 ? '9+' : $notif_count ?>
                </span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="width:300px; max-height:350px; overflow-y:auto;">
                <li>
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifikasi</span>
                        <?php if ($notif_count > 0): ?>
                        <a href="#" class="small text-danger text-decoration-none" onclick="bacaSemua(event)">
                            Tandai semua dibaca
                        </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php
                $notifs = $koneksi->query("
                    SELECT * FROM notifikasi
                    WHERE id_penerima=" . intval($_SESSION['id_pengguna']) . "
                    ORDER BY waktu_kirim DESC LIMIT 5
                ");
                if ($notifs && $notifs->num_rows > 0):
                    while ($n = $notifs->fetch_assoc()):
                ?>
                <li>
                    <a class="dropdown-item py-2 notif-item <?= !$n['status_baca'] ? 'fw-semibold bg-light' : '' ?>"
                       href="#"
                       data-id="<?= $n['id_notifikasi'] ?>"
                       data-baca="<?= $n['status_baca'] ?>"
                       onclick="bacaNotif(event, this)">
                        <div class="d-flex gap-2 align-items-start">
                            <?php if (!$n['status_baca']): ?>
                            <span style="width:8px;height:8px;border-radius:50%;background:#dc3545;flex-shrink:0;margin-top:6px"></span>
                            <?php else: ?>
                            <span style="width:8px;height:8px;flex-shrink:0;margin-top:6px"></span>
                            <?php endif; ?>
                            <i class="bi bi-<?= $n['tipe_notifikasi']==='stok_kritis'?'exclamation-triangle-fill text-danger':'bell text-primary' ?> mt-1"></i>
                            <div>
                                <div class="small"><?= htmlspecialchars($n['judul']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem">
                                    <?= format_waktu_singkat($n['waktu_kirim']) ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <?php endwhile; else: ?>
                <li><p class="text-muted text-center small py-3 mb-0">Tidak ada notifikasi</p></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center small text-danger" href="#">
                        Lihat semua notifikasi
                    </a>
                </li>
            </ul>
        </li>

        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
               href="#" role="button" data-bs-toggle="dropdown">
                <div style="width:32px;height:32px;border-radius:50%;background:#dc3545;
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:0.8rem;color:white;">
                    <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'A', 0, 1)) ?>
                </div>
                <span class="d-none d-lg-inline small">
                    <?= htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]) ?>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <div class="dropdown-header">
                        <div class="fw-semibold"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                        <div class="small text-muted"><?= badge_role($_SESSION['role']) ?></div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person-fill me-2"></i>Profil Saya</a></li>
                <li><a class="dropdown-item" href="pengaturan.php"><i class="bi bi-gear-fill me-2"></i>Pengaturan</a></li>
                <?php if (isSuperAdmin()): ?>
                <li><a class="dropdown-item text-danger" href="audit_log.php"><i class="bi bi-journal-text me-2"></i>Audit Log</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger fw-semibold" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
<script>
function bacaNotif(e, el) {
    e.preventDefault();
    const id    = el.dataset.id;
    const sudah = el.dataset.baca == '1';
    if (sudah) return; // sudah dibaca, skip

    fetch('notif_baca.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'aksi=baca_satu&id_notifikasi=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            // Hapus titik merah & bold
            el.classList.remove('fw-semibold', 'bg-light');
            el.dataset.baca = '1';
            const dot = el.querySelector('span[style*="dc3545"]');
            if (dot) dot.style.background = 'transparent';
            // Update badge
            updateBadge(data.sisa);
        }
    });
}

function bacaSemua(e) {
    e.preventDefault();
    fetch('notif_baca.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'aksi=baca_semua'
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            // Reset semua item
            document.querySelectorAll('.notif-item').forEach(el => {
                el.classList.remove('fw-semibold', 'bg-light');
                el.dataset.baca = '1';
                const dot = el.querySelector('span[style*="dc3545"]');
                if (dot) dot.style.background = 'transparent';
            });
            // Sembunyikan tombol "tandai semua"
            const btnSemua = document.querySelector('[onclick="bacaSemua(event)"]');
            if (btnSemua) btnSemua.style.display = 'none';
            updateBadge(0);
        }
    });
}

function updateBadge(sisa) {
    const badge = document.querySelector('.navbar .badge.bg-danger');
    if (!badge) return;
    if (sisa <= 0) {
        badge.style.display = 'none';
    } else {
        badge.style.display = '';
        badge.textContent = sisa > 9 ? '9+' : sisa;
    }
}
</script>
