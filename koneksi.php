<?php
/**
 * SIDORAH - Sistem Informasi Donor Darah
 * File: koneksi.php
 * Deskripsi: Koneksi database + helper functions + audit log
 */

define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'dbsidorah');
define('DB_CHARSET', 'utf8mb4');

$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($koneksi->connect_error) {
    die('Koneksi database gagal: ' . $koneksi->connect_error);
}
$koneksi->set_charset(DB_CHARSET);
$koneksi->query("SET time_zone = '+08:00'"); // WITA

// Set timezone PHP ke WITA
date_default_timezone_set('Asia/Makassar');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

// Role constants
define('ROLE_SUPER_ADMIN',   'super_admin');
define('ROLE_ADMIN',         'admin');
define('ROLE_PETUGAS_MEDIS', 'petugas_medis');
define('ROLE_MANAJEMEN',     'manajemen');
define('ROLE_PENDONOR',      'pendonor');

// ── Auth helpers ──────────────────────────────────────────────
function bersihkan($koneksi, $data) {
    return $koneksi->real_escape_string(htmlspecialchars(trim($data)));
}
function redirect($url) { header("Location: $url"); exit(); }
function sudahLogin()   { return isset($_SESSION['id_pengguna']) && !empty($_SESSION['id_pengguna']); }
function isSuperAdmin() { return sudahLogin() && $_SESSION['role'] === ROLE_SUPER_ADMIN; }
function isAdmin()      { return sudahLogin() && in_array($_SESSION['role'], [ROLE_SUPER_ADMIN, ROLE_ADMIN]); }

function cekRole($role_diizinkan) {
    if (!sudahLogin()) return false;
    if (isSuperAdmin()) return true;
    return is_array($role_diizinkan)
        ? in_array($_SESSION['role'], $role_diizinkan)
        : $_SESSION['role'] === $role_diizinkan;
}
function paksa_login($ke = 'login.php')         { if (!sudahLogin())       redirect($ke); }
function paksa_role($role, $ke = 'dashboard.php') { paksa_login(); if (!cekRole($role)) redirect($ke); }

// ── Audit Log ────────────────────────────────────────────────
function catat_log($koneksi, $aksi, $modul=null, $detail=null, $sebelum=null, $sesudah=null, $status='sukses') {
    $id   = $_SESSION['id_pengguna']  ?? null;
    $nama = $_SESSION['nama_lengkap'] ?? 'Sistem';
    $role = $_SESSION['role']         ?? 'unknown';
    $ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua   = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $sb   = $sebelum ? json_encode($sebelum, JSON_UNESCAPED_UNICODE) : null;
    $ss   = $sesudah ? json_encode($sesudah, JSON_UNESCAPED_UNICODE) : null;

    $stmt = $koneksi->prepare("
        INSERT INTO audit_log
            (id_pengguna,nama_pengguna,role_pengguna,aksi,modul,detail,data_sebelum,data_sesudah,ip_address,user_agent,status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param('issssssssss', $id,$nama,$role,$aksi,$modul,$detail,$sb,$ss,$ip,$ua,$status);
    $stmt->execute();
    $stmt->close();
}

// ── Format helpers ────────────────────────────────────────────
function tanggal_indo($tgl) {
    if (!$tgl || $tgl==='0000-00-00') return '-';
    $b=['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
        '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    $t=explode('-',$tgl);
    return (int)$t[2].' '.($b[$t[1]]??'').' '.$t[0];
}
function hitung_umur($tgl) {
    if (!$tgl) return '-';
    return (new DateTime($tgl))->diff(new DateTime())->y.' tahun';
}
function format_waktu_singkat($ts) {
    if (!$ts) return '-';
    $tz   = new DateTimeZone('Asia/Makassar');
    $now  = new DateTime('now', $tz);
    $tgl  = new DateTime($ts, $tz);
    $diff = $now->getTimestamp() - $tgl->getTimestamp();
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff/60).' menit lalu';
    if ($diff < 86400)  return floor($diff/3600).' jam lalu';
    if ($diff < 172800) return 'Kemarin';
    if ($diff < 604800) return floor($diff/86400).' hari lalu';
    return tanggal_indo($tgl->format('Y-m-d'));
}
function badge_role($role) {
    $m=['super_admin'=>['bg-dark','bi-stars','Super Admin'],
        'admin'=>['bg-danger','bi-shield-fill','Admin'],
        'petugas_medis'=>['bg-primary','bi-heart-pulse-fill','Petugas Medis'],
        'manajemen'=>['bg-warning text-dark','bi-briefcase-fill','Manajemen'],
        'pendonor'=>['bg-success','bi-droplet-fill','Pendonor']];
    $r=$m[$role]??['bg-secondary','bi-person',ucfirst($role)];
    return "<span class='badge {$r[0]}'><i class='bi {$r[1]} me-1'></i>{$r[2]}</span>";
}
function badge_status_akun($status) {
    $m=['aktif'=>['bg-success','Aktif'],'nonaktif'=>['bg-secondary','Nonaktif'],'terkunci'=>['bg-danger','Terkunci']];
    $s=$m[$status]??['bg-secondary',ucfirst($status)];
    return "<span class='badge {$s[0]}'>{$s[1]}</span>";
}
?>