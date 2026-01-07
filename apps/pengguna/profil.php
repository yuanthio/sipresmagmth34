<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

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

    .btn-float {
        float: right;
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

        .btn-float {
            float: none;
        }
    }

    .modal .modal-body .modal-img {
        max-width: 100%;
        /* Membatasi lebar gambar agar tidak melebihi modal */
        height: auto;
        /* Menjaga proporsi gambar */
        border-radius: 0;
        /* Menghilangkan border-radius di modal */
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
</div><!--/.row-->

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
                // Validasi untuk menampilkan memberitahukan ketika pengguna berhasil mengubah password
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Ubah Password berhasil');</script>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ubah Password gagal');</script>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat pengguna mengedit sesuatu
                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Profil Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Profil Gagal Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal_format') {
                        echo "<script>showAlert('error', 'Gagal!', 'Format file harus JPG, JPEG atau PNG');</script>";
                    } else if ($_GET['edit'] == 'gagal_ukuran') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file harus kurang dari 1MB');</script>";
                    }
                }
                ?>

                <?php
                // Menghubungkan database
                include 'config/database.php';
                date_default_timezone_set('Asia/Jakarta');
                $kode_pengguna = $_SESSION["kode_pengguna"];
                $sql = "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_pengguna' LIMIT 1";
                $hasil = mysqli_query($kon, $sql);
                $data = mysqli_fetch_array($hasil);

                // Ambil log aktivitas terakhir untuk mahasiswa (dengan aktivitas = 'Login')
                $sql_log = "SELECT tanggal FROM tbl_log_aktivitas 
                WHERE kode_pengguna='$kode_pengguna' 
                AND level='Mahasiswa' 
                AND aktivitas='login'
                ORDER BY tanggal DESC LIMIT 1";
                $hasil_log = mysqli_query($kon, $sql_log);
                $data_log = mysqli_fetch_array($hasil_log);

                // Format tanggal login terakhir (jika ada)
                $terakhir_login = "-";
                if ($data_log) {
                    $terakhir_login = date("d-m-Y H:i:s", strtotime($data_log['tanggal']));
                }

                // Periksa apakah formulir dikirimkan
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
                    // Dapatkan informasi file
                    $namaFile = $_FILES['file']['name'];
                    $fileTempName = $_FILES['file']['tmp_name'];
                    $ukuranFile = $_FILES['file']['size'];
                    $tipeFile = $_FILES['file']['type'];

                    // Bersihkan nama file agar hanya menyisakan karakter alfanumerik, underscore, dan titik
                    $namaFileAsli = preg_replace('/[^a-zA-Z0-9_. ]/', '', pathinfo($namaFile, PATHINFO_FILENAME));
                    $direktoriUpload = 'apps/data_laporan_magang/upload/';
                    $ekstensiFile = pathinfo($namaFile, PATHINFO_EXTENSION);
                    $namaFileUnik = $namaFileAsli . '.' . $ekstensiFile;
                    $jalurFile = $direktoriUpload . $namaFileUnik;

                    // Periksa ukuran file (1 MB = 1024 KB)
                    $ukuranMax = 1024; // 1MB
                    if ($ukuranFile > $ukuranMax * 1024) {
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                html: '<h2 style=\"font-size: 2.5em; font-weight: bold;\">Gagal!</h2><p style=\"font-size: 1.5em;\">File tidak boleh melebihi 1MB.</p>',
                                confirmButtonText: '<span style=\"font-size: 1.5em;\">Ok</span>'
                            });
                        </script>";
                    } else {
                        // Periksa format file
                        $formatDukung = array('pdf', 'doc', 'docx');
                        if (!in_array(strtolower($ekstensiFile), $formatDukung)) {
                            echo "<script>
                                Swal.fire({
                                    icon: 'error',
                                    html: '<h2 style=\"font-size: 2.5em; font-weight: bold;\">Gagal!</h2><p style=\"font-size: 1.5em;\">Maaf, format tidak didukung. Hanya file PDF, DOC, dan DOCX yang diperbolehkan.</p>',
                                    confirmButtonText: '<span style=\"font-size: 1.5em;\">Ok</span>'
                                });
                            </script>";
                        } else {
                            // Periksa apakah pengguna sudah mengunggah file sebelumnya
                            $queryCekFile = "SELECT COUNT(*) as total FROM tbl_laporan WHERE kode_mahasiswa = '$kode_pengguna'";
                            $resultCekFile = mysqli_query($kon, $queryCekFile);
                            $rowCekFile = mysqli_fetch_assoc($resultCekFile);

                            if ($rowCekFile['total'] > 0) {
                                echo "<script>
                                    Swal.fire({
                                        icon: 'warning',
                                        html: '<h2 style=\"font-size: 2.5em; font-weight: bold;\">Maaf!</h2><p style=\"font-size: 1.5em;\">Anda sudah mengupload file sebelumnya, Anda tidak dapat mengupload file lagi.</p>',
                                        confirmButtonText: '<span style=\"font-size: 1.5em;\">Ok</span>'
                                    });
                                </script>";
                            } else {
                                // Pindahkan file yang diunggah ke direktori yang diinginkan
                                if (move_uploaded_file($fileTempName, $jalurFile)) {

                                    // Dapatkan informasi lain yang diperlukan
                                    date_default_timezone_set('Asia/Jakarta'); // Atur zona waktu Indonesia
                                    $tanggal = date('Y-m-d');
                                    $hari = getNamaHari(date('w')); // Mendapatkan nama hari dalam bahasa Indonesia
                
                                    function formatSizeUnits($bytes)
                                    {
                                        $units = array('B', 'KB', 'MB', 'GB', 'TB');
                                        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $bytes /= 1024, $i++)
                                            ;
                                        return round($bytes, 2) . ' ' . $units[$i];
                                    }

                                    // Masukkan data ke dalam tbl_laporan
                                    $queryInsert = "INSERT INTO tbl_laporan (nama, kode_mahasiswa, universitas, tanggal, hari, file_laporan, ukuran_file) 
                                    SELECT nama, kode_mahasiswa, universitas, '$tanggal', '$hari', '$namaFileUnik', '" . formatSizeUnits($ukuranFile) . "' 
                                    FROM tbl_mahasiswa 
                                    WHERE kode_mahasiswa = '$kode_pengguna'";

                                    if (mysqli_query($kon, $queryInsert)) {
                                        echo "<script>
                                            Swal.fire({
                                                icon: 'success',
                                                html: '<h2 style=\"font-size: 2.5em; font-weight: bold;\">Berhasil!</h2><p style=\"font-size: 1.5em;\">File laporan magang berhasil diunggah</p>',
                                                showConfirmButton: false,
                                                timer: 1700
                                            });
                                        </script>";
                                    } else {
                                        echo "<script>
                                            Swal.fire({
                                                icon: 'error',
                                                html: '<h2 style=\"font-size: 2.5em; font-weight: bold;\">Gagal!</h2><p style=\"font-size: 1.5em;\">Terjadi kesalahan saat memasukkan data laporan</p>',
                                                confirmButtonText: '<span style=\"font-size: 1.5em;\">Ok</span>'
                                            });
                                        </script>";
                                    }
                                }
                            }
                        }
                    }
                }
                ?>

                <?php

                function getNamaHari($dayOfWeek)
                {
                    $days = array(
                        'Minggu',
                        'Senin',
                        'Selasa',
                        'Rabu',
                        'Kamis',
                        'Jumat',
                        'Sabtu'
                    );
                    return $days[$dayOfWeek];
                }

                function getDetailMentor($kode_mentor)
                {
                    include 'config/database.php';
                    $sql = "SELECT * FROM tbl_mentor WHERE kode_mentor='$kode_mentor' LIMIT 1";
                    $result = mysqli_query($kon, $sql);
                    return mysqli_fetch_array($result);
                }

                $detailMentor = getDetailMentor($data['kode_mentor']);

                ?>
                <div class="row">
                    <div class="col-lg-3 col-xs-12 text-center" style="margin-bottom: 40px;">
                        <div style="width: 100%; height: auto; text-align: center;">
                            <a href="#" data-toggle="modal" data-target="#photoModal">
                                <img style="width: 270px; height: 270px; object-fit: cover; border-radius: 50%; border: 4px solid rgb(13, 10, 44);"
                                    src="apps/mahasiswa/foto/<?php echo !empty($data['foto']) && file_exists('apps/mahasiswa/foto/' . $data['foto']) ? $data['foto'] : 'foto_default.png'; ?>"
                                    class="rounded" alt="Foto Mahasiswa">
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
                                        <img src="apps/mahasiswa/foto/<?php echo !empty($data['foto']) ? $data['foto'] : 'foto_default.png'; ?>"
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
                                        <td class="isi-tabel">: <?php echo $data['nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>NIM / NIS</td>
                                        <td class="isi-tabel">: <?php echo $data['nim']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Universitas / Sekolah</td>
                                        <td class="isi-tabel">: <?php echo $data['universitas']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Jurusan</td>
                                        <td class="isi-tabel">: <?php echo $data['jurusan']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td class="isi-tabel">: <?php echo $data['email']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal Masuk</td>
                                        <td class="isi-tabel">:
                                            <?php echo date('d/m/Y', strtotime($data["mulai_magang"])); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal Selesai</td>
                                        <td class="isi-tabel">:
                                            <?php echo date('d/m/Y', strtotime($data["akhir_magang"])); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>No Telp</td>
                                        <td class="isi-tabel">: <?php echo $data['no_telp']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Unit Kerja / Instansi</td>
                                        <td class="isi-tabel">: <?php echo $data['unit_kerja']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td class="isi-tabel">: <?php echo $data['alamat']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Mentor</td>
                                        <td class="isi-tabel">: <?php echo $data['mentor']; ?>
                                            <!-- Tombol untuk membuka modal -->
                                            <button type="button" class="btn btn-primary btn-float" data-toggle="modal"
                                                data-target="#modalDetailMentor"><i class="fa fa-info-circle"></i>
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Status Magang</td>
                                        <td width="75%">
                                            : <span
                                                class="label <?php echo $data['status_magang'] === 'Aktif' ? 'label-success' : 'label-danger'; ?>"><?php echo $data['status_magang']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Terakhir Login</td>
                                        <td class="isi-tabel">: <?php echo $terakhir_login; ?></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button class="btn btn-primary tombol_edit"
                                                kode_mahasiswa="<?php echo $data['kode_mahasiswa']; ?>"><i
                                                    style="margin-right: 5px;" class="fa fa-edit"></i>Edit
                                                Profile</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="percetakan">
                            <div class="form-group">
                                <button style="margin-bottom: 5px;"
                                    kode_mahasiswa="<?php echo $data['kode_mahasiswa']; ?>"
                                    class="password btn btn-warning btn-circle"><i class="fa fa-key"></i> Ubah
                                    Password</button>
                                <button style="margin-bottom: 5px;"
                                    id_mahasiswa='<?php echo $_SESSION['id_mahasiswa']; ?>'
                                    class="cetak_suket btn btn-info cetak-button"><i class="fa fa-print"></i> Cetak
                                    Surat Keterangan Selesai Magang</button>
                                <button style="margin-bottom: 5px;"
                                    id_mahasiswa='<?php echo $_SESSION['id_mahasiswa']; ?>'
                                    class="cetak_sertifikat btn btn-info cetak-button"><i class="fa fa-print"></i> Cetak
                                    Sertifikat</button>
                            </div>
                            <?php
                            function isInternshipPeriodWithin3Days($endDate)
                            {
                                $endDateObj = new DateTime($endDate);
                                $currentDate = new DateTime();
                                $interval = $currentDate->diff($endDateObj);
                                $daysDiff = $interval->days;

                                // Periksa apakah periode magang telah berakhir
                                if ($currentDate > $endDateObj) {
                                    return false; // Jika periode magang telah berakhir
                                } elseif ($daysDiff <= 3) {
                                    return true; // Jika periode magang tersisa 3 hari atau kurang
                                } else {
                                    return false; // Jika periode magang masih lebih dari 3 hari
                                }
                            }

                            $endDate = $data["akhir_magang"];
                            $isWithin3Days = isInternshipPeriodWithin3Days($endDate);
                            ?>
                            <div class="form-group keterangan">
                                <span style="color: white;">Keterangan :</span><br>
                                <?php
                                if ($isWithin3Days) {
                                    echo '<span style="color: white;">Sudah bisa mencetak surat keterangan selesai magang, sertifikat, dan unggah laporan magang.</span>';
                                } else {
                                    echo '<span style="color: white;">';

                                    // Periksa apakah periode magang telah berakhir
                                    $endDateObj = new DateTime($endDate);
                                    $currentDate = new DateTime();
                                    if ($currentDate > $endDateObj) {
                                        echo 'Sudah tidak bisa mencetak surat keterangan selesai magang dan sertifikat dikarenakan periode magang sudah habis.';
                                    } else {
                                        echo 'Belum bisa mencetak surat keterangan selesai magang dan sertifikat dikarenakan periode magang anda belum tersisa 3 hari.';
                                    }

                                    echo '</span>';
                                }
                                ?>
                            </div>
                            <div class="form-group">
                                <span class="remaining-time" style="color: white;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

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

<!-- Modal detail mentor -->
<div class="modal fade" id="modalDetailMentor" tabindex="-1" role="dialog" aria-labelledby="modalDetailMentorLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalDetailMentorLabel">Detail Mentor</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 text-center">
                        <div style="margin-bottom: 40px;">
                            <?php
                            $foto = !empty($detailMentor['foto']) ? $detailMentor['foto'] : 'profile.png';
                            ?>
                            <img src="apps/pengguna/foto_mentor/<?php echo $foto; ?>"
                                alt="<?php echo $detailMentor['nama']; ?>"
                                style="width: 200px; height: 200px; object-fit: cover; border-radius: 50%; border: 4px solid rgb(13, 10, 44);">
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="table-responsive">
                            <table class="table" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
                                <tbody>
                                    <tr>
                                        <td>Nama</td>
                                        <td>:
                                            <?php echo $detailMentor['nama']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nip</td>
                                        <td>:
                                            <?php echo $detailMentor['nip']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>:
                                            <?php echo $detailMentor['email']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Unit Kerja</td>
                                        <td>:
                                            <?php echo $detailMentor['unit_kerja']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jabatan</td>
                                        <td>:
                                            <?php echo $detailMentor['jabatan']; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/4752e5dd73.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function () {
        function isInternshipPeriodWithin3Days() {
            let currentDate = new Date();
            let endDate = new Date("<?php echo date('Y-m-d', strtotime($data['akhir_magang'])); ?>");
            let timeDiff = endDate.getTime() - currentDate.getTime();
            let daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24));
            return daysDiff <= 3;
        }

        function toggleCetakButtons() {
            let isWithin3Days = isInternshipPeriodWithin3Days();
            console.log("Is within 3 days: ", isWithin3Days); // Debugging
            $('.cetak-button').prop('disabled', !isWithin3Days);

            if (isWithin3Days) {
                $('.keterangan').html('<span style="color: white;">Sudah bisa mencetak surat keterangan selesai magang dan sertifikat.</span>');
            } else {
                let currentDate = new Date();
                let endDate = new Date("<?php echo date('Y-m-d', strtotime($data['akhir_magang'])); ?>");
                if (currentDate > endDate) {
                    $('.keterangan').html('<span style="color: white;">Sudah tidak bisa mencetak surat keterangan selesai magang dan sertifikat dikarenakan periode magang sudah habis.</span>');
                } else {
                    $('.keterangan').html('<span style="color: white;">Belum bisa mencetak surat keterangan selesai magang dan sertifikat dikarenakan periode magang anda belum tersisa 3 hari.</span>');
                }
            }
        }

        toggleCetakButtons();

        $(window).resize(function () {
            toggleCetakButtons();
        });

        function updateRemainingInternshipTime() {
            let currentDate = new Date();
            let endDate = new Date("<?php echo date('Y-m-d', strtotime($data['akhir_magang'])); ?>");
            let timeDiff = endDate.getTime() - currentDate.getTime();

            if (timeDiff <= 0) {
                // Jika periode magang telah berakhir
                $('.remaining-time').html('<span style="color: red;">Periode magang telah berakhir.</span>');
                $('.cetak-button').prop('disabled', true); // Menonaktifkan tombol cetak
            } else {
                let daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24));
                let hoursDiff = Math.floor((timeDiff % (1000 * 3600 * 24)) / (1000 * 3600));
                let minutesDiff = Math.floor((timeDiff % (1000 * 3600)) / (1000 * 60));
                let secondsDiff = Math.floor((timeDiff % (1000 * 60)) / 1000);

                // Menambahkan nol di depan jam, menit, dan detik jika nilainya di bawah 10
                hoursDiff = hoursDiff < 10 ? '0' + hoursDiff : hoursDiff;
                minutesDiff = minutesDiff < 10 ? '0' + minutesDiff : minutesDiff;
                secondsDiff = secondsDiff < 10 ? '0' + secondsDiff : secondsDiff;

                let remainingTimeText = '';

                if (daysDiff > 0) {
                    remainingTimeText = '<p style="color: white;">Sisa periode magang anda :</p><span style="color: black; background-color: white; padding: 5px; border-radius: 5px; font-weight: bold;">' + daysDiff + ' hari ' + hoursDiff + ' : ' + minutesDiff + ' : ' + secondsDiff + '</span>';

                    if (daysDiff <= 3) {
                        remainingTimeText = '<p style="color: white;">Sisa periode magang anda :</p><span style="color: red; background-color: white; padding: 5px; border-radius: 5px; font-weight: bold;">' + daysDiff + ' hari ' + hoursDiff + ' : ' + minutesDiff + ' : ' + secondsDiff + '</span>';
                    }
                } else {
                    remainingTimeText = '<p style="color: white;">Sisa periode magang anda :</p><span style="color: red; background-color: white; padding: 5px; border-radius: 5px; font-weight: bold;">' + hoursDiff + ' : ' + minutesDiff + ' : ' + secondsDiff + '</span>';
                }

                $('.remaining-time').html(remainingTimeText);
            }
        }

        updateRemainingInternshipTime();
        setInterval(function () {
            updateRemainingInternshipTime();
        }, 1000);

        $('.tombol_edit').on('click', function () {
            let kode_mahasiswa = $(this).attr("kode_mahasiswa");
            $.ajax({
                url: 'apps/pengguna/edit_profil.php',
                method: 'post',
                data: {
                    kode_mahasiswa: kode_mahasiswa
                },
                success: function (data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Edit Profil';
                }
            });
            $('#modal').modal('show');
        });

        $('.password').on('click', function () {
            let kode_mahasiswa = $(this).attr("kode_mahasiswa");

            $.ajax({
                url: 'apps/pengguna/ubah_password.php',
                method: 'post',
                data: {
                    kode_mahasiswa: kode_mahasiswa
                },
                success: function (data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Ubah Password';
                }
            });

            $('#modal').modal('show');
        });

        $('.cetak_nilai').on('click', function () {
            let id_mahasiswa = $(this).attr("id_mahasiswa");

            $.ajax({
                url: 'apps/pengguna/cetak.php',
                method: 'post',
                data: {
                    id_mahasiswa: id_mahasiswa
                },
                success: function (data) {
                    $('#tampil_data').html('<embed src="data:application/pdf;base64,' + data + '" type="application/pdf" width="100%" height="600px"/>');
                    document.getElementById("judul").innerHTML = 'Pratinjau Nilai PKL';
                    $('#modal').modal('show');
                }
            });
        });

        $('.cetak_suket').on('click', function () {
            let id_mahasiswa = $(this).attr("id_mahasiswa");

            $.ajax({
                url: 'apps/pengguna/cetak_suket.php',
                method: 'post',
                data: {
                    id_mahasiswa: id_mahasiswa
                },
                success: function (data) {
                    $('#tampil_data').html('<embed src="data:application/pdf;base64,' + data + '" type="application/pdf" width="100%" height="600px"/>');
                    document.getElementById("judul").innerHTML = 'Pratinjau Surat Keterangan';
                    $('#modal').modal('show');
                }
            });
        });

        $('.cetak_sertifikat').on('click', function () {
            let id_mahasiswa = $(this).attr("id_mahasiswa");

            $.ajax({
                url: 'apps/pengguna/cetak_sertifikat.php',
                method: 'post',
                data: {
                    id_mahasiswa: id_mahasiswa
                },
                success: function (data) {
                    $('#tampil_data').html('<embed src="data:application/pdf;base64,' + data + '" type="application/pdf" width="100%" height="600px"/>');
                    document.getElementById("judul").innerHTML = 'Pratinjau Sertifikat';
                    $('#modal').modal('show');
                }
            });
        });
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