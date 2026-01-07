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

    .filter {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 200px;
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

    /* Atur margin kanan pada tombol tambah untuk memberikan ruang di antara tombol dan dropdown */
    #tombol_tambah_suket {
        margin-right: 10px;
    }

    @media (max-width: 768px) {
        .filter {
            margin-bottom: 4px;
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
        <li class="active">Data Akhir Magang</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Akhir Magang
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="row justify-content-end">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_selesai_magang" />
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

                // Menampilkan alert untuk hasil penghapusan data
                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Berhasil Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Data Gagal Dihapus');</script>";
                    }
                }
                ?>
                <div class="form-group filter"
                    style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn btn-success filter" id="tombol_tambah_suket"><i
                            class="fa fa-plus"></i> Tambah</button>
                    <select id="filter_jenis" class="form-control filter">
                        <option value="semua">Tampilkan Semua</option>
                        <option value="Surat Keterangan">Surat Keterangan</option>
                        <option value="Sertifikat">Sertifikat</option>
                        <option value="Penilaian Kinerja">Penilaian Kinerja</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Universitas / Sekolah</th>
                                <th>Jenis Data</th>
                                <th>Hari</th>
                                <th>Di Unggah</th>
                                <th width="110">Ukuran File</th>
                                <th width="300">Nama File</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "config/database.php";
                            include "config/function.php";

                            // Query untuk mengambil data dari tabel tbl_suket
                            $query = "SELECT * FROM tbl_suket";

                            // Menambahkan kondisi pencarian jika ada
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $searchKeyword = mysqli_real_escape_string($kon, $_GET['search']);
                                $query .= " WHERE nama LIKE '%$searchKeyword%' OR universitas LIKE '%$searchKeyword%' OR jenis_data LIKE '%$searchKeyword%' OR hari LIKE '%$searchKeyword%' OR tanggal LIKE '%$searchKeyword%'";
                            }

                            // Menambahkan pengurutan data berdasarkan tanggal secara descending
                            $query .= " ORDER BY tanggal DESC";

                            $result = mysqli_query($kon, $query);

                            // Loop untuk menampilkan data dalam tabel
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Ambil ekstensi file_suket
                                $file_ext = pathinfo($row['file_suket'], PATHINFO_EXTENSION);

                                // Tentukan ikon berdasarkan ekstensi file
                                if ($file_ext == 'pdf') {
                                    $icon = 'pdf.png';
                                } elseif ($file_ext == 'doc') {
                                    $icon = 'doc.png';
                                } elseif ($file_ext == 'docx') {
                                    $icon = 'docx.png';
                                } else {
                                    $icon = 'default.png'; // jika ada ekstensi yang tidak diketahui
                                }
                                ?>
                                <tr data-jenis="<?php echo $row['jenis_data']; ?>">
                                    <td>
                                        <?php echo $no; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['nama']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['universitas']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['jenis_data']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['hari']; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $tgl = date("d", strtotime($row['tanggal']));
                                        $bulan = date("m", strtotime($row['tanggal']));
                                        $tahun = date("Y", strtotime($row['tanggal']));
                                        // Menampilkan tanggal dalam format yang lebih mudah dibaca
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $row['ukuran_file']; ?>
                                    </td>
                                    <td>
                                        <img width="30" src="apps/data_selesai_magang/format_file/<?php echo $icon; ?>"
                                            alt="icon">
                                        <?php echo $row['file_suket']; ?>
                                    </td>
                                    <td>
                                        <button id_panduan="<?php echo $row['id_suket']; ?>"
                                            class="tombol_edit_suket btn btn-warning btn-circle"
                                            title="Edit Data Akhir Magang"><i class="fa fa-edit"></i></button>
                                        <a href="apps/data_selesai_magang/download.php?id_suket=<?php echo $row['id_suket']; ?>"
                                            class="btn btn-primary" title="Unduh Data Akhir Magang"><i
                                                class="fa fa-download"></i></a>
                                        <a href="apps/data_selesai_magang/hapus.php?id_suket=<?php echo $row['id_suket']; ?>"
                                            class="btn-hapus btn btn-danger btn-circle" title="Hapus Data Akhir Magang">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $no++;
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
    // Tambah admin
    $('#tombol_tambah_suket').on('click', function () {
        $.ajax({
            url: 'apps/data_selesai_magang/tambah_suket.php',
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
    // Edit suket
    $('.tombol_edit_suket').on('click', function () {
        var id_suket = $(this).attr("id_suket");
        $.ajax({
            url: 'apps/data_selesai_magang/edit.php',
            method: 'post',
            data: {
                id_suket: id_suket
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
    $(document).ready(function () {
        $('#filter_jenis').on('change', function () {
            var jenis = $(this).val();

            $('tbody tr').hide();

            // Tampilkan hanya baris dengan jenis data yang dipilih
            if (jenis === 'semua') {
                $('tbody tr').show();
            } else {
                $('tbody tr[data-jenis="' + jenis + '"]').show();
            }
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