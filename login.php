<?php
/**
 * SIDORAH - Sistem Informasi Donor Darah
 * File: login.php
 * Deskripsi: Halaman login dengan autentikasi + session
 */

require_once 'koneksi.php';

// Jika sudah login, langsung arahkan ke dashboard
if (sudahLogin()) {
    if ($_SESSION['role'] === 'pendonor') {
        header('Location: portal_pendonor.php');
        exit();
    }
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

// ============================================================
// PROSES LOGIN (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = bersihkan($koneksi, $_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi input tidak kosong
    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';

    } else {
        // Cari user berdasarkan email
        $stmt = $koneksi->prepare("
            SELECT id_pengguna, nama_lengkap, email, password, role, status_akun
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            // Email tidak ditemukan
            $error = 'Email atau password salah.';

        } elseif ($user['status_akun'] === 'nonaktif') {
            $error = 'Akun Anda telah dinonaktifkan. Hubungi administrator.';

        } elseif ($user['status_akun'] === 'terkunci') {
            $error = 'Akun Anda terkunci. Hubungi administrator.';

        } elseif (!password_verify($password, $user['password'])) {
            // Password salah
            $error = 'Email atau password salah.';

        } else {
            // LOGIN BERHASIL — simpan ke session
            session_regenerate_id(false); // Regenerate ID tanpa hapus session lama

            $_SESSION['id_pengguna']  = $user['id_pengguna'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email']        = $user['email'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['login_time']   = time();

            // Update last_login
            $koneksi->query("UPDATE users SET last_login = NOW() WHERE id_pengguna = {$user['id_pengguna']}");

            // Arahkan sesuai role
            if ($user['role'] === 'pendonor') {
                header('Location: portal_pendonor.php');
                exit();
            } else {
                header('Location: dashboard.php');
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — SIDORAH</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --merah:       #c0392b;
            --merah-tua:   #922b21;
            --merah-muda:  #fadbd8;
            --merah-bg:    #fdf2f2;
            --gelap:       #1a1a2e;
            --abu:         #6c757d;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--gelap);
            overflow: hidden;
        }

        /* ── Panel Kiri (Branding) ── */
        .panel-kiri {
            width: 45%;
            background: linear-gradient(145deg, #7b0d1e 0%, #c0392b 50%, #e74c3c 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .panel-kiri::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -150px; left: -150px;
        }
        .panel-kiri::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -100px; right: -80px;
        }

        .brand-icon {
            width: 90px; height: 90px;
            background: rgba(255,255,255,0.15);
            border-radius: 28px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.8rem;
            color: white;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative; z-index: 1;
        }

        .brand-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
            margin-bottom: 0.4rem;
            position: relative; z-index: 1;
        }

        .brand-sub {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.75);
            text-align: center;
            max-width: 280px;
            line-height: 1.6;
            position: relative; z-index: 1;
        }

        .stats-row {
            display: flex;
            gap: 1.5rem;
            margin-top: 3rem;
            position: relative; z-index: 1;
        }

        .stat-item {
            text-align: center;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 1rem 1.4rem;
            backdrop-filter: blur(8px);
        }

        .stat-item .angka {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
        }

        .stat-item .label {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* ── Panel Kanan (Form) ── */
        .panel-kanan {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 3.5rem;
            overflow-y: auto;
        }

        .login-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gelap);
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: var(--abu);
            margin-top: 0.4rem;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.4rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.2rem;
        }

        .input-group-custom .icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 1rem;
            z-index: 2;
        }

        .input-group-custom input {
            width: 100%;
            height: 52px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding: 0 16px 0 46px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--gelap);
            background: #f9fafb;
            transition: all 0.2s;
            outline: none;
        }

        .input-group-custom input:focus {
            border-color: var(--merah);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.1);
        }

        .input-group-custom .toggle-pw {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #adb5bd;
            cursor: pointer;
            font-size: 1rem;
            z-index: 2;
            padding: 0;
        }
        .input-group-custom .toggle-pw:hover { color: var(--merah); }

        .btn-login {
            width: 100%;
            height: 52px;
            background: linear-gradient(95deg, var(--merah) 0%, var(--merah-tua) 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            letter-spacing: 0.3px;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(192, 57, 43, 0.4);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            color: #b91c1c;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.2rem;
        }

        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 1.5rem 0;
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: var(--merah);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: 0.2s;
        }
        .back-link:hover { color: var(--merah-tua); gap: 10px; }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--merah-muda);
            color: var(--merah-tua);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .panel-kiri { display: none; }
            .panel-kanan { padding: 2.5rem 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- ── Panel Kiri: Branding ── -->
    <div class="panel-kiri">
        <div class="brand-icon">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <div class="brand-title">SIDORAH</div>
        <div class="brand-sub">
            Sistem Informasi Donor Darah Rumah Sakit — kelola pendonor, stok darah, dan kegiatan dalam satu platform.
        </div>

    </div>

    <!-- ── Panel Kanan: Form Login ── -->
    <div class="panel-kanan">
        <div style="max-width: 400px; width: 100%; margin: 0 auto;">

            <div class="login-header mb-4">
                <div class="role-badge">
                    <i class="bi bi-heart-fill"></i> Portal Donor Darah
                </div>
                <h2>Selamat Datang</h2>
                <p>Masuk ke dashboard SIDORAH dengan akun yang terdaftar.</p>
            </div>

            <!-- Pesan Error -->
            <?php if ($error): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Form Login -->
            <form method="POST" action="" id="formLogin" novalidate>

                <!-- Email -->
                <label class="form-label">Alamat Email</label>
                <div class="input-group-custom">
                    <i class="bi bi-envelope icon"></i>
                    <input
                        type="email"
                        name="email"
                        id="inputEmail"
                        placeholder="email@rumahsakit.id"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <!-- Password -->
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock icon"></i>
                    <input
                        type="password"
                        name="password"
                        id="inputPassword"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword()" id="btnToggle">
                        <i class="bi bi-eye" id="ikonMata"></i>
                    </button>
                </div>

                <!-- Remember & Lupa Password -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:0.875rem; color:#374151;">
                        <input type="checkbox" name="remember" style="accent-color: var(--merah);">
                        Ingat saya
                    </label>
                    <a href="lupa_password.php" style="font-size:0.875rem; color:var(--merah); text-decoration:none; font-weight:600;">
                        Lupa password?
                    </a>
                </div>

                <!-- Tombol Login -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk ke Dashboard
                </button>

            </form>

            <hr class="divider">

            <a href="index.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Halaman Utama
            </a>

        </div>
    </div>

<script>
    // Toggle show/hide password
    function togglePassword() {
        const input = document.getElementById('inputPassword');
        const ikon  = document.getElementById('ikonMata');
        if (input.type === 'password') {
            input.type = 'text';
            ikon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            ikon.className = 'bi bi-eye';
        }
    }

    // Loading state saat submit
    document.getElementById('formLogin').addEventListener('submit', function () {
        const btn = document.getElementById('btnLogin');
        btn.classList.add('loading');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memverifikasi...';
    });
</script>

</body>
</html>