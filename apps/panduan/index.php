<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
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
            // Menghapus parameter URL setelah menampilkan alert
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('add');
                url.searchParams.delete('edit');
                url.searchParams.delete('hapus');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<style>
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
        <li class="active">Unggah Panduan</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Unggah Panduan
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <?php
                // Tampilkan alert jika berhasil ditambahkan
                if (isset($_GET['add'])) {
                    if ($_GET['add'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Berhasil Diupload');</script>";
                    } else if ($_GET['add'] == 'gagal') {
                        $reasonText = "Terjadi kesalahan";
                        if (isset($_GET['reason'])) {
                            if ($_GET['reason'] == 'data_ganda') {
                                $reasonText = "Jenis data untuk karyawan magang yang bersangkutan sudah ada";
                            } elseif ($_GET['reason'] == 'ukuran_terlalu_besar') {
                                $reasonText = "Ukuran file tidak boleh melebihi 1MB";
                            } elseif ($_GET['reason'] == 'jenis_file_tidak_diizinkan') {
                                $reasonText = "Jenis file tidak diizinkan. Hanya file dengan format doc, docx, atau pdf yang diperbolehkan.";
                            } else {
                                $reasonText = "Terjadi kesalahan saat mengunggah file";
                            }
                        }
                        echo "<script>showAlert('error', 'Gagal!', '$reasonText');</script>";
                    }
                }

                // Menampilkan alert untuk hasil edit data
                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Berhasil Diedit');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        $reasonText = "Terjadi kesalahan saat mengedit data";
                        if (isset($_GET['reason'])) {
                            if ($_GET['reason'] == 'jenis_file_tidak_diizinkan') {
                                $reasonText = "Jenis file tidak diizinkan. Hanya file dengan format doc, docx, atau pdf yang diperbolehkan.";
                            }
                        }
                        echo "<script>showAlert('error', 'Gagal!', '$reasonText');</script>";
                    }
                }

                // Menampilkan alert untuk hasil penghapusan data
                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Berhasil Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Data Gagal Dihapus');</script>";
                    }
                }
                ?>
                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah_panduan"><i
                            class="fa fa-plus"></i>Tambah</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Jenis Data</th>
                                <th>Level</th>
                                <th>Hari</th>
                                <th>Di Unggah</th>
                                <th>Ukuran File</th>
                                <th width="350">Nama File</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "config/database.php";
                            include "config/function.php";

                            $query = "SELECT * FROM tbl_panduan ORDER BY id_panduan DESC"; // Adjust if needed
                            $result = mysqli_query($kon, $query);
                            $no = 1;

                            // Check if there are results
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $file_extension = pathinfo($row['file_panduan'], PATHINFO_EXTENSION); // Mengambil ekstensi file
                            
                                    if ($file_extension == 'pdf') {
                                        $icon = 'pdf.png';
                                    } elseif ($file_extension == 'docx') {
                                        $icon = 'docx.png';
                                    } else {
                                        $icon = 'doc.png';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['jenis_data']; ?></td>
                                        <td><?php echo $row['level']; ?></td>
                                        <td><?php echo $row['hari']; ?></td>
                                        <td>
                                            <?php
                                            // Mendapatkan tanggal dari database
                                            $tanggal = strtotime($row['tanggal']);
                                            $hari = date('d', $tanggal);
                                            $bulan = date('n', $tanggal);
                                            $tahun = date('Y', $tanggal);

                                            echo $hari . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun; // Menampilkan hari, bulan, dan tahun
                                            ?>
                                        </td>
                                        <td><?php echo $row['ukuran_file']; ?></td>
                                        <td>
                                            <img width="30" src="apps/panduan/format_file/<?php echo $icon; ?>" alt="icon">
                                            <?php echo $row['file_panduan']; ?>
                                        </td>
                                        <td>
                                            <button id_panduan="<?php echo $row['id_panduan']; ?>"
                                                class="tombol_edit_panduan btn btn-warning btn-circle" title="Edit Panduan"><i
                                                    class="fa fa-edit"></i></button>
                                            <a href="apps/panduan/download.php?id_panduan=<?php echo $row['id_panduan']; ?>"
                                                class="btn btn-primary" title="Unduh Panduan"><i class="fa fa-download"></i></a>
                                            <a href="apps/panduan/hapus.php?id_panduan=<?php echo $row['id_panduan']; ?>"
                                                class="btn-hapus btn btn-danger btn-circle" title="Hapus Panduan">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center">Tidak ditemukan ada data</td></tr>';
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

<script>
    $(document).ready(function () {
        $('[title]').tooltip();
    });
</script>

<script>
    // Tambah panduan
    $('#tombol_tambah_panduan').on('click', function () {
        $.ajax({
            url: 'apps/panduan/tambah.php',
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
    // Edit panduan
    $('.tombol_edit_panduan').on('click', function () {
        var id_panduan = $(this).attr("id_panduan");
        $.ajax({
            url: 'apps/panduan/edit.php',
            method: 'post',
            data: {
                id_panduan: id_panduan
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Data';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus').forEach(button => {
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