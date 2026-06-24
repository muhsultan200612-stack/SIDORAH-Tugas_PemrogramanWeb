<?php
/**
 * SIDORAH - api_iot.php
 * API endpoint untuk ESP32 kirim data sensor
 * URL: http://[IP_SERVER]/sidorah/api_iot.php
 *
 * Contoh request dari ESP32:
 * POST http://10.99.133.126/sidorah/api_iot.php
 * Header: Content-Type: application/json
 * Body: {"api_key":"SIDORAH-IOT-2026","type":"suhu","suhu":4.2,"kelembaban":65.3,"device_id":"ESP32-001"}
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Koneksi DB via koneksi.php
require_once __DIR__ . '/koneksi.php';

function resp($status, $message, $data = null) {
    echo json_encode([
        'status'    => $status,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Ambil API key dari pengaturan
$res_key = $koneksi->query("SELECT nilai FROM pengaturan WHERE kunci='iot_api_key' LIMIT 1");
$api_key = ($res_key && $res_key->num_rows > 0) ? $res_key->fetch_assoc()['nilai'] : 'SIDORAH-IOT-2026';

// Ambil setting suhu
$suhu_min = 2;
$suhu_max = 6;
$res_suhu = $koneksi->query("SELECT kunci, nilai FROM pengaturan WHERE kunci IN ('suhu_min_normal','suhu_max_normal')");
if ($res_suhu) {
    while ($r = $res_suhu->fetch_assoc()) {
        if ($r['kunci'] === 'suhu_min_normal') $suhu_min = (float)$r['nilai'];
        if ($r['kunci'] === 'suhu_max_normal') $suhu_max = (float)$r['nilai'];
    }
}

// ── GET: Ambil data terbaru (untuk ESP32 cek status) ──────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? 'status';

    if ($type === 'suhu') {
        $data = $koneksi->query("SELECT * FROM sensor_suhu ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
        resp('ok', 'Data suhu terbaru', $data);
    }

    if ($type === 'stok') {
        $data = $koneksi->query("
            SELECT golongan_darah, rhesus, SUM(jumlah_kantong) as total
            FROM stok_darah WHERE status_stok != 'expired'
            GROUP BY golongan_darah, rhesus
        ")->fetch_all(MYSQLI_ASSOC);
        resp('ok', 'Data stok darah', $data);
    }

    resp('ok', 'SIDORAH IoT API aktif', [
        'version'   => '1.0',
        'endpoints' => ['POST /api_iot.php'],
        'types'     => ['suhu', 'stok', 'rfid']
    ]);
}

// ── POST: Terima data dari ESP32 ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Baca input JSON
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    // Fallback ke form data
    if (!$data) $data = $_POST;

    // Validasi API key
    $incoming_key = $data['api_key'] ?? '';
    if ($incoming_key !== $api_key) {
        resp('error', 'API key tidak valid', null);
    }

    $type      = $data['type'] ?? '';
    $device_id = $data['device_id'] ?? 'ESP32-001';
    $lokasi    = $data['lokasi'] ?? 'Ruang Penyimpanan Darah';

    // ── 1. Data Suhu (DHT22) ──────────────────────────────────
    if ($type === 'suhu') {
        $suhu      = (float)($data['suhu'] ?? 0);
        $kelembaban= isset($data['kelembaban']) ? (float)$data['kelembaban'] : null;

        // Tentukan status
        if ($suhu < $suhu_min || $suhu > $suhu_max + 4) {
            $status = 'kritis';
        } elseif ($suhu > $suhu_max) {
            $status = 'warning';
        } else {
            $status = 'normal';
        }

        $stmt = $koneksi->prepare("INSERT INTO sensor_suhu (suhu, kelembaban, device_id, lokasi, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param('ddsss', $suhu, $kelembaban, $device_id, $lokasi, $status);

        if ($stmt->execute()) {
            // Buat notifikasi darurat jika suhu kritis
            if ($status === 'kritis') {
                $judul = "⚠️ SUHU PENYIMPANAN DARAH KRITIS!";
                $pesan = "Sensor mendeteksi suhu $suhu°C di $lokasi. Segera periksa lemari penyimpanan darah!";
                $koneksi->query("INSERT INTO notifikasi_darurat (judul, pesan, tingkat, id_pembuat) VALUES ('$judul', '$pesan', 'darurat', 1)");
            } elseif ($status === 'warning') {
                $judul = "⚠️ Peringatan Suhu Penyimpanan";
                $pesan = "Suhu penyimpanan darah mencapai $suhu°C di $lokasi. Harap segera cek.";
                $koneksi->query("INSERT INTO notifikasi_darurat (judul, pesan, tingkat, id_pembuat) VALUES ('$judul', '$pesan', 'warning', 1)");
            }
            resp('ok', "Data suhu diterima: {$suhu}°C ({$status})", ['suhu'=>$suhu,'status'=>$status]);
        }
        resp('error', 'Gagal simpan data suhu');
    }

    // ── 2. Data Stok Load Cell ────────────────────────────────
    if ($type === 'stok') {
        $goldar  = $koneksi->real_escape_string($data['golongan_darah'] ?? 'O');
        $rhesus  = $koneksi->real_escape_string($data['rhesus'] ?? 'Positif');
        $kantong = (int)($data['jumlah_kantong'] ?? 0);
        $berat   = isset($data['berat_gram']) ? (float)$data['berat_gram'] : null;

        $stmt = $koneksi->prepare("INSERT INTO sensor_stok (golongan_darah, rhesus, jumlah_kantong, berat_gram, device_id) VALUES (?,?,?,?,?)");
        $stmt->bind_param('ssiis', $goldar, $rhesus, $kantong, $berat, $device_id);

        if ($stmt->execute()) {
            // Update stok darah utama
            $status_stok = $kantong <= 0 ? 'habis' : ($kantong <= 5 ? 'kritis' : 'tersedia');
            $koneksi->query("
                UPDATE stok_darah SET jumlah_kantong=$kantong, status_stok='$status_stok'
                WHERE golongan_darah='$goldar' AND rhesus='$rhesus' AND status_stok != 'expired'
                ORDER BY tanggal_kadaluarsa ASC LIMIT 1
            ");
            resp('ok', "Data stok diterima: $goldar $rhesus = $kantong kantong", ['kantong'=>$kantong,'status'=>$status_stok]);
        }
        resp('error', 'Gagal simpan data stok');
    }

    // ── 3. RFID Scan ──────────────────────────────────────────
    if ($type === 'rfid') {
        $uid    = $koneksi->real_escape_string($data['rfid_uid'] ?? '');
        $lokasi = $koneksi->real_escape_string($data['lokasi'] ?? 'Pintu Masuk Donor');

        if (!$uid) resp('error', 'UID RFID kosong');

        // Cari pendonor dengan UID ini
        $pendonor = $koneksi->query("
            SELECT p.id_pendonor, u.nama_lengkap, p.golongan_darah, p.rhesus,
                   p.total_donor, p.donor_terakhir, p.status_aktif
            FROM pendonor p
            JOIN users u ON p.id_pengguna = u.id_pengguna
            WHERE p.rfid_uid = '$uid' LIMIT 1
        ")->fetch_assoc();

        $status = $pendonor ? 'dikenal' : 'tidak_dikenal';
        $id_pendonor = $pendonor ? $pendonor['id_pendonor'] : null;

        $stmt = $koneksi->prepare("INSERT INTO rfid_scan (rfid_uid, id_pendonor, device_id, lokasi, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sisss', $uid, $id_pendonor, $device_id, $lokasi, $status);
        $stmt->execute();

        if ($pendonor) {
            // Cek boleh donor (standar PMI: 90 hari)
            $boleh = true;
            if ($pendonor['donor_terakhir']) {
                $diff = (new DateTime())->diff(new DateTime($pendonor['donor_terakhir']));
                if ($diff->days < 90) $boleh = false;
            }

            resp('ok', "Pendonor dikenali: {$pendonor['nama_lengkap']}", [
                'dikenal'        => true,
                'nama'           => $pendonor['nama_lengkap'],
                'golongan_darah' => $pendonor['golongan_darah'].($pendonor['rhesus']==='Positif'?'+':'-'),
                'total_donor'    => $pendonor['total_donor'],
                'boleh_donor'    => $boleh,
                'status_aktif'   => $pendonor['status_aktif']
            ]);
        } else {
            resp('ok', 'UID tidak dikenal', ['dikenal'=>false, 'uid'=>$uid]);
        }
    }

    resp('error', 'Tipe data tidak dikenal. Gunakan: suhu, stok, atau rfid');
}

resp('error', 'Method tidak didukung. Gunakan GET atau POST');