<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

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
            timer: type === 'error' ? null : 1700,
            showConfirmButton: (type === 'error' || type === 'warning'),
            confirmButtonText: '<span style="font-size: 1.5em;">Ok</span>'
        }).then(() => {
            // Menghapus parameter URL setelah menampilkan alert
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('tambah');
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

<?php
include 'config/database.php';
include 'config/function.php';

// ===== Pagination setup =====
// ambil nilai limit dari GET, default 25
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
if (!in_array($limit, [25, 50, 75, 100])) {
    $limit = 25; // fallback biar aman
}

$page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// filter pencarian
$nama = isset($_GET['nama']) ? trim($_GET["nama"]) : "";
$tanggal_awal = isset($_GET["tanggal_awal"]) ? $_GET["tanggal_awal"] : "";
$tanggal_akhir = isset($_GET["tanggal_akhir"]) ? $_GET["tanggal_akhir"] : "";
$mentor_name = ($_SESSION["level"] == 'Mentor') ? $_SESSION["nama_mentor"] : "";

// generate base query sesuai level
if ($_SESSION["level"] == 'Admin' || $_SESSION["level"] == 'admin') {
    if (!empty($nama) || (!empty($tanggal_awal) && !empty($tanggal_akhir))) {
        $nama = implode(" ", array_map('ucfirst', explode(" ", $nama)));
        $sqlBase = CariKegiatan($nama, $tanggal_awal, $tanggal_akhir, "");
    } else {
        $sqlBase = DataKegiatan("");
    }
} elseif ($_SESSION["level"] == 'Mentor') {
    if (!empty($nama)) {
        $sqlBase = CariKegiatan($nama, $tanggal_awal, $tanggal_akhir, $mentor_name);
    } else {
        $sqlBase = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, 
                        tbl_mahasiswa.universitas, tbl_kegiatan.id_kegiatan, 
                        tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
                        tbl_kegiatan.foto,
                        tbl_kegiatan.waktu_awal, 
                        tbl_kegiatan.waktu_akhir, 
                        DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari
                    FROM tbl_mahasiswa 
                    JOIN tbl_kegiatan ON tbl_mahasiswa.id_mahasiswa = tbl_kegiatan.id_mahasiswa 
                    WHERE tbl_mahasiswa.mentor = '$mentor_name'
                    ORDER BY tbl_kegiatan.tanggal DESC";
    }
} else {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}

// hitung total data
$countQuery = "SELECT COUNT(*) as total FROM ($sqlBase) AS totalData";
$countResult = mysqli_query($kon, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// ambil data sesuai halaman
$sql = $sqlBase . " LIMIT $limit OFFSET $offset";
$hasil = mysqli_query($kon, $sql);
$no = $offset;
?>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Kegiatan</li>
    </ol>
</div><!--/.row-->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Kegiatan
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_kegiatan" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Nama Karyawan Magang :</label>
                                <input type="text" name="nama" id="nama" class="form-control" value=""
                                    placeholder="Cari Mahasiswa" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Awal :</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Akhir :</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                </br>
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
                if (isset($_GET['tambah'])) {
                    if ($_GET['tambah'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Menambahkan Kegiatan Harian');</script>";
                    } else if ($_GET['tambah'] == 'gagal' && isset($_GET['pesan'])) {
                        echo "<script>showAlert('error', 'Gagal!', '" . $_GET['pesan'] . "');</script>";
                    }
                }

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Mengubah Kegiatan Harian');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Mengubah Kegiatan Harian');</script>";
                    } else if ($_GET['edit'] == 'format_error') {
                        echo "<script>showAlert('error', 'Gagal!', 'Format file harus JPG, JPEG, atau PNG');</script>";
                    } else if ($_GET['edit'] == 'size_error') {
                        echo "<script>showAlert('error', 'Gagal!', 'File tidak boleh melebihi 1MB');</script>";
                    }
                }

                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Menghapus Kegiatan Harian');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Menghapus Kegiatan Harian');</script>";
                    }
                }
                ?>

                <div class="form-group filter">
                    <?php if ($_SESSION["level"] == 'Admin'): ?>
                        <button type="button" class="btn btn-success" id="tambah_kegiatan">
                            <i class="tambah_kegiatan fa fa-plus"></i> Tambah
                        </button>
                    <?php endif; ?>
                    <select id="dataFilter" class="form-control" onchange="changeLimit(this.value)">
                        <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="75" <?= $limit == 75 ? 'selected' : '' ?>>75</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama</th>
                                <th class="text-center">Hari</th>
                                <th class="text-center" width="150">Tanggal</th>
                                <th class="text-center" width="120">Jam</th>
                                <th class="text-center" width="270">Kegiatan</th>
                                <th class="text-center" width="250">Foto Kegiatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($data = mysqli_fetch_array($hasil)):
                                $no++; ?>
                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= $data['nama']; ?></td>
                                    <td class="text-center"><?= MendapatkanHari($data["hari"]); ?></td>
                                    <td class="text-center">
                                        <?php
                                        $tgl = date("d", strtotime($data['tanggal']));
                                        $bulan = date("m", strtotime($data['tanggal']));
                                        $tahun = date("Y", strtotime($data['tanggal']));
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (!empty($data['waktu_awal']) && !empty($data['waktu_akhir'])) {
                                            $wAwal = preg_split('/\s*,\s*/', trim($data['waktu_awal']));
                                            $wAkhir = preg_split('/\s*,\s*/', trim($data['waktu_akhir']));
                                            $display = [];
                                            foreach ($wAwal as $i => $wa) {
                                                $display[] = substr($wa, 0, 5) . ' - ' . substr($wAkhir[$i], 0, 5);
                                            }
                                            echo implode(', ', $display);
                                        } else {
                                            echo "-";
                                        }
                                        ?>
                                    </td>
                                    <td><?= $data['kegiatan']; ?></td>
                                    <td class="text-center">
                                        <?php
                                        $fotos = explode(', ', $data['foto']);
                                        if (empty($fotos[0])) {
                                            echo "<img src='apps/data_kegiatan/foto_kegiatan/gambar_default/No_gambar.jpg' width='130' height='130' style='object-fit:cover;'>";
                                        } else {
                                            foreach ($fotos as $foto) {
                                                echo "<div style='display:block; width: 100%; margin:5px; position:relative;'>";
                                                if ($foto != "foto_default.png") {
                                                    echo "<a href='#' class='btn-show-foto' data-src='apps/data_kegiatan/foto_kegiatan/$foto' 
                                                    style='position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                                                    background:#fff;padding:2px 5px;border-radius:5px;font-size:12px;z-index:1;'>Lihat Foto</a>";
                                                }
                                                echo "<img src='apps/data_kegiatan/foto_kegiatan/$foto' width='130' height='130' style='object-fit:cover;'>";
                                                echo "</div>";
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION["level"] == 'Admin'): ?>
                                            <button style="margin-bottom: 5px;" id_mahasiswa="<?= $data['id_mahasiswa']; ?>"
                                                id_kegiatan="<?= $data['id_kegiatan']; ?>"
                                                class="ubah_kegiatan btn btn-warning"><i class="fa fa-edit"></i></button>
                                        <?php endif; ?>
                                        <button style="margin-bottom: 5px;" id_mahasiswa="<?= $data['id_mahasiswa']; ?>"
                                            class="cetak_kegiatan btn btn-primary"><i class="fa fa-print"></i></button>
                                        <?php if ($_SESSION["level"] == 'Admin'): ?>
                                            <a style="margin-bottom: 5px;"
                                                href="apps/data_kegiatan/hapus.php?id_kegiatan=<?= $data['id_kegiatan']; ?>"
                                                class="btn-hapus-kegiatan btn btn-danger"><i class="fa fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" style="display: flex; justify-content: center;">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li>
                                <a
                                    href="?page=data_kegiatan&halaman=<?= $page - 1; ?>&limit=<?= $limit ?>&nama=<?= urlencode($nama); ?>&tanggal_awal=<?= $tanggal_awal; ?>&tanggal_akhir=<?= $tanggal_akhir; ?>">«
                                    Prev</a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);

                        if ($start > 1) {
                            echo "<li><a href='?page=data_kegiatan&halaman=1&limit=$limit&nama=" . urlencode($nama) . "&tanggal_awal=$tanggal_awal&tanggal_akhir=$tanggal_akhir'>1</a></li>";
                            if ($start > 2)
                                echo "<li class='disabled'><span>...</span></li>";
                        }

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $page) ? "class='active'" : "";
                            echo "<li $active><a href='?page=data_kegiatan&halaman=$i&limit=$limit&nama=" . urlencode($nama) . "&tanggal_awal=$tanggal_awal&tanggal_akhir=$tanggal_akhir'>$i</a></li>";
                        }

                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1)
                                echo "<li class='disabled'><span>...</span></li>";
                            echo "<li><a href='?page=data_kegiatan&halaman=$totalPages&limit=$limit&nama=" . urlencode($nama) . "&tanggal_awal=$tanggal_awal&tanggal_akhir=$tanggal_akhir'>$totalPages</a></li>";
                        }
                        ?>

                        <?php if ($page < $totalPages): ?>
                            <li>
                                <a
                                    href="?page=data_kegiatan&halaman=<?= $page + 1; ?>&limit=<?= $limit ?>&nama=<?= urlencode($nama); ?>&tanggal_awal=<?= $tanggal_awal; ?>&tanggal_akhir=<?= $tanggal_akhir; ?>">Next
                                    »</a>
                            </li>
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
                    style="width: 400px; height: 400px; object-fit: cover; border: 4px solid rgb(13, 10, 44);">
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

<script>
    // Tambah kegiatan dari admin
    $('#tambah_kegiatan').on('click', function () {
        $.ajax({
            url: 'apps/data_kegiatan/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Kegiatan';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Mengubah kegiatan dari admin
    $('.ubah_kegiatan').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        var id_kegiatan = $(this).attr("id_kegiatan");
        $.ajax({
            url: 'apps/data_kegiatan/edit.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa,
                id_kegiatan: id_kegiatan
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Kegiatan';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-kegiatan').forEach(button => {
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
    // cetak absensi
    $('.cetak_kegiatan').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_kegiatan/cetak.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Cetak Kegiatan';
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

<script>
    function changeLimit(val) {
        const url = new URL(window.location.href);
        url.searchParams.set("limit", val);
        url.searchParams.set("halaman", 1); // reset ke halaman 1 biar gak out of range
        window.location.href = url.toString();
    }
</script>