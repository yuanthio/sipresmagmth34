<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">

<?php
if ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'Mentor' && $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

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
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('hapus');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<style>
    @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");

    .table-responsive {
        overflow-y: auto;
        max-height: 500px;
    }

    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
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
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb" style="background-color: #eaeaea">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Laporan Magang</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Laporan Magang
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_laporan_magang" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="search" id="search" class="form-control"
                                    value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                                    placeholder="Pencarian">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">

                <?php
                if (isset($_SESSION['alert'])) {
                    $alert = $_SESSION['alert'];
                    unset($_SESSION['alert']);
                    echo "<script>showAlert('{$alert['type']}', '{$alert['title']}', '{$alert['message']}');</script>";
                }

                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Menghapus data laporan magang berhasil');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Menghapus data laporan magang gagal');</script>";
                    }
                }
                ?>

                <?php if ($_SESSION["level"] == 'Admin'): ?>
                    <div class="form-group">
                        <button type="button" class="btn btn-success" id="tombol_tambah_laporan"><i
                                class="fa fa-plus"></i>Tambah</button>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Universitas / Sekolah</th>
                                <th>Hari</th>
                                <th>Tanggal</th>
                                <th width="110">Ukuran File</th>
                                <th width="300">File Laporan</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengimpor file konfigurasi database dan fungsi terkait
                            include 'config/database.php';
                            include 'config/function.php';

                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $searchKeyword = mysqli_real_escape_string($kon, $_GET['search']);
                                $query = "SELECT * FROM tbl_laporan 
                                WHERE nama LIKE '%$searchKeyword%' OR universitas LIKE '%$searchKeyword%' 
                                ORDER BY tanggal DESC"; // Menyusun data berdasarkan tanggal secara descending
                            } else {
                                $query = "SELECT * FROM tbl_laporan 
                                ORDER BY tanggal DESC"; // Menyusun data berdasarkan tanggal secara descending
                            }

                            if ($_SESSION["level"] == 'Mentor') {
                                // Jika yang login adalah seorang mentor
                                $mentor_name = $_SESSION["nama_mentor"];
                                $query = "SELECT * FROM tbl_laporan 
                                INNER JOIN tbl_mahasiswa ON tbl_laporan.nama = tbl_mahasiswa.nama
                                WHERE tbl_mahasiswa.mentor = '$mentor_name'
                                ORDER BY tbl_laporan.tanggal DESC"; // Menyusun data berdasarkan tanggal secara descending
                            }

                            $result = mysqli_query($kon, $query);
                            $no = 1;

                            // Loop untuk menampilkan data
                            while ($data = mysqli_fetch_assoc($result)) {
                                // Ambil ekstensi file
                                $file_ext = strtolower(pathinfo($data['file_laporan'], PATHINFO_EXTENSION));

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
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['universitas']; ?></td>
                                    <td><?php echo $data['hari']; ?></td>
                                    <td class="text-center">
                                        <?php
                                        $tgl = date("d", strtotime($data['tanggal']));
                                        $bulan = date("m", strtotime($data['tanggal']));
                                        $tahun = date("Y", strtotime($data['tanggal']));
                                        // Menampilkan tanggal dalam format yang lebih mudah dibaca
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                                        ?>
                                    </td>
                                    <td><?php echo $data['ukuran_file']; ?></td>
                                    <td>
                                        <img width="30" src="<?= $icon_path; ?>" alt="<?= $file_ext; ?>">
                                        <?php echo basename($data['file_laporan']); ?>
                                    </td>
                                    <!-- Menggunakan basename untuk menampilkan nama file tanpa path -->
                                    <td>
                                        <a href="apps/data_laporan_magang/download.php?id=<?php echo $data['id_laporan']; ?>"
                                            class="btn btn-primary" title="Unduh Laporan Magang"><i
                                                class="fa fa-download"></i></a>
                                        <?php if ($_SESSION["level"] == 'Admin'): ?>
                                            <a href="apps/data_laporan_magang/hapus.php?id_laporan=<?php echo $data['id_laporan']; ?>"
                                                class="btn-hapus-laporan btn btn-danger btn-circle"
                                                title="Hapus Laporan Magang">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
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
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div id="tampil_data">
                </div>
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
    // Tambah admin
    $('#tombol_tambah_laporan').on('click', function () {
        $.ajax({
            url: 'apps/data_laporan_magang/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Data';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-laporan').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            Swal.fire({
                title: "<span style='font-size: 1.5em;'>Apa anda yakin??</span>",
                html: "<span style='font-size: 1.5em;'>Anda tidak akan dapat mengembalikan data ini!!</span>",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "<span style='font-size: 1.5em;'>Ya, hapus!</span>",
                cancelButtonText: "<span style='font-size: 1.5em;'>Batal</span>"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
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