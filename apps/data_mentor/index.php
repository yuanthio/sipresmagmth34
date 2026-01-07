<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
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
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Mentor</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Mentor
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_mentor" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="cari" id="cari" class="form-control"
                                    value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>"
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
</div>

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <?php
                // Validasi untuk menampilkan pesan pemberitahuan menggunakan SweetAlert2
                if (isset($_GET['add'])) {
                    if ($_GET['add'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Mentor Telah Disimpan');</script>";
                    } else if ($_GET['add'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Mentor Gagal Disimpan');</script>";
                    } else if ($_GET['add'] == 'ukuran_terlalu_besar') {
                        echo "<script>showAlert('warning', 'Ukuran Terlalu Besar', 'Ukuran file tidak boleh lebih dari 1MB');</script>";
                    } else if ($_GET['add'] == 'format_tidak_valid') {
                        echo "<script>showAlert('warning', 'Format Tidak Valid', 'Hanya diperbolehkan JPG, JPEG, atau PNG');</script>";
                    }
                }

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Mentor Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Mentor Gagal Diupdate');</script>";
                    } else if ($_GET['edit'] == 'ukuran_terlalu_besar') {
                        echo "<script>showAlert('warning', 'Ukuran Terlalu Besar!', 'Ukuran file tidak boleh lebih dari 1MB');</script>";
                    } else if ($_GET['edit'] == 'ekstensi_tidak_valid') {
                        echo "<script>showAlert('warning', 'Ekstensi Tidak Valid!', 'Hanya diperbolehkan JPG, JPEG, atau PNG');</script>";
                    }
                }

                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Mentor Berhasil Diatur');</script>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Mentor Gagal');</script>";
                    }
                }

                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Mentor Telah Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Mentor Gagal Dihapus');</script>";
                    }
                }
                ?>

                <?php
                // Ambil semua unit kerja dari tbl_unit_kerja
                $query_unit_kerja = mysqli_query($kon, "SELECT nama FROM tbl_unit_kerja");
                $unit_kerja = [];
                while ($row = mysqli_fetch_assoc($query_unit_kerja)) {
                    $unit_kerja[] = $row['nama'];
                }

                // Ambil semua jabatan dari tbl_jabatan
                $query_jabatan = mysqli_query($kon, "SELECT unit_kerja, nama FROM tbl_jabatan");
                $jabatan_per_unit = [];
                while ($row = mysqli_fetch_assoc($query_jabatan)) {
                    $uk = $row['unit_kerja'];
                    $jabatan = $row['nama'];
                    $jabatans = array_map('trim', explode(',', $jabatan)); // pisahkan jika nama mengandung banyak jabatan
                    foreach ($jabatans as $j) {
                        $jabatan_per_unit[$uk][] = $j;
                    }
                }

                // Ambil semua kombinasi jabatan dan unit kerja dari tbl_mentor
                $query_mentor = mysqli_query($kon, "SELECT unit_kerja, jabatan FROM tbl_mentor");
                $mentor_jabatan_terpakai = [];
                while ($row = mysqli_fetch_assoc($query_mentor)) {
                    $uk = $row['unit_kerja'];
                    $jabatan = $row['jabatan'];
                    $jabatans = array_map('trim', explode(',', $jabatan));
                    foreach ($jabatans as $j) {
                        $mentor_jabatan_terpakai[$uk][] = $j;
                    }
                }

                // Cek apakah masih ada jabatan yang belum dipakai untuk masing-masing unit kerja
                $semua_unit_kerja_sudah_terpakai = true;

                foreach ($jabatan_per_unit as $uk => $jabatans) {
                    $j_terpakai = isset($mentor_jabatan_terpakai[$uk]) ? $mentor_jabatan_terpakai[$uk] : [];
                    // Bandingkan yang belum dipakai
                    $belum_terpakai = array_diff($jabatans, $j_terpakai);
                    if (!empty($belum_terpakai)) {
                        $semua_unit_kerja_sudah_terpakai = false;
                        break;
                    }
                }

                // Disable tombol jika semua jabatan pada semua unit kerja sudah terpakai
                $disable_tombol = $semua_unit_kerja_sudah_terpakai;
                ?>

                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah" <?php echo $disable_tombol ? 'disabled title="Semua jabatan pada setiap unit kerja sudah digunakan oleh mentor. Tambahkan jabatan/unit kerja baru."' : ''; ?>>
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead style="z-index: 2;">
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Unit Kerja</th>
                                <th>Jabatan</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            // include database
                            include 'config/database.php';

                            $cari = isset($_GET['cari']) ? mysqli_real_escape_string($kon, $_GET['cari']) : '';

                            if (!empty($cari)) {
                                $sql = "SELECT * FROM tbl_mentor 
                                WHERE nip LIKE '%$cari%' 
                                OR nama LIKE '%$cari%' 
                                OR email LIKE '%$cari%' 
                                OR unit_kerja LIKE '%$cari%' 
                                OR jabatan LIKE '%$cari%'";
                            } else {
                                $sql = "SELECT * FROM tbl_mentor";
                            }

                            $hasil = mysqli_query($kon, $sql);
                            $no = 0;
                            //Menampilkan data dengan perulangan while
                            while ($data = mysqli_fetch_array($hasil)):
                                $no++;
                                ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo $data['nip']; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['email']; ?></td>
                                    <td><?php echo $data['unit_kerja']; ?></td>
                                    <td><?php echo $data['jabatan']; ?></td>
                                    <td class="text-center" style="position: relative;">
                                        <?php
                                        $fotos = explode(', ', $data['foto']); // Memisahkan nama file foto yang dipisahkan koma
                                        if (empty($fotos[0])) {
                                            // Jika tidak ada foto, tampilkan gambar default
                                            echo "<div style='width: 100%; text-align: center; margin-bottom: 10px; position: relative;'>";
                                            echo "<img src='apps/pengguna/foto_mentor/foto_default.png' alt='No Image' style='width: 130px; height: 130px; border-radius: 50%; object-fit: cover;'>";
                                            echo "</div>";
                                        } else {
                                            // Menampilkan foto yang ada
                                            foreach ($fotos as $foto) {
                                                echo "<div style='width: 100%; text-align: center; margin-bottom: 10px; position: relative; display: inline-block;'>";

                                                // Tombol overlay untuk melihat foto jika bukan foto default
                                                if ($foto != "foto_default.png") {
                                                    echo "<a href='#' class='btn-show-foto' data-src='apps/pengguna/foto_mentor/$foto' style=' position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; color: black; border-radius: 5px; text-decoration: none; z-index: 1; font-size: 12px; padding: 5px; '>Lihat Foto</a>";
                                                }

                                                // Tampilkan gambar
                                                echo "<img src='apps/pengguna/foto_mentor/$foto' alt='$foto' style='width: 130px; height: 130px; border-radius: 50%; object-fit: cover; display: block;'>";
                                                echo "</div>";
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button kode_mentor="<?php echo $data['kode_mentor']; ?>"
                                            class="tombol_setting_pengguna btn btn-primary btn-circle"
                                            title="Setting Mentor"><i class="fa fa-user"></i></button>
                                        <button id_mentor="<?php echo $data['id_mentor']; ?>"
                                            class="tombol_edit btn btn-warning btn-circle" title="Edit Mentor"><i
                                                class="fa fa-edit"></i></button>
                                        <a href="apps/data_mentor/hapus.php?id_mentor=<?php echo $data['id_mentor']; ?>&kode_mentor=<?php echo $data['kode_mentor']; ?>"
                                            class="btn-hapus-mentor btn btn-danger btn-circle" title="Hapus Mentor"><i
                                                class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <!-- bagian akhir (penutup) while -->
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Data akan di load menggunakan AJAX -->
<script>
    // Tambah admin
    $('#tombol_tambah').on('click', function () {
        $.ajax({
            url: 'apps/data_mentor/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Mentor';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Setting Mentor
    $('.tombol_setting_pengguna').on('click', function () {
        var kode_mentor = $(this).attr("kode_mentor");
        $.ajax({
            url: 'apps/data_mentor/pengguna.php',
            method: 'post',
            data: {
                kode_mentor: kode_mentor
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Setting Mentor';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>


<script>
    // Edit admin
    $('.tombol_edit').on('click', function () {
        var id_mentor = $(this).attr("id_mentor");
        $.ajax({
            url: 'apps/data_mentor/edit.php',
            method: 'post',
            data: {
                id_mentor: id_mentor
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Mentor';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-mentor').forEach(button => {
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