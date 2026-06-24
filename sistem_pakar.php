<?php
require_once 'koneksi.php';
paksa_login();
paksa_role([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_PETUGAS_MEDIS]);

$halaman_aktif = 'sistem_pakar.php';

// Ambil statistik dari database
$total_darurat  = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE tingkat_urgensi='darurat'")->fetch_assoc()['n'];
$total_mendesak = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE tingkat_urgensi='mendesak'")->fetch_assoc()['n'];
$total_normal   = $koneksi->query("SELECT COUNT(*) as n FROM permintaan_darah WHERE tingkat_urgensi='normal'")->fetch_assoc()['n'];

// Ambil riwayat permintaan dengan Hb
$riwayat = $koneksi->query("
    SELECT nama_pasien, no_rekam_medis, golongan_darah, rhesus,
           hemoglobin, tingkat_urgensi, status_permintaan, tanggal_permintaan
    FROM permintaan_darah
    WHERE hemoglobin IS NOT NULL
    ORDER BY tanggal_permintaan DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Fungsi klasifikasi Hb
function klasifikasiHb($hb) {
    if ($hb === null) return ['label'=>'Tidak diketahui','level'=>'unknown','warna'=>'secondary','icon'=>'❓','skor'=>0];
    $hb = (float)$hb;
    if ($hb < 5)      return ['label'=>'Kritis','level'=>'kritis','warna'=>'danger','icon'=>'🔴','skor'=>1,'urgensi'=>'Darurat'];
    if ($hb < 7)      return ['label'=>'Berat','level'=>'berat','warna'=>'warning','icon'=>'🟠','skor'=>2,'urgensi'=>'Darurat'];
    if ($hb < 10)     return ['label'=>'Sedang','level'=>'sedang','warna'=>'info','icon'=>'🟡','skor'=>3,'urgensi'=>'Mendesak'];
    return               ['label'=>'Ringan','level'=>'ringan','warna'=>'success','icon'=>'🟢','skor'=>4,'urgensi'=>'Normal'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Decision Support System — SIDORAH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <style>
        :root {
            --merah: #c0392b;
            --merah-muda: #e74c3c;
            --gelap: #1a1a2e;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }

        /* Hero */
        .hero-sc {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .hero-sc::before {
            content: '🧠';
            position: absolute;
            right: 2rem; top: 50%;
            transform: translateY(-50%);
            font-size: 6rem;
            opacity: 0.15;
        }
        .hero-sc .badge-sc {
            background: rgba(255,255,255,0.15);
            color: white;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 0.75rem;
        }

        /* Card */
        .card { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }

        /* Rule cards */
        .rule-card {
            border-radius: 14px;
            padding: 1.25rem;
            border-left: 5px solid;
            background: white;
            transition: transform 0.2s;
        }
        .rule-card:hover { transform: translateY(-3px); }
        .rule-card.kritis   { border-color: #dc3545; background: #fff5f5; }
        .rule-card.berat    { border-color: #fd7e14; background: #fff8f0; }
        .rule-card.sedang   { border-color: #ffc107; background: #fffdf0; }
        .rule-card.ringan   { border-color: #198754; background: #f0fff4; }

        /* Kalkulator */
        .kalkulator-card { background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .hb-input {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.75rem;
            width: 100%;
            outline: none;
            transition: border-color 0.3s;
        }
        .hb-input:focus { border-color: var(--merah); }

        /* Hasil */
        .hasil-box {
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .hasil-box.darurat  { background: linear-gradient(135deg,#fee2e2,#fecaca); border: 2px solid #dc3545; }
        .hasil-box.mendesak { background: linear-gradient(135deg,#fff7ed,#fed7aa); border: 2px solid #f97316; }
        .hasil-box.normal   { background: linear-gradient(135deg,#f0fdf4,#bbf7d0); border: 2px solid #22c55e; }
        .hasil-box.default  { background: #f8fafc; border: 2px dashed #cbd5e1; }

        /* Tabel riwayat */
        .badge-darurat  { background:#fee2e2;color:#991b1b; }
        .badge-mendesak { background:#fff7ed;color:#9a3412; }
        .badge-normal   { background:#f0fdf4;color:#166534; }
        .badge-kritis-hb{ background:#fee2e2;color:#991b1b; }
        .badge-berat-hb { background:#fff7ed;color:#9a3412; }
        .badge-sedang-hb{ background:#fef9c3;color:#713f12; }
        .badge-ringan-hb{ background:#dcfce7;color:#166534; }

        /* Alur */
        .alur-step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .alur-num {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--gelap);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
            flex-shrink: 0;
        }
        .alur-arrow {
            text-align: center;
            color: #94a3b8;
            font-size: 1.5rem;
            margin: 0.25rem 0;
        }

        /* Stat badge */
        .stat-sc {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body id="page-top">
<div id="layoutSidenav">
    <?php include 'includes/sidenav.php'; ?>

    <div id="layoutSidenav_content">
        <?php include 'includes/topnav.php'; ?>
        <main>
            <div class="container-fluid px-4 py-4">

                <!-- Hero -->
                <div class="hero-sc">
                    <div class="badge-sc">🧠 Sistem Pendukung Keputusan · Rule-Based Expert System</div>
                    <h2 class="fw-bold mb-1" style="font-size:1.6rem">Decision Support System (DSS) Rekomendasi Urgensi Transfusi Darah</h2>
                    <p class="mb-0" style="opacity:0.8;font-size:0.9rem">
                        Sistem ini <strong>hanya memberikan rekomendasi awal</strong> berdasarkan kadar hemoglobin (Hb) menggunakan
                        <strong>Rule-Based Expert System</strong>. <span style="color:#fca5a5">Keputusan akhir tetap berada di tangan dokter.</span>
                    </p>
                </div>

                <!-- Disclaimer dokter -->
                <div class="alert d-flex align-items-start gap-3 mb-4" style="background:#fff7ed;border:1.5px solid #f97316;border-radius:14px">
                    <span style="font-size:1.8rem;line-height:1">⚕️</span>
                    <div>
                        <div class="fw-bold" style="color:#9a3412">Perhatian: Sistem Pendukung Keputusan Medis</div>
                        <div class="small" style="color:#7c3d12">
                            Hasil analisis sistem pakar ini <strong>bukan diagnosis medis</strong> dan <strong>tidak menggantikan keputusan dokter</strong>.
                            Seluruh tindakan transfusi darah harus melalui <strong>pemeriksaan dan persetujuan dokter yang berwenang</strong>
                            sesuai standar prosedur medis yang berlaku. Sistem ini hanya sebagai alat bantu awal untuk memprioritaskan penanganan.
                        </div>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- Kiri: Kalkulator + Riwayat -->
                    <div class="col-lg-7">

                        <!-- Kalkulator SC -->
                        <div class="kalkulator-card mb-4">
                            <h5 class="fw-bold mb-1"><i class="bi bi-calculator-fill text-danger me-2"></i>Kalkulator Rekomendasi Sistem Pakar</h5>
                            <p class="text-muted small mb-3">Masukkan nilai Hb pasien untuk mendapatkan <strong>rekomendasi awal</strong> — bukan keputusan akhir</p>

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="fw-semibold small mb-1">Kadar Hemoglobin (g/dL)</label>
                                    <input type="number" id="inputHb" class="hb-input"
                                           step="0.1" min="0" max="20" placeholder="0.0"
                                           oninput="jalankanSP(this.value)">
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Normal dewasa: 12–17 g/dL</small>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <label class="fw-semibold small mb-1">
                                        🤖 Rekomendasi Sistem
                                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem">Belum keputusan final</span>
                                    </label>
                                    <div class="hasil-box default" id="hasilBox">
                                        <div style="font-size:2.5rem;margin-bottom:0.5rem">🔬</div>
                                        <div class="text-muted">Masukkan nilai Hb untuk memulai analisis</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail rekomendasi -->
                            <div id="detailReko" class="mt-3" style="display:none">
                                <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                                    <div class="fw-semibold mb-2"><i class="bi bi-clipboard2-pulse me-1 text-danger"></i>Rekomendasi Tindakan dari Sistem:</div>
                                    <div id="isiReko" class="small"></div>
                                </div>
                            </div>

                            <!-- Bagian persetujuan dokter -->
                            <div id="sectionDokter" class="mt-3" style="display:none">
                                <hr>
                                <div class="fw-semibold mb-2" style="color:#1e40af">
                                    <i class="bi bi-person-badge-fill me-1"></i>Verifikasi & Keputusan Dokter
                                </div>
                                <div class="p-3 rounded-3" style="background:#eff6ff;border:1.5px solid #3b82f6">
                                    <div class="small text-muted mb-3">
                                        ⚠️ Rekomendasi di atas hanya bersifat <strong>informatif</strong>. Dokter wajib melakukan pemeriksaan klinis
                                        sebelum mengambil keputusan tindakan transfusi.
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Nama Dokter Pemeriksa</label>
                                            <input type="text" id="namaDokter" class="form-control form-control-sm" placeholder="dr. ...">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Keputusan Dokter</label>
                                            <select id="keputusanDokter" class="form-select form-select-sm" onchange="updateKeputusan()">
                                                <option value="">— Pilih keputusan —</option>
                                                <option value="setuju">✅ Setuju dengan rekomendasi sistem</option>
                                                <option value="modifikasi">✏️ Setuju dengan modifikasi</option>
                                                <option value="tolak">❌ Tidak setuju / Tindakan berbeda</option>
                                            </select>
                                        </div>
                                        <div class="col-12" id="catatanDokterBox" style="display:none">
                                            <label class="form-label small fw-semibold">Catatan / Keputusan Dokter</label>
                                            <textarea id="catatanDokter" class="form-control form-control-sm" rows="2"
                                                placeholder="Tuliskan keputusan/modifikasi tindakan dari dokter..."></textarea>
                                        </div>
                                        <div class="col-12">
                                            <div id="hasilKeputusan" class="small" style="display:none"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Analisis -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-danger me-2"></i>Riwayat Analisis Sistem Pakar</h6>
                                <?php if (empty($riwayat)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-2"></i>
                                    <p class="mt-2 small">Belum ada data dengan nilai Hb</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th>Pasien</th>
                                                <th>Gol. Darah</th>
                                                <th>Hb (g/dL)</th>
                                                <th>Klasifikasi SC</th>
                                                <th>Urgensi</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($riwayat as $r):
                                            $hb = (float)$r['hemoglobin'];
                                            $kls = klasifikasiHb($hb);
                                            $sym = $r['rhesus']==='Positif'?'+':'-';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold small"><?= htmlspecialchars($r['nama_pasien']) ?></div>
                                                <div class="text-muted" style="font-size:0.75rem"><?= $r['no_rekam_medis'] ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger"><?= $r['golongan_darah'].$sym ?></span>
                                            </td>
                                            <td class="fw-bold"><?= number_format($hb,1) ?></td>
                                            <td>
                                                <span class="badge rounded-pill badge-<?= $kls['level'] ?>-hb px-2">
                                                    <?= $kls['icon'] ?> <?= $kls['label'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $u = $r['tingkat_urgensi'];
                                                $ub = ['darurat'=>'badge-darurat','mendesak'=>'badge-mendesak','normal'=>'badge-normal'];
                                                $ui = ['darurat'=>'🚨','mendesak'=>'⚠️','normal'=>'✅'];
                                                ?>
                                                <span class="badge rounded-pill <?= $ub[$u]??'bg-secondary' ?> px-2">
                                                    <?= ($ui[$u]??'').' '.ucfirst($u) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php $sp = $r['status_permintaan']; ?>
                                                <span class="badge rounded-pill <?= $sp==='terpenuhi'?'bg-success':($sp==='menunggu'?'bg-warning text-dark':'bg-secondary') ?>">
                                                    <?= ucfirst($sp) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <!-- Kanan: Rules + Alur + Stat -->
                    <div class="col-lg-5">

                        <!-- Statistik -->
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="stat-sc">
                                    <div class="fw-bold fs-4 text-danger"><?= $total_darurat ?></div>
                                    <div class="small text-muted">🚨 Darurat</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-sc">
                                    <div class="fw-bold fs-4 text-warning"><?= $total_mendesak ?></div>
                                    <div class="small text-muted">⚠️ Mendesak</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-sc">
                                    <div class="fw-bold fs-4 text-success"><?= $total_normal ?></div>
                                    <div class="small text-muted">✅ Normal</div>
                                </div>
                            </div>
                        </div>

                        <!-- Knowledge Base / Rules -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bi bi-book-fill text-danger me-2"></i>Knowledge Base — Basis Pengetahuan</h6>
                                <p class="text-muted small mb-3">Aturan yang digunakan sistem pakar berdasarkan standar medis WHO:</p>

                                <div class="d-flex flex-column gap-2">
                                    <div class="rule-card kritis">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-danger">🔴 KRITIS — Darurat</span>
                                            <span class="badge bg-danger">Hb &lt; 5 g/dL</span>
                                        </div>
                                        <div class="small text-muted">Anemia sangat berat. Transfusi segera diperlukan untuk mencegah gagal organ.</div>
                                    </div>

                                    <div class="rule-card berat">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-warning">🟠 BERAT — Darurat</span>
                                            <span class="badge bg-warning text-dark">Hb 5–6.9 g/dL</span>
                                        </div>
                                        <div class="small text-muted">Anemia berat. Perlu transfusi segera dan pemantauan intensif.</div>
                                    </div>

                                    <div class="rule-card sedang">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold" style="color:#713f12">🟡 SEDANG — Mendesak</span>
                                            <span class="badge bg-warning text-dark">Hb 7–9.9 g/dL</span>
                                        </div>
                                        <div class="small text-muted">Anemia sedang. Transfusi dipertimbangkan berdasarkan kondisi klinis pasien.</div>
                                    </div>

                                    <div class="rule-card ringan">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-success">🟢 RINGAN — Normal</span>
                                            <span class="badge bg-success">Hb ≥ 10 g/dL</span>
                                        </div>
                                        <div class="small text-muted">Anemia ringan atau tidak anemia. Transfusi umumnya tidak diperlukan segera.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alur Inferensi -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bi bi-diagram-3-fill text-danger me-2"></i>Alur Inferensi Sistem Pakar</h6>
                                <div class="d-flex flex-column gap-1">
                                    <div class="alur-step">
                                        <div class="alur-num">1</div>
                                        <div>
                                            <div class="fw-semibold small">Input Nilai Hb Pasien</div>
                                            <div class="text-muted" style="font-size:0.78rem">Petugas memasukkan kadar hemoglobin saat membuat permintaan darah</div>
                                        </div>
                                    </div>
                                    <div class="alur-arrow">↓</div>
                                    <div class="alur-step">
                                        <div class="alur-num">2</div>
                                        <div>
                                            <div class="fw-semibold small">Mesin Inferensi (Inference Engine)</div>
                                            <div class="text-muted" style="font-size:0.78rem">Sistem mencocokkan nilai Hb dengan basis aturan (IF-THEN rules)</div>
                                        </div>
                                    </div>
                                    <div class="alur-arrow">↓</div>
                                    <div class="alur-step">
                                        <div class="alur-num">3</div>
                                        <div>
                                            <div class="fw-semibold small">Rekomendasi Sistem (Bukan Keputusan Final)</div>
                                            <div class="text-muted" style="font-size:0.78rem">Sistem menghasilkan label urgensi dan <em>saran</em> tindakan medis sebagai referensi awal</div>
                                        </div>
                                    </div>
                                    <div class="alur-arrow">↓</div>
                                    <div class="alur-step" style="border:1.5px solid #3b82f6;background:#eff6ff">
                                        <div class="alur-num" style="background:#1d4ed8">4</div>
                                        <div>
                                            <div class="fw-semibold small" style="color:#1e40af">⚕️ Verifikasi & Keputusan Dokter</div>
                                            <div class="text-muted" style="font-size:0.78rem">Dokter memeriksa pasien secara klinis, lalu <strong>menyetujui, memodifikasi, atau menolak</strong> rekomendasi sistem</div>
                                        </div>
                                    </div>
                                    <div class="alur-arrow">↓</div>
                                    <div class="alur-step">
                                        <div class="alur-num">5</div>
                                        <div>
                                            <div class="fw-semibold small">Tindakan Transfusi Dilaksanakan</div>
                                            <div class="text-muted" style="font-size:0.78rem">Tindakan hanya dilakukan setelah ada persetujuan resmi dari dokter yang berwenang</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div><!-- end row -->

            </div>
        </main>

        <footer class="py-3 bg-white border-top mt-4">
            <div class="container-fluid px-4 text-center text-muted small">
                SIDORAH © <?= date('Y') ?> — Sistem Pakar berbasis Rule-Based Expert System
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<script>
const RULES = [
    {
        min: 0, max: 4.99,
        label: 'Kritis', icon: '🔴', urgensi: 'DARURAT',
        cls: 'darurat',
        warna: '#dc3545',
        reko: [
            '🚨 Transfusi darah <strong>segera</strong> — kondisi mengancam jiwa',
            '🏥 Rawat inap di ICU diperlukan',
            '💉 Siapkan kantong darah WB/PRC <strong>prioritas tertinggi</strong>',
            '👨‍⚕️ Segera hubungi dokter spesialis penyakit dalam',
            '📊 Monitoring tanda vital setiap 15 menit'
        ]
    },
    {
        min: 5, max: 6.99,
        label: 'Berat', icon: '🟠', urgensi: 'DARURAT',
        cls: 'darurat',
        warna: '#f97316',
        reko: [
            '🚨 Transfusi darah <strong>segera</strong> dalam 1-2 jam',
            '🏥 Rawat inap dengan pemantauan ketat',
            '💉 Persiapkan 2-4 kantong darah',
            '👨‍⚕️ Konsultasi dokter segera',
            '📊 Cek ulang Hb setelah transfusi'
        ]
    },
    {
        min: 7, max: 9.99,
        label: 'Sedang', icon: '🟡', urgensi: 'MENDESAK',
        cls: 'mendesak',
        warna: '#eab308',
        reko: [
            '⚠️ Transfusi <strong>dipertimbangkan</strong> berdasarkan gejala klinis',
            '🏥 Rawat jalan atau observasi rawat inap',
            '💊 Pertimbangkan suplemen zat besi & asam folat',
            '👨‍⚕️ Evaluasi penyebab anemia',
            '📊 Kontrol Hb dalam 1-2 minggu'
        ]
    },
    {
        min: 10, max: 999,
        label: 'Ringan', icon: '🟢', urgensi: 'NORMAL',
        cls: 'normal',
        warna: '#22c55e',
        reko: [
            '✅ Transfusi <strong>belum diperlukan</strong> saat ini',
            '💊 Suplemen zat besi jika diperlukan',
            '🥗 Perbaiki pola makan dengan makanan tinggi zat besi',
            '📊 Kontrol Hb rutin setiap 1 bulan',
            '👨‍⚕️ Konsultasi lanjut jika ada gejala'
        ]
    }
];

function jalankanSP(val) {
    const hb = parseFloat(val);
    const box = document.getElementById('hasilBox');
    const detail = document.getElementById('detailReko');
    const isi = document.getElementById('isiReko');
    const sectionDokter = document.getElementById('sectionDokter');

    if (isNaN(hb) || val === '') {
        box.className = 'hasil-box default';
        box.innerHTML = '<div style="font-size:2.5rem;margin-bottom:0.5rem">🔬</div><div class="text-muted">Masukkan nilai Hb untuk memulai analisis</div>';
        detail.style.display = 'none';
        sectionDokter.style.display = 'none';
        return;
    }

    const rule = RULES.find(r => hb >= r.min && hb <= r.max);
    if (!rule) return;

    box.className = `hasil-box ${rule.cls}`;
    box.innerHTML = `
        <div style="font-size:2.5rem;margin-bottom:0.5rem">${rule.icon}</div>
        <div style="font-size:1.2rem;font-weight:800;color:${rule.warna}">${rule.label}</div>
        <div class="fw-semibold mt-1">Rekomendasi Urgensi: ${rule.urgensi}</div>
        <div class="small mt-1" style="opacity:0.8">Hb: <strong>${hb.toFixed(1)} g/dL</strong></div>
        <div class="mt-2 small" style="background:rgba(0,0,0,0.08);border-radius:8px;padding:4px 10px;color:#374151">
            ⚠️ Menunggu verifikasi dokter
        </div>
    `;

    isi.innerHTML = rule.reko.map(r => `<div class="mb-1">• ${r}</div>`).join('');
    detail.style.display = 'block';
    sectionDokter.style.display = 'block';
}

function updateKeputusan() {
    const keputusan = document.getElementById('keputusanDokter').value;
    const catatanBox = document.getElementById('catatanDokterBox');
    const hasilKeputusan = document.getElementById('hasilKeputusan');

    if (keputusan === 'modifikasi' || keputusan === 'tolak') {
        catatanBox.style.display = 'block';
    } else {
        catatanBox.style.display = 'none';
    }

    if (keputusan) {
        const map = {
            'setuju':    { bg:'#dcfce7', color:'#166534', icon:'✅', text:'Dokter menyetujui rekomendasi sistem. Tindakan dapat dilanjutkan sesuai rekomendasi.' },
            'modifikasi':{ bg:'#fff7ed', color:'#9a3412', icon:'✏️', text:'Dokter memodifikasi rekomendasi. Tindakan dilakukan sesuai keputusan dokter.' },
            'tolak':     { bg:'#fee2e2', color:'#991b1b', icon:'❌', text:'Dokter tidak menyetujui rekomendasi sistem. Tindakan sepenuhnya berdasarkan keputusan dokter.' },
        };
        const m = map[keputusan];
        hasilKeputusan.style.display = 'block';
        hasilKeputusan.innerHTML = `
            <div class="p-2 rounded-3 fw-semibold" style="background:${m.bg};color:${m.color}">
                ${m.icon} ${m.text}
            </div>`;
    } else {
        hasilKeputusan.style.display = 'none';
    }
}
</script>
<script src="js/scripts.js"></script>
</body>
</html>