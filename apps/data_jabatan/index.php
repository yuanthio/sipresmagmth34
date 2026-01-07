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
                url.searchParams.delete('tambah');
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
        <li class="active">Data Jabatan</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Jabatan
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_jabatan" />
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
                if (isset($_GET['tambah'])) {
                    if ($_GET['tambah'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Jabatan Berhasil Ditambahkan');</script>";
                    } else if ($_GET['tambah'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Jabatan Gagal Ditambahkan');</script>";
                    } else if ($_GET['tambah'] == 'nama_sudah_ada') {
                        echo "<script>showAlert('error', 'Gagal!', 'Nama Jabatan Sudah Ada di Unit Kerja Tersebut');</script>";
                    } else if ($_GET['tambah'] == 'nama_sama') {
                        echo "<script>showAlert('error', 'Gagal!', 'Nama Jabatan Duplikat dalam Input Form');</script>";
                    }
                }

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Jabatan Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Jabatan Gagal Diupdate');</script>";
                    } else if ($_GET['edit'] == 'nama_sudah_ada') {
                        echo "<script>showAlert('error', 'Gagal!', 'Nama Jabatan Sudah Ada, Gunakan Nama Lain');</script>";
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
                        echo "<script>showAlert('success', 'Berhasil!', 'Unit Kerja Telah Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Unit Kerja Gagal Dihapus');</script>";
                    }
                }
                ?>

                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah">
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tabel_unit_kerja">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Jabatan</th>
                                <th>Unit Kerja</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';
                            $no = 1;

                            $cari = isset($_GET['cari']) ? mysqli_real_escape_string($kon, $_GET['cari']) : '';
                            if (!empty($cari)) {
                                $query = mysqli_query($kon, "SELECT id_jabatan, nama, unit_kerja 
                                FROM tbl_jabatan 
                                WHERE nama LIKE '%$cari%' OR unit_kerja LIKE '%$cari%' 
                                ORDER BY unit_kerja, nama ASC");
                            } else {
                                $query = mysqli_query($kon, "SELECT id_jabatan, nama, unit_kerja FROM tbl_jabatan ORDER BY unit_kerja, nama ASC");
                            }

                            $unitKerjaPrev = "";
                            $jabatanList = [];
                            $lastIdJabatan = null;

                            while ($data = mysqli_fetch_array($query)) {
                                $id_jabatan = $data['id_jabatan'];
                                $nama_jabatan = htmlspecialchars($data['nama']);
                                $unit_kerja = htmlspecialchars($data['unit_kerja']);

                                if ($unitKerjaPrev != $unit_kerja) {
                                    // Tampilkan data sebelumnya
                                    if (!empty($jabatanList)) {
                                        $jabatanNames = implode(', ', $jabatanList);
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $no++; ?></td>
                                            <td><?php echo $jabatanNames; ?></td>
                                            <td><?php echo $unitKerjaPrev; ?></td>
                                            <td>
                                                <button id_jabatan="<?php echo $lastIdJabatan; ?>"
                                                    class="tombol_edit btn btn-warning btn-circle" title="Edit Jabatan">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <a href="apps/data_jabatan/hapus.php?id=<?php echo $lastIdJabatan; ?>"
                                                    class="btn btn-danger btn-hapus-jabatan" title="Hapus Jabatan">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    // Reset untuk unit kerja baru
                                    $unitKerjaPrev = $unit_kerja;
                                    $jabatanList = explode(',', $nama_jabatan);
                                    $jabatanList = array_map('trim', $jabatanList); // Hapus spasi ekstra
                                    $lastIdJabatan = $id_jabatan;
                                } else {
                                    // Tambahkan ke daftar jabatan
                                    $additionalJabatan = explode(',', $nama_jabatan);
                                    $additionalJabatan = array_map('trim', $additionalJabatan);
                                    $jabatanList = array_merge($jabatanList, $additionalJabatan);
                                    $lastIdJabatan = $id_jabatan; // Update dengan ID terakhir dalam grup
                                }
                            }

                            // Tampilkan sisa data terakhir
                            if (!empty($jabatanList)) {
                                $jabatanNames = implode(', ', $jabatanList);
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php echo $jabatanNames; ?></td>
                                    <td><?php echo $unitKerjaPrev; ?></td>
                                    <td>
                                        <button id_jabatan="<?php echo $lastIdJabatan; ?>"
                                            class="tombol_edit btn btn-warning btn-circle" title="Edit Jabatan">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="apps/data_jabatan/hapus.php?id=<?php echo $lastIdJabatan; ?>"
                                            class="btn btn-danger btn-hapus-jabatan" title="Hapus Jabatan">
                                            <i class="fa fa-trash"></i>
                                        </a>
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

<script>
    $(document).ready(function () {
        $('[title]').tooltip();
    });
</script>

<script>
    // Tambah jabatan
    $('#tombol_tambah').on('click', function () {
        $.ajax({
            url: 'apps/data_jabatan/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Jabatan';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Edit jabatan
    $('.tombol_edit').on('click', function () {
        var id_jabatan = $(this).attr("id_jabatan");
        $.ajax({
            url: 'apps/data_jabatan/edit.php',
            method: 'post',
            data: {
                id_jabatan: id_jabatan
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Jabatan';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-jabatan').forEach(button => {
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