<?php
/**
 * SIDORAH - notif_baca.php
 * Handler AJAX: tandai notifikasi sudah dibaca
 */
require_once 'koneksi.php';
header('Content-Type: application/json');

if (!sudahLogin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id_user = $_SESSION['id_pengguna'];
$aksi    = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// Tandai satu notifikasi
if ($aksi === 'baca_satu') {
    $id_notif = (int)($_POST['id_notifikasi'] ?? 0);
    if ($id_notif > 0) {
        $koneksi->query("
            UPDATE notifikasi SET status_baca=1
            WHERE id_notifikasi=$id_notif AND id_penerima=$id_user
        ");
    }
    $sisa = $koneksi->query("SELECT COUNT(*) as n FROM notifikasi WHERE id_penerima=$id_user AND status_baca=0")->fetch_assoc()['n'];
    echo json_encode(['status' => 'ok', 'sisa' => (int)$sisa]);
    exit();
}

// Tandai semua sudah dibaca
if ($aksi === 'baca_semua') {
    $koneksi->query("UPDATE notifikasi SET status_baca=1 WHERE id_penerima=$id_user AND status_baca=0");
    echo json_encode(['status' => 'ok', 'sisa' => 0]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);