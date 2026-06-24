<?php
/**
 * SIDORAH - monitoring_iot.php
 * Dashboard monitoring IoT real-time
 */
require_once 'koneksi.php';
paksa_login();
if ($_SESSION['role'] === 'pendonor') { header('Location: portal_pendonor.php'); exit(); }

// Data suhu terbaru
$suhu_terbaru = $koneksi->query("SELECT * FROM sensor_suhu ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$suhu_24jam   = $koneksi->query("SELECT * FROM sensor_suhu WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
$suhu_avg     = $koneksi->query("SELECT ROUND(AVG(suhu),1) as avg, MIN(suhu) as min, MAX(suhu) as max FROM sensor_suhu WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc();

// RFID scan terbaru
$rfid_terbaru = $koneksi->query("
    SELECT r.*, p.golongan_darah, p.rhesus, u.nama_lengkap
    FROM rfid_scan r
    LEFT JOIN pendonor p ON r.id_pendonor=p.id_pendonor
    LEFT JOIN users u ON p.id_pengguna=u.id_pengguna
    ORDER BY r.waktu_scan DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Setting suhu normal
$suhu_min = 2; $suhu_max = 6;
$res = $koneksi->query("SELECT kunci, nilai FROM pengaturan WHERE kunci IN ('suhu_min_normal','suhu_max_normal')");
if ($res) while ($r = $res->fetch_assoc()) {
    if ($r['kunci']==='suhu_min_normal') $suhu_min=(float)$r['nilai'];
    if ($r['kunci']==='suhu_max_normal') $suhu_max=(float)$r['nilai'];
}

// API key
$res_key = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='iot_api_key' LIMIT 1");
$api_key = ($res_key && $res_key->num_rows>0) ? $res_key->fetch_assoc()['nilai'] : 'SIDORAH-IOT-2026';

// IP server
$res_ip = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='ip_server' LIMIT 1");
$ip_server = ($res_ip && $res_ip->num_rows>0) ? $res_ip->fetch_assoc()['nilai'] : $_SERVER['SERVER_ADDR'];

$status_suhu = 'normal';
$suhu_val    = null;
if ($suhu_terbaru) {
    $suhu_val    = (float)$suhu_terbaru['suhu'];
    $status_suhu = $suhu_terbaru['status'];
}

$halaman_aktif = 'monitoring_iot.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring Suhu Darah — SIDORAH</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .suhu-display {
            font-size: 4rem; font-weight: 900; line-height: 1;
        }
        .status-normal  { color: #15803d; }
        .status-warning { color: #d97706; }
        .status-kritis  { color: #dc3545; }
        .iot-card { border-radius: 16px; border: none; }
        .pulse-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #4ade80; display: inline-block;
            animation: pulse-anim 1.5s infinite;
        }
        @keyframes pulse-anim {
            0%,100%{opacity:1;transform:scale(1)}
            50%{opacity:0.4;transform:scale(0.7)}
        }
        .rfid-item { padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; }
        .rfid-item:last-child { border-bottom: none; }
        .suhu-gauge {
            width: 180px; height: 180px; border-radius: 50%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            margin: 0 auto;
        }
        .suhu-gauge.normal  { background: radial-gradient(circle, #f0fdf4, #dcfce7); border: 4px solid #15803d; }
        .suhu-gauge.warning { background: radial-gradient(circle, #fffbeb, #fef3c7); border: 4px solid #d97706; }
        .suhu-gauge.kritis  { background: radial-gradient(circle, #fef2f2, #fee2e2); border: 4px solid #dc3545; animation: kritis-pulse 1s infinite; }
        @keyframes kritis-pulse { 0%,100%{box-shadow:0 0 0 0 rgba(220,53,69,0.4)} 50%{box-shadow:0 0 0 15px rgba(220,53,69,0)} }
        .code-block {
            background: #1e293b; color: #e2e8f0; border-radius: 10px;
            padding: 1rem; font-family: monospace; font-size: 0.78rem;
            overflow-x: auto; white-space: pre;
        }
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
                        <h1 class="h3 mb-0">
                            <i class="bi bi-cpu-fill text-danger me-2"></i>Monitoring Suhu Darah
                            <span class="ms-2" style="font-size:0.9rem">
                                <span class="pulse-dot"></span>
                                <span class="text-success small ms-1">Live</span>
                            </span>
                        </h1>
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Monitoring Suhu Darah</li>
                        </ol>
                    </div>
                    <div class="d-flex gap-2">
                        <!-- Filter periode -->
                        <form method="GET" class="d-flex gap-2 align-items-center no-print">
                            <input type="date" name="dari" class="form-control form-control-sm"
                                   value="<?= $_GET['dari'] ?? date('Y-m-d') ?>">
                            <span class="small text-muted">s/d</span>
                            <input type="date" name="sampai" class="form-control form-control-sm"
                                   value="<?= $_GET['sampai'] ?? date('Y-m-d') ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">Filter</button>
                        </form>
                        <a href="export_laporan.php?tipe=suhu&format=excel&dari=<?= $_GET['dari'] ?? date('Y-m-d') ?>&sampai=<?= $_GET['sampai'] ?? date('Y-m-d') ?>"
                           class="btn btn-success btn-sm no-print">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </a>
                        <a href="export_laporan.php?tipe=suhu&format=pdf&dari=<?= $_GET['dari'] ?? date('Y-m-d') ?>&sampai=<?= $_GET['sampai'] ?? date('Y-m-d') ?>"
                           target="_blank" class="btn btn-danger btn-sm no-print">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </a>
                        <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm no-print">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <?php if (!$suhu_terbaru): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Belum ada data dari sensor. Pastikan ESP32 sudah terhubung dan mengirim data ke API.
                </div>
                <?php endif; ?>

                <!-- Alert suhu kritis -->
                <?php if ($status_suhu === 'kritis'): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-thermometer-high fs-4"></i>
                    <div>
                        <strong>SUHU KRITIS!</strong> Suhu penyimpanan darah <?= $suhu_val ?>°C —
                        di luar batas normal (<?= $suhu_min ?>–<?= $suhu_max ?>°C). Segera periksa!
                    </div>
                </div>
                <?php elseif ($status_suhu === 'warning'): ?>
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-thermometer-half fs-4"></i>
                    <strong>Peringatan:</strong> Suhu <?= $suhu_val ?>°C mendekati batas maksimum.
                </div>
                <?php endif; ?>

                <div class="row g-4">

                    <!-- Gauge Suhu -->
                    <div class="col-md-4">
                        <div class="card iot-card shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4">
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-thermometer-half text-danger me-2"></i>
                                    Suhu Penyimpanan Darah
                                </h6>
                                <small class="text-muted">Sensor DHT22 via ESP32</small>
                            </div>
                            <div class="card-body text-center py-4">
                                <div class="suhu-gauge <?= $status_suhu ?>">
                                    <div class="suhu-display status-<?= $status_suhu ?>">
                                        <?= $suhu_val !== null ? $suhu_val : '--' ?>°
                                    </div>
                                    <div class="small fw-semibold status-<?= $status_suhu ?>">
                                        <?= ucfirst($status_suhu) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem">Celsius</div>
                                </div>
                                <div class="mt-3 d-flex justify-content-center gap-3 small text-muted">
                                    <span>Min: <?= $suhu_avg['min'] ?? '--' ?>°C</span>
                                    <span>Avg: <?= $suhu_avg['avg'] ?? '--' ?>°C</span>
                                    <span>Max: <?= $suhu_avg['max'] ?? '--' ?>°C</span>
                                </div>
                                <div class="mt-2 small text-muted">
                                    Normal: <?= $suhu_min ?>°C – <?= $suhu_max ?>°C
                                </div>
                                <?php if ($suhu_terbaru): ?>
                                <div class="mt-2 text-muted" style="font-size:0.75rem">
                                    Update: <?= date('d/m/Y H:i:s', strtotime($suhu_terbaru['created_at'])) ?>
                                    <?php if ($suhu_terbaru['kelembaban']): ?>
                                    · Kelembaban: <?= $suhu_terbaru['kelembaban'] ?>%
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Suhu 24 Jam -->
                    <div class="col-md-8">
                        <div class="card iot-card shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4">
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-graph-up text-danger me-2"></i>
                                    Grafik Suhu 24 Jam Terakhir
                                </h6>
                                <small class="text-muted"><?= count($suhu_24jam) ?> data poin</small>
                            </div>
                            <div class="card-body px-4">
                                <?php if (empty($suhu_24jam)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-graph-up fs-2 d-block mb-2"></i>
                                    Belum ada data suhu dari sensor
                                </div>
                                <?php else: ?>
                                <canvas id="chartSuhu" style="max-height:220px"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- RFID Scan Log -->
                    <div class="col-md-6">
                        <div class="card iot-card shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4">
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-person-badge-fill text-danger me-2"></i>
                                    Log Scan RFID/QR
                                </h6>
                                <small class="text-muted">10 scan terakhir</small>
                            </div>
                            <div class="card-body px-4 py-3">
                                <?php if (empty($rfid_terbaru)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-qr-code fs-2 d-block mb-2"></i>
                                    Belum ada scan RFID
                                </div>
                                <?php else: foreach ($rfid_terbaru as $r): ?>
                                <div class="rfid-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;
                                            background:<?= $r['status']==='dikenal'?'#d1fae5':'#fee2e2' ?>;
                                            display:flex;align-items:center;justify-content:center">
                                            <i class="bi bi-<?= $r['status']==='dikenal'?'person-check-fill text-success':'person-x-fill text-danger' ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:0.82rem">
                                                <?= $r['status']==='dikenal'
                                                    ? htmlspecialchars($r['nama_lengkap'])
                                                    : 'UID Tidak Dikenal' ?>
                                            </div>
                                            <div class="text-muted" style="font-size:0.72rem">
                                                <?= htmlspecialchars($r['rfid_uid']) ?>
                                                <?php if ($r['golongan_darah']): ?>
                                                · <span class="text-danger fw-semibold">
                                                    <?= $r['golongan_darah'].($r['rhesus']==='Positif'?'+':'-') ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted" style="font-size:0.72rem;text-align:right">
                                        <?= date('H:i:s', strtotime($r['waktu_scan'])) ?><br>
                                        <?= date('d/m', strtotime($r['waktu_scan'])) ?>
                                    </div>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panduan Koneksi ESP32 -->
                    <div class="col-md-6">
                        <div class="card iot-card shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4">
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-plug-fill text-danger me-2"></i>
                                    Konfigurasi ESP32
                                </h6>
                                <small class="text-muted">Copy kode ini ke Arduino IDE</small>
                            </div>
                            <div class="card-body px-4 py-3">
                                <div class="mb-2 small fw-semibold text-muted">URL API:</div>
                                <div class="code-block mb-3">http://<?= $ip_server ?>/sidorah/api_iot.php</div>

                                <div class="mb-2 small fw-semibold text-muted">API Key:</div>
                                <div class="code-block mb-3"><?= htmlspecialchars($api_key) ?></div>

                                <div class="mb-2 small fw-semibold text-muted">Contoh Kirim Suhu (Arduino):</div>
                                <div class="code-block"><?php echo htmlspecialchars(
'#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <ArduinoJson.h>

const char* ssid     = "NAMA_WIFI";
const char* password = "PASS_WIFI";
const char* api_url  = "http://'.$ip_server.'/sidorah/api_iot.php";
const char* api_key  = "'.$api_key.'";

DHT dht(4, DHT22); // Pin D4

void setup() {
  Serial.begin(115200);
  dht.begin();
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) delay(500);
  Serial.println("WiFi Connected!");
}

void loop() {
  float suhu = dht.readTemperature();
  float hum  = dht.readHumidity();

  if (!isnan(suhu)) {
    HTTPClient http;
    http.begin(api_url);
    http.addHeader("Content-Type", "application/json");

    String body = "{\"api_key\":\"" + String(api_key) + 
                  "\",\"type\":\"suhu\",\"suhu\":" + String(suhu) +
                  ",\"kelembaban\":" + String(hum) + 
                  ",\"device_id\":\"ESP32-001\"}";

    int code = http.POST(body);
    Serial.println("Response: " + String(code));
    http.end();
  }
  delay(30000); // Kirim tiap 30 detik
}'); ?></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="text-muted small">SIDORAH &copy; <?= date('Y') ?> — IoT Monitoring</div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
<?php if (!empty($suhu_24jam)): ?>
<script>
const suhuData = <?= json_encode($suhu_24jam) ?>;
const suhuMin  = <?= $suhu_min ?>;
const suhuMax  = <?= $suhu_max ?>;

new Chart(document.getElementById('chartSuhu'), {
    type: 'line',
    data: {
        labels: suhuData.map(d => {
            const t = new Date(d.created_at);
            return t.getHours()+':'+String(t.getMinutes()).padStart(2,'0');
        }),
        datasets: [
            {
                label: 'Suhu (°C)',
                data: suhuData.map(d => parseFloat(d.suhu)),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220,53,69,0.1)',
                borderWidth: 2, tension: 0.3, fill: true,
                pointRadius: 3, pointBackgroundColor: suhuData.map(d =>
                    d.status==='kritis'?'#dc3545':d.status==='warning'?'#d97706':'#15803d'
                )
            },
            {
                label: 'Batas Atas ('+suhuMax+'°C)',
                data: suhuData.map(() => suhuMax),
                borderColor: '#fbbf24', borderDash: [5,3],
                borderWidth: 1.5, pointRadius: 0, fill: false
            },
            {
                label: 'Batas Bawah ('+suhuMin+'°C)',
                data: suhuData.map(() => suhuMin),
                borderColor: '#60a5fa', borderDash: [5,3],
                borderWidth: 1.5, pointRadius: 0, fill: false
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 12 } },
            y: { beginAtZero: false, ticks: { callback: v => v+'°C' } }
        }
    }
});
</script>
<?php endif; ?>

<!-- Auto refresh tiap 30 detik -->
<script>setTimeout(() => location.reload(), 30000);</script>

</body>
</html>