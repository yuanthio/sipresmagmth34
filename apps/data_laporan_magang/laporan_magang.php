<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<?php
if ($_SESSION["level"] != 'Mahasiswa' and $_SESSION["level"] != 'mahasiswa') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<style>
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
</style>

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
            // Setelah SweetAlert ditutup, hapus parameter 'edit' dan 'unggah' dari URL
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('edit'); // Hapus parameter 'edit'
                url.searchParams.delete('unggah'); // Hapus parameter 'unggah'
                window.history.replaceState(null, '', url); // Update URL tanpa reload halaman
            }
        });
    }
</script>

<div id="loader-overlay">
    <div class="loader"></div>
</div>


<div class="row">
    <ol class="breadcrumb" style="background-color: #eaeaea">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Unggah Laporan Magang</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px; margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Laporan Magang
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <?php
                // SweetAlert akan ditampilkan sebelum tombol upload jika ada status di URL
                if (isset($_GET['unggah'])) {
                    $status = $_GET['unggah'];
                    if ($status === 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Laporan magang berhasil diunggah');</script>";
                    } elseif ($status === 'gagal_ukuran') {
                        echo "<script>showAlert('error', 'Gagal!', 'File tidak boleh melebihi 1MB');</script>";
                    } elseif ($status === 'gagal_format') {
                        echo "<script>showAlert('error', 'Gagal!', 'Format file harus PDF, DOC, atau DOCX');</script>";
                    } elseif ($status === 'sudah_unggah') {
                        echo "<script>showAlert('warning', 'Gagal!', 'Anda sudah mengunggah laporan sebelumnya');</script>";
                    } elseif ($status === 'gagal_query') {
                        echo "<script>showAlert('error', 'Gagal!', 'Terjadi kesalahan pada query database');</script>";
                    } elseif ($status === 'gagal_upload') {
                        echo "<script>showAlert('error', 'Gagal!', 'Gagal mengunggah file');</script>";
                    }
                } elseif (isset($_GET['edit'])) {
                    $status = $_GET['edit'];
                    if ($status === 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Laporan magang berhasil diedit');</script>";
                    } elseif ($status === 'gagal_ukuran') {
                        echo "<script>showAlert('error', 'Gagal!', 'File tidak boleh melebihi 1MB');</script>";
                    } elseif ($status === 'gagal_format') {
                        echo "<script>showAlert('error', 'Gagal!', 'Format file harus PDF, DOC, atau DOCX');</script>";
                    } elseif ($status === 'gagal_query') {
                        echo "<script>showAlert('error', 'Gagal!', 'Terjadi kesalahan pada query database');</script>";
                    } elseif ($status === 'gagal_upload') {
                        echo "<script>showAlert('error', 'Gagal!', 'Gagal mengunggah file');</script>";
                    }
                }
                ?>
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="upload_laporan"><i
                            class="upload_laporan bi bi-upload"></i> Unggah</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered yuan" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Universitas / Sekolah</th>
                                <th>Hari</th>
                                <th>Diunggah</th>
                                <th>Ukuran File</th>
                                <th>File Laporan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';
                            include 'config/function.php';

                            $kode_pengguna = $_SESSION["kode_pengguna"];

                            // Ambil data laporan magang mahasiswa yang sedang login
                            $query = "SELECT * FROM tbl_laporan WHERE kode_mahasiswa = '$kode_pengguna'";
                            $result = mysqli_query($kon, $query);

                            if (mysqli_num_rows($result) > 0) {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Format tanggal untuk menampilkan bulan dalam bahasa Indonesia
                                    $tanggal = new DateTime($row['tanggal']);
                                    $bulan = MendapatkanBulan($tanggal->format('n')); // Format 'n' untuk mendapatkan bulan sebagai angka tanpa leading zero
                                    $tanggalIndo = $tanggal->format('j') . ' ' . $bulan . ' ' . $tanggal->format('Y');

                                    // Ambil ekstensi file
                                    $file_ext = strtolower(pathinfo($row['file_laporan'], PATHINFO_EXTENSION));

                                    // Tentukan gambar yang sesuai dengan ekstensi file
                                    $icon_path = '';
                                    if ($file_ext == 'pdf') {
                                        $icon_path = 'apps/data_laporan_magang/logo_drag_and_drop/pdf.png';
                                    } elseif ($file_ext == 'doc') {
                                        $icon_path = 'apps/data_laporan_magang/logo_drag_and_drop/doc.png';
                                    } elseif ($file_ext == 'docx') {
                                        $icon_path = 'apps/data_laporan_magang/logo_drag_and_drop/docx.png';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= $row['nama']; ?></td>
                                        <td><?= $row['universitas']; ?></td>
                                        <td><?= $row['hari']; ?></td>
                                        <td><?= $tanggalIndo; ?></td>
                                        <td><?= $row['ukuran_file']; ?></td>
                                        <td>
                                            <!-- Tampilkan ikon file berdasarkan ekstensi -->
                                            <img width="30" src="<?= $icon_path; ?>" alt="<?= $file_ext; ?>">
                                            <?= $row['file_laporan']; ?>
                                        </td>
                                        <td>
                                            <button id_laporan="<?php echo $row['id_laporan']; ?>"
                                                class="ubah_laporan btn btn-warning" title="Edit Laporan"
                                                style="margin-bottom: 5px;"><i class="fa fa-edit"></i></button>
                                            <a href="apps/data_laporan_magang/download.php?id=<?= $row['id_laporan']; ?>"
                                                class="btn btn-primary" style="margin-bottom: 5px;"
                                                title="Unduh Laporan Magang">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada laporan yang diunggah</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
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
                <div id="tampil_data"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                    Close</button>
            </div>

        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/4752e5dd73.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        $('[title]').tooltip();
    });
</script>

<script>
    // Upload Laporan
    $('#upload_laporan').on('click', function () {
        $.ajax({
            url: 'apps/pengguna/mulai_upload_laporan.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Unggah Laporan Magang';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Edit Upload Laporan
    $('.ubah_laporan').on('click', function () {
        $.ajax({
            url: 'apps/pengguna/edit_upload_laporan.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Laporan Magang';
            }
        });
        // Membuka modal
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