<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">

<script>
    function showAlert(type, title, text) {
        Swal.fire({
            icon: type,
            title: `<span style="font-size: 1.5em;">${title}</span>`,
            html: `<span style="font-size: 1.5em;">${text}</span>`,
            timer: (type === 'error' || type === 'warning') ? null : 1700,
            showConfirmButton: (type === 'error' || type === 'warning'),
            confirmButtonText: '<span style="font-size: 1.5em;">Ok</span>'
        }).then(() => {
            // Remove the URL parameters after showing the alert
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('edit');
                url.searchParams.delete('pengguna');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<style>
    @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");

    .btn {
        transition: .4s all;
    }

    .btn:hover {
        transform: translateY(-3px);
    }

    .isi-tabel {
        width: 75%;
    }

    /* Overlay */
    #loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(24, 18, 92, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;

        opacity: 1;
        visibility: visible;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    /* Hidden state */
    #loader-overlay.fade-out {
        opacity: 0;
        visibility: hidden;
    }

    .loader {
        width: 50px;
        aspect-ratio: 1;
        display: grid;
    }

    .loader::before,
    .loader::after {
        content: "";
        grid-area: 1/1;
        --c: no-repeat radial-gradient(farthest-side, #25b09b 92%, #0000);
        background:
            var(--c) 50% 0,
            var(--c) 50% 100%,
            var(--c) 100% 50%,
            var(--c) 0 50%;
        background-size: 12px 12px;
        animation: l12 1s infinite;
    }

    .loader::before {
        margin: 4px;
        filter: hue-rotate(45deg);
        background-size: 8px 8px;
        animation-timing-function: linear
    }

    @keyframes l12 {
        100% {
            transform: rotate(.5turn)
        }
    }

    @media (max-width: 768px) {
        .rounded {
            width: 250px;
            height: 250px;
            border-radius: 50%;
        }
    }

    @media (max-width: 576px) {
        .isi-tabel {
            width: 50%;
        }

        .rounded {
            width: 150px;
            height: 150px;
            border-radius: 50%;
        }
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Profil</li>
    </ol>
</div>

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12" style="margin-top: 20px;">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Profil
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <?php
                // Menghubungkan database
                include 'config/database.php';
                $kode_pengguna = $_SESSION["kode_pengguna"];
                $sql = "SELECT * FROM tbl_mentor WHERE kode_mentor='$kode_pengguna' LIMIT 1";
                $hasil = mysqli_query($kon, $sql);
                $data = mysqli_fetch_array($hasil);

                // Ambil log aktivitas terakhir untuk mahasiswa (dengan aktivitas = 'Login')
                $sql_log = "SELECT tanggal FROM tbl_log_aktivitas 
                WHERE kode_pengguna='$kode_pengguna' 
                AND level='Mentor' 
                AND aktivitas='login'
                ORDER BY tanggal DESC LIMIT 1";
                $hasil_log = mysqli_query($kon, $sql_log);
                $data_log = mysqli_fetch_array($hasil_log);

                // Format tanggal login terakhir (jika ada)
                $terakhir_login = "-";
                if ($data_log) {
                    $terakhir_login = date("d-m-Y H:i:s", strtotime($data_log['tanggal']));
                }
                ?>

                <?php
                // Validasi untuk menampilkan memberitahukan ketika mahasiswa mengubah password
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Ubah Password berhasil');</script>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ubah Password gagal');</script>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat mentor mengedit profil
                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Profil Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Profil Gagal Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal_format') {
                        echo "<script>showAlert('error', 'Gagal!', 'Format file harus JPG, JPEG atau PNG');</script>";
                    } else if ($_GET['edit'] == 'gagal_ukuran') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file tidak boleh melebihi 1MB');</script>";
                    }
                }
                ?>
                <div class="row">
                    <div class="col-lg-3 col-xs-12 text-center" style="margin-bottom: 20px;">
                        <div style="width: 100%; height: auto; text-align: center;">
                            <?php
                            $foto = !empty($data['foto']) ? $data['foto'] : 'foto_default.png';
                            ?>
                            <a href="#" data-toggle="modal" data-target="#photoModal">
                                <img style="width: 270px; height: 270px; object-fit: cover; border-radius: 50%; border: 4px solid rgb(13, 10, 44);"
                                    src="apps/pengguna/foto_mentor/<?php echo $foto; ?>" class="rounded"
                                    alt="Foto Mentor">
                            </a>
                        </div>
                        <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body text-center" style="width: 100%; text-align: center; margin-bottom: 10px;">
                                        <!-- Tambahkan class modal-img untuk memastikan gambar tetap di dalam modal -->
                                        <img src="apps/pengguna/foto_mentor/<?php echo $foto; ?>"
                                            class="img-fluid modal-img" alt="Cinque Terre" style="width: 400px; height: 400px; border-radius: 50%; object-fit: cover; border: 4px solid rgb(13, 10, 44);">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-xs-12">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Nama</td>
                                        <td class="isi-tabel">:
                                            <?php echo $data['nama']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>NIP</td>
                                        <td class="isi-tabel">:
                                            <?php echo $data['nip']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td class="isi-tabel">:
                                            <?php echo $data['email']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Unit Kerja / Instansi</td>
                                        <td class="isi-tabel">:
                                            <?php echo $data['unit_kerja']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jabatan</td>
                                        <td class="isi-tabel">:
                                            <?php echo $data['jabatan']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Terakhir Login</td>
                                        <td class="isi-tabel">: <?php echo $terakhir_login; ?></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button class="btn btn-primary tombol_edit"
                                                kode_mentor="<?php echo $data['kode_mentor']; ?>"><i
                                                    style="margin-right: 5px;" class="fa fa-edit"></i>Edit
                                                Profile</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <button style="margin-bottom: 5px;" kode_mentor="<?php echo $data['kode_mentor']; ?>"
                                class="password btn btn-warning btn-circle"><i class="fa fa-key"></i> Ubah
                                Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div id="tampil_data">
                    <!-- Data akan di load menggunakan AJAX -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                    Close</button>
            </div>

        </div>
    </div>
</div>
<!-- Modal -->

<script>
    $('.password').on('click', function () {
        let kode_mentor = $(this).attr("kode_mentor");

        $.ajax({
            url: 'apps/pengguna/ubah_password_mentor.php',
            method: 'post',
            data: {
                kode_mentor: kode_mentor
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Ubah Password';
            }
        });

        $('#modal').modal('show');
    });

    $('.tombol_edit').on('click', function () {
        let kode_mentor = $(this).attr("kode_mentor");
        $.ajax({
            url: 'apps/pengguna/edit_profil_mentor.php',
            method: 'post',
            data: {
                kode_mentor: kode_mentor
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Profil';
            }
        });
        $('#modal').modal('show');
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loader = document.getElementById("loader-overlay");

        if (sessionStorage.getItem("sudah_load")) {
            // Kalau sudah pernah load, langsung sembunyikan
            loader.classList.add("fade-out");
        } else {
            // Kalau pertama kali load, kasih delay sebelum fade out
            setTimeout(function () {
                loader.classList.add("fade-out");
                sessionStorage.setItem("sudah_load", "true");
            }, 1000); // spinner muncul 1 detik lalu fade out
        }
    });
</script>