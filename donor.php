<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Donor Darah</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body{
            background: #f5f5f5;
        }

        .donor-card{
            border: none;
            border-radius: 20px;
        }

        .form-control,
        .form-select{
            height: 50px;
            border-radius: 12px;
        }

        textarea.form-control{
            height: auto;
        }

        .title{
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card donor-card shadow-lg p-4">

                <div class="text-center mb-4">
                    <h2 class="title">
                        <i class="bi bi-heart-pulse-fill"></i>
                        Form Donor Darah
                    </h2>

                    <p class="text-muted">
                        Silakan isi data diri dengan benar
                    </p>
                </div>

                <form action="" method="POST">

                    <div class="row">

                        <!-- Nama -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nama Lengkap
                            </label>

                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   placeholder="Masukkan nama lengkap"
                                   required>
                        </div>

                        <!-- NIK -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                NIK
                            </label>

                            <input type="text"
                                   name="nik"
                                   class="form-control"
                                   placeholder="Masukkan NIK"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="email@gmail.com">
                        </div>

                        <!-- Telepon -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nomor Telepon
                            </label>

                            <input type="text"
                                   name="telepon"
                                   class="form-control"
                                   placeholder="08xxxxxxxxxx"
                                   required>
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Jenis Kelamin
                            </label>

                            <select name="jk" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option>Laki-laki</option>
                                <option>Perempuan</option>
                            </select>
                        </div>

                        <!-- Golongan Darah -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Golongan Darah
                            </label>

                            <select name="goldar" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option>A+</option>
                                <option>A-</option>
                                <option>B+</option>
                                <option>B-</option>
                                <option>AB+</option>
                                <option>AB-</option>
                                <option>O+</option>
                                <option>O-</option>
                            </select>
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Tempat Lahir
                            </label>

                            <input type="text"
                                   name="tempat_lahir"
                                   class="form-control"
                                   placeholder="Masukkan tempat lahir">
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Tanggal Lahir
                            </label>

                            <input type="date"
                                   name="tanggal_lahir"
                                   class="form-control"
                                   required>
                        </div>

                        <!-- Berat Badan -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Berat Badan (kg)
                            </label>

                            <input type="number"
                                   name="berat_badan"
                                   class="form-control"
                                   placeholder="Contoh: 55">
                        </div>

                        <!-- Pekerjaan -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Pekerjaan
                            </label>

                            <input type="text"
                                   name="pekerjaan"
                                   class="form-control"
                                   placeholder="Masukkan pekerjaan">
                        </div>

                        <!-- Alamat -->
                        <div class="col-12 mb-3">
                            <label class="form-label">
                                Alamat Lengkap
                            </label>

                            <textarea name="alamat"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Masukkan alamat lengkap"></textarea>
                        </div>

                        <!-- Riwayat Penyakit -->
                        <div class="col-12 mb-4">
                            <label class="form-label">
                                Riwayat Penyakit
                            </label>

                            <textarea name="riwayat"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Kosongkan jika tidak ada"></textarea>
                        </div>

                    </div>

                    <!-- Tombol -->
                    <div class="d-grid">
                        <button type="submit"
                                class="btn btn-danger btn-lg rounded-pill">

                            <i class="bi bi-heart-pulse-fill"></i>
                            Daftar Donor Sekarang

                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>
</html>