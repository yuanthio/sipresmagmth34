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
        <li class="active">Data Rating</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px; margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Rating
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">

                <?php
                // Validasi untuk menampilkan pesan pemberitahuan menggunakan SweetAlert2
                if (isset($_GET['add'])) {
                    if ($_GET['add'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Rating Telah Disimpan');</script>";
                    } else if ($_GET['add'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Rating Gagal Disimpan');</script>";
                    } else if ($_GET['add'] == 'duplikat') {
                        echo "<script>showAlert('warning', 'Peringatan!', 'Rating sudah pernah ditambahkan');</script>";
                    }
                }

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Rating Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Rating Gagal Diupdate');</script>";
                    }
                }

                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Rating Telah Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Rating Gagal Dihapus');</script>";
                    }
                }
                ?>

                <?php
                // Ambil rata-rata dan jumlah rating
                $query_statistik = "SELECT AVG(rating) AS rata_rata_rating, COUNT(*) AS jumlah_rating FROM tbl_rating";
                $hasil_statistik = mysqli_query($kon, $query_statistik);
                $data_statistik = mysqli_fetch_assoc($hasil_statistik);

                $rata_rata = number_format($data_statistik['rata_rata_rating'], 1); // format 1 desimal
                $jumlah_rating = $data_statistik['jumlah_rating'];
                ?>

                <div class="form-group" style="color: white; display: flex; align-items: center;">
                    <!-- Tombol Tambah -->
                    <button type="button" class="btn btn-success me-3" style="margin-right: 10px;" id="tombol_tambah">
                        <i class="fa fa-plus"></i> Tambah
                    </button>

                    <!-- Info Rating -->
                    <div class="info">
                        <?php if ($jumlah_rating > 0): ?>
                            <div style="display: flex; flex-direction: column;">
                                <div>
                                    <i class="fa fa-star" style="color: gold; font-size: 1.2em; margin-right: 5px;"></i>
                                    <strong style="margin-right: 10px;"><?php echo $rata_rata; ?></strong>
                                </div>
                                <span class="text-muted"><?php echo $jumlah_rating; ?> Pengguna telah memberikan
                                    rating</span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Belum ada rating</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead style="z-index: 2;">
                            <tr>
                                <th width="50">No</th>
                                <th width="200">Nama</th>
                                <th width="100">Level</th>
                                <th width="120">Rating</th>
                                <th>Pesan</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            include 'config/database.php';

                            $no = 1;
                            $query = "SELECT id_rating, kode_pengguna, nama, level, rating, pesan, tanggal FROM tbl_rating ORDER BY tanggal DESC";
                            $result = mysqli_query($kon, $query);

                            if (mysqli_num_rows($result) > 0) {
                                while ($data = mysqli_fetch_array($result)) {
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($data['level']); ?></td>
                                        <td>
                                            <?php
                                            for ($i = 1; $i <= $data['rating']; $i++) {
                                                echo '<i class="fa fa-star" style="color: rgb(243, 224, 9);"></i> ';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo nl2br(htmlspecialchars($data['pesan'])); ?></td>
                                        <td class="text-center">
                                            <button id_rating="<?php echo $data['id_rating']; ?>"
                                                class="tombol_edit btn btn-warning btn-circle" title="Edit Rating"><i
                                                    class="fa fa-edit"></i></button>
                                            <a href="apps/data_rating/hapus.php?id_rating=<?php echo $data['id_rating']; ?>"
                                                class="btn btn-danger btn-circle btn-hapus-rating" title="Hapus Rating">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">Data masih kosong</td></tr>';
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
    $('#tombol_tambah').on('click', function () {
        $.ajax({
            url: 'apps/data_rating/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Rating';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Edit rating
    $('.tombol_edit').on('click', function () {
        var id_rating = $(this).attr("id_rating");
        $.ajax({
            url: 'apps/data_rating/edit.php',
            method: 'post',
            data: {
                id_rating: id_rating
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Rating';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-rating').forEach(button => {
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