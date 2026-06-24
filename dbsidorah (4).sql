-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Jun 2026 pada 06.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbsidorah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id_log` int(11) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `nama_pengguna` varchar(100) DEFAULT NULL,
  `role_pengguna` varchar(50) DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `modul` varchar(50) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `data_sebelum` longtext DEFAULT NULL,
  `data_sesudah` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` enum('sukses','gagal','peringatan') DEFAULT 'sukses',
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `audit_log`
--

INSERT INTO `audit_log` (`id_log`, `id_pengguna`, `nama_pengguna`, `role_pengguna`, `aksi`, `modul`, `detail`, `data_sebelum`, `data_sesudah`, `ip_address`, `user_agent`, `status`, `waktu`) VALUES
(1, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: Muhammad Sultan (muhsultan@sidorah.com) role: admin', NULL, '{\"id\":2,\"nama\":\"Muhammad Sultan\",\"email\":\"muhsultan@sidorah.com\",\"role\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:00:46'),
(2, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: aktif → nonaktif', '{\"status\":\"aktif\"}', '{\"status\":\"nonaktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:04:15'),
(3, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: nonaktif → aktif', '{\"status\":\"nonaktif\"}', '{\"status\":\"aktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:04:20'),
(4, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: aktif → nonaktif', '{\"status\":\"aktif\"}', '{\"status\":\"nonaktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:04:23'),
(5, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: nonaktif → aktif', '{\"status\":\"nonaktif\"}', '{\"status\":\"aktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:04:28'),
(6, 1, 'Super Administrator', 'super_admin', 'HAPUS_AKUN', 'users', 'Super admin menghapus akun: Muhammad Sultan (muhsultan@sidorah.com) role: admin', '{\"id_pengguna\":\"2\",\"nama_lengkap\":\"Muhammad Sultan\",\"email\":\"muhsultan@sidorah.com\",\"password\":\"$2y$10$7yRSdxxvYaQTf3NTIWlV5u8FgPpjbY4EW93BicjXmzq7tJf922lmq\",\"no_telepon\":\"082223641592\",\"role\":\"admin\",\"status_akun\":\"aktif\",\"created_at\":\"2026-05-29 21:00:46\",\"updated_at\":\"2026-05-29 21:04:28\",\"last_login\":null}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:36:50'),
(7, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: Muhammad Sultan (muhsultan@sidorah.id) role: admin', NULL, '{\"id\":3,\"nama\":\"Muhammad Sultan\",\"email\":\"muhsultan@sidorah.id\",\"role\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:37:40'),
(8, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: Dwiayy (dwiayy@sidorah.id) role: pendonor', NULL, '{\"id\":4,\"nama\":\"Dwiayy\",\"email\":\"dwiayy@sidorah.id\",\"role\":\"pendonor\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 13:42:21'),
(9, 4, 'Dwiayy', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Dwiayy daftar ke: Donor Darah Rutin Juni 2026', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 14:47:12'),
(10, 3, 'Muhammad Sultan', 'admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Dwiayy → Donor Darah Rutin Juni 2026: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 14:47:43'),
(11, 3, 'Muhammad Sultan', 'admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Dwiayy (ID: 2)', '{\"nama\":\"Dwiayy\"}', '{\"nama\":\"Dwiayy\",\"goldar\":\"O Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 14:51:30'),
(12, 3, 'Muhammad Sultan', 'admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Dwiayy (ID: 1)', '{\"nama\":\"Dwiayy\"}', '{\"nama\":\"Dwiayy\",\"goldar\":\"O Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 14:51:45'),
(13, 3, 'Muhammad Sultan', 'admin', 'EDIT_KEGIATAN', 'kegiatan_donor', 'Edit kegiatan ID 7: Donor Darah Rutin Juni 2026', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 15:08:50'),
(14, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: Syifa Audyah (syifaaudyah@sidorah.id) role: pendonor', NULL, '{\"id\":5,\"nama\":\"Syifa Audyah\",\"email\":\"syifaaudyah@sidorah.id\",\"role\":\"pendonor\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 15:13:50'),
(15, 1, 'Super Administrator', 'super_admin', 'EDIT_AKUN', 'users', 'Super admin mengedit akun ID 5: Syifa Audyah (syifaaudyah@sidorah.id)', '{\"nama\":\"Syifa Audyah\",\"email\":\"syifaaudyah@sidorah.id\",\"role\":\"pendonor\"}', '{\"nama\":\"Syifa Audyah\",\"email\":\"syifaaudyah@sidorah.id\",\"role\":\"pendonor\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-29 15:15:57'),
(16, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Azrul (lokasifay@gmail.com)', NULL, '{\"nama\":\"Azrul\",\"email\":\"lokasifay@gmail.com\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 03:33:44'),
(17, 6, 'Azrul', 'pendonor', 'GANTI_PASSWORD', 'users', 'Pendonor Azrul ganti password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 03:55:48'),
(18, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Nursila (nursila@sidorah.id)', NULL, '{\"nama\":\"Nursila\",\"email\":\"nursila@sidorah.id\",\"goldar\":\"O Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:22:44'),
(19, 1, 'Super Administrator', 'super_admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Nursila (ID: 4)', '{\"nama\":\"Nursila\"}', '{\"nama\":\"Nursila\",\"goldar\":\"O Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:23:40'),
(20, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 1 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:26:53'),
(21, 3, 'Muhammad Sultan', 'admin', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 1: 1 kantong, status: expired', '{\"id_stok\":\"1\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-05-30\",\"tanggal_kadaluarsa\":\"2006-06-12\",\"status_stok\":\"expired\",\"updated_at\":\"2026-05-30 12:26:53\"}', '{\"kantong\":1,\"status\":\"expired\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:27:08'),
(22, 3, 'Muhammad Sultan', 'admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 1 kantong', '{\"id_stok\":\"1\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-05-30\",\"tanggal_kadaluarsa\":\"2026-06-12\",\"status_stok\":\"expired\",\"updated_at\":\"2026-05-30 12:27:08\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:27:17'),
(23, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:27:48'),
(24, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Zahra Firdauzi (ara@sidorah.id)', NULL, '{\"nama\":\"Zahra Firdauzi\",\"email\":\"ara@sidorah.id\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 04:30:32'),
(25, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: hera (hera@sidorah.id) role: manajemen', NULL, '{\"id\":9,\"nama\":\"hera\",\"email\":\"hera@sidorah.id\",\"role\":\"manajemen\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 05:02:22'),
(26, 1, 'Super Administrator', 'super_admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'Donor Darah Rutin Juni 2026 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:09:31'),
(27, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: Muh azrul (azrul@sidorah.id) role: petugas_medis', NULL, '{\"id\":10,\"nama\":\"Muh azrul\",\"email\":\"azrul@sidorah.id\",\"role\":\"petugas_medis\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:17:51'),
(28, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Azrul: layak, Hb=12.5, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:21:36'),
(29, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 2 kantong A Positif untuk Araa (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:24:16'),
(30, 1, 'Super Administrator', 'super_admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Araa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:25:06'),
(31, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk yusuf (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:26:04'),
(32, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'yusuf: menunggu → tidak_terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"tidak_terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:26:12'),
(33, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Araa (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:27:52'),
(34, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: Donor Darah (2026-05-30)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:40:04'),
(35, 7, 'Nursila', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Nursila daftar ke: Donor Darah', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:40:19'),
(36, 7, 'Nursila', 'pendonor', 'BATAL_PENDAFTARAN', 'pendaftaran', 'Pendonor Nursila membatalkan pendaftaran', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:41:04'),
(37, 4, 'Dwiayy', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Dwiayy daftar ke: Donor Darah', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:46:15'),
(38, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk qilaa (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:57:21'),
(39, 10, 'Muh azrul', 'petugas_medis', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Dwiayy → Donor Darah: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 06:59:07'),
(40, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O (tingkat: darurat, goldar: O)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 07:07:05'),
(41, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: dipo (dipo@sidorah.id)', NULL, '{\"nama\":\"dipo\",\"email\":\"dipo@sidorah.id\",\"goldar\":\"B Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 11:49:00'),
(42, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'qilaa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 11:50:14'),
(43, 11, 'dipo', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor dipo daftar ke: Donor Darah', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 12:07:28'),
(44, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Araa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 12:09:09'),
(45, 3, 'Muhammad Sultan', 'admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'dipo → Donor Darah: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 12:09:46'),
(46, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor dipo: layak, Hb=13.5, Tekanan=130', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 12:48:28'),
(47, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong AB Positif untuk Sahrul (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 12:53:39'),
(48, 3, 'Muhammad Sultan', 'admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (tingkat: darurat, goldar: AB)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:00:29'),
(49, 10, 'Muh azrul', 'petugas_medis', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Azrul (ID: 3)', '{\"nama\":\"Azrul\"}', '{\"nama\":\"Azrul\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:09:02'),
(50, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:11:23'),
(51, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Sahrul: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:12:20'),
(52, 3, 'Muhammad Sultan', 'admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (tingkat: darurat, goldar: Semua)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:16:11'),
(53, 3, 'Muhammad Sultan', 'admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Zahra Firdauzi (ID: 5)', '{\"nama\":\"Zahra Firdauzi\"}', '{\"nama\":\"Zahra Firdauzi\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:20:37'),
(54, 3, 'Muhammad Sultan', 'admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Zahra Firdauzi (ID: 5)', '{\"nama\":\"Zahra Firdauzi\"}', '{\"nama\":\"Zahra Firdauzi\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 13:20:46'),
(55, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 14:21:14'),
(56, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O → nonaktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 14:21:38'),
(57, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → nonaktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 14:22:05'),
(58, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 14:22:17'),
(59, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Dinda (dinda@sidorah.id)', NULL, '{\"nama\":\"Dinda\",\"email\":\"dinda@sidorah.id\",\"goldar\":\"B Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 15:38:29'),
(60, 13, 'Dinda', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Dinda daftar ke: Donor Darah', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 15:39:11'),
(61, 10, 'Muh azrul', 'petugas_medis', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Azrul (ID: 3)', '{\"nama\":\"Azrul\"}', '{\"nama\":\"Azrul\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-30 15:43:05'),
(62, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong AB Positif untuk Salsa (normal)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:21:34'),
(63, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong AB Positif untuk Salsa (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:23:49'),
(64, 1, 'Super Administrator', 'super_admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'Donor Darah → dibatalkan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:24:56'),
(65, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: Donor Darah Bulanan (2026-05-31)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:26:28'),
(66, 4, 'Dwiayy', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Dwiayy daftar ke: Donor Darah Bulanan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:26:56'),
(67, 1, 'Super Administrator', 'super_admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Dwiayy → Donor Darah Bulanan: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:27:42'),
(68, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PENDAFTARAN', 'pendaftaran', 'Daftarkan Dwiayy ke Donor Darah Bulanan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:29:21'),
(69, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Dwiayy: layak, Hb=12.5, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:30:02'),
(70, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 2 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 06:31:25'),
(71, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: aktif → nonaktif', '{\"status\":\"aktif\"}', '{\"status\":\"nonaktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:54:28'),
(72, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: nonaktif → aktif', '{\"status\":\"nonaktif\"}', '{\"status\":\"aktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:54:31'),
(73, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: aktif → nonaktif', '{\"status\":\"aktif\"}', '{\"status\":\"nonaktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:54:37'),
(74, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun Muhammad Sultan diubah: nonaktif → aktif', '{\"status\":\"nonaktif\"}', '{\"status\":\"aktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:55:34'),
(75, 1, 'Super Administrator', 'super_admin', 'TAMBAH_AKUN', 'users', 'Super admin menambah akun baru: salsa (salsa@sidorah.id) role: super_admin', NULL, '{\"id\":14,\"nama\":\"salsa\",\"email\":\"salsa@sidorah.id\",\"role\":\"super_admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:56:32'),
(76, 1, 'Super Administrator', 'super_admin', 'EDIT_AKUN', 'users', 'Super admin mengedit akun ID 14: salsa (salsa@sidorah.id)', '{\"nama\":\"salsa\",\"email\":\"salsa@sidorah.id\",\"role\":\"super_admin\"}', '{\"nama\":\"salsa\",\"email\":\"salsa@sidorah.id\",\"role\":\"super_admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:56:58'),
(77, 1, 'Super Administrator', 'super_admin', 'TOGGLE_STATUS_AKUN', 'users', 'Status akun salsa diubah: aktif → nonaktif', '{\"status\":\"aktif\"}', '{\"status\":\"nonaktif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 15:58:27'),
(78, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: putri (putri@sidorah.id)', NULL, '{\"nama\":\"putri\",\"email\":\"putri@sidorah.id\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 16:04:43'),
(79, 15, 'putri', 'pendonor', 'GANTI_PASSWORD', 'users', 'Pendonor putri ganti password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-05-31 16:05:58'),
(80, 1, 'Super Administrator', 'super_admin', 'UPDATE_PROFIL', 'users', 'Super Administrator update profil', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:33:48'),
(81, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:35:29'),
(82, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:36:37'),
(83, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:38:34'),
(84, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:39:14'),
(85, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:40:09'),
(86, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:44:40'),
(87, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:44:51'),
(88, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:51:37'),
(89, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 06:51:49'),
(90, 9, 'hera', 'manajemen', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 07:37:42'),
(91, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 07:44:20'),
(92, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:26:19'),
(93, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:26:25'),
(94, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:26:32'),
(95, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:26:38'),
(96, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN_DONOR', 'pengaturan', 'Update aturan donor', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:29:23'),
(97, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:29:29'),
(98, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:29:36'),
(99, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:31:35'),
(100, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:38:57'),
(101, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:38:59'),
(102, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 09:39:01'),
(103, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 10:25:28'),
(104, 1, 'Super Administrator', 'super_admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'Donor Darah Bulanan → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 14:11:41'),
(105, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: Donor Darah Rutin Juni 2026 (2026-06-01)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 14:12:47'),
(106, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: sekar ayu (sekar@sidorah.id)', NULL, '{\"nama\":\"sekar ayu\",\"email\":\"sekar@sidorah.id\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 15:06:43'),
(107, 1, 'Super Administrator', 'super_admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: sekar ayu (ID: 9)', '{\"nama\":\"sekar ayu\"}', '{\"nama\":\"sekar ayu\",\"goldar\":\"AB Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-01 15:08:14'),
(108, 1, 'Super Administrator', 'super_admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'Donor Darah Rutin Juni 2026 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 00:27:21'),
(109, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: DONOR DARAH BULANAN JUNI 2026 (2026-06-02)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 00:28:03'),
(110, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 00:33:58'),
(111, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 01:08:08'),
(112, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 01:11:50'),
(113, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Zahrah Aulia (aulia@sidorah.id)', NULL, '{\"nama\":\"Zahrah Aulia\",\"email\":\"aulia@sidorah.id\",\"goldar\":\"AB Negatif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 02:00:47'),
(114, 17, 'Zahrah Aulia', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Zahrah Aulia daftar ke: DONOR DARAH BULANAN JUNI 2026', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 02:04:21'),
(115, 3, 'Muhammad Sultan', 'admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Zahrah Aulia → DONOR DARAH BULANAN JUNI 2026: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 02:07:14'),
(116, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Zahrah Aulia: layak, Hb=14.5, Tekanan=300/200', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 02:10:24'),
(117, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 20 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 02:11:51'),
(118, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:16:54'),
(119, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:16:56'),
(120, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:16:57'),
(121, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:16:59'),
(122, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (tingkat: darurat, goldar: Semua)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:18:29'),
(123, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:43:24'),
(124, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 03:43:28'),
(125, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Aprilianti Saputri (sidorah.april@gmail.com)', NULL, '{\"nama\":\"Aprilianti Saputri\",\"email\":\"sidorah.april@gmail.com\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 04:22:40'),
(126, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-02 04:27:30'),
(127, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-02 04:27:53'),
(128, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 04:50:16'),
(129, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 04:59:42'),
(130, NULL, 'Sistem', 'unknown', 'RESET_PASSWORD', 'users', 'Reset password berhasil: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 05:00:05'),
(131, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 05:05:09'),
(132, NULL, 'Sistem', 'unknown', 'RESET_PASSWORD', 'users', 'Reset password berhasil: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 05:05:37'),
(133, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 05:53:49'),
(134, NULL, 'Sistem', 'unknown', 'RESET_PASSWORD', 'users', 'Reset password berhasil: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-02 05:54:07'),
(135, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-03 09:48:51'),
(136, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-03 09:48:57'),
(137, NULL, 'Sistem', 'unknown', 'RESET_PASSWORD', 'users', 'Reset password berhasil: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-03 09:50:08'),
(138, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-03 11:57:15'),
(139, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-04 05:36:47'),
(140, 1, 'Super Administrator', 'super_admin', 'UPDATE_PROFIL', 'users', 'Super Administrator update profil', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-04 05:37:12'),
(141, 1, 'Super Administrator', 'super_admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'DONOR DARAH BULANAN JUNI 2026 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-04 05:40:24'),
(142, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (2026-06-04)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-04 05:42:02'),
(143, 18, 'Aprilianti Saputri', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Aprilianti Saputri daftar ke: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 05:42:34'),
(144, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 05:44:09'),
(145, NULL, 'Sistem', 'unknown', 'RESET_PASSWORD', 'users', 'Reset password berhasil: sidorah.april@gmail.com', NULL, NULL, '10.99.133.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 05:46:17'),
(146, 1, 'Super Administrator', 'super_admin', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0001 untuk indri', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 07:57:58'),
(147, 1, 'Super Administrator', 'super_admin', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 1 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 08:01:22'),
(148, 1, 'Super Administrator', 'super_admin', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 1 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 08:03:02'),
(149, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 09:16:34'),
(150, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 09:56:41'),
(151, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: Anggun (anggun@sidorah.id)', NULL, '{\"nama\":\"Anggun\",\"email\":\"anggun@sidorah.id\",\"goldar\":\"O Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:27:58'),
(152, 19, 'Anggun', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Anggun daftar ke: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:29:26'),
(153, 3, 'Muhammad Sultan', 'admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Anggun → BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:29:59'),
(154, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Anggun: layak, Hb=12.5, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:31:50'),
(155, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 10 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:32:46'),
(156, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 1 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:36:21'),
(157, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0002 untuk azrul', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:42:28');
INSERT INTO `audit_log` (`id_log`, `id_pengguna`, `nama_pengguna`, `role_pengguna`, `aksi`, `modul`, `detail`, `data_sebelum`, `data_sesudah`, `ip_address`, `user_agent`, `status`, `waktu`) VALUES
(158, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 2 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:44:41'),
(159, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 20 kantong A Positif untuk STVEN (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:50:29'),
(160, 3, 'Muhammad Sultan', 'admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH A+ (tingkat: darurat, goldar: A)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 12:56:34'),
(161, 3, 'Muhammad Sultan', 'admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:00:15'),
(162, 4, 'Dwiayy', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Dwiayy daftar ke: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:01:36'),
(163, 3, 'Muhammad Sultan', 'admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:02:12'),
(164, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 4 kantong', '{\"id_stok\":\"7\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"4\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-07\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-04 20:42:28\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:09:49'),
(165, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 2 kantong', '{\"id_stok\":\"2\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"2\",\"tanggal_masuk\":\"2026-05-30\",\"tanggal_kadaluarsa\":\"2026-06-12\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-05-30 14:21:36\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:09:52'),
(166, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 1 kantong', '{\"id_stok\":\"3\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-05-30\",\"tanggal_kadaluarsa\":\"2026-06-12\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-05-30 21:11:23\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:09:55'),
(167, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Negatif: 19 kantong', '{\"id_stok\":\"6\",\"golongan_darah\":\"AB\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"19\",\"tanggal_masuk\":\"2026-06-02\",\"tanggal_kadaluarsa\":\"2026-06-23\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 15:57:58\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:09:59'),
(168, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 10 kantong', '{\"id_stok\":\"8\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 20:32:46\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:10:03'),
(169, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 10 kantong', '{\"id_stok\":\"5\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-02\",\"tanggal_kadaluarsa\":\"2026-06-02\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-04 13:34:55\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:10:06'),
(170, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 2 kantong', '{\"id_stok\":\"4\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"2\",\"tanggal_masuk\":\"2026-05-31\",\"tanggal_kadaluarsa\":\"2026-05-29\",\"status_stok\":\"expired\",\"updated_at\":\"2026-05-31 14:31:25\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:10:09'),
(171, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 1 kantong', '{\"id_stok\":\"9\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-03\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-04 20:36:21\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:10:12'),
(172, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 4 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:10:43'),
(173, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 11 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":11}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:11:09'),
(174, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 11 kantong', '{\"id_stok\":\"11\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"11\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"0026-06-05\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-04 21:11:09\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:11:14'),
(175, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 12 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:11:35'),
(176, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 7 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:15:06'),
(177, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 7 kantong', '{\"id_stok\":\"13\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"7\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-07\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 21:15:06\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:16:46'),
(178, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 6 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:17:43'),
(179, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 4 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:22:12'),
(180, 3, 'Muhammad Sultan', 'admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 4 kantong', '{\"id_stok\":\"10\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"4\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-05\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-04 21:10:43\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:25:02'),
(181, 3, 'Muhammad Sultan', 'admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 12 kantong', '{\"id_stok\":\"12\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"12\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-06\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 21:11:35\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:26:13'),
(182, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 5 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:28:43'),
(183, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 13:33:36'),
(184, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 5 kantong', '{\"id_stok\":\"16\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-04 21:28:43\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:42:43'),
(185, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 4 kantong', '{\"id_stok\":\"15\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"4\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-10-05\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-04 21:22:12\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:42:46'),
(186, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 6 kantong', '{\"id_stok\":\"14\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"6\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-05\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 21:17:43\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:42:49'),
(187, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"17\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 21:33:36\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:42:52'),
(188, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:43:21'),
(189, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:44:06'),
(190, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"19\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-06\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 22:44:06\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:44:38'),
(191, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:45:48'),
(192, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0003 untuk JEREMI', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:50:39'),
(193, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0004 untuk ANGGUN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 14:53:56'),
(194, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 4 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:11:06'),
(195, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:13:05'),
(196, 10, 'Muh azrul', 'petugas_medis', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 20: 9 kantong, status: tersedia', '{\"id_stok\":\"20\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"9\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-09\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 22:53:56\"}', '{\"kantong\":9,\"status\":\"tersedia\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:13:32'),
(197, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0005 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:14:10'),
(198, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 5 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:14:38'),
(199, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:15:52'),
(200, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0006 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:18:53'),
(201, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 6 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:19:16'),
(202, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:21:28'),
(203, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 1 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:26:30'),
(204, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:40:49'),
(205, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 9 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 15:59:39'),
(206, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 0 kantong', '{\"id_stok\":\"18\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-06\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-04 22:50:39\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:08:43'),
(207, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 0 kantong', '{\"id_stok\":\"22\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-04 23:18:53\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:08:47'),
(208, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 1 kantong', '{\"id_stok\":\"21\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-04 23:13:05\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:08:50'),
(209, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 1 kantong', '{\"id_stok\":\"24\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-07-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-04 23:26:30\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:08:59'),
(210, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 8 kantong', '{\"id_stok\":\"20\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"8\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-09\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 23:14:10\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:09:04'),
(211, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 10 kantong', '{\"id_stok\":\"23\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-07-01\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 23:21:28\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:09:09'),
(212, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 9 kantong', '{\"id_stok\":\"26\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"9\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-07\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-04 23:59:39\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:09:13'),
(213, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:10:54'),
(214, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:11:10'),
(215, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 1 kantong', '{\"id_stok\":\"27\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-01-03\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-05 00:10:54\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:11:16'),
(216, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 1 kantong', '{\"id_stok\":\"25\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-01-01\",\"tanggal_kadaluarsa\":\"2026-02-01\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-04 23:40:49\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:11:19'),
(217, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok A Positif: +6 = 7 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:11:36'),
(218, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 7 kantong', '{\"id_stok\":\"28\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"7\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:11:36\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:11:50'),
(219, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:12:17'),
(220, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok A Positif: +6 = 7 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:12:34'),
(221, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 5 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:19:53'),
(222, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 5 kantong', '{\"id_stok\":\"30\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"0026-06-30\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-05 00:19:53\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:20:09'),
(223, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 4 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:20:31'),
(224, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok A Positif: +15 = 22 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:21:06'),
(225, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok A Positif: +10 = 32 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:22:42'),
(226, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok AB Positif: +10 = 14 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:23:12'),
(227, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 32 kantong', '{\"id_stok\":\"29\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"32\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:22:42\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:29:14'),
(228, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 14 kantong', '{\"id_stok\":\"31\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"14\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:23:12\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:29:16'),
(229, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:29:30'),
(230, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok A Positif: +10 = 11 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:29:49'),
(231, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 2 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:30:39'),
(232, 10, 'Muh azrul', 'petugas_medis', 'MERGE_STOK', 'stok_darah', 'Merge stok AB Positif: +8 = 10 kantong', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:31:11'),
(233, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 11 kantong', '{\"id_stok\":\"32\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"11\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:29:49\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:37:29'),
(234, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 10 kantong', '{\"id_stok\":\"33\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:31:11\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:37:33'),
(235, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:37:44'),
(236, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 1 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:38:00'),
(237, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 9 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:38:26'),
(238, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 9 kantong', '{\"id_stok\":\"36\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"9\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:38:26\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:41:52'),
(239, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 9 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-04 16:43:26'),
(240, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"34\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:37:44\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:17:25'),
(241, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 9 kantong', '{\"id_stok\":\"37\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"9\",\"tanggal_masuk\":\"2026-06-04\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 00:43:26\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:20:34'),
(242, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 9 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:20:55'),
(243, 1, 'Super Administrator', 'super_admin', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 38: 3 kantong, status: tersedia', '{\"id_stok\":\"38\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"9\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:20:55\"}', '{\"kantong\":3,\"status\":\"tersedia\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:22:02'),
(244, 1, 'Super Administrator', 'super_admin', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 35: 0 kantong, status: tersedia', '{\"id_stok\":\"35\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-30\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:20:55\"}', '{\"kantong\":0,\"status\":\"tersedia\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:22:16'),
(245, 1, 'Super Administrator', 'super_admin', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 35: 1 kantong, status: tersedia', '{\"id_stok\":\"35\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"tanggal_masuk\":\"2026-06-30\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:22:16\"}', '{\"kantong\":1,\"status\":\"tersedia\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:22:33'),
(246, 1, 'Super Administrator', 'super_admin', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 38: 8 kantong, status: tersedia', '{\"id_stok\":\"38\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"3\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:22:02\"}', '{\"kantong\":8,\"status\":\"tersedia\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:22:40'),
(247, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0007 untuk sultan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:25:02'),
(248, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 7 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:26:01'),
(249, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0008 untuk indri', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:37:14'),
(250, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 8 → proses', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:39:02'),
(251, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 8 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:39:09'),
(252, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 1 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:41:02'),
(253, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 8 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:45:29'),
(254, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0009 untuk indrinti', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:48:28'),
(255, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 9 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:49:03'),
(256, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:49:58'),
(257, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:50:27'),
(258, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:50:57'),
(259, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:51:15'),
(260, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 2 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:51:44'),
(261, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 5 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:52:06'),
(262, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 5 kantong', '{\"id_stok\":\"46\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-03-30\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-05 12:52:06\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:52:16'),
(263, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 5 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:52:44'),
(264, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0010 untuk mila', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:56:45'),
(265, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 10 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:57:24'),
(266, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 1 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:58:15'),
(267, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"48\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:58:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:58:28'),
(268, 10, 'Muh azrul', 'petugas_medis', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 35: 1 kantong, status: kritis', '{\"id_stok\":\"35\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-30\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:58:15\"}', '{\"kantong\":1,\"status\":\"kritis\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:59:11'),
(269, 10, 'Muh azrul', 'petugas_medis', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 40: 1 kantong, status: kritis', '{\"id_stok\":\"40\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:58:15\"}', '{\"kantong\":1,\"status\":\"kritis\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 04:59:22'),
(270, 10, 'Muh azrul', 'petugas_medis', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 39: 1 kantong, status: kritis', '{\"id_stok\":\"39\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:58:15\"}', '{\"kantong\":1,\"status\":\"kritis\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:00:04'),
(271, 10, 'Muh azrul', 'petugas_medis', 'EDIT_STOK', 'stok_darah', 'Edit stok ID 45: 2 kantong, status: kritis', '{\"id_stok\":\"45\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"2\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:58:15\"}', '{\"kantong\":2,\"status\":\"kritis\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:00:13'),
(272, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 6 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:01:01'),
(273, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 0 kantong', '{\"id_stok\":\"38\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:01'),
(274, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 0 kantong', '{\"id_stok\":\"47\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:08'),
(275, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 6 kantong', '{\"id_stok\":\"49\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"6\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:12'),
(276, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"41\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:49:58\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:15'),
(277, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 5 kantong', '{\"id_stok\":\"43\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:50:57\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:19'),
(278, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Negatif: 5 kantong', '{\"id_stok\":\"44\",\"golongan_darah\":\"A\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"5\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:51:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:26'),
(279, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Negatif: 5 kantong', '{\"id_stok\":\"42\",\"golongan_darah\":\"A\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"5\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 12:51:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:33'),
(280, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"35\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-30\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:36'),
(281, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"39\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:40'),
(282, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"40\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:44');
INSERT INTO `audit_log` (`id_log`, `id_pengguna`, `nama_pengguna`, `role_pengguna`, `aksi`, `modul`, `detail`, `data_sebelum`, `data_sesudah`, `ip_address`, `user_agent`, `status`, `waktu`) VALUES
(283, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 2 kantong', '{\"id_stok\":\"45\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"2\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:01:01\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:04:46'),
(284, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:05:09'),
(285, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"50\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:05:09\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:05:26'),
(286, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:05:39'),
(287, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 5 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:05:53'),
(288, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 5 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:06:23'),
(289, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 5 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:07:23'),
(290, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:07:49'),
(291, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0011 untuk sultan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:08:52'),
(292, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'STVEN: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:10:08'),
(293, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Salsa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:10:09'),
(294, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 5 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:13:40'),
(295, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 0 kantong AB Positif untuk sultan (normal)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:15:25'),
(296, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk sultan (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:16:28'),
(297, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk mila (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:16:57'),
(298, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 6 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:19:08'),
(299, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 6 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:19:37'),
(300, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 6 kantong', '{\"id_stok\":\"57\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"6\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-03-04\",\"status_stok\":\"expired\",\"updated_at\":\"2026-06-05 13:19:08\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:19:51'),
(301, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 1 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:23:02'),
(302, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 6 kantong', '{\"id_stok\":\"58\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"6\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-05 13:19:37\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:32:00'),
(303, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 1 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:34:39'),
(304, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 1 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:35:06'),
(305, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 6 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-05 05:35:30'),
(306, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 11 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:27:24'),
(307, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'mila: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:29:47'),
(308, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: yani (yani@sidorah.id)', NULL, '{\"nama\":\"yani\",\"email\":\"yani@sidorah.id\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:44:52'),
(309, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: DONOR DARAH ULTAH 67 (2026-06-06)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:48:19'),
(310, 20, 'yani', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor yani daftar ke: DONOR DARAH ULTAH 67', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'sukses', '2026-06-06 05:49:15'),
(311, 1, 'Super Administrator', 'super_admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'yani → DONOR DARAH ULTAH 67: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:49:39'),
(312, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor yani: layak, Hb=12.5, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:50:59'),
(313, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:52:28'),
(314, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0012 untuk ISMA', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:55:13'),
(315, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 12 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 05:56:40'),
(316, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 06:07:59'),
(317, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (tingkat: darurat, goldar: A)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 06:10:06'),
(318, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 6 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 06:54:14'),
(319, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk sultan (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:00:02'),
(320, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Salsa (normal)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:15:23'),
(321, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk SULTAN (normal)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:18:40'),
(322, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk awan (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:23:05'),
(323, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'awan: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:17'),
(324, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Salsa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:33'),
(325, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'sultan: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:36'),
(326, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Salsa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:38'),
(327, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'SULTAN: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:39'),
(328, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'sultan: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:41'),
(329, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'sultan: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:24:44'),
(330, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:50:31'),
(331, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 07:50:55'),
(332, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 2 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:05:33'),
(333, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0013 untuk ANGGUN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:12:17'),
(334, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 13 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:12:32'),
(335, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0014 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:14:26'),
(336, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 2 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:36:32'),
(337, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:36:56'),
(338, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 0 kantong', '{\"id_stok\":\"55\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-10\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-06 16:36:32\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:04'),
(339, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 0 kantong', '{\"id_stok\":\"51\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-06 16:36:32\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:07'),
(340, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 0 kantong', '{\"id_stok\":\"62\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"0\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"habis\",\"updated_at\":\"2026-06-06 16:36:32\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:10'),
(341, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 2 kantong', '{\"id_stok\":\"68\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"2\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-06 16:37:36\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:14'),
(342, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 10 kantong', '{\"id_stok\":\"69\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-06 16:36:56\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:17'),
(343, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 5 kantong', '{\"id_stok\":\"54\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-06 16:16:22\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:22'),
(344, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 5 kantong', '{\"id_stok\":\"52\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-06 16:16:22\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:31'),
(345, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"59\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-06 16:16:22\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:39'),
(346, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 5 kantong', '{\"id_stok\":\"64\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-06 16:37:36\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:47:43'),
(347, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:48:56'),
(348, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:57:22'),
(349, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 14 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 08:59:02'),
(350, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Salsa (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 09:00:26'),
(351, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor yani: layak, Hb=12.5, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 09:06:09'),
(352, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0015 untuk ANGGUN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 09:10:36'),
(353, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 15 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 09:12:15'),
(354, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk ISMA (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 11:42:27'),
(355, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Jeremi (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 14:15:38'),
(356, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0016 untuk Jeremi', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 14:16:58'),
(357, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Jeremi: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 14:17:21'),
(358, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Indrianti (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 14:47:13'),
(359, 3, 'Muhammad Sultan', 'admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Indrianti: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 14:48:21'),
(360, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk STVEN (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 15:02:44'),
(361, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'STVEN: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 15:03:29'),
(362, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0017 untuk STVEN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 15:04:20'),
(363, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'sukses', '2026-06-06 16:05:04'),
(364, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 16:05:37'),
(365, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 8 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 16:05:50'),
(366, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 6 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-06 16:06:05'),
(367, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:34:41'),
(368, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:34:45'),
(369, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:34:51'),
(370, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH A+ → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:34:54'),
(371, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH A+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:34:58'),
(372, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 8 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:38:03'),
(373, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 5 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:38:38'),
(374, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 6 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:43:15'),
(375, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 6 kantong', '{\"id_stok\":\"77\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"6\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:43:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:46:38'),
(376, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 5 kantong', '{\"id_stok\":\"76\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:43:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:46:58'),
(377, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 3 kantong', '{\"id_stok\":\"70\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"3\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-07 00:43:03\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:25'),
(378, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 8 kantong', '{\"id_stok\":\"75\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"8\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:38:03\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:30'),
(379, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 6 kantong', '{\"id_stok\":\"74\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"6\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 00:06:05\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:34'),
(380, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 4 kantong', '{\"id_stok\":\"53\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"4\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:43:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:37'),
(381, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 1 kantong', '{\"id_stok\":\"61\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:43:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:41'),
(382, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 8 kantong', '{\"id_stok\":\"73\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"8\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 00:05:50\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:44'),
(383, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"72\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 00:05:37\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:47'),
(384, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 5 kantong', '{\"id_stok\":\"56\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-07 14:39:26\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:53'),
(385, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"60\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"jenis_darah\":\"WB\",\"tanggal_masuk\":\"2026-06-05\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-07 00:43:03\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:47:57'),
(386, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:48:10'),
(387, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:48:48'),
(388, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:49:09'),
(389, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 10 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:49:24'),
(390, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:49:38'),
(391, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:49:57'),
(392, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 10 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:50:15'),
(393, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 5 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:51:10'),
(394, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Negatif: 5 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:51:25'),
(395, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 5 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:51:44'),
(396, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 5 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:52:06'),
(397, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Negatif: 5 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-07 06:52:25'),
(398, 1, 'Super Administrator', 'super_admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: Donor Darah Rutin RS SIDORAH (2026-06-08)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:11:20'),
(399, 1, 'Super Administrator', 'super_admin', 'EDIT_KEGIATAN', 'kegiatan_donor', 'Edit kegiatan ID 14: Donor Darah Rutin RS SIDORAH', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:12:05'),
(400, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:18:29'),
(401, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:21:12'),
(402, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:30:34'),
(403, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:31:39'),
(404, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:34:32'),
(405, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 02:34:38'),
(406, 4, 'Dwiayy', 'pendonor', 'DAFTAR_KEGIATAN', 'pendaftaran', 'Pendonor Dwiayy daftar ke: Donor Darah Rutin RS SIDORAH', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 06:51:05'),
(407, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 07:21:43'),
(408, 4, 'Dwiayy', 'pendonor', 'BATAL_PENDAFTARAN', 'pendaftaran', 'Pendonor Dwiayy membatalkan pendaftaran', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 07:22:55'),
(409, 4, 'Dwiayy', 'pendonor', 'BATAL_PENDAFTARAN', 'pendaftaran', 'Pendonor Dwiayy membatalkan pendaftaran', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 07:22:59'),
(410, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O+ (tingkat: darurat, goldar: Semua)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 07:23:57'),
(411, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 07:32:29'),
(412, 1, 'Super Administrator', 'super_admin', 'UPDATE_PENGATURAN', 'pengaturan', 'Update info rumah sakit', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 07:33:09'),
(413, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 1 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'sukses', '2026-06-09 08:07:03'),
(414, 3, 'Muhammad Sultan', 'admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Aprilianti Saputri → BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-11 02:02:15'),
(415, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_KEGIATAN', 'kegiatan_donor', 'Tambah kegiatan: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (2026-06-11)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-11 02:04:29'),
(416, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Aprilianti Saputri: layak, Hb=12, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-11 02:05:26'),
(417, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-11 02:06:50'),
(418, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0018 untuk sultan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 05:39:42'),
(419, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 18 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 05:40:14'),
(420, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0019 untuk azrul', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 05:48:46'),
(421, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 19 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:10:57'),
(422, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 17 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:11:04'),
(423, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 16 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:11:11'),
(424, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0020 untuk salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:14:52'),
(425, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 20 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:15:18'),
(426, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PENDAFTARAN', 'pendaftaran', 'Daftarkan Anggun ke BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:23:31');
INSERT INTO `audit_log` (`id_log`, `id_pengguna`, `nama_pengguna`, `role_pengguna`, `aksi`, `modul`, `detail`, `data_sebelum`, `data_sesudah`, `ip_address`, `user_agent`, `status`, `waktu`) VALUES
(427, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Dinda: layak, Hb=12.5, Tekanan=110', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:37:54'),
(428, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Dinda: ditunda, Hb=12.5, Tekanan=', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:39:27'),
(429, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Aprilianti Saputri: layak, Hb=0, Tekanan=', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:43:38'),
(430, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Anggun: layak, Hb=0, Tekanan=', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 06:46:41'),
(431, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 14 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":14}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 07:09:58'),
(432, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0021 untuk sultan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 07:11:55'),
(433, 3, 'Muhammad Sultan', 'admin', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 21 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 07:12:18'),
(434, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Anggun: layak, Hb=12.5, Tekanan=', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 07:27:46'),
(435, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Aprilianti Saputri: layak, Hb=12.5, Tekanan=', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 07:38:53'),
(436, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Anggun: layak, Hb=0, Tekanan=', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 07:45:36'),
(437, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 10:56:15'),
(438, 1, 'Super Administrator', 'super_admin', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Anggun: layak, Hb=12.5, Tekanan=110/80', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:10:38'),
(439, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Negatif: 5 kantong', '{\"id_stok\":\"85\",\"golongan_darah\":\"A\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-12 15:31:18\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:12:58'),
(440, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 4 kantong', '{\"id_stok\":\"92\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"4\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-12\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-12 15:11:55\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:03'),
(441, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 14 kantong', '{\"id_stok\":\"84\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"14\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"donor\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-12 19:10:38\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:09'),
(442, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 10 kantong', '{\"id_stok\":\"82\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:49:38\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:19'),
(443, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Negatif: 10 kantong', '{\"id_stok\":\"91\",\"golongan_darah\":\"A\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-11\",\"tanggal_kadaluarsa\":\"2026-07-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-11 10:06:50\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:27'),
(444, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 5 kantong', '{\"id_stok\":\"79\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-12 18:56:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:38'),
(445, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"80\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:49:09\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:44'),
(446, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Negatif: 5 kantong', '{\"id_stok\":\"89\",\"golongan_darah\":\"O\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-07 14:52:25\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:49'),
(447, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 2 kantong', '{\"id_stok\":\"63\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"2\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-06\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-12 18:56:15\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:13:56'),
(448, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok O Positif: 5 kantong', '{\"id_stok\":\"88\",\"golongan_darah\":\"O\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-09 10:04:58\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:01'),
(449, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok A Positif: 10 kantong', '{\"id_stok\":\"83\",\"golongan_darah\":\"A\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-07 14:49:57\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:06'),
(450, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Negatif: 5 kantong', '{\"id_stok\":\"87\",\"golongan_darah\":\"AB\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-07 14:51:44\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:11'),
(451, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Positif: 1 kantong', '{\"id_stok\":\"90\",\"golongan_darah\":\"AB\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-09\",\"tanggal_kadaluarsa\":\"2026-11-04\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-12 15:31:18\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:16'),
(452, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Positif: 1 kantong', '{\"id_stok\":\"81\",\"golongan_darah\":\"B\",\"rhesus\":\"Positif\",\"jumlah_kantong\":\"1\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-12 15:11:55\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:20'),
(453, 1, 'Super Administrator', 'super_admin', 'HAPUS_STOK', 'stok_darah', 'Hapus stok B Negatif: 5 kantong', '{\"id_stok\":\"86\",\"golongan_darah\":\"B\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"5\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"manual\",\"tanggal_masuk\":\"2026-06-07\",\"tanggal_kadaluarsa\":\"2026-06-30\",\"status_stok\":\"kritis\",\"updated_at\":\"2026-06-07 14:51:25\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:25'),
(454, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:14:56'),
(455, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Positif: 10 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:15:14'),
(456, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:15:22'),
(457, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 10 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:15:31'),
(458, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:15:54'),
(459, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok B Negatif: 10 kantong', NULL, '{\"goldar\":\"B\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:16:07'),
(460, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:16:31'),
(461, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:17:11'),
(462, 1, 'Super Administrator', 'super_admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Negatif: 10 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:17:24'),
(463, 1, 'Super Administrator', 'super_admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'ISMA: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 11:18:04'),
(464, 1, 'Super Administrator', 'super_admin', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Anggun: layak, Hb=12.5, Tekanan=120/80', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 12:05:41'),
(465, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0022 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 12:19:29'),
(466, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 22 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 12:51:41'),
(467, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 12:52:00'),
(468, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 15 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":15}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 12:52:19'),
(469, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 13:00:09'),
(470, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk sultan (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 13:30:58'),
(471, 1, 'Super Administrator', 'super_admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Salsa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 13:31:22'),
(472, 1, 'Super Administrator', 'super_admin', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'sultan: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-12 13:31:24'),
(473, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 3 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:02:25'),
(474, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0023 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:10:27'),
(475, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 9 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:10:53'),
(476, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0024 untuk azrul', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:12:38'),
(477, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 23 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:12:56'),
(478, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 24 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:13:11'),
(479, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0025 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:17:23'),
(480, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 9 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:17:42'),
(481, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0026 untuk Salsa', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:18:39'),
(482, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 26 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:20:35'),
(483, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 25 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:20:47'),
(484, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0027 untuk azrul', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:36:06'),
(485, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0028 untuk Azrul', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:42:14'),
(486, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:42:29'),
(487, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:42:39'),
(488, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Negatif: 1 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Negatif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:42:46'),
(489, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 28 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:43:01'),
(490, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 27 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 14:43:10'),
(491, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor yani: tidak_layak, Hb=11, Tekanan=120/90', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 15:28:12'),
(492, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Lala (darurat)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 15:43:01'),
(493, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Lala: menunggu → tidak_terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"tidak_terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 15:56:53'),
(494, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Sahrul (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:08:19'),
(495, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk Yolaa (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:14:11'),
(496, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Yolaa: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:15:05'),
(497, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'Sahrul: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:18:32'),
(498, 3, 'Muhammad Sultan', 'admin', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 15 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":15}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:47:44'),
(499, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0029 untuk STVEN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:49:33'),
(500, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 20 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:54:33'),
(501, 3, 'Muhammad Sultan', 'admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'DONOR DARAH ULTAH 67 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:55:16'),
(502, 3, 'Muhammad Sultan', 'admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:55:19'),
(503, 3, 'Muhammad Sultan', 'admin', 'UBAH_STATUS_KEGIATAN', 'kegiatan_donor', 'Donor Darah Rutin RS SIDORAH → dibatalkan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-13 16:55:22'),
(504, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O+ (tingkat: darurat, goldar: O)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:18:16'),
(505, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:22:52'),
(506, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O (tingkat: darurat, goldar: O)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:23:18'),
(507, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:24:27'),
(508, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O (tingkat: darurat, goldar: O)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:25:08'),
(509, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:26:41'),
(510, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O (tingkat: darurat, goldar: Semua)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:25'),
(511, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:38'),
(512, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:39'),
(513, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:43'),
(514, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:43'),
(515, 1, 'Super Administrator', 'super_admin', 'TOGGLE_NOTIF', 'notifikasi_darurat', 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O → aktif', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:44'),
(516, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:27:50'),
(517, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O (tingkat: darurat, goldar: Semua)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:28:31'),
(518, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH O', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:29:15'),
(519, 1, 'Super Administrator', 'super_admin', 'BUAT_NOTIF_DARURAT', 'notifikasi_darurat', 'Buat notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+ (tingkat: darurat, goldar: Semua)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:30:03'),
(520, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '10.220.240.247', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'sukses', '2026-06-14 06:41:23'),
(521, NULL, 'Sistem', 'unknown', 'LUPA_PASSWORD', 'users', 'Request reset password: sidorah.april@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:41:56'),
(522, NULL, 'Sistem', 'unknown', 'RESET_PASSWORD', 'users', 'Reset password berhasil: sidorah.april@gmail.com', NULL, NULL, '10.220.240.126', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:42:53'),
(523, 1, 'Super Administrator', 'super_admin', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Zahra Firdauzi: tidak_layak, Hb=3, Tekanan=110/80', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:54:56'),
(524, 1, 'Super Administrator', 'super_admin', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor Zahra Firdauzi: layak, Hb=15, Tekanan=110/80', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 06:56:53'),
(525, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: reski (resk@sidorah.id)', NULL, '{\"nama\":\"reski\",\"email\":\"resk@sidorah.id\",\"goldar\":\"A Negatif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 07:03:37'),
(526, 1, 'Super Administrator', 'super_admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: reski (ID: 14)', '{\"nama\":\"reski\"}', '{\"nama\":\"reski\",\"goldar\":\"A Negatif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 07:04:19'),
(527, 1, 'Super Administrator', 'super_admin', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor reski: layak, Hb=15, Tekanan=110/90', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-14 07:07:31'),
(528, 1, 'Super Administrator', 'super_admin', 'HAPUS_NOTIF', 'notifikasi_darurat', 'Hapus notifikasi: BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 07:03:38'),
(529, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 29 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 07:05:28'),
(530, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0030 untuk Muhammad Jody Asfary', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 07:17:45'),
(531, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 30 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 07:18:47'),
(532, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 07:19:55'),
(533, 1, 'Super Administrator', 'super_admin', 'TOGGLE_PENDONOR', 'pendonor', 'Aprilianti Saputri dinonaktifkan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:18:11'),
(534, 1, 'Super Administrator', 'super_admin', 'TOGGLE_PENDONOR', 'pendonor', 'Aprilianti Saputri diaktifkan', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:18:14'),
(535, 1, 'Super Administrator', 'super_admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Aprilianti Saputri (ID: 11)', '{\"nama\":\"Aprilianti Saputri\"}', '{\"nama\":\"Aprilianti Saputri\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:25:48'),
(536, 1, 'Super Administrator', 'super_admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: Aprilianti Saputri (ID: 11)', '{\"nama\":\"Aprilianti Saputri\"}', '{\"nama\":\"Aprilianti Saputri\",\"goldar\":\"A Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:26:17'),
(537, 1, 'Super Administrator', 'super_admin', 'EDIT_PENDONOR', 'pendonor', 'Edit data pendonor: sekar ayu (ID: 9)', '{\"nama\":\"sekar ayu\"}', '{\"nama\":\"sekar ayu\",\"goldar\":\"AB Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:26:29'),
(538, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor putri: ditunda, Hb=12.5, Tekanan=110/100', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:29:14'),
(539, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_RIWAYAT_DONOR', 'riwayat_donor', 'Rekam donor putri: layak, Hb=13, Tekanan=100/100', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:30:41'),
(540, 10, 'Muh azrul', 'petugas_medis', 'HAPUS_STOK', 'stok_darah', 'Hapus stok AB Negatif: 10 kantong', '{\"id_stok\":\"112\",\"golongan_darah\":\"AB\",\"rhesus\":\"Negatif\",\"jumlah_kantong\":\"10\",\"jenis_darah\":\"WB\",\"sumber_stok\":\"pmi\",\"tanggal_masuk\":\"2026-06-15\",\"tanggal_kadaluarsa\":\"2026-07-20\",\"status_stok\":\"tersedia\",\"updated_at\":\"2026-06-15 15:19:55\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:45:19'),
(541, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 5 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:45:36'),
(542, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Positif: 1 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Positif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:46:06'),
(543, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 1 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:46:24'),
(544, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0031 untuk Muhammad Jody Asfary', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:47:11'),
(545, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_TRANSFUSI', 'transfusi_darah', 'Update status transfusi ID 31 → selesai', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:47:25'),
(546, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok AB Negatif: 10 kantong', NULL, '{\"goldar\":\"AB\",\"rhesus\":\"Negatif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:48:21'),
(547, 1, 'Super Administrator', 'super_admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Dwiayy → Donor Darah Bulanan: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:50:38'),
(548, 1, 'Super Administrator', 'super_admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Anggun → BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:50:41'),
(549, 1, 'Super Administrator', 'super_admin', 'VERIFIKASI_PENDAFTARAN', 'pendaftaran', 'Dinda → Donor Darah: disetujui', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:50:43'),
(550, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: farhan (farhan@sidorah.id)', NULL, '{\"nama\":\"farhan\",\"email\":\"farhan@sidorah.id\",\"goldar\":\"B Positif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 08:52:35'),
(551, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PENDONOR', 'pendonor', 'Menambah pendonor baru: rizal (rizal@sidorah.id)', NULL, '{\"nama\":\"rizal\",\"email\":\"rizal@sidorah.id\",\"goldar\":\"AB Negatif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:00:49'),
(552, 18, 'Aprilianti Saputri', 'pendonor', 'GANTI_PASSWORD', 'users', 'Pendonor Aprilianti Saputri ganti password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:04:59'),
(553, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0032 untuk Muhammad Jody Asfary', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:06:19'),
(554, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0033 untuk Muhammad Jody Asfary', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:11:28'),
(555, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_TRANSFUSI', 'transfusi_darah', 'Transfusi TRF-202606-0034 untuk Muhammad Jody Asfary', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:12:02'),
(556, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:12:18'),
(557, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok A Positif: 10 kantong', NULL, '{\"goldar\":\"A\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:12:37'),
(558, 10, 'Muh azrul', 'petugas_medis', 'TAMBAH_STOK', 'stok_darah', 'Tambah stok O Positif: 10 kantong', NULL, '{\"goldar\":\"O\",\"rhesus\":\"Positif\",\"kantong\":10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:12:57'),
(559, 1, 'Super Administrator', 'super_admin', 'TAMBAH_PERMINTAAN', 'permintaan_darah', 'Permintaan 1 kantong A Positif untuk STVEN (mendesak)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 09:16:51'),
(560, 10, 'Muh azrul', 'petugas_medis', 'UPDATE_PERMINTAAN', 'permintaan_darah', 'STVEN: menunggu → terpenuhi', '{\"status\":\"menunggu\"}', '{\"status\":\"terpenuhi\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'sukses', '2026-06-15 10:14:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan_donor`
--

CREATE TABLE `kegiatan_donor` (
  `id_kegiatan` int(11) NOT NULL,
  `nama_kegiatan` varchar(150) DEFAULT NULL,
  `tanggal_kegiatan` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `lokasi` varchar(200) DEFAULT NULL,
  `kuota_peserta` int(11) DEFAULT NULL,
  `jumlah_terdaftar` int(11) DEFAULT 0,
  `persyaratan` text DEFAULT NULL,
  `status_kegiatan` enum('aktif','selesai','dibatalkan') DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kegiatan_donor`
--

INSERT INTO `kegiatan_donor` (`id_kegiatan`, `nama_kegiatan`, `tanggal_kegiatan`, `waktu_mulai`, `waktu_selesai`, `lokasi`, `kuota_peserta`, `jumlah_terdaftar`, `persyaratan`, `status_kegiatan`, `id_admin`) VALUES
(7, 'Donor Darah Rutin Juni 2026', '2026-06-15', '08:00:00', '12:00:00', 'Aula RS Andi Makassau', 50, 1, '', 'selesai', 1),
(8, 'Donor Darah', '2026-05-30', '08:00:00', '12:00:00', 'Aula RS Suppa', 50, 3, '', 'dibatalkan', 1),
(9, 'Donor Darah Bulanan', '2026-05-31', '08:00:00', '20:00:00', 'AULA RS Andi Makassau', 50, 2, '', 'selesai', 1),
(10, 'Donor Darah Rutin Juni 2026', '2026-06-01', '08:00:00', '12:00:00', 'Aula RS Andi Makassau', 50, 0, 'Usia 17 - 45 Kesehatan Jasmani dan Rohani', 'selesai', 1),
(11, 'DONOR DARAH BULANAN JUNI 2026', '2026-06-02', '08:00:00', '12:00:00', 'AULA RUMAH SAKIT SIDORAH', 50, 1, '', 'selesai', 1),
(12, 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', '2026-06-04', '08:00:00', '12:00:00', 'AULA RUMAH SAKIT SIDORAH', 50, 2, '', 'selesai', 1),
(13, 'DONOR DARAH ULTAH 67', '2026-06-06', '08:00:00', '18:00:00', 'Aula RS Andi Makassau', 50, 1, '', 'selesai', 1),
(14, 'Donor Darah Rutin RS SIDORAH', '2026-06-09', '08:00:00', '12:00:00', 'Aula RS SIDORAH', 50, 0, '', 'dibatalkan', 1),
(15, 'BUTUH DONOR DARAH SEGERA - GOLONGAN DARAH AB+', '2026-06-11', '08:00:00', '12:00:00', 'AULA RUMAH SAKIT SIDORAH', 50, 1, '', 'selesai', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `judul` varchar(150) DEFAULT NULL,
  `isi_pesan` text DEFAULT NULL,
  `tipe_notifikasi` enum('jadwal','stok_kritis','konfirmasi','pengumuman') DEFAULT NULL,
  `id_pengirim` int(11) DEFAULT NULL,
  `id_penerima` int(11) DEFAULT NULL,
  `status_baca` tinyint(1) DEFAULT 0,
  `waktu_kirim` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `judul`, `isi_pesan`, `tipe_notifikasi`, `id_pengirim`, `id_penerima`, `status_baca`, `waktu_kirim`) VALUES
(1, 'Test Notifikasi', 'Ini adalah notifikasi test', 'pengumuman', 1, 3, 1, '2026-06-15 09:18:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_darurat`
--

CREATE TABLE `notifikasi_darurat` (
  `id_notif` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `golongan_darah` enum('A','B','AB','O','Semua') DEFAULT 'Semua',
  `rhesus` enum('Positif','Negatif','Semua') DEFAULT 'Semua',
  `tingkat` enum('info','warning','darurat') DEFAULT 'darurat',
  `id_kegiatan` int(11) DEFAULT NULL,
  `id_pembuat` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expired_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expired_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `password_reset`
--

INSERT INTO `password_reset` (`id`, `id_pengguna`, `token`, `expired_at`, `used`, `created_at`) VALUES
(22, 18, '4cf3da391dd9fe5d81b4a4475b9c00a8e5f010c9aeb795a9ffb83ffab6a6991e', '2026-06-14 15:41:29', 1, '2026-06-14 06:41:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_pendonor` int(11) DEFAULT NULL,
  `id_kegiatan` int(11) DEFAULT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_pendaftaran` enum('menunggu','disetujui','ditolak','batal') DEFAULT 'menunggu',
  `catatan_admin` text DEFAULT NULL,
  `tanggal_verifikasi` timestamp NULL DEFAULT NULL,
  `id_admin_verifikasi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftaran`
--

INSERT INTO `pendaftaran` (`id_pendaftaran`, `id_pendonor`, `id_kegiatan`, `tanggal_daftar`, `status_pendaftaran`, `catatan_admin`, `tanggal_verifikasi`, `id_admin_verifikasi`) VALUES
(1, 2, 7, '2026-05-29 14:47:12', 'disetujui', '', '2026-05-29 08:47:43', 3),
(2, 4, 8, '2026-05-30 06:40:19', 'batal', NULL, NULL, NULL),
(3, 2, 8, '2026-05-30 06:46:15', 'disetujui', '', '2026-05-30 00:59:07', 10),
(4, 6, 8, '2026-05-30 12:07:28', 'disetujui', '', '2026-05-30 06:09:46', 3),
(5, 7, 8, '2026-05-30 15:39:11', 'disetujui', '', '2026-06-15 08:50:43', 1),
(6, 2, 9, '2026-05-31 06:26:56', 'disetujui', '', '2026-05-31 00:27:42', 1),
(7, 2, 9, '2026-05-31 06:29:21', 'disetujui', '', '2026-06-15 08:50:38', 1),
(8, 10, 11, '2026-06-02 02:04:21', 'disetujui', '', '2026-06-01 20:07:14', 3),
(9, 11, 12, '2026-06-04 05:42:34', 'disetujui', '', '2026-06-10 20:02:15', 3),
(10, 12, 12, '2026-06-04 12:29:26', 'disetujui', '', '2026-06-04 06:29:59', 3),
(11, 2, 12, '2026-06-04 13:01:36', 'batal', NULL, NULL, NULL),
(12, 13, 13, '2026-06-06 05:49:15', 'disetujui', '', '2026-06-05 23:49:39', 1),
(13, 2, 14, '2026-06-09 06:51:05', 'batal', NULL, NULL, NULL),
(14, 12, 15, '2026-06-12 06:23:31', 'disetujui', '', '2026-06-15 08:50:41', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendonor`
--

CREATE TABLE `pendonor` (
  `id_pendonor` int(11) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL COMMENT 'Nomor Induk Kependudukan',
  `rfid_uid` varchar(50) DEFAULT NULL COMMENT 'UID kartu RFID pendonor',
  `tanggal_lahir` date DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `golongan_darah` enum('A','B','AB','O') DEFAULT NULL,
  `rhesus` enum('Positif','Negatif') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT NULL,
  `total_donor` int(11) DEFAULT 0,
  `donor_terakhir` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendonor`
--

INSERT INTO `pendonor` (`id_pendonor`, `id_pengguna`, `nik`, `rfid_uid`, `tanggal_lahir`, `tempat_lahir`, `jenis_kelamin`, `pekerjaan`, `golongan_darah`, `rhesus`, `alamat`, `berat_badan`, `riwayat_penyakit`, `status_aktif`, `total_donor`, `donor_terakhir`) VALUES
(2, 4, NULL, NULL, '2000-01-15', NULL, 'P', NULL, 'O', 'Positif', 'Jl. Contoh No. 1, Makassar', 52.00, '', 1, 1, '2026-05-31'),
(3, 6, '', NULL, '2004-02-02', '', 'P', '', 'A', 'Positif', 'Jl. Nurusamawati', 60.00, '', 1, 1, '2026-05-30'),
(4, 7, NULL, NULL, '1968-06-01', NULL, 'P', NULL, 'O', 'Positif', 'Jl.Nurusamawati', 64.00, '', 1, 0, NULL),
(5, 8, NULL, NULL, '2006-06-12', NULL, 'P', NULL, 'A', 'Positif', '', 45.00, '', 1, 1, '2026-06-14'),
(6, 11, NULL, NULL, '2001-01-01', NULL, 'L', NULL, 'B', 'Positif', 'Jl. Syamsul Bahri', 65.00, '', 1, 1, '2026-05-30'),
(7, 13, '7372043011678', NULL, '2006-06-12', 'Makassar', 'P', 'Mahasiswa', 'B', 'Positif', 'Jl.Bumi Harapan', 54.00, '', 1, 1, '2026-06-12'),
(8, 15, '7378888888888', NULL, '2007-06-12', 'Makassar', 'P', 'Mahasiswa', 'A', 'Positif', 'Jl.Bumi Harapan', 55.00, '', 1, 1, '2026-06-15'),
(9, 16, '7375555555', NULL, '2005-12-30', 'Parepare', 'P', '', 'AB', 'Positif', 'Jl. Jendral Sudirman', 60.00, '', 1, 0, NULL),
(10, 17, '737154545454', NULL, '2006-01-23', 'pare pare', 'P', 'Mahasiswa', 'AB', 'Negatif', 'Soreang', 100.00, 'Demam', 1, 1, '2026-06-02'),
(11, 18, '', NULL, '2006-06-04', 'Pinrang', 'P', 'Mahasiswa', 'A', 'Positif', 'Jl. Jendral Sudirman', 56.00, '', 1, 3, '2026-06-12'),
(12, 19, '7378888888888', NULL, '2006-11-16', 'Parepare', 'P', 'Mahasiswa', 'O', 'Positif', 'Jl. Timurama', 39.00, '', 1, 6, '2026-06-12'),
(13, 20, '7375555555', NULL, '1987-10-09', 'Parepare', 'P', 'IRT', 'A', 'Positif', 'Jl. Bau Massepe', 67.00, '', 1, 2, '2026-06-06'),
(14, 21, '7379076555', NULL, '2005-01-30', 'Pinrang', 'P', 'Mahasiswa', 'A', 'Negatif', 'Jl. Jendral Ahmad Yani', 55.00, '', 1, 1, '2026-06-14'),
(15, 22, '737456234567', NULL, '2006-06-30', 'Makassar', 'L', 'Mahasiswa', 'B', 'Positif', 'Jl. Sultan Bandara Internasional', 60.00, '', 1, 0, NULL),
(16, 23, '737200090', NULL, '2002-03-23', 'Makassar', 'L', 'PNS', 'AB', 'Negatif', 'Jl. Jendral Ahmad Yani.', 70.00, '', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `kunci` varchar(100) NOT NULL,
  `nilai` text DEFAULT NULL,
  `keterangan` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `kunci`, `nilai`, `keterangan`) VALUES
(1, 'nama_rs', 'SIDORAH', 'Nama Rumah Sakit'),
(2, 'alamat_rs', 'Jl. Kesehatan No.1, Makassar', 'Alamat Rumah Sakit'),
(3, 'telp_rs', '0411-123-456', 'Telepon Rumah Sakit'),
(4, 'email_rs', 'info@sidorah.id', 'Email Rumah Sakit'),
(5, 'jam_operasional', '24 Jam / 7 Hari', 'Jam Operasional'),
(6, 'telp_darurat', '119', 'Nomor Darurat'),
(7, 'min_usia_donor', '17', 'Usia Minimum Donor'),
(8, 'max_usia_donor', '65', 'Usia Maksimum Donor'),
(9, 'min_bb_donor', '45', 'Berat Badan Minimum Donor (kg)'),
(10, 'min_hb_donor', '12.5', 'Hemoglobin Minimum Donor (g/dL)'),
(11, 'jeda_donor_bulan', '3', 'Jeda Minimum Antar Donor (bulan)'),
(12, 'password_default_pendonor', 'Donor123!', 'Password Default Pendonor Baru'),
(13, 'ip_server', '10.220.240.126', 'IP Server untuk QR Code'),
(14, 'suhu_min_normal', '2', 'Suhu minimum normal penyimpanan darah (°C)'),
(15, 'suhu_max_normal', '6', 'Suhu maksimum normal penyimpanan darah (°C)'),
(16, 'iot_api_key', 'SIDORAH-IOT-2026', 'API Key untuk autentikasi ESP32'),
(17, 'iot_device_id', 'ESP32-001', 'ID perangkat IoT utama');

-- --------------------------------------------------------

--
-- Struktur dari tabel `permintaan_darah`
--

CREATE TABLE `permintaan_darah` (
  `id_permintaan` int(11) NOT NULL,
  `golongan_darah` enum('A','B','AB','O') DEFAULT NULL,
  `rhesus` enum('Positif','Negatif') DEFAULT NULL,
  `jumlah_kantong` int(11) DEFAULT NULL,
  `hemoglobin` decimal(4,1) DEFAULT NULL,
  `jenis_darah` enum('WB','PRC') DEFAULT 'WB',
  `nama_pasien` varchar(100) DEFAULT NULL,
  `no_rekam_medis` varchar(50) DEFAULT NULL,
  `tingkat_urgensi` enum('normal','mendesak','darurat') DEFAULT NULL,
  `status_permintaan` enum('menunggu','terpenuhi','tidak_terpenuhi') DEFAULT NULL,
  `tanggal_permintaan` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_petugas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `permintaan_darah`
--

INSERT INTO `permintaan_darah` (`id_permintaan`, `golongan_darah`, `rhesus`, `jumlah_kantong`, `hemoglobin`, `jenis_darah`, `nama_pasien`, `no_rekam_medis`, `tingkat_urgensi`, `status_permintaan`, `tanggal_permintaan`, `id_petugas`) VALUES
(1, 'A', 'Positif', 2, NULL, 'WB', 'Araa', 'RM-233', 'darurat', 'terpenuhi', '2026-05-30 06:24:16', 10),
(2, 'A', 'Positif', 1, NULL, 'WB', 'yusuf', 'RM-233', 'darurat', 'tidak_terpenuhi', '2026-05-30 06:26:04', 10),
(3, 'A', 'Positif', 1, NULL, 'WB', 'Araa', 'RM-233', 'darurat', 'terpenuhi', '2026-05-30 06:27:52', 10),
(4, 'A', 'Positif', 1, NULL, 'WB', 'qilaa', 'RM-233', 'darurat', 'terpenuhi', '2026-05-30 06:57:21', 10),
(5, 'AB', 'Positif', 1, NULL, 'WB', 'Sahrul', 'RM-0098', 'mendesak', 'terpenuhi', '2026-05-30 12:53:39', 10),
(6, 'AB', 'Positif', 1, NULL, 'WB', 'Salsa', 'RM-255', 'normal', 'terpenuhi', '2026-05-31 06:21:34', 1),
(7, 'AB', 'Positif', 1, NULL, 'WB', 'Salsa', 'RM-255', 'darurat', 'terpenuhi', '2026-05-31 06:23:49', 1),
(8, 'A', 'Positif', 20, NULL, 'WB', 'STVEN', 'RM-330', 'darurat', 'terpenuhi', '2026-06-04 12:50:29', 10),
(9, 'AB', 'Positif', 0, NULL, 'WB', 'sultan', 'RM-122345', 'normal', 'terpenuhi', '2026-06-05 05:15:25', 10),
(10, 'A', 'Positif', 1, NULL, 'WB', 'sultan', 'RM-12234', 'mendesak', 'terpenuhi', '2026-06-05 05:16:28', 10),
(11, 'A', 'Positif', 1, NULL, 'WB', 'mila', 'RM-12234', 'darurat', 'terpenuhi', '2026-06-05 05:16:57', 10),
(12, 'A', 'Positif', 1, NULL, 'WB', 'sultan', 'RM-255', 'mendesak', 'terpenuhi', '2026-06-06 07:00:02', 10),
(13, 'A', 'Positif', 1, 6.0, 'WB', 'Salsa', 'RM-255', 'normal', 'terpenuhi', '2026-06-06 07:15:23', 10),
(14, 'A', 'Positif', 1, 8.5, 'WB', 'SULTAN', 'RM-880', 'normal', 'terpenuhi', '2026-06-06 07:18:40', 10),
(15, 'A', 'Positif', 1, 3.0, 'WB', 'awan', 'RM-110', 'darurat', 'terpenuhi', '2026-06-06 07:23:05', 10),
(16, 'A', 'Positif', 1, 7.5, 'WB', 'Salsa', 'RM-255', 'mendesak', 'terpenuhi', '2026-06-06 09:00:26', 10),
(17, 'A', 'Positif', 1, 3.0, 'WB', 'ISMA', 'RM-255', 'darurat', 'terpenuhi', '2026-06-06 11:42:27', 10),
(18, 'A', 'Positif', 1, 3.0, 'WB', 'Jeremi', 'RM-12234', 'darurat', 'terpenuhi', '2026-06-06 14:15:38', 3),
(19, 'A', 'Positif', 1, 3.0, 'WB', 'Indrianti', 'RM-123', 'darurat', 'terpenuhi', '2026-06-06 14:47:13', 10),
(20, 'A', 'Positif', 1, 3.0, 'PRC', 'STVEN', 'RM-255', 'darurat', 'terpenuhi', '2026-06-06 15:02:44', 10),
(21, 'A', 'Positif', 1, 3.0, 'WB', 'sultan', 'RM-12234', 'darurat', 'terpenuhi', '2026-06-12 13:30:58', 1),
(22, 'A', 'Positif', 1, 3.0, 'WB', 'Lala', 'RM-111', 'darurat', 'tidak_terpenuhi', '2026-06-13 15:43:01', 10),
(23, 'A', 'Positif', 1, 9.0, 'WB', 'Sahrul', 'RM-0098', 'mendesak', 'terpenuhi', '2026-06-13 16:08:19', 3),
(24, 'A', 'Positif', 1, 6.0, 'WB', 'Yolaa', 'RM-8976', 'mendesak', 'terpenuhi', '2026-06-13 16:14:11', 3),
(25, 'A', 'Positif', 1, 9.0, 'WB', 'STVEN', 'RM-330', 'mendesak', 'terpenuhi', '2026-06-15 09:16:51', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rfid_scan`
--

CREATE TABLE `rfid_scan` (
  `id` int(11) NOT NULL,
  `rfid_uid` varchar(50) NOT NULL COMMENT 'UID kartu RFID',
  `id_pendonor` int(11) DEFAULT NULL,
  `waktu_scan` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_id` varchar(50) DEFAULT 'ESP32-001',
  `lokasi` varchar(100) DEFAULT 'Pintu Masuk Donor',
  `status` enum('dikenal','tidak_dikenal') DEFAULT 'tidak_dikenal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_donor`
--

CREATE TABLE `riwayat_donor` (
  `id_riwayat` int(11) NOT NULL,
  `id_pendaftaran` int(11) DEFAULT NULL,
  `id_pendonor` int(11) DEFAULT NULL,
  `tanggal_donor` date DEFAULT NULL,
  `volume_darah_ml` int(11) DEFAULT 450,
  `hasil_pemeriksaan` enum('layak','tidak_layak','ditunda') DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `hemoglobin` decimal(4,1) DEFAULT NULL,
  `catatan_medis` text DEFAULT NULL,
  `id_petugas_medis` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_donor`
--

INSERT INTO `riwayat_donor` (`id_riwayat`, `id_pendaftaran`, `id_pendonor`, `tanggal_donor`, `volume_darah_ml`, `hasil_pemeriksaan`, `tekanan_darah`, `hemoglobin`, `catatan_medis`, `id_petugas_medis`) VALUES
(1, NULL, 3, '2026-05-30', 450, 'layak', '110', 12.5, '', 10),
(2, NULL, 6, '2026-05-30', 450, 'layak', '130', 13.5, '', 10),
(3, NULL, 2, '2026-05-31', 450, 'layak', '110', 12.5, 'Sehat', 10),
(4, NULL, 10, '2026-06-02', 450, 'layak', '300/200', 14.5, '', 10),
(5, NULL, 12, '2026-06-04', 450, 'layak', '110', 12.5, '', 10),
(6, NULL, 13, '2026-06-06', 450, 'layak', '110', 12.5, '', 10),
(7, NULL, 13, '2026-06-06', 450, 'layak', '110', 12.5, '', 10),
(8, NULL, 11, '2026-06-11', 450, 'layak', '110', 12.0, '', 10),
(9, NULL, 7, '2026-06-12', 450, 'layak', '110', 12.5, '', 10),
(10, NULL, 7, '2026-06-12', 450, 'ditunda', '', 12.5, '', 10),
(11, NULL, 11, '2026-06-12', 450, 'layak', '', 0.0, '', 10),
(12, NULL, 12, '2026-06-12', 450, 'layak', '', 0.0, '', 10),
(13, NULL, 12, '2026-06-12', 450, 'layak', '', 12.5, '', 10),
(14, NULL, 11, '2026-06-12', 450, 'layak', '', 12.5, '', 10),
(15, NULL, 12, '2026-06-12', 450, 'layak', '', 0.0, '', 10),
(16, NULL, 12, '2026-06-12', 450, 'layak', '110/80', 12.5, '', 1),
(17, NULL, 12, '2026-06-12', 450, 'layak', '120/80', 12.5, '', 1),
(18, NULL, 13, '2026-06-13', 450, 'tidak_layak', '120/90', 11.0, '', 10),
(19, NULL, 5, '2026-06-14', 450, 'tidak_layak', '110/80', 3.0, '', 1),
(20, NULL, 5, '2026-06-14', 450, 'layak', '110/80', 15.0, '', 1),
(21, NULL, 14, '2026-06-14', 450, 'layak', '110/90', 15.0, '', 1),
(22, NULL, 8, '2026-06-15', 450, 'ditunda', '110/100', 12.5, 'baik', 10),
(23, NULL, 8, '2026-06-15', 450, 'layak', '100/100', 13.0, '', 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sensor_stok`
--

CREATE TABLE `sensor_stok` (
  `id` int(11) NOT NULL,
  `golongan_darah` enum('A','B','AB','O') NOT NULL,
  `rhesus` enum('Positif','Negatif') NOT NULL,
  `jumlah_kantong` int(11) NOT NULL DEFAULT 0,
  `berat_gram` decimal(8,2) DEFAULT NULL COMMENT 'Berat dari load cell',
  `device_id` varchar(50) DEFAULT 'ESP32-001',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sensor_suhu`
--

CREATE TABLE `sensor_suhu` (
  `id` int(11) NOT NULL,
  `suhu` decimal(5,2) NOT NULL COMMENT 'Suhu dalam Celsius',
  `kelembaban` decimal(5,2) DEFAULT NULL COMMENT 'Kelembaban %',
  `device_id` varchar(50) DEFAULT 'ESP32-001' COMMENT 'ID perangkat ESP32',
  `lokasi` varchar(100) DEFAULT 'Ruang Penyimpanan Darah',
  `status` enum('normal','warning','kritis') DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_darah`
--

CREATE TABLE `stok_darah` (
  `id_stok` int(11) NOT NULL,
  `golongan_darah` enum('A','B','AB','O') DEFAULT NULL,
  `rhesus` enum('Positif','Negatif') DEFAULT NULL,
  `jumlah_kantong` int(11) DEFAULT NULL,
  `jenis_darah` enum('WB','PRC') DEFAULT 'WB',
  `sumber_stok` varchar(50) DEFAULT 'manual',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `status_stok` enum('tersedia','habis','kritis','expired') DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok_darah`
--

INSERT INTO `stok_darah` (`id_stok`, `golongan_darah`, `rhesus`, `jumlah_kantong`, `jenis_darah`, `sumber_stok`, `tanggal_masuk`, `tanggal_kadaluarsa`, `status_stok`, `updated_at`) VALUES
(65, 'A', 'Positif', 0, 'WB', 'donor', '2026-06-06', '2026-06-30', 'habis', '2026-06-15 09:12:37'),
(66, 'A', 'Positif', 0, 'PRC', 'manual', '2026-06-06', '2026-06-30', 'habis', '2026-06-15 09:12:37'),
(67, 'A', 'Positif', 0, 'PRC', 'manual', '2026-06-06', '2026-06-30', 'habis', '2026-06-15 09:12:37'),
(71, 'A', 'Positif', 0, 'WB', 'manual', '2026-06-06', '2026-06-30', 'habis', '2026-06-15 09:12:37'),
(78, 'A', 'Positif', 0, 'WB', 'manual', '2026-06-07', '2026-06-30', 'habis', '2026-06-15 09:12:37'),
(93, 'A', 'Positif', 0, 'PRC', 'pmi', '2026-06-12', '2026-07-17', 'habis', '2026-06-15 09:12:37'),
(94, 'A', 'Positif', 0, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'habis', '2026-06-15 09:12:37'),
(95, 'B', 'Positif', 10, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'tersedia', '2026-06-12 11:15:14'),
(96, 'AB', 'Positif', 10, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'tersedia', '2026-06-12 11:15:22'),
(97, 'O', 'Positif', 11, 'WB', 'donor', '2026-06-12', '2026-07-17', 'tersedia', '2026-06-12 12:05:41'),
(98, 'A', 'Negatif', 1, 'WB', 'donor', '2026-06-12', '2026-07-17', 'kritis', '2026-06-14 07:07:31'),
(99, 'B', 'Negatif', 10, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'tersedia', '2026-06-12 11:16:07'),
(100, 'A', 'Positif', 0, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'habis', '2026-06-15 09:12:37'),
(101, 'AB', 'Negatif', 0, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'habis', '2026-06-15 08:48:21'),
(102, 'O', 'Negatif', 10, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'tersedia', '2026-06-12 11:17:24'),
(103, 'A', 'Positif', 0, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'habis', '2026-06-15 09:12:37'),
(104, 'AB', 'Positif', 15, 'WB', 'pmi', '2026-06-12', '2026-07-17', 'tersedia', '2026-06-12 12:52:19'),
(105, 'A', 'Negatif', 0, 'WB', 'pmi', '2026-06-13', '2026-07-18', 'habis', '2026-06-13 14:42:46'),
(106, 'A', 'Negatif', 0, 'WB', 'pmi', '2026-06-13', '2026-07-18', 'habis', '2026-06-13 14:42:46'),
(107, 'A', 'Positif', 0, 'WB', 'pmi', '2026-06-13', '2026-07-18', 'habis', '2026-06-15 09:12:37'),
(108, 'A', 'Positif', 0, 'WB', 'pmi', '2026-06-13', '2026-07-18', 'habis', '2026-06-15 09:12:37'),
(109, 'A', 'Negatif', 1, 'WB', 'pmi', '2026-06-13', '2026-07-18', 'kritis', '2026-06-13 14:42:46'),
(110, 'A', 'Positif', 0, 'PRC', 'pmi', '2026-06-14', '2026-07-19', 'habis', '2026-06-15 09:12:37'),
(111, 'A', 'Positif', 0, 'WB', 'pmi', '2026-06-14', '2026-07-19', 'habis', '2026-06-15 09:12:37'),
(113, 'AB', 'Negatif', 0, 'WB', 'pmi', '2026-06-15', '2026-07-20', 'habis', '2026-06-15 08:48:21'),
(114, 'AB', 'Positif', 1, 'WB', 'pmi', '2026-06-15', '2026-07-20', 'tersedia', '2026-06-15 08:46:06'),
(115, 'AB', 'Negatif', 0, 'WB', 'pmi', '2026-06-15', '2026-07-20', 'habis', '2026-06-15 08:48:21'),
(116, 'AB', 'Negatif', 10, 'WB', 'pmi', '2026-06-15', '2026-07-20', 'tersedia', '2026-06-15 08:48:21'),
(117, 'A', 'Positif', 9, 'WB', 'pmi', '2026-06-15', '2026-07-20', 'tersedia', '2026-06-15 10:14:42'),
(118, 'A', 'Positif', 10, 'PRC', 'pmi', '2026-06-15', '2026-07-20', 'tersedia', '2026-06-15 09:12:37'),
(119, 'O', 'Positif', 10, 'PRC', 'pmi', '2026-06-15', '2026-07-20', 'tersedia', '2026-06-15 09:12:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transfusi_darah`
--

CREATE TABLE `transfusi_darah` (
  `id_transfusi` int(11) NOT NULL,
  `no_transfusi` varchar(20) NOT NULL COMMENT 'Nomor unik transfusi',
  `nama_pasien` varchar(150) NOT NULL,
  `no_rekam_medis` varchar(50) DEFAULT NULL,
  `usia_pasien` int(3) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT 'L',
  `golongan_darah` enum('A','B','AB','O') NOT NULL,
  `rhesus` enum('Positif','Negatif') NOT NULL,
  `id_stok` int(11) DEFAULT NULL COMMENT 'Kantong darah yang digunakan',
  `volume_ml` int(11) DEFAULT 450,
  `jenis_darah` enum('WB','PRC') DEFAULT 'WB',
  `tanggal_transfusi` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `ruangan` varchar(100) DEFAULT NULL,
  `nama_dokter` varchar(150) DEFAULT NULL COMMENT 'Dokter penanggung jawab transfusi',
  `diagnosa` text DEFAULT NULL,
  `indikasi` varchar(200) DEFAULT NULL,
  `reaksi_transfusi` enum('tidak_ada','ringan','sedang','berat') DEFAULT 'tidak_ada',
  `keterangan_reaksi` text DEFAULT NULL,
  `status` enum('proses','selesai','dibatalkan') DEFAULT 'proses',
  `id_petugas` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transfusi_darah`
--

INSERT INTO `transfusi_darah` (`id_transfusi`, `no_transfusi`, `nama_pasien`, `no_rekam_medis`, `usia_pasien`, `jenis_kelamin`, `golongan_darah`, `rhesus`, `id_stok`, `volume_ml`, `jenis_darah`, `tanggal_transfusi`, `jam_mulai`, `jam_selesai`, `ruangan`, `nama_dokter`, `diagnosa`, `indikasi`, `reaksi_transfusi`, `keterangan_reaksi`, `status`, `id_petugas`, `catatan`, `created_at`) VALUES
(1, 'TRF-202606-0001', 'indri', 'RM-12234', 19, 'P', 'AB', '', NULL, 450, 'WB', '2026-06-04', '15:57:00', '16:01:00', 'ICU', NULL, '', 'trauma', 'berat', 'demam', 'selesai', 1, '', '2026-06-04 07:57:58'),
(2, 'TRF-202606-0002', 'azrul', 'RM-330', 30, 'P', 'A', '', NULL, 450, 'WB', '2026-06-04', '20:40:00', '20:43:00', 'ICU', 'dr.Stven', 'Demam', '', 'tidak_ada', 'demam', 'selesai', 10, '', '2026-06-04 12:42:28'),
(3, 'TRF-202606-0003', 'JEREMI', 'RM-990', 19, 'L', 'A', '', NULL, 450, 'WB', '2026-06-04', '03:00:00', '00:00:00', 'ICU', 'dr. STVEN S.KOM.MKOM', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-04 14:50:39'),
(4, 'TRF-202606-0004', 'ANGGUN', 'RM-2110', 20, 'P', 'A', '', NULL, 450, 'WB', '2026-06-04', '10:00:00', '23:00:00', 'ICU', 'dr. Jeremi', '', '', 'sedang', '', 'selesai', 10, '', '2026-06-04 14:53:56'),
(5, 'TRF-202606-0005', 'Salsa', 'RM-255', 10, 'L', 'A', '', NULL, 450, 'WB', '2026-06-04', '12:00:00', '00:00:00', 'ICU', 'mmm', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-04 15:14:10'),
(6, 'TRF-202606-0006', 'Salsa', 'RM-880', 11, 'L', 'A', '', NULL, 450, 'WB', '2026-06-04', '00:00:00', '00:00:00', 'ICU', 'dr. STVEN S.KOM.MKOM', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-04 15:18:53'),
(7, 'TRF-202606-0007', 'sultan', 'RM-122345', 20, 'L', 'B', '', NULL, 3600, 'WB', '2026-06-05', '12:24:00', '09:00:00', 'ICU', 'dr. surya', '', '', 'sedang', 'demam', 'selesai', 10, '', '2026-06-05 04:25:02'),
(8, 'TRF-202606-0008', 'indri', 'RM-12234', 90, 'P', 'B', '', NULL, 3150, 'WB', '2026-06-05', '12:37:00', '13:00:00', 'ICU', 'dr. surya', '', '', 'ringan', '', 'selesai', 10, '', '2026-06-05 04:37:14'),
(9, 'TRF-202606-0009', 'indrinti', 'RM-12234', 1, 'P', 'B', '', NULL, 3150, 'WB', '2026-06-05', '12:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-05 04:48:28'),
(10, 'TRF-202606-0010', 'mila', 'RM-122345', 11, 'P', 'B', '', NULL, 2250, 'WB', '2026-06-05', '12:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-05 04:56:45'),
(11, 'TRF-202606-0011', 'sultan', '', 9, 'L', 'A', '', NULL, 2250, 'WB', '2026-06-05', '10:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-05 05:08:52'),
(12, 'TRF-202606-0012', 'ISMA', 'RM-990', 11, 'P', 'A', '', NULL, 2700, 'WB', '2026-06-06', '13:54:00', '18:00:00', 'ICU', 'dr.yani', '', '', 'tidak_ada', 'demam', 'selesai', 10, '', '2026-06-06 05:55:13'),
(13, 'TRF-202606-0013', 'ANGGUN', 'RM-110', 0, 'L', 'A', '', 67, 250, 'PRC', '2026-06-06', '16:00:00', '00:00:00', 'ICU', 'dr. Jeremi', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-06 08:12:17'),
(14, 'TRF-202606-0014', 'Salsa', 'RM-255', 9, 'L', 'A', '', 67, 250, 'PRC', '2026-06-06', '16:14:00', '00:00:00', 'ICU', 'dr.yani', '', '', 'sedang', '', 'selesai', 10, '', '2026-06-06 08:14:26'),
(15, 'TRF-202606-0015', 'ANGGUN', 'RM-2110', 0, 'L', 'A', '', 66, 250, 'PRC', '2026-06-06', '10:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'ringan', '', 'selesai', 10, '', '2026-06-06 09:10:36'),
(16, 'TRF-202606-0016', 'Jeremi', 'RM-12234', 11, 'L', 'A', '', NULL, 450, 'WB', '2026-06-06', '22:16:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 3, '', '2026-06-06 14:16:58'),
(17, 'TRF-202606-0017', 'STVEN', 'RM-255', 1, 'L', 'A', '', NULL, 450, 'WB', '2026-06-06', '23:04:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-06 15:04:20'),
(18, 'TRF-202606-0018', 'sultan', 'RM-255', 10, 'L', 'A', '', NULL, 6750, 'WB', '2026-06-12', '03:09:00', '03:06:00', 'ICU', 'dr. surya', '', '', 'ringan', '', 'selesai', 10, '', '2026-06-12 05:39:42'),
(19, 'TRF-202606-0019', 'azrul', 'RM-21100', 11, 'L', 'A', '', 78, 6750, 'WB', '2026-06-12', '03:00:00', '00:00:00', 'ICU', 'dr.Stven', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-12 05:48:46'),
(20, 'TRF-202606-0020', 'salsa', 'RM-255', 1, 'P', 'B', '', NULL, 4500, 'WB', '2026-06-12', '10:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-12 06:14:52'),
(21, 'TRF-202606-0021', 'sultan', '', 0, 'L', 'B', '', NULL, 4500, 'WB', '2026-06-12', '03:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'ringan', '', 'selesai', 3, '', '2026-06-12 07:11:55'),
(22, 'TRF-202606-0022', 'Salsa', 'RM-330', 9, 'L', 'A', '', 94, 4050, 'WB', '2026-06-12', '12:00:00', '00:00:00', 'ICU', 'dr.yani', '', '', 'ringan', '', 'selesai', 10, '', '2026-06-12 12:19:29'),
(23, 'TRF-202606-0023', 'Salsa', 'RM-660', 11, 'L', 'A', '', 98, 4050, 'WB', '2026-06-13', '10:00:00', '22:00:00', 'Bangsal', 'dr. Putri Aulia', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-13 14:10:26'),
(24, 'TRF-202606-0024', 'azrul', 'RM-330', 10, 'L', 'A', '', 98, 4500, 'WB', '2026-06-13', '00:00:00', '22:00:00', 'ICU', 'dr. Putri Aulia', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-13 14:12:38'),
(25, 'TRF-202606-0025', 'Salsa', 'RM-255', 8, 'L', 'A', '', 105, 3600, 'WB', '2026-06-13', '00:00:00', '09:09:00', 'ICU', '', '', '', 'sedang', '', 'selesai', 10, '', '2026-06-13 14:17:23'),
(26, 'TRF-202606-0026', 'Salsa', 'RM-255', 9, 'L', 'A', '', 106, 4050, 'WB', '2026-06-13', '00:00:00', '20:00:00', 'ICU', 'dr. surya', '', 'anemia', 'tidak_ada', '', 'selesai', 10, '', '2026-06-13 14:18:39'),
(27, 'TRF-202606-0027', 'azrul', 'RM-330', 11, 'L', 'A', '', 106, 4500, 'WB', '2026-06-13', '00:00:00', '00:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-13 14:36:06'),
(28, 'TRF-202606-0028', 'Azrul', 'RM-255', 11, 'L', 'A', '', 106, 450, 'WB', '2026-06-13', '00:00:00', '09:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-13 14:42:13'),
(29, 'TRF-202606-0029', 'STVEN', 'RM-255', 11, 'L', 'A', '', 99, 4050, 'WB', '2026-06-14', '10:00:00', '18:00:00', 'ICU', 'dr. Jeremi', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-13 16:49:33'),
(30, 'TRF-202606-0030', 'Muhammad Jody Asfary', '', 11, 'L', 'AB', '', 101, 4500, 'WB', '2026-06-15', '03:00:00', '09:00:00', 'Bangsal', 'dr. Ahliq, Sp.pd', 'Demam, sakit kepala ringan.', '', 'berat', 'demam ringan', 'selesai', 10, 'bentar lagi sembuh.', '2026-06-15 07:17:45'),
(31, 'TRF-202606-0031', 'Muhammad Jody Asfary', '', 10, 'L', 'AB', '', 113, 2700, 'WB', '2026-06-15', '09:09:00', '12:00:00', 'ICU', 'dr. surya', '', '', 'tidak_ada', '', 'selesai', 10, '', '2026-06-15 08:47:11'),
(32, 'TRF-202606-0032', 'Muhammad Jody Asfary', 'RM-87700', 10, 'L', 'A', '', 65, 11700, 'WB', '2026-06-15', '10:00:00', NULL, 'ICU', 'dr. Ahliq, Sp.pd', '', '', 'tidak_ada', NULL, 'proses', 10, NULL, '2026-06-15 09:06:19'),
(33, 'TRF-202606-0033', 'Muhammad Jody Asfary', 'RM-87700', 10, 'L', 'A', '', 93, 4750, 'PRC', '2026-06-15', '12:00:00', NULL, 'ICU', 'dr. surya', '', '', 'tidak_ada', NULL, 'proses', 10, NULL, '2026-06-15 09:11:28'),
(34, 'TRF-202606-0034', 'Muhammad Jody Asfary', 'RM-87700', 1, 'L', 'A', '', 110, 250, 'PRC', '2026-06-15', '09:00:00', NULL, 'ICU', 'dr. Ahliq, Sp.pd', '', '', 'tidak_ada', NULL, 'proses', 10, NULL, '2026-06-15 09:12:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(15) DEFAULT NULL,
  `role` enum('super_admin','admin','pendonor','petugas_medis','manajemen') DEFAULT NULL,
  `status_akun` enum('aktif','nonaktif','terkunci') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_pengguna`, `nama_lengkap`, `email`, `password`, `no_telepon`, `role`, `status_akun`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'Super Administrator', 'superadmin@sidorah.id', '$2y$10$n8dfFbrmMYmZPf7h./G8J.BOdvysJQC/zcGoWYr7UpJ9G9lLwhtFC', '08100000001', 'super_admin', 'aktif', '2026-05-29 11:14:32', '2026-06-21 05:35:53', '2026-06-21 05:35:53'),
(3, 'Muhammad Sultan', 'muhsultan@sidorah.id', '$2y$10$zxCM.X5wpospqDI4gnk6fOSKUmqu3Lik3WPWTBSZKs1g0YFh7Nf5u', '082223641592', 'admin', 'aktif', '2026-05-29 13:37:40', '2026-06-19 03:46:43', '2026-06-19 03:46:43'),
(4, 'Dwiayy', 'dwiayy@sidorah.id', '$2y$10$az01a6tCMfw5ALuvwPlHw.oGvW1OQ/8GAOloQc6VwCr4AV7nnMGDy', '082223641592', 'pendonor', 'aktif', '2026-05-29 13:42:21', '2026-06-21 02:16:22', '2026-06-21 02:16:22'),
(5, 'Syifa Audyah', 'syifaaudyah@sidorah.id', '$2y$10$yM8ImKGGqURAf0ysYYGuZuSZN0rNuhhxXkgxQWwJI25UNDnf7UtEW', '0821111111', 'pendonor', 'aktif', '2026-05-29 15:13:50', '2026-05-30 03:52:59', '2026-05-30 03:52:59'),
(6, 'Azrul', 'lokasifay@gmail.com', '$2y$10$krzo4WKbRUL/rNf70Juew.UX3q7rzZ.HAE2pfcUNvEb8/cN2oE15u', '08222222222', 'pendonor', 'aktif', '2026-05-30 03:33:44', '2026-05-30 14:11:48', '2026-05-30 14:11:48'),
(7, 'Nursila', 'nursila@sidorah.id', '$2y$10$aPvDmYTDQy3HaMSQrT8Ca.evjoVXAMte2/qtN79YFAloegJiSRiQ2', '0899999999', 'pendonor', 'aktif', '2026-05-30 04:22:44', '2026-05-30 06:04:27', '2026-05-30 06:04:27'),
(8, 'Zahra Firdauzi', 'ara@sidorah.id', '$2y$10$MZ.RrJrhqXEdedpkKqP0vedoVpxWsO0WNkSwmr30u5SWhMYQKhYEK', '081111111', 'pendonor', 'aktif', '2026-05-30 04:30:32', '2026-06-01 13:04:03', '2026-06-01 13:04:03'),
(9, 'hera', 'hera@sidorah.id', '$2y$10$ZbvmkacmNKdojxa6Cqg.FeI4/BrKrlDH3etJIjodET4K/2Kgc6Agi', '082222222', 'manajemen', 'aktif', '2026-05-30 05:02:22', '2026-06-13 15:06:56', '2026-06-13 15:06:56'),
(10, 'Muh azrul', 'azrul@sidorah.id', '$2y$10$x7GuTq0uDjIXk93mG.xLH.2GYX.4oGCBroivQrrrYWjySg3IbuXB.', '081111111', 'petugas_medis', 'aktif', '2026-05-30 06:17:51', '2026-06-20 11:59:34', '2026-06-20 11:59:34'),
(11, 'dipo', 'dipo@sidorah.id', '$2y$10$B.8HDhWUybGWCUikKjN6PueYVhAe6obGiLmpy.CIgCV3NPvo9Rmg.', '089999999', 'pendonor', 'aktif', '2026-05-30 11:49:00', '2026-05-30 11:49:49', '2026-05-30 11:49:49'),
(13, 'Dinda', 'dinda@sidorah.id', '$2y$10$3gWrpStP6GqDtdaTIqlnQ.R49O1TEvOF5BFdPAR9vt9PCxdKf0r1W', '087777777', 'pendonor', 'aktif', '2026-05-30 15:38:29', '2026-06-01 08:03:05', '2026-06-01 08:03:05'),
(15, 'putri', 'putri@sidorah.id', '$2y$10$xgDrJqkhJPoUpOPqkFQm6e6JxzSiJE/GNWHXAu/V77lE.ET4Het4K', '08777777', 'pendonor', 'aktif', '2026-05-31 16:04:43', '2026-05-31 16:08:00', '2026-05-31 16:08:00'),
(16, 'sekar ayu', 'sekar@sidorah.id', '$2y$10$ApNwjo5t3ojEBLp0K7rcmeeY1QqshEw7PwK/PCmNh8HzeHAJm.Fja', '089999999', 'pendonor', 'aktif', '2026-06-01 15:06:43', '2026-06-01 15:06:43', NULL),
(17, 'Zahrah Aulia', 'aulia@sidorah.id', '$2y$10$baMtN/4qVKsp9VXAymydMey46Q0vwZdSY1/MHUdw9reR4V3opzH3G', '085471515', 'pendonor', 'aktif', '2026-06-02 02:00:47', '2026-06-02 02:03:07', '2026-06-02 02:03:07'),
(18, 'Aprilianti Saputri', 'sidorah.april@gmail.com', '$2y$10$MJIbKjPcNxL7N5..FZfteu.q4GudM1iFY15nXOSSziKFfCxuleC6u', '0844444444', 'pendonor', 'aktif', '2026-06-02 04:22:40', '2026-06-15 09:05:08', '2026-06-15 09:05:08'),
(19, 'Anggun', 'anggun@sidorah.id', '$2y$10$G2priZp5030tfgjcPPuXI.izOeeRPCNE7TSnsFfFeoDwkHG/qnV6u', '08999999', 'pendonor', 'aktif', '2026-06-04 12:27:58', '2026-06-09 02:09:23', '2026-06-09 02:09:23'),
(20, 'yani', 'yani@sidorah.id', '$2y$10$E9VCE9fI8lAfhKZ3MxvwpOlXjYjdm0P5qBf9IDL4D9XWDcq/Okm/a', '', 'pendonor', 'aktif', '2026-06-06 05:44:52', '2026-06-06 09:02:34', '2026-06-06 09:02:34'),
(21, 'reski', 'resk@sidorah.id', '$2y$10$BMWsEmyd.KCtqtUQ/gNwuugsmeBNrhfcNICV8NM57KGNgF6lrf5j2', '0844444444', 'pendonor', 'aktif', '2026-06-14 07:03:37', '2026-06-14 07:06:13', '2026-06-14 07:06:13'),
(22, 'farhan', 'farhan@sidorah.id', '$2y$10$UtRiK1MBT.IXJ8.KHc4qK.6tvFSkt02T8XGF9gy5eCIwmJ2XWTyea', '089000000', 'pendonor', 'aktif', '2026-06-15 08:52:35', '2026-06-15 08:52:35', NULL),
(23, 'rizal', 'rizal@sidorah.id', '$2y$10$JecKuSXtHPsQZyj8UKhv4O3dGrRY3MWZOFOioIfzbeisUrL.08F.e', '089000000', 'pendonor', 'aktif', '2026-06-15 09:00:49', '2026-06-15 09:01:43', '2026-06-15 09:01:43');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_waktu` (`waktu`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `kegiatan_donor`
--
ALTER TABLE `kegiatan_donor`
  ADD PRIMARY KEY (`id_kegiatan`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_pengirim` (`id_pengirim`),
  ADD KEY `id_penerima` (`id_penerima`);

--
-- Indeks untuk tabel `notifikasi_darurat`
--
ALTER TABLE `notifikasi_darurat`
  ADD PRIMARY KEY (`id_notif`),
  ADD KEY `idx_status_darurat` (`status`),
  ADD KEY `idx_expired_at` (`expired_at`);

--
-- Indeks untuk tabel `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indeks untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD KEY `id_pendonor` (`id_pendonor`),
  ADD KEY `id_kegiatan` (`id_kegiatan`),
  ADD KEY `id_admin_verifikasi` (`id_admin_verifikasi`);

--
-- Indeks untuk tabel `pendonor`
--
ALTER TABLE `pendonor`
  ADD PRIMARY KEY (`id_pendonor`),
  ADD UNIQUE KEY `uq_pendonor_user` (`id_pengguna`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kunci` (`kunci`);

--
-- Indeks untuk tabel `permintaan_darah`
--
ALTER TABLE `permintaan_darah`
  ADD PRIMARY KEY (`id_permintaan`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indeks untuk tabel `rfid_scan`
--
ALTER TABLE `rfid_scan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pendonor` (`id_pendonor`);

--
-- Indeks untuk tabel `riwayat_donor`
--
ALTER TABLE `riwayat_donor`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_pendaftaran` (`id_pendaftaran`),
  ADD KEY `id_pendonor` (`id_pendonor`),
  ADD KEY `id_petugas_medis` (`id_petugas_medis`),
  ADD KEY `idx_tanggal_donor` (`tanggal_donor`);

--
-- Indeks untuk tabel `sensor_stok`
--
ALTER TABLE `sensor_stok`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sensor_suhu`
--
ALTER TABLE `sensor_suhu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_suhu_status` (`status`),
  ADD KEY `idx_suhu_waktu` (`created_at`);

--
-- Indeks untuk tabel `stok_darah`
--
ALTER TABLE `stok_darah`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `idx_status_stok` (`status_stok`),
  ADD KEY `idx_kadaluarsa` (`tanggal_kadaluarsa`);

--
-- Indeks untuk tabel `transfusi_darah`
--
ALTER TABLE `transfusi_darah`
  ADD PRIMARY KEY (`id_transfusi`),
  ADD UNIQUE KEY `no_transfusi` (`no_transfusi`),
  ADD KEY `id_stok` (`id_stok`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=561;

--
-- AUTO_INCREMENT untuk tabel `kegiatan_donor`
--
ALTER TABLE `kegiatan_donor`
  MODIFY `id_kegiatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `notifikasi_darurat`
--
ALTER TABLE `notifikasi_darurat`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `pendonor`
--
ALTER TABLE `pendonor`
  MODIFY `id_pendonor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `permintaan_darah`
--
ALTER TABLE `permintaan_darah`
  MODIFY `id_permintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `rfid_scan`
--
ALTER TABLE `rfid_scan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_donor`
--
ALTER TABLE `riwayat_donor`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `sensor_stok`
--
ALTER TABLE `sensor_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `sensor_suhu`
--
ALTER TABLE `sensor_suhu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stok_darah`
--
ALTER TABLE `stok_darah`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT untuk tabel `transfusi_darah`
--
ALTER TABLE `transfusi_darah`
  MODIFY `id_transfusi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `kegiatan_donor`
--
ALTER TABLE `kegiatan_donor`
  ADD CONSTRAINT `kegiatan_donor_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_pengirim`) REFERENCES `users` (`id_pengguna`),
  ADD CONSTRAINT `notifikasi_ibfk_2` FOREIGN KEY (`id_penerima`) REFERENCES `users` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`id_pendonor`) REFERENCES `pendonor` (`id_pendonor`),
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_kegiatan`) REFERENCES `kegiatan_donor` (`id_kegiatan`),
  ADD CONSTRAINT `pendaftaran_ibfk_3` FOREIGN KEY (`id_admin_verifikasi`) REFERENCES `users` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `pendonor`
--
ALTER TABLE `pendonor`
  ADD CONSTRAINT `pendonor_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `users` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `permintaan_darah`
--
ALTER TABLE `permintaan_darah`
  ADD CONSTRAINT `permintaan_darah_ibfk_1` FOREIGN KEY (`id_petugas`) REFERENCES `users` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `riwayat_donor`
--
ALTER TABLE `riwayat_donor`
  ADD CONSTRAINT `riwayat_donor_ibfk_1` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`),
  ADD CONSTRAINT `riwayat_donor_ibfk_2` FOREIGN KEY (`id_pendonor`) REFERENCES `pendonor` (`id_pendonor`),
  ADD CONSTRAINT `riwayat_donor_ibfk_3` FOREIGN KEY (`id_petugas_medis`) REFERENCES `users` (`id_pengguna`);

--
-- Ketidakleluasaan untuk tabel `transfusi_darah`
--
ALTER TABLE `transfusi_darah`
  ADD CONSTRAINT `fk_transfusi_stok` FOREIGN KEY (`id_stok`) REFERENCES `stok_darah` (`id_stok`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
