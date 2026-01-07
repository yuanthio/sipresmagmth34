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
            // Menghapus parameter URL setelah menampilkan alert
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('tambah');
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
    }

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
// Mengimpor file konfigurasi database
include 'config/database.php';
include "config/function.php";

// Mendapatkan ID mahasiswa dari sesi yang telah disimpan
$id_mahasiswa = $_SESSION["id_mahasiswa"];
$sql = "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa = $id_mahasiswa LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

$mulai_magang = $data['mulai_magang'];
$akhir_magang = $data['akhir_magang'];
setlocale(LC_TIME, 'id_ID');

$tanggal_masuk = "";
if ($mulai_magang) {
    $tanggal_masuk = date("%d %B %Y", strtotime($mulai_magang));
}

$tanggal_keluar = "";
if ($akhir_magang) {
    $tanggal_keluar = date("%d %B %Y", strtotime($akhir_magang));
}

// Mendapatkan status dari tabel tbl_absensi untuk hari ini
$queryStatus = "SELECT status FROM tbl_absensi WHERE id_mahasiswa = $id_mahasiswa AND tanggal = CURDATE()";
$resultStatus = mysqli_query($kon, $queryStatus);
$dataStatus = mysqli_fetch_assoc($resultStatus);

$statusHariIni = null;
if ($dataStatus && isset($dataStatus['status'])) {
    $statusHariIni = $dataStatus['status'];
}

?>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Kegiatan Harian</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12" style="margin-top: 20px;">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Kegiatan Harian
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <?php
                $tanggal_sekarang = date("Y-m-d");
                date_default_timezone_set("Asia/Jakarta");

                if ($tanggal_sekarang > $akhir_magang) {
                    echo '<div id="div_periode" class="alert alert-danger"><strong>Periode Kegiatan Harian Selesai <i class="fa-solid fa-triangle-exclamation"></i></strong></div>';
                } else {
                    $tanggal_sekarang_dt = new DateTime();
                    $hari_sekarang = $tanggal_sekarang_dt->format('l');

                    $days_mapping = [
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        'Sunday' => 'Minggu'
                    ];
                    $hari_sekarang_id = $days_mapping[$hari_sekarang];

                    $query_hari_libur = "SELECT status FROM tbl_hari_libur WHERE hari = '$hari_sekarang_id'";
                    $result_hari_libur = mysqli_query($kon, $query_hari_libur);
                    $data_hari_libur = mysqli_fetch_assoc($result_hari_libur);
                    $status_hari_libur = isset($data_hari_libur['status']) ? $data_hari_libur['status'] : 'Masuk';

                    // Cek apakah tanggal hari ini termasuk rentang libur
                    $query_tanggal_libur = "SELECT * FROM tbl_tanggal_libur WHERE '$tanggal_sekarang' BETWEEN tanggal_awal AND tanggal_akhir";
                    $result_tanggal_libur = mysqli_query($kon, $query_tanggal_libur);
                    $is_tanggal_libur = mysqli_num_rows($result_tanggal_libur) > 0;

                    if ($is_tanggal_libur) {
                        $row_libur = mysqli_fetch_assoc($result_tanggal_libur);
                        $tanggal_awal = strtotime($row_libur['tanggal_awal']);
                        $tanggal_akhir = strtotime($row_libur['tanggal_akhir']);

                        $tanggal_awal_format = date('d', $tanggal_awal) . ' ' . MendapatkanBulan(date('n', $tanggal_awal)) . ' ' . date('Y', $tanggal_awal);
                        $tanggal_akhir_format = date('d', $tanggal_akhir) . ' ' . MendapatkanBulan(date('n', $tanggal_akhir)) . ' ' . date('Y', $tanggal_akhir);

                        $alasan_libur = !empty($row_libur['alasan_libur']) ? $row_libur['alasan_libur'] : 'tanpa keterangan';

                        echo '<div id="div_libur" class="alert alert-warning">
                                <strong>Presensi harian libur mulai tanggal ' . $tanggal_awal_format . ' sampai dengan tanggal ' . $tanggal_akhir_format . ' dikarenakan ' . $alasan_libur . ' <i class="fa-solid fa-mug-hot"></i></strong>
                            </div>';
                    } elseif ($status_hari_libur == 'Libur') {
                        echo '<div id="div_libur" class="alert alert-warning"><strong>Presensi Harian Libur <i class="fa-solid fa-mug-hot"></i></strong></div>';
                    } else {
                        // Cek presensi hari ini
                        $query_presensi = "SELECT * FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = CURDATE()";
                        $result_presensi = mysqli_query($kon, $query_presensi);

                        if (mysqli_num_rows($result_presensi) == 0) {
                            echo "<script>
                                Swal.fire({
                                    icon: 'warning',
                                    html: '<h2 style=\"font-size: 2.5em; font-weight: bold;\">Peringatan!</h2><p style=\"font-size: 1.5em;\">Anda belum presensi, kegiatan harian tidak bisa dilakukan</p>',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                        setTimeout(() => {
                                            window.location.href = 'index.php?page=absen';
                                        }, 3000);
                                    }
                                });
                            </script>";
                        }
                    }
                }
                ?>
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="kegiatan" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Awal:</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Akhir:</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <br>
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
                if (isset($_GET['tambah'])) {
                    if ($_GET['tambah'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Menambahkan Kegiatan Harian');</script>";
                    } else if ($_GET['tambah'] == 'gagal_ukuran') {
                        echo "<script>showAlert('error', 'Gagal!', 'File tidak boleh melebihi 1MB');</script>";
                    } else if ($_GET['tambah'] == 'gagal_format') {
                        echo "<script>showAlert('error', 'Gagal!', 'Format file harus JPG, JPEG, atau PNG');</script>";
                    } else {
                        echo "<script>showAlert('warning', 'Sudah!', 'Menambahkan Kegiatan Harian');</script>";
                    }
                }
                ?>

                <?php
                include 'config/database.php';
                date_default_timezone_set("Asia/Jakarta");

                $tanggal_sekarang = date("Y-m-d");
                $hari_sekarang = date("l", strtotime($tanggal_sekarang));

                // Mapping hari
                $days_mapping = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu'
                ];
                $hari_sekarang_id = $days_mapping[$hari_sekarang];

                // --- Default false supaya tidak undefined
                $is_tanggal_libur = false;

                // Cek hari libur mingguan
                $query_hari_libur = "SELECT status FROM tbl_hari_libur WHERE hari = '$hari_sekarang_id'";
                $result_hari_libur = mysqli_query($kon, $query_hari_libur);
                $data_hari_libur = mysqli_fetch_assoc($result_hari_libur);
                $status_hari_libur = isset($data_hari_libur['status']) ? $data_hari_libur['status'] : 'Masuk';

                // Cek tanggal libur rentang
                $query_tanggal_libur = "SELECT * FROM tbl_tanggal_libur WHERE '$tanggal_sekarang' BETWEEN tanggal_awal AND tanggal_akhir";
                $result_tanggal_libur = mysqli_query($kon, $query_tanggal_libur);
                if (mysqli_num_rows($result_tanggal_libur) > 0) {
                    $is_tanggal_libur = true;
                }
                ?>

                <?php
                include 'config/database.php';
                date_default_timezone_set("Asia/Jakarta");

                $id_mahasiswa = $_SESSION["id_mahasiswa"];

                // --- Pagination setup
                $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
                if (!in_array($limit, [25, 50, 75, 100])) {
                    $limit = 25; // fallback
                }

                $page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
                if ($page < 1)
                    $page = 1;
                $offset = ($page - 1) * $limit;

                // --- Query total data
                $sql_count = "SELECT COUNT(*) as total FROM tbl_kegiatan WHERE id_mahasiswa = $id_mahasiswa";
                if (isset($_GET['tanggal_awal']) && isset($_GET['tanggal_akhir'])) {
                    $tanggal_awal = $_GET["tanggal_awal"];
                    $tanggal_akhir = $_GET["tanggal_akhir"];
                    $sql_count .= " AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
                }
                $result_count = mysqli_query($kon, $sql_count);
                $row_count = mysqli_fetch_assoc($result_count);
                $total_data = $row_count['total'];
                $total_halaman = ceil($total_data / $limit);

                // --- Query data kegiatan
                $sql = "SELECT id_kegiatan, hari, tanggal, waktu_awal, waktu_akhir, kegiatan, foto 
                FROM tbl_kegiatan 
                WHERE id_mahasiswa = $id_mahasiswa";

                if (isset($_GET['tanggal_awal']) && isset($_GET['tanggal_akhir'])) {
                    $tanggal_awal = $_GET["tanggal_awal"];
                    $tanggal_akhir = $_GET["tanggal_akhir"];
                    $sql .= " AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
                }

                $sql .= " ORDER BY tanggal DESC LIMIT $limit OFFSET $offset";
                $hasil = mysqli_query($kon, $sql);
                ?>

                <div class="form-group filter">
                    <?php
                    // Cek apakah hari ini adalah hari libur
                    if ($status_hari_libur == 'Libur' || $is_tanggal_libur) {
                        echo '<button class="btn btn-success" disabled><i class="fa fa-plus"></i> Tambah</button>';
                    } else {
                        // Cek apakah masih dalam periode magang
                        if ($tanggal_sekarang >= $mulai_magang && $tanggal_sekarang <= $akhir_magang) {
                            echo '<button id_mahasiswa="' . $id_mahasiswa . '" id="tombol_kegiatan" class="btn btn-success"><i class="fa fa-plus"></i> Tambah</button>';
                        } else {
                            echo '<button class="btn btn-success" disabled><i class="fa fa-plus"></i> Tambah</button>';
                        }
                    }
                    ?>
                    <button id_mahasiswa="<?php echo $_SESSION['id_mahasiswa']; ?>"
                        class="cetak_kegiatan btn btn-primary btn-circle" id="cetak_kegiatan"><i
                            class="fa fa-print"></i> Cetak</button>
                    <select id="dataFilter" class="form-control" onchange="changeLimit(this.value)">
                        <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="75" <?= $limit == 75 ? 'selected' : '' ?>>75</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead style="z-index: 2;">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Hari</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center" width="120px">Jam</th>
                                <th class="text-center" width="500px">Kegiatan</th>
                                <th class="text-center" width="250px">Foto Kegiatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($hasil) == 0) {
                                echo '<tr><td colspan="6" class="text-center">Data masih kosong</td></tr>';
                            } else {
                                $no = $offset;
                                while ($data = mysqli_fetch_array($hasil)):
                                    $no++;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no; ?></td>
                                        <td class="text-center"><?php echo MendapatkanHari($data['hari']); ?></td>
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
                                            $waktu_awal_array = explode(", ", $data['waktu_awal']);
                                            $waktu_akhir_array = explode(", ", $data['waktu_akhir']);
                                            $jam = [];
                                            for ($i = 0; $i < count($waktu_awal_array); $i++) {
                                                $waktu_awal_formatted = date('H:i', strtotime($waktu_awal_array[$i]));
                                                $waktu_akhir_formatted = date('H:i', strtotime($waktu_akhir_array[$i]));
                                                $jam[] = $waktu_awal_formatted . ' - ' . $waktu_akhir_formatted;
                                            }
                                            echo implode(', ', $jam);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $string_kegiatan = $data['kegiatan'];
                                            $kegiatan_array = explode(", ", $string_kegiatan);
                                            echo implode(", ", $kegiatan_array);
                                            ?>
                                        </td>
                                        <td class="text-center" style="position: relative;">
                                            <?php
                                            $fotos = explode(', ', $data['foto']);
                                            if (empty($fotos[0])) {
                                                echo "<div style='width: 100%; text-align: center; margin-bottom: 10px; position: relative;'>";
                                                echo "<img src='apps/data_kegiatan/foto_kegiatan/gambar_default/No_gambar.jpg' alt='No Image' style='width: 130px; height: 130px; object-fit: cover;'>";
                                                echo "</div>";
                                            } else {
                                                foreach ($fotos as $foto) {
                                                    echo "<div style='width: 100%; text-align: center; margin-bottom: 10px; position: relative; display: block;'>";
                                                    if ($foto != 'foto_default.png') {
                                                        echo "<a href='#' class='btn-show-foto' data-src='apps/data_kegiatan/foto_kegiatan/$foto' 
                                            style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 4px 8px; border-radius: 5px; font-size: 12px; text-decoration: none; z-index: 1; color: black;'>Lihat Foto</a>";
                                                    }
                                                    echo "<img src='apps/data_kegiatan/foto_kegiatan/$foto' alt='$foto' style='width: 130px; height: 130px; object-fit: cover; display: block;'>";
                                                    echo "</div>";
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav style="display: flex; justify-content: center;">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li>
                                <a href="?page=kegiatan&halaman=<?= $page - 1; ?>&limit=<?= $limit; ?>">&laquo; Prev</a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $range = 2; // jumlah halaman di kiri dan kanan halaman aktif
                        $start = max(1, $page - $range);
                        $end = min($total_halaman, $page + $range);

                        // tampilkan halaman pertama
                        if ($start > 1) {
                            echo '<li><a href="?page=kegiatan&halaman=1&limit=' . $limit . '">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                        }

                        // tampilkan halaman di sekitar halaman aktif
                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="' . $active . '"><a href="?page=kegiatan&halaman=' . $i . '&limit=' . $limit . '">' . $i . '</a></li>';
                        }

                        // tampilkan halaman terakhir
                        if ($end < $total_halaman) {
                            if ($end < $total_halaman - 1) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                            echo '<li><a href="?page=kegiatan&halaman=' . $total_halaman . '&limit=' . $limit . '">' . $total_halaman . '</a></li>';
                        }
                        ?>

                        <?php if ($page < $total_halaman): ?>
                            <li>
                                <a href="?page=kegiatan&halaman=<?= $page + 1; ?>&limit=<?= $limit; ?>">Next &raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
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
                    style="width: 400px; height: 400px; object-fit: cover;">
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
                <div id="tampil_data"></div>
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
        var scale = 1;

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
            scale += 0.1;
            updateTransform();
        });

        // Zoom Out
        $('#zoom-out').on('click', function () {
            if (scale > 0.1) {
                scale -= 0.1;
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
    $('#tombol_kegiatan').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/pengguna/mulai_kegiatan.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa
            },
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
    $('#cetak_kegiatan').on('click', function () {
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
    $(document).ready(function () {
        var hari = new Date().getDay();
        var statusHariIni = <?php echo json_encode($statusHariIni); ?>;

        // Mengatur disabled berdasarkan status
        if (statusHariIni == 2) {
            $('#tombol_kegiatan').attr('disabled', true);
        }

        // pengecekan untuk status absensi
        if (statusHariIni === null || statusHariIni === undefined || statusHariIni === "null") {
            $('#tombol_kegiatan').attr('disabled', true);
            console.log("Tombol dinonaktifkan karena status absensi belum ada.");
        } else {
            console.log("Status absensi untuk hari ini: " + statusHariIni);
        }
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
    function changeLimit(limit) {
        const params = new URLSearchParams(window.location.search);
        params.set('limit', limit);
        params.set('halaman', 1);
        window.location.href = window.location.pathname + '?' + params.toString();
    }
</script>