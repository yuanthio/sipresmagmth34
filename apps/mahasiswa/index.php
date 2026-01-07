<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<style>
    .table-responsive {
        overflow-y: auto;
        max-height: 500px;
    }

    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
        z-index: 2;
    }

    .modal-body img {
        max-width: 100%;
        max-height: 80vh;
        width: auto;
        height: auto;
        margin: auto;
        display: block;
    }

    #modalBody {
        overflow: hidden;
        position: relative;
    }

    #modalFotoImg {
        transition: transform 0.3s ease;
        cursor: grab;
    }

    #modalFotoImg:active {
        cursor: grabbing;
    }

    .fa-search-plus,
    .fa-search-minus {
        font-size: 1.2em;
        margin-right: 4px;
    }

    .btn-show-foto {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1;
        background-color: white;
        color: black;
        padding: 2px 2px;
        border-radius: 5px;
        text-decoration: none;
        font-size: .8em;
        box-shadow: 0 4px 2px 5px rgba(0, 0, 0, .3);
        transition: .3s;
    }

    .btn-show-foto:hover {
        background-color: black;
        color: white;
        text-decoration: none;
        /* Ubah warna latar belakang saat hover */
    }

    /* Mengatur tata letak gambar di bawah link */
    img {
        display: block;
        margin: 0 auto;
    }

    .filter {
        display: flex;
        column-gap: 5px;
    }

    #dataFilter {
        width: 70px;
    }

    #unit_kerja_filter {
        width: 160px;
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

    @media (max-width: 1260px) {
        .filter {
            margin-bottom: 4px;
        }
    }
</style>

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
            // Remove the URL parameters after showing the alert
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('add');
                url.searchParams.delete('edit');
                url.searchParams.delete('pengguna');
                url.searchParams.delete('hapus');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<?php
include 'config/database.php';
date_default_timezone_set('Asia/Jakarta');

// pagination setup
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
if (!in_array($limit, [25, 50, 75, 100])) {
    $limit = 25; // fallback jika user ubah URL manual
}

$page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;
?>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb" style="background-color: #eaeaea">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Karyawan Magang</li>
    </ol>
</div><!--/.row-->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Karyawan Magang
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="mahasiswa" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="cari" id="cari" class="form-control"
                                    value="<?php echo isset($_GET['cari']) ? $_GET['cari'] : ''; ?>"
                                    placeholder="Pencarian">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">

                <?php
                if (isset($_GET['add'])) {
                    if ($_GET['add'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Mahasiswa Telah Disimpan');</script>";
                    } else if ($_GET['add'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Data Mahasiswa Gagal Disimpan');</script>";
                    }
                }

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Mahasiswa Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Data Mahasiswa Gagal Diupdate');</script>";
                    }
                }

                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Setting Data Mahasiswa Berhasil');</script>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Setting Data Mahasiswa Gagal');</script>";
                    }
                }

                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Mahasiswa Telah Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Data Mahasiswa Gagal Dihapus');</script>";
                    }
                }
                ?>

                <div class="form-group filter">
                    <?php if ($_SESSION["level"] == 'Admin'): ?>
                        <button type="button" class="btn btn-success filter" id="tombol_tambah"><i class="fa fa-plus"></i>
                            Tambah</button>
                    <?php endif; ?>
                    <select id="unit_kerja_filter" class="form-control">
                        <option value="semua">Tampilkan Semua</option>
                        <option value="Subbag SDM">Subbag SDM</option>
                        <option value="Subbag Umum dan TI">Subbag Umum dan TI</option>
                        <option value="Subbag Humas">Subbag Humas</option>
                        <option value="Subbag TU Kalan">Subbag TU Kalan</option>
                        <option value="Subbag Keuangan">Subbag Keuangan</option>
                        <option value="Subbag Hukum">Subbag Hukum</option>
                    </select>
                    <select id="dataFilter" class="form-control" onchange="changeLimit(this.value)">
                        <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="75" <?= $limit == 75 ? 'selected' : '' ?>>75</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th width="150">Nama</th>
                                <th width="150">Universitas / Sekolah</th>
                                <th width="150">NIM / NIS</th>
                                <th width="150">Mulai Magang</th>
                                <th width="150">Akhir Magang</th>
                                <th width="150">Unit Kerja</th>
                                <th width="150">Mentor</th>
                                <th width="150">Status Magang</th>
                                <th width="150">Foto</th>
                                <th width="170">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';
                            date_default_timezone_set('Asia/Jakarta');

                            if ($_SESSION["level"] == 'Mentor') {
                                $mentor_name = $_SESSION["nama_mentor"];
                                $sql_base = "SELECT * FROM tbl_mahasiswa WHERE mentor = '$mentor_name'";
                            } else {
                                $sql_base = "SELECT * FROM tbl_mahasiswa WHERE 1=1";
                            }

                            if (isset($_GET['cari']) && $_GET['cari'] != "") {
                                $cari = trim($_GET["cari"]);
                                $sql_base .= " AND (nama LIKE '%$cari%' OR 
                                                    nim LIKE '%$cari%' OR 
                                                    universitas LIKE '%$cari%' OR 
                                                    jurusan LIKE '%$cari%')";
                            }

                            // total data untuk pagination
                            $sql_count = $sql_base;
                            $hasil_count = mysqli_query($kon, $sql_count);
                            $total_data = mysqli_num_rows($hasil_count);
                            $total_halaman = ceil($total_data / $limit);

                            // query data mahasiswa dengan limit
                            $sql = $sql_base . " ORDER BY mulai_magang DESC LIMIT $limit OFFSET $offset";
                            $hasil = mysqli_query($kon, $sql);
                            $no = $offset;

                            while ($data = mysqli_fetch_array($hasil)):
                                $no++;
                                $periode_magang_selesai = strtotime($data["akhir_magang"]);
                                $sekarang = strtotime(date('Y-m-d'));
                                $status_magang = ($sekarang <= $periode_magang_selesai) ? "Aktif" : "Tidak Aktif";

                                $id_mahasiswa = $data['id_mahasiswa'];
                                $updateQuery = "UPDATE tbl_mahasiswa SET status_magang = '$status_magang' WHERE id_mahasiswa = $id_mahasiswa";
                                mysqli_query($kon, $updateQuery);

                                $mentor_name = $data['mentor'];
                                $checkMentorQuery = "SELECT kode_mentor, nip, jabatan FROM tbl_mentor WHERE nama = '$mentor_name'";
                                $resultMentor = mysqli_query($kon, $checkMentorQuery);

                                if ($rowMentor = mysqli_fetch_assoc($resultMentor)) {
                                    $kode_mentor = $rowMentor['kode_mentor'];
                                    $nip_mentor = $rowMentor['nip'];
                                    $jabatan_mentor = $rowMentor['jabatan'];

                                    $updateKodeMentorQuery = "UPDATE tbl_mahasiswa SET kode_mentor = '$kode_mentor', nip_mentor = '$nip_mentor' WHERE id_mahasiswa = $id_mahasiswa";
                                    mysqli_query($kon, $updateKodeMentorQuery);

                                    $updateJabatanMentorQuery = "UPDATE tbl_mahasiswa SET jabatan_mentor = '$jabatan_mentor' WHERE id_mahasiswa = $id_mahasiswa";
                                    mysqli_query($kon, $updateJabatanMentorQuery);
                                }
                                ?>
                                <tr unit_kerja="<?php echo $data['unit_kerja']; ?>">
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['universitas']; ?></td>
                                    <td><?php echo $data['nim']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($data["mulai_magang"])); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($data["akhir_magang"])); ?></td>
                                    <td><?php echo $data['unit_kerja']; ?></td>
                                    <td><?php echo $data['mentor']; ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badge_class = ($status_magang == 'Aktif') ? 'label label-success' : 'label label-danger';
                                        echo '<span class="' . $badge_class . '">' . $status_magang . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center" style="position: relative;">
                                        <?php
                                        $fotos = explode(', ', $data['foto']);
                                        if (empty($fotos[0])) {
                                            echo "-";
                                        } else {
                                            foreach ($fotos as $foto) {
                                                echo "<div style='width: 100%; text-align: center; margin-bottom: 10px;'>";
                                                if ($foto != "foto_default.png") {
                                                    echo "<a href='#' class='btn-show-foto' data-src='apps/mahasiswa/foto/$foto' style='display: block; margin-bottom: 5px;'>Lihat Foto</a>";
                                                }
                                                echo "<img src='apps/mahasiswa/foto/$foto' alt='$foto' style='width: 130px; height: 130px; border-radius: 50%; object-fit: cover;'>";
                                                echo "</div>";
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button style="margin-bottom: 5px;"
                                            id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>"
                                            class="tombol_detail btn btn-default btn-circle" title="Detail Karyawan Magang">
                                            <i class="fa fa-mouse-pointer"></i>
                                        </button>
                                        <?php if ($_SESSION["level"] == 'Admin'): ?>
                                            <button style="margin-bottom: 5px;"
                                                kode_mahasiswa="<?php echo $data['kode_mahasiswa']; ?>"
                                                class="tombol_setting btn btn-primary btn-circle"
                                                title="Setting Karyawan Magang">
                                                <i class="fa fa-user"></i>
                                            </button>
                                            <button style="margin-bottom: 5px;"
                                                id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>"
                                                class="tombol_edit btn btn-warning btn-circle" title="Edit Karyawan Magang">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <a style="margin-bottom: 5px;"
                                                href="apps/mahasiswa/hapus.php?id_mahasiswa=<?php echo $data['id_mahasiswa']; ?>&kode_mahasiswa=<?php echo $data['kode_mahasiswa']; ?>"
                                                class="btn-hapus-mahasiswa btn btn-danger btn-circle"
                                                title="Hapus Karyawan Magang">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- pagination -->
                <nav style="display: flex; justify-content: center;">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li><a
                                    href="?page=mahasiswa&halaman=<?= $page - 1; ?>&limit=<?= $limit; ?>&cari=<?= isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">&laquo;
                                    Prev</a></li>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end = min($total_halaman, $page + $range);

                        if ($start > 1) {
                            echo '<li><a href="?page=mahasiswa&halaman=1&limit=' . $limit . '&cari=' . (isset($_GET['cari']) ? $_GET['cari'] : '') . '">1</a></li>';
                            if ($start > 2)
                                echo '<li class="disabled"><span>...</span></li>';
                        }

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="' . $active . '"><a href="?page=mahasiswa&halaman=' . $i . '&limit=' . $limit . '&cari=' . (isset($_GET['cari']) ? $_GET['cari'] : '') . '">' . $i . '</a></li>';
                        }

                        if ($end < $total_halaman) {
                            if ($end < $total_halaman - 1)
                                echo '<li class="disabled"><span>...</span></li>';
                            echo '<li><a href="?page=mahasiswa&halaman=' . $total_halaman . '&limit=' . $limit . '&cari=' . (isset($_GET['cari']) ? $_GET['cari'] : '') . '">' . $total_halaman . '</a></li>';
                        }
                        ?>

                        <?php if ($page < $total_halaman): ?>
                            <li><a
                                    href="?page=mahasiswa&halaman=<?= $page + 1; ?>&limit=<?= $limit; ?>&cari=<?= isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">Next
                                    &raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div><!--/.row-->

<!-- Modal untuk menampilkan gambar besar -->
<div class="modal fade" id="modalFoto" tabindex="-1" role="dialog" aria-labelledby="modalFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalFotoLabel">Lihat Foto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="modalBody"
                style="width: 100%; text-align: center; margin-bottom: 10px;">
                <img id="modalFotoImg" src="" alt="Foto" class="img-fluid"
                    style="width: 400px; height: 400px; border-radius: 50%; object-fit: cover; border: 4px solid rgb(13, 10, 44);">
            </div>
            <div class="modal-footer">
                <button id="zoom-in" class="btn btn-primary"><i class="fa fa-search-plus"></i> Zoom In</button>
                <button id="zoom-out" class="btn btn-warning"><i class="fa fa-search-minus"></i> Zoom Out</button>
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
    $(document).ready(function () {
        var scale = 1;
        var minScale = 0.5;
        var maxScale = 2.0;

        // Ketika tombol foto di tabel diklik
        $(document).on('click', '.btn-show-foto', function (event) {
            event.preventDefault(); // Menghentikan aksi default dari link
            var src = $(this).data('src');
            $('#modalFotoImg').attr('src', src);
            $('#modalFoto').modal('show');
            scale = 1;
            updateTransform();
        });

        // Zoom In
        $('#zoom-in').on('click', function () {
            if (scale < maxScale) {
                scale = Math.min(maxScale, scale + 0.1);
                updateTransform();
            }
        });

        // Zoom Out
        $('#zoom-out').on('click', function () {
            if (scale > minScale) {
                scale = Math.max(minScale, scale - 0.1);
                updateTransform();
            }
        });

        // Reset ukuran gambar saat modal ditutup
        $('#modalFoto').on('hidden.bs.modal', function () {
            scale = 1;
            updateTransform();
        });

        // Update transformasi gambar
        function updateTransform() {
            $('#modalFotoImg').css('transform', 'scale(' + scale + ')');
        }
    });
</script>

<!-- Tambah admin: Ketika tombol "Tambah Karyawan Magang" ditekan, tampilkan form tambah -->
<script>
    $('#tombol_tambah').on('click', function () {
        $.ajax({
            url: 'apps/mahasiswa/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Karyawan Magang';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<!-- Detail Mahasiswa: Ketika tombol "Detail" pada data mahasiswa ditekan, tampilkan detail mahasiswa -->
<script>
    $('.tombol_detail').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/detail.php',
            method: 'post',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Detail Karyawan Magang';
            }
        });
        $('#modal').modal('show');
    });
</script>

<!-- Setting Mahasiswa: Ketika tombol "Setting" pada data mahasiswa ditekan, tampilkan pengaturan mahasiswa -->
<script>
    $('.tombol_setting').on('click', function () {
        var kode_mahasiswa = $(this).attr("kode_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/pengguna.php',
            method: 'post',
            data: {
                kode_mahasiswa: kode_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Setting Karyawan Magang';
            }
        });
        $('#modal').modal('show');
    });
</script>

<!-- Edit Mahasiswa: Ketika tombol "Edit" pada data mahasiswa ditekan, tampilkan form edit -->
<script>
    $('.tombol_edit').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/edit.php',
            method: 'post',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Karyawan Magang';
            }
        });
        $('#modal').modal('show');
    });
</script>

<!-- Hapus admin: Menampilkan konfirmasi sebelum menghapus mahasiswa -->
<script>
    document.querySelectorAll('.btn-hapus-mahasiswa').forEach(button => {
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
        $('#unit_kerja_filter').on('change', function () {
            var unitKerja = $(this).val();

            if (unitKerja === 'semua') {
                $('tbody tr').show();
            } else {
                $('tbody tr').hide();
                $('tbody tr[unit_kerja="' + unitKerja + '"]').show();
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

<script>
    function changeLimit(val) {
        const url = new URL(window.location.href);
        url.searchParams.set("limit", val);
        url.searchParams.set("halaman", 1); // reset ke halaman 1 saat ganti limit
        window.location.href = url.toString();
    }
</script>