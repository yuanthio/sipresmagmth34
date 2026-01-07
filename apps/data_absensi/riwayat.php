<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

<?php
// Mengatur timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Ambil data mahasiswa yang sedang login
$id_mahasiswa = $_SESSION["id_mahasiswa"];

// Query untuk mengambil data nama dari tbl_mahasiswa
$query_mahasiswa = "SELECT nama, kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
$result_mahasiswa = mysqli_query($kon, $query_mahasiswa);
$data_mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
$nama_mahasiswa = $data_mahasiswa['nama'];
$kode_mahasiswa = $data_mahasiswa['kode_mahasiswa'];

// Query untuk mengambil data kode_pengguna dari tbl_user
$query_user = "SELECT kode_pengguna, level FROM tbl_user WHERE kode_pengguna = '$kode_mahasiswa' AND level = 'Mahasiswa'";
$result_user = mysqli_query($kon, $query_user);
$data_user = mysqli_fetch_assoc($result_user);
$kode_pengguna = $data_user['kode_pengguna'];
$level = $data_user['level'];
$tanggal_sekarang = date('Y-m-d H:i:s');
$aktivitas = "Melihat riwayat presensi";
$status_log = (mysqli_num_rows($hasil) > 0) ? "berhasil" : "gagal";

// Masukkan log ke tabel tbl_log_aktivitas
$insert_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
               VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level', '$kode_pengguna', '$aktivitas', '$status_log')";

mysqli_query($kon, $insert_log);
?>

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

    .statistik thead,
    tbody {
        color: rgb(58, 58, 58);
    }

    .catatan {
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
        box-shadow: 0 4px 2px 5px rgba(0, 0, 0, .2);
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

    .progress-bar {
        min-width: 4%;
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

    .filter {
        display: flex;
        column-gap: 5px;
    }

    #dataFilter {
        width: 70px;
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

    @media (max-width: 576px) {
        .text-nilai {
            font-size: .9em;
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
        <li class="active">Riwayat Presensi</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12" style="margin-top: 20px;">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Riwayat Presensi
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="riwayat" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Awal :</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                                    id="dataTable" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Akhir :</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                                    id="dataTable" required>
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

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Statistik Kehadiran
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="table-responsive">
                    <table class="table table-bordered table-center statistik" style="min-width: 600px;"
                        cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center" width="100">No</th>
                                <th class="text-center" width="150">Status Kehadiran</th>
                                <th class="text-center" width="150">Jumlah Hari</th>
                                <th class="text-center" width="250">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';
                            $id_mahasiswa = $_SESSION["id_mahasiswa"];

                            $sql = "SELECT
                                COUNT(CASE WHEN tbl_absensi.status = 1 THEN 1 END) AS jumlah_hadir,
                                COUNT(CASE WHEN tbl_absensi.status = 2 THEN 1 END) AS jumlah_izin,
                                COUNT(CASE WHEN tbl_absensi.status = 3 THEN 1 END) AS jumlah_terlambat,
                                COUNT(CASE WHEN tbl_absensi.status = 4 THEN 1 END) AS jumlah_tidak_hadir,
                                COUNT(CASE WHEN tbl_absensi.status = 5 THEN 1 END) AS jumlah_wfa
                            FROM tbl_absensi
                            WHERE tbl_absensi.id_mahasiswa = '$id_mahasiswa'";

                            $hasil = mysqli_query($kon, $sql);

                            if (mysqli_num_rows($hasil) > 0) {
                                $data = mysqli_fetch_array($hasil);

                                $jumlahHadir = $data['jumlah_hadir'];
                                $jumlahIzin = $data['jumlah_izin'];
                                $jumlahTerlambat = $data['jumlah_terlambat'];
                                $jumlahTidakHadir = $data['jumlah_tidak_hadir'];
                                $jumlahWfa = $data['jumlah_wfa'];

                                $totalHari = $jumlahHadir + $jumlahIzin + $jumlahTerlambat + $jumlahTidakHadir + $jumlahWfa;

                                $persentaseHadir = ($totalHari > 0) ? round(($jumlahHadir / $totalHari) * 100) : 0;
                                $persentaseIzin = ($totalHari > 0) ? round(($jumlahIzin / $totalHari) * 100) : 0;
                                $persentaseTerlambat = ($totalHari > 0) ? round(($jumlahTerlambat / $totalHari) * 100) : 0;
                                $persentaseTidakHadir = ($totalHari > 0) ? round(($jumlahTidakHadir / $totalHari) * 100) : 0;
                                $persentaseWfa = ($totalHari > 0) ? round(($jumlahWfa / $totalHari) * 100) : 0;
                                ?>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td class="text-center"><span class="label label-success">Hadir</span></td>
                                    <td class="text-center"><?php echo $jumlahHadir; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar progress-bar-success" role="progressbar"
                                                style="width: <?php echo max($persentaseHadir, 4); ?>%;">
                                                <?php echo $persentaseHadir; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">2</td>
                                    <td class="text-center"><span class="label label-info">Izin</span></td>
                                    <td class="text-center"><?php echo $jumlahIzin; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar progress-bar-info" role="progressbar"
                                                style="width: <?php echo max($persentaseIzin, 4); ?>%;">
                                                <?php echo $persentaseIzin; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">3</td>
                                    <td class="text-center"><span class="label label-warning">Terlambat</span></td>
                                    <td class="text-center"><?php echo $jumlahTerlambat; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar progress-bar-warning" role="progressbar"
                                                style="width: <?php echo max($persentaseTerlambat, 4); ?>%;">
                                                <?php echo $persentaseTerlambat; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">4</td>
                                    <td class="text-center"><span class="label label-danger">Tidak Hadir</span></td>
                                    <td class="text-center"><?php echo $jumlahTidakHadir; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar progress-bar-danger" role="progressbar"
                                                style="width: <?php echo max($persentaseTidakHadir, 4); ?>%;">
                                                <?php echo $persentaseTidakHadir; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">5</td>
                                    <td class="text-center"><span class="label label-primary">WFA</span></td>
                                    <td class="text-center"><?php echo $jumlahWfa; ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar progress-bar-primary" role="progressbar"
                                                style="width: <?php echo max($persentaseWfa, 4); ?>%;">
                                                <?php echo $persentaseWfa; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            } else {
                                echo '<tr><td colspan="4" class="text-center">Data masih kosong</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
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
                include 'config/database.php';
                include 'config/function.php';
                
                date_default_timezone_set('Asia/Jakarta'); // WIB
                
                $id_mahasiswa = $_SESSION["id_mahasiswa"];
                
                $tanggal_awal  = isset($_GET['tanggal_awal'])  ? $_GET['tanggal_awal']  : '';
                $tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
                
                // --- Pagination setup
                $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25; // default 25
                if (!in_array($limit, [25, 50, 75, 100])) {
                    $limit = 25; // fallback aman
                }
                
                $page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit;
                
                $extraQS = '';
                if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
                    $extraQS = '&tanggal_awal=' . urlencode($tanggal_awal) . '&tanggal_akhir=' . urlencode($tanggal_akhir);
                }
                
                $where = "WHERE tbl_absensi.id_mahasiswa = '$id_mahasiswa'";
                if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
                    $where .= " AND tbl_absensi.tanggal >= '$tanggal_awal' AND tbl_absensi.tanggal <= '$tanggal_akhir'";
                }
                
                $sql_count = "
                    SELECT COUNT(*) AS total
                    FROM tbl_absensi
                    $where
                ";
                $result_count = mysqli_query($kon, $sql_count);
                $row_count = mysqli_fetch_assoc($result_count);
                $total_data = (int)$row_count['total'];
                $total_halaman = ($total_data > 0) ? (int)ceil($total_data / $limit) : 1;
                
                $sql_stat = "
                    SELECT
                      COUNT(*) AS jumlah_total,
                      SUM(
                        CASE
                          WHEN tbl_absensi.status = 1 AND IFNULL(tbl_kegiatan.kegiatan,' - ') <> ' - ' THEN 1
                          WHEN tbl_absensi.status = 2 AND (
                                IFNULL(tbl_alasan.alasan,' - ') <> ' - '
                             OR IFNULL(tbl_kegiatan.kegiatan,' - ') <> ' - '
                          ) THEN 1
                          WHEN tbl_absensi.status = 5 AND IFNULL(tbl_kegiatan.kegiatan,' - ') <> ' - ' THEN 1
                          ELSE 0
                        END
                      ) AS jumlah_hadir_terkonfirmasi
                    FROM tbl_absensi
                    LEFT JOIN tbl_alasan
                           ON tbl_absensi.tanggal = tbl_alasan.tanggal
                          AND tbl_absensi.id_mahasiswa = tbl_alasan.id_mahasiswa
                    LEFT JOIN tbl_kegiatan
                           ON tbl_absensi.tanggal = tbl_kegiatan.tanggal
                          AND tbl_absensi.id_mahasiswa = tbl_kegiatan.id_mahasiswa
                    $where
                ";
                $res_stat = mysqli_query($kon, $sql_stat);
                $stat = mysqli_fetch_assoc($res_stat);
                
                $jumlahTotal = (int)($stat['jumlah_total'] ?? 0);
                $jumlahKehadiran = (int)($stat['jumlah_hadir_terkonfirmasi'] ?? 0);
                
                $persentaseKehadiran = ($jumlahTotal > 0) ? round(($jumlahKehadiran / $jumlahTotal) * 100) : 0;
                
                if ($persentaseKehadiran >= 90) {
                    $nilaiKehadiran = 'Sangat Rajin';
                } elseif ($persentaseKehadiran >= 80) {
                    $nilaiKehadiran = 'Rajin';
                } elseif ($persentaseKehadiran >= 70) {
                    $nilaiKehadiran = 'Cukup Rajin';
                } elseif ($persentaseKehadiran >= 60) {
                    $nilaiKehadiran = 'Kurang Rajin';
                } else {
                    $nilaiKehadiran = 'Tidak Rajin';
                }
                
                // Update nilai_kehadiran jika belum dikonfirmasi
                $cekKonfirmasi = mysqli_query($kon, "SELECT konfirmasi_nilai FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");
                $dataKonfirmasi = mysqli_fetch_assoc($cekKonfirmasi);
                if ($dataKonfirmasi && $dataKonfirmasi['konfirmasi_nilai'] !== 'diubah') {
                    $updateNilaiKehadiran = "UPDATE tbl_mahasiswa SET nilai_kehadiran = '$persentaseKehadiran' WHERE id_mahasiswa = '$id_mahasiswa'";
                    mysqli_query($kon, $updateNilaiKehadiran);
                }
                
                $sql = "
                    SELECT
                        tbl_absensi.id_absensi,
                        tbl_absensi.id_mahasiswa,
                        tbl_alasan.id_alasan,
                        DAYNAME(tbl_absensi.tanggal) AS hari,
                        tbl_absensi.waktu,
                        tbl_absensi.tanggal,
                        IFNULL(tbl_alasan.alasan, ' - ') AS alasan,
                        IFNULL(tbl_alasan.foto, '') AS foto_alasan,
                        IFNULL(tbl_kegiatan.kegiatan, ' - ') AS kegiatan,
                        tbl_kegiatan.foto AS foto_kegiatan,
                        IFNULL(b.bukti_wfa, '') AS bukti_wfa,
                        (CASE
                            WHEN tbl_absensi.status = 1 THEN 'Hadir'
                            WHEN tbl_absensi.status = 2 THEN 'Izin'
                            WHEN tbl_absensi.status = 3 THEN 'Terlambat'
                            WHEN tbl_absensi.status = 4 THEN 'Tidak Hadir'
                            WHEN tbl_absensi.status = 5 THEN 'WFA'
                            ELSE 'Belum Absensi'
                        END) AS status
                    FROM tbl_absensi
                    LEFT JOIN tbl_alasan
                           ON tbl_absensi.tanggal = tbl_alasan.tanggal
                          AND tbl_absensi.id_mahasiswa = tbl_alasan.id_mahasiswa
                    LEFT JOIN tbl_kegiatan
                           ON tbl_absensi.tanggal = tbl_kegiatan.tanggal
                          AND tbl_absensi.id_mahasiswa = tbl_kegiatan.id_mahasiswa
                    LEFT JOIN tbl_bukti_wfa b
                           ON tbl_absensi.tanggal = b.tanggal
                          AND tbl_absensi.id_mahasiswa = b.id_mahasiswa
                    $where
                    ORDER BY tbl_absensi.tanggal DESC
                    LIMIT $limit OFFSET $offset
                ";
                $hasil = mysqli_query($kon, $sql);
                
                // Untuk nomor urut tabel
                $no = $offset;
                ?>
                
                <div class="form-group filter" style="display:flex; gap:8px; align-items:center;">
                    <!-- Tombol untuk mencetak absensi -->
                    <button id_mahasiswa="<?php echo $_SESSION['id_mahasiswa']; ?>" type="button"
                            class="cetak btn btn-primary" id="cetak">
                        <i class="fa fa-print"></i> Cetak
                    </button>
                
                    <!-- Pilihan jumlah data per halaman -->
                    <select id="dataFilter" class="form-control" style="width:120px;" onchange="changeLimit(this.value)">
                        <option value="25"  <?= $limit == 25  ? 'selected' : '' ?>>25</option>
                        <option value="50"  <?= $limit == 50  ? 'selected' : '' ?>>50</option>
                        <option value="75"  <?= $limit == 75  ? 'selected' : '' ?>>75</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-center" id="dataTable" width="100%" cellspacing="0">
                        <thead style="z-index: 2;">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Hari</th>
                                <th class="text-center" width="150">Tanggal</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-center">Kehadiran</th>
                                <th class="text-center" width="250">Kegiatan</th>
                                <th class="text-center" width="250">Keterangan</th>
                                <th class="text-center" width="250">Foto</th>
                                <th class="text-center" width="70">aksi</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($hasil && mysqli_num_rows($hasil) > 0) {
                            while ($data = mysqli_fetch_array($hasil)) {
                                $no++;
                                $status_text = $data['status'];
                
                                // Label status
                                $label = '-';
                                switch ($status_text) {
                                    case 'Hadir':        $label = '<span class="label label-success">Hadir</span>'; break;
                                    case 'Izin':         $label = '<span class="label label-info">Izin</span>'; break;
                                    case 'Terlambat':    $label = '<span class="label label-warning">Terlambat</span>'; break;
                                    case 'Tidak Hadir':  $label = '<span class="label label-danger">Tidak Hadir</span>'; break;
                                    case 'WFA':          $label = '<span class="label label-primary">WFA</span>'; break;
                                }
                
                                // Tentukan konfirmasi_status (✓/X) sesuai logic awal
                                $konfirmasi_status = '';
                                if ($status_text === 'Hadir' && $data['kegiatan'] === ' - ') {
                                    $konfirmasi_status = 'X';
                                } elseif ($status_text === 'Hadir' && $data['kegiatan'] !== ' - ') {
                                    $konfirmasi_status = '✓';
                                } elseif ($status_text === 'Izin' && $data['kegiatan'] !== ' - ') {
                                    $konfirmasi_status = '✓';
                                } elseif ($status_text === 'Izin' && $data['alasan'] !== ' - ') {
                                    $konfirmasi_status = '✓';
                                } elseif ($status_text === 'Tidak Hadir') {
                                    $konfirmasi_status = 'X';
                                } elseif ($status_text === 'Terlambat') {
                                    $konfirmasi_status = 'X';
                                } elseif ($status_text === 'WFA' && $data['kegiatan'] !== ' - ') {
                                    $konfirmasi_status = '✓';
                                } elseif ($status_text === 'WFA' && $data['kegiatan'] === ' - ') {
                                    $konfirmasi_status = 'X';
                                }
                
                                // Simpan ke DB (seperti kode awal)
                                $id_absensi = (int)$data['id_absensi'];
                                $updateSql = "UPDATE tbl_absensi SET konfirmasi_status = '$konfirmasi_status' WHERE id_absensi = $id_absensi";
                                mysqli_query($kon, $updateSql);
                
                                // Waktu
                                $waktu = ($data['waktu'] === '00:00:00') ? '-' : $data['waktu'];
                
                                // ===== persiapan data foto / bukti wfa =====
                                $fotoPath = '';
                                $fotos = [];
                                if ($status_text === 'Izin') {
                                    $fotoPath = 'apps/pengguna/bukti_alasan/';
                                    $fotos = $data['foto_alasan'] !== '' ? explode(', ', $data['foto_alasan']) : [];
                                } else {
                                    $fotoPath = 'apps/data_kegiatan/foto_kegiatan/';
                                    $fotos = $data['foto_kegiatan'] !== '' ? explode(', ', $data['foto_kegiatan']) : [];
                                }
                
                                // Bukti WFA
                                $bukti_wfa = isset($data['bukti_wfa']) ? $data['bukti_wfa'] : '';
                                $wfa_file_path = $bukti_wfa ? 'apps/data_absensi/file_wfa/' . $bukti_wfa : '';
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no; ?></td>
                                    <td class="text-center">
                                        <?php
                                        $hari = $data['hari'];
                                        echo MendapatkanHari($hari);
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $tgl = date("d", strtotime($data['tanggal']));
                                        $bulan = date("m", strtotime($data['tanggal']));
                                        $tahun = date("Y", strtotime($data['tanggal']));
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                                        ?>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($waktu); ?></td>
                                    <td class="text-center"><?php echo $label; ?></td>
                                    <td><?php echo htmlspecialchars($data['kegiatan']); ?></td>
                
                                    <!-- Keterangan Izin: jika WFA tampilkan file (pdf/doc/docx), selain itu tampilkan alasan -->
                                    <td>
                                        <?php
                                        if ($status_text === 'WFA') {
                                            if (!empty($bukti_wfa)) {
                                                $ext = strtolower(pathinfo($bukti_wfa, PATHINFO_EXTENSION));
                                                // Tampilkan hanya pdf/doc/docx; untuk jpg/png/jpeg tidak ditampilkan
                                                if (in_array($ext, ['pdf', 'doc', 'docx'])) {
                                                    $icon = '';
                                                    if ($ext === 'pdf')  $icon = 'apps/data_absensi/extensi_file/pdf.png';
                                                    if ($ext === 'doc')  $icon = 'apps/data_absensi/extensi_file/doc.png';
                                                    if ($ext === 'docx') $icon = 'apps/data_absensi/extensi_file/docx.png';
                
                                                    if ($wfa_file_path && file_exists($wfa_file_path)) {
                                                        $safeName = htmlspecialchars($bukti_wfa);
                                                        echo '<div style="display:flex; align-items:center;">';
                                                        echo '<a href="apps/data_absensi/download_bukti_wfa_mhs.php?file=' . urlencode($bukti_wfa) . '" class="text-decoration-none" style="display:flex; align-items:center;">';
                                                        if ($icon) {
                                                            echo '<img src="' . $icon . '" width="20" alt="' . $ext . '" style="margin-right:6px;">';
                                                        }
                                                        echo $safeName;
                                                        echo '</a>';
                                                        echo '</div>';
                                                    } else {
                                                        echo '<div class="text-center"><span class="text-danger">File tidak ditemukan</span></div>';
                                                    }
                                                } else {
                                                    // kalau file WFA berupa gambar: sesuai permintaan, jangan tampilkan -> tampil '-'
                                                    echo '<div class="text-center">-</div>';
                                                }
                                            } else {
                                                echo '<div class="text-center">-</div>';
                                            }
                                        } else {
                                            // Untuk status selain WFA, tunjukkan alasan jika ada
                                            echo ($data['alasan'] !== ' - ')
                                                ? htmlspecialchars($data['alasan'])
                                                : '<div class="text-center">-</div>';
                                        }
                                        ?>
                                    </td>
                
                                    <!-- Foto kolom (izin/kegiatan) -->
                                    <td class="text-center" style="position: relative;">
                                        <?php
                                        if (empty($fotos) || (count($fotos) === 1 && $fotos[0] === '')) {
                                            echo "<div style='width: 100%; text-align: center; margin-bottom: 10px; position: relative;'>";
                                            echo "<img src='apps/data_kegiatan/foto_kegiatan/gambar_default/No_gambar.jpg' alt='No Image' style='width: 130px; height: 130px; object-fit: cover;'>";
                                            echo "</div>";
                                        } else {
                                            foreach ($fotos as $foto) {
                                                if (empty($foto)) continue;
                                                echo "<div style='width: 100%; text-align: center; margin-bottom: 10px; position: relative; display: block;'>";
                                                if ($foto != "foto_default.png") {
                                                    echo "<a href='#' class='btn-show-foto' data-src='{$fotoPath}{$foto}' style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1; text-decoration: none; font-size: 12px; padding: 4px 8px; border-radius: 5px; background-color: #fff; color: black;'>Lihat Foto</a>";
                                                }
                                                echo "<img src='{$fotoPath}{$foto}' alt='".htmlspecialchars($foto, ENT_QUOTES)."' style='width: 130px; height: 130px; object-fit: cover; display: block;'>";
                                                echo "</div>";
                                            }
                                        }
                                        ?>
                                    </td>
                
                                    <td class="text-center">
                                        <button style="margin-bottom: 5px;" id_absensi="<?php echo $data['id_absensi']; ?>"
                                                class="cek-lokasi btn btn-info btn-circle" title="Cek Lokasi">
                                            <i class="fa fa-map-marker"></i>
                                        </button>
                                        <button style="margin-bottom: 5px;" id_absensi="<?php echo $data['id_absensi']; ?>"
                                                class="cek-foto btn btn-success btn-circle" title="Cek Foto">
                                            <i class="bi bi-camera-fill"></i>
                                        </button>
                                    </td>
                
                                    <td class="text-center">
                                        <?php
                                        if ($status_text === 'Hadir' && $data['kegiatan'] === ' - ') {
                                            echo '<span class="label label-danger">X</span>';
                                        } elseif ($status_text === 'Hadir' && $data['kegiatan'] !== ' - ') {
                                            echo '<span class="label label-info">✓</span>';
                                        } elseif ($status_text === 'Izin' && $data['kegiatan'] !== ' - ') {
                                            echo '<span class="label label-info">✓</span>';
                                        } elseif ($status_text === 'Izin' && $data['alasan'] !== ' - ') {
                                            echo '<span class="label label-info">✓</span>';
                                        } elseif ($status_text === 'Tidak Hadir') {
                                            echo '<span class="label label-danger">X</span>';
                                        } elseif ($status_text === 'Terlambat') {
                                            echo '<span class="label label-danger">X</span>';
                                        } elseif ($status_text === 'WFA' && $data['kegiatan'] !== ' - ') {
                                            echo '<span class="label label-info">✓</span>';
                                        } elseif ($status_text === 'WFA' && $data['kegiatan'] === ' - ') {
                                            echo '<span class="label label-danger">X</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            } // end while
                        } else {
                            echo '<tr><td colspan="10" class="text-center">Data masih kosong</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav style="display:flex; justify-content:center;">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li>
                                <a href="?page=riwayat&halaman=<?= $page - 1; ?>&limit=<?= $limit ?>">&laquo; Prev</a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $range = 2; // jumlah halaman kiri-kanan dari halaman aktif
                        $start = max(1, $page - $range);
                        $end = min($total_halaman, $page + $range);

                        // tampilkan halaman pertama
                        if ($start > 1) {
                            echo '<li><a href="?page=riwayat&halaman=1&limit=' . $limit . '">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                        }

                        // tampilkan halaman di sekitar halaman aktif
                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="' . $active . '"><a href="?page=riwayat&halaman=' . $i . '&limit=' . $limit . '">' . $i . '</a></li>';
                        }

                        // tampilkan halaman terakhir
                        if ($end < $total_halaman) {
                            if ($end < $total_halaman - 1) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                            echo '<li><a href="?page=riwayat&halaman=' . $total_halaman . '&limit=' . $limit . '">' . $total_halaman . '</a></li>';
                        }
                        ?>

                        <?php if ($page < $total_halaman): ?>
                            <li>
                                <a href="?page=riwayat&halaman=<?= $page + 1; ?>&limit=<?= $limit ?>">Next &raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="row catatan"
                    style="position: sticky; bottom: 0; background-color: rgb(24, 18, 92); padding-top: 10px; z-index: 2;">
                    <div class="col-lg-6">
                        <div class="form-group" style="background-color: white; padding: 10px;">
                            <div class="nilai"
                                style="color: white; font-size: 1.2em; background-color: rgb(13, 10, 44); padding: 10px;">
                                <span class="text-nilai">
                                    Nilai Kedisiplinan anda saat ini : <?php echo $persentaseKehadiran; ?>%
                                </span> <br>
                                <span class="text-nilai">Keterangan Nilai : </span>
                                <?php
                                $keteranganNilai = '';

                                // Menentukan keterangan nilai berdasarkan kategori nilai
                                switch ($nilaiKehadiran) {
                                    case 'Sangat Rajin':
                                        $keteranganNilai = '<span style="color: white;">A (Sangat Rajin)</span>';
                                        break;
                                    case 'Rajin':
                                        $keteranganNilai = '<span style="color: white;">B (Rajin)</span>';
                                        break;
                                    case 'Cukup Rajin':
                                        $keteranganNilai = '<span style="color: white;">C (Cukup Rajin)</span>';
                                        break;
                                    case 'Kurang Rajin':
                                        $keteranganNilai = '<span style="color: white;">D (Kurang Rajin)</span>';
                                        break;
                                    case 'Tidak Rajin':
                                        $keteranganNilai = '<span style="color: white;">E (Tidak Rajin)</span>';
                                        break;
                                    default:
                                        $keteranganNilai = '<span style="color: white;">Tidak Diketahui</span>';
                                }
                                echo $keteranganNilai;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group" style="background-color: white; padding: 10px;">
                            <div class="note"
                                style="font-size: 1.2em; background-color: rgb(13, 10, 44); padding: 10px;">
                                <span class="text-nilai" style="color: white;">Catatan :</span><br>
                                <span class="text-nilai" style="color: white; font-weight:bold;"><i>Nilai
                                        berdasarkan
                                        dari kehadiran dan kegiatan harian anda!</i></span>
                            </div>
                        </div>
                    </div>
                </div>
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
                <button id="zoom-out" class="btn btn-warning"><i class="fa fa-search-minus"></i> Zoom
                    Out</button>
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
                    <!-- Data akan di load menggunakan AJAX -->
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
    // Cek Lokasi - Ambil Latitude dan Longitude
    $('.cek-lokasi').on('click', function () {
        var id_absensi = $(this).attr("id_absensi");

        $.ajax({
            url: 'apps/data_absensi/cek_lokasi_mhs.php',  // Buat file cek_lokasi.php untuk mengambil data lokasi
            method: 'POST',
            data: { id_absensi: id_absensi },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Lokasi Presensi';
            }
        });

        // Membuka modal
        $('#modal').modal('show');
    });

    // Setelah modal terbuka sepenuhnya, inisialisasi peta
    $('#modal').on('shown.bs.modal', function () {
        // Periksa apakah peta sudah ada
        if (typeof map === "undefined") {
            return; // Peta belum diinisialisasi
        }

        // Memastikan peta diperbarui setelah modal muncul
        map.invalidateSize(); // Memastikan peta dirender ulang setelah modal ditampilkan
    });
</script>

<script>
    $('.cek-foto').on('click', function () {
        var id_absensi = $(this).attr("id_absensi");

        $.ajax({
            url: 'apps/data_absensi/cek_foto_mhs.php',
            method: 'POST',
            data: { id_absensi: id_absensi },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Foto Presensi';
            }
        });

        // Membuka modal
        $('#modal').modal('show');
    });
</script>

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
    // Setting absensi
    $('.cetak').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_absensi/cetak.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Cetak Absensi';
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
        url.searchParams.set("limit", val); // ganti/isi parameter limit
        url.searchParams.set("halaman", 1); // reset ke halaman 1 tiap kali ganti limit
        window.location.href = url.toString();
    }
</script>