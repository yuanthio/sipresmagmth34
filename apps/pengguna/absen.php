<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<?php
// Memeriksa level pengguna. Jika bukan Mahasiswa, tampilkan pesan dan keluar.
if ($_SESSION["level"] != 'Mahasiswa' and $_SESSION["level"] != 'mahasiswa') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
?>

<style>
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
                url.searchParams.delete('mulai');
                url.searchParams.delete('error');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<?php
// Mengambil data pengguna dari sesi login
include 'config/database.php';
include 'config/function.php';
$id_mahasiswa = $_SESSION["id_mahasiswa"];
date_default_timezone_set("Asia/Jakarta");

// Mengambil informasi mahasiswa dari database
$sql = "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa = $id_mahasiswa LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

// Menyimpan data mahasiswa ke variabel
$nama = $data['nama'];
$universitas = $data['universitas'];
$nim = $data['nim'];
$mulai_magang = $data['mulai_magang'];
$akhir_magang = $data['akhir_magang'];
$foto = $data['foto'];

// Mengubah format tanggal ke dalam bahasa Indonesia
setlocale(LC_TIME, 'id_ID');
$tanggal_sekarang = new DateTime();
$tanggal_masuk = date("%d %B %Y", strtotime($mulai_magang));
$tanggal_keluar = date("%d %B %Y", strtotime($akhir_magang));
?>

<?php
// Mengambil data pengaturan absensi
include 'config/database.php';
$sql = "SELECT * FROM tbl_setting_absensi LIMIT 1";
$query = mysqli_query($kon, $sql);
$setting = mysqli_fetch_array($query);

// Menyimpan data pengaturan absensi ke variabel
$mulai_absen = $setting['mulai_absen'];
$akhir_absen = $setting['akhir_absen'];
?>

<?php
include 'config/database.php';

$tanggal_hari_ini = date('Y-m-d');

// Query untuk mendapatkan data absensi terbaru
$query = "
    SELECT a.id_absensi, a.id_mahasiswa, a.status, a.waktu, a.tanggal, a.latitude, a.longitude,
           m.nama, m.alamat
    FROM tbl_absensi a
    INNER JOIN (
        SELECT id_mahasiswa, MAX(waktu) as waktu_terbaru
        FROM tbl_absensi
        WHERE DATE(tanggal) = CURDATE()
        GROUP BY id_mahasiswa
    ) latest_absen ON a.id_mahasiswa = latest_absen.id_mahasiswa AND a.waktu = latest_absen.waktu_terbaru
    JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
    WHERE a.latitude IS NOT NULL AND a.longitude IS NOT NULL
    AND a.id_mahasiswa = '$id_mahasiswa'
";

$result = mysqli_query($kon, $query);

$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = $row;
}

// Query untuk mendapatkan lokasi presensi dari tabel tbl_lokasi_presensi
$query_lokasi_presensi = "
    SELECT id_lokasi_presensi, latitude, longitude, radius, status_aktif
    FROM tbl_lokasi_presensi
    WHERE status_aktif = 1
";

$result_lokasi_presensi = mysqli_query($kon, $query_lokasi_presensi);

$lokasi_presensi = [];
while ($row = mysqli_fetch_assoc($result_lokasi_presensi)) {
    $lokasi_presensi[] = $row;
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
        <li class="active">Presensi</li>
    </ol>
</div>
<!--/.row-->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">Presensi</div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <?php
                include 'config/database.php';
                $tanggal_sekarang = date("Y-m-d");

                // Cek apakah tanggal sekarang lebih besar dari tanggal akhir magang
                if ($tanggal_sekarang > $akhir_magang) {
                    echo '<div id="div_periode" class="alert alert-danger"><strong>Periode Presensi Harian Selesai <i class="fa-solid fa-triangle-exclamation"></i></strong></div>';
                } else {
                    $hari_sekarang = date("l", strtotime($tanggal_sekarang));

                    // Mapping nama hari
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

                    // Cek status hari libur dari tbl_hari_libur
                    $query_hari_libur = "SELECT status FROM tbl_hari_libur WHERE hari = '$hari_sekarang_id'";
                    $result_hari_libur = mysqli_query($kon, $query_hari_libur);
                    $data_hari_libur = mysqli_fetch_assoc($result_hari_libur);
                    $status_hari_libur = $data_hari_libur['status'];

                    // Cek apakah tanggal sekarang berada di antara tanggal_awal dan tanggal_akhir di tbl_tanggal_libur
                    $query_tanggal_libur = "SELECT tanggal_awal, tanggal_akhir, alasan_libur FROM tbl_tanggal_libur WHERE '$tanggal_sekarang' BETWEEN tanggal_awal AND tanggal_akhir";
                    $result_tanggal_libur = mysqli_query($kon, $query_tanggal_libur);
                    $is_tanggal_libur = mysqli_num_rows($result_tanggal_libur) > 0;

                    // Jika libur berdasarkan hari atau tanggal libur
                    if ($status_hari_libur == 'Libur' || $is_tanggal_libur) {
                        if ($is_tanggal_libur) {
                            $row_libur = mysqli_fetch_assoc($result_tanggal_libur);

                            $tanggal_awal = strtotime($row_libur['tanggal_awal']);
                            $tanggal_akhir = strtotime($row_libur['tanggal_akhir']);

                            $tanggal_awal_libur = date('d', $tanggal_awal) . ' ' . MendapatkanBulan(date('n', $tanggal_awal)) . ' ' . date('Y', $tanggal_awal);
                            $tanggal_akhir_libur = date('d', $tanggal_akhir) . ' ' . MendapatkanBulan(date('n', $tanggal_akhir)) . ' ' . date('Y', $tanggal_akhir);

                            $alasan_libur = $row_libur['alasan_libur'];
                            echo '<div id="div_libur" class="alert alert-warning"><strong>Presensi harian libur mulai tanggal ' . $tanggal_awal_libur . ' sampai dengan tanggal ' . $tanggal_akhir_libur . ' dikarenakan ' . $alasan_libur . ' <i class="fa-solid fa-mug-hot"></i></strong></div>';
                        } else {
                            echo '<div id="div_libur" class="alert alert-warning"><strong>Presensi Harian Libur <i class="fa-solid fa-mug-hot"></i></strong></div>';
                        }
                    } else {
                        // Alert status presensi
                        if (isset($_GET['mulai'])) {
                            if ($_GET['mulai'] == 'berhasil') {
                                echo "<script>showAlert('success', 'Berhasil!', 'Presensi Berhasil');</script>";
                            } else if ($_GET['mulai'] == 'belum_dimulai') {
                                echo "<script>showAlert('warning', 'Maaf!', 'Waktu Presensi Belum Dimulai');</script>";
                            } else if ($_GET['mulai'] == 'lewat') {
                                echo "<script>showAlert('warning', 'Maaf!', 'Rentang Waktu Presensi Anda Sudah Lewat');</script>";
                            }
                        }

                        // Alert error upload foto presensi
                        if (isset($_GET['error'])) {
                            if ($_GET['error'] == 'file_bukan_gambar') {
                                echo "<script>showAlert('error', 'Gagal!', 'File yang diupload bukan gambar.');</script>";
                            } else if ($_GET['error'] == 'file_terlalu_besar') {
                                echo "<script>showAlert('error', 'Gagal!', 'Ukuran file terlalu besar (maksimal 1MB).');</script>";
                            } else if ($_GET['error'] == 'ekstensi_tidak_valid') {
                                echo "<script>showAlert('error', 'Gagal!', 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan.');</script>";
                            } else if ($_GET['error'] == 'gagal_upload') {
                                echo "<script>showAlert('error', 'Gagal!', 'Terjadi kesalahan saat mengunggah file.');</script>";
                            } else if ($_GET['error'] == 'jarak_terlalu_jauh') {
                                $jarak_text = isset($_GET['jarak']) ? $_GET['jarak'] : 'tidak diketahui';
                                echo "<script>showAlert('error', 'Gagal!', 'Jarak terlalu jauh dari titik lokasi presensi. Anda berjarak $jarak_text dari titik lokasi presensi.');</script>";
                            }

                            // ✅ Alert khusus untuk bukti WFA
                            else if ($_GET['error'] == 'ekstensi_tidak_valid_wfa') {
                                echo "<script>showAlert('error', 'Gagal!', 'File bukti WFA hanya boleh JPG, JPEG, PNG, PDF, DOC, DOCX.');</script>";
                            } else if ($_GET['error'] == 'file_terlalu_besar_wfa') {
                                echo "<script>showAlert('error', 'Gagal!', 'Ukuran file bukti WFA terlalu besar (maksimal 1MB).');</script>";
                            } else if ($_GET['error'] == 'gagal_upload_wfa') {
                                echo "<script>showAlert('error', 'Gagal!', 'Terjadi kesalahan saat mengunggah bukti WFA.');</script>";
                            }
                        }
                    }
                }
                ?>

                <?php
                include 'config/database.php';

                $tanggal_sekarang = date("Y-m-d");

                // Cek status kamera dari tabel tbl_kamera
                $kamera_perangkat_nonaktif = false;
                $cek_kamera = mysqli_query($kon, "SELECT kamera_perangkat FROM tbl_kamera LIMIT 1");
                if ($cek_kamera && mysqli_num_rows($cek_kamera) > 0) {
                    $data_kamera = mysqli_fetch_assoc($cek_kamera);
                    if ($data_kamera['kamera_perangkat'] == 0) {
                        $kamera_perangkat_nonaktif = true;
                    }
                }

                // default fallback foto
                $foto = 'foto_default.png';
                $folder = 'apps/pengguna/kamera/';
                $lokasi_file = $folder . $foto;

                // Ambil data absensi hari ini (sekali saja)
                $query = "SELECT id_absensi, status, kamera, input_admin FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = CURDATE() LIMIT 1";
                $result = mysqli_query($kon, $query);
                $data_absensi = $result ? mysqli_fetch_assoc($result) : null;

                $status = $data_absensi['status'] ?? null;
                $input_admin = $data_absensi['input_admin'] ?? null;
                $kamera_db = $data_absensi['kamera'] ?? null;

                $is_presensi_tanpa_foto = false;

                if ($data_absensi) {
                    if ($status == 1 || $status == 3) { // Hadir atau Terlambat
                        if (!empty($kamera_db)) {
                            $foto = $kamera_db;
                            $folder = 'apps/pengguna/kamera/';
                            $lokasi_file = $folder . $foto;
                        } else {
                            // jika input_admin atau input_mahasiswa tanpa foto
                            $is_presensi_tanpa_foto = ($input_admin === 'input_admin' || $input_admin === 'input_mahasiswa');
                            $folder = 'apps/pengguna/kamera/';
                            $lokasi_file = $folder . $foto;
                        }
                    } elseif ($status == 2) { // Izin
                        $query_alasan = "SELECT foto FROM tbl_alasan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = CURDATE() LIMIT 1";
                        $result_alasan = mysqli_query($kon, $query_alasan);
                        $data_alasan = $result_alasan ? mysqli_fetch_assoc($result_alasan) : null;

                        if ($data_alasan && !empty($data_alasan['foto'])) {
                            $foto = $data_alasan['foto'];
                        } else {
                            $foto = 'No_gambar.jpg'; // fallback untuk alasan
                        }

                        $folder = 'apps/pengguna/bukti_alasan/';
                        $lokasi_file = $folder . $foto;
                    } else {
                        // Untuk status lain (termasuk 5), lokasi_file tetap default; WFA akan mengambil dari tbl_bukti_wfa saat diperlukan
                    }
                }

                // Pastikan file benar-benar ada (khusus untuk $lokasi_file yang kita set)
                if (isset($lokasi_file) && !file_exists($lokasi_file)) {
                    $lokasi_file = ($status == 2) ? 'apps/pengguna/bukti_alasan/No_gambar.jpg' : 'apps/pengguna/kamera/foto_default.png';
                }

                $is_presensi_admin = false;
                if (in_array($status, [1, 2, 5])) { // Hadir, Izin, WFA
                    $q_admin_check = mysqli_query($kon, "SELECT input_admin FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = CURDATE() LIMIT 1");
                    $data_admin = $q_admin_check ? mysqli_fetch_assoc($q_admin_check) : null;
                    if ($data_admin && $data_admin['input_admin'] == 'input_admin') {
                        $is_presensi_admin = true;
                    }
                }
                ?>

                <?php if ($is_presensi_admin): ?>
                    <div style="padding: 10px 0; color:rgb(220, 176, 53); font-weight: bold;">
                        <i class="bi bi-info-circle-fill"></i> Presensi diinput oleh administrator
                    </div>
                <?php endif; ?>

                <div class="row" style="padding: 0 20px;">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Nama Karyawan Magang</td>
                                    <td width="85%">:
                                        <?php echo $nama; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>NIM / NIS</td>
                                    <td width="85%">:
                                        <?php echo $nim; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Universitas / Sekolah</td>
                                    <td width="85%">:
                                        <?php echo $universitas; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td width="85%">:
                                        <?php

                                        date_default_timezone_set('Asia/Jakarta');
                                        $tanggal_sekarang = date("Y-m-d");
                                        $hari = date("l", strtotime($tanggal_sekarang));
                                        $tgl = date("d", strtotime($tanggal_sekarang)); // Ambil hari (misal: 01)
                                        $bulan = date("m", strtotime($tanggal_sekarang)); // Ambil bulan (misal: 11)
                                        $tahun = date("Y", strtotime($tanggal_sekarang)); // Ambil tahun (misal: 2023)
                                        echo MendapatkanHari($hari) . ', ' . $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Waktu</td>
                                    <td width="85%">:
                                        <?php
                                        // Sertakan file konfigurasi database
                                        include 'config/database.php';

                                        $tanggal_sekarang = date("Y-m-d");
                                        $hari_sekarang = date("l", strtotime($tanggal_sekarang));

                                        // Mapping nama hari dalam bahasa Inggris ke bahasa Indonesia
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

                                        // Ambil status hari libur
                                        $query_hari_libur = "SELECT status FROM tbl_hari_libur WHERE hari = '$hari_sekarang_id'";
                                        $result_hari_libur = mysqli_query($kon, $query_hari_libur);
                                        $data_hari_libur = mysqli_fetch_assoc($result_hari_libur);
                                        $status_hari_libur = $data_hari_libur['status'];

                                        // Ambil data mulai_magang dan akhir_magang dari tbl_mahasiswa
                                        $query_magang = "SELECT mulai_magang, akhir_magang FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
                                        $result_magang = mysqli_query($kon, $query_magang);
                                        $data_magang = mysqli_fetch_assoc($result_magang);

                                        // Cek apakah hari ini berada di dalam periode magang
                                        if ($tanggal_sekarang >= $data_magang['mulai_magang'] && $tanggal_sekarang <= $data_magang['akhir_magang']) {
                                            // Periksa jika hari ini adalah hari libur
                                            if ($status_hari_libur == 'Libur' || $is_tanggal_libur) {
                                                echo '<span class="label label-warning">Hari Libur</span>';
                                            } else {
                                                // Periksa apakah mahasiswa sudah absen hari ini
                                                $query_absensi = "SELECT waktu FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal_sekarang'";
                                                $result_absensi = mysqli_query($kon, $query_absensi);

                                                if (mysqli_num_rows($result_absensi) > 0) {
                                                    $data_absensi = mysqli_fetch_assoc($result_absensi);
                                                    $waktu = ($data_absensi['waktu'] === '00:00:00') ? '-' : $data_absensi['waktu'];
                                                    echo $waktu;
                                                } else {
                                                    echo '<span class="label label-default">Belum Presensi<span>';
                                                }
                                            }
                                        } else {
                                            // Jika di luar periode magang, tampilkan pesan
                                            echo '<span class="label label-danger">Periode Magang Habis!</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td width="85%">:
                                        <?php
                                        // Sertakan file konfigurasi database
                                        include 'config/database.php';

                                        $tanggal_sekarang = date("Y-m-d");
                                        $hari_sekarang = date("l", strtotime($tanggal_sekarang));

                                        // Mapping nama hari dalam bahasa Inggris ke bahasa Indonesia
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

                                        // Ambil status hari libur
                                        $query_hari_libur = "SELECT status FROM tbl_hari_libur WHERE hari = '$hari_sekarang_id'";
                                        $result_hari_libur = mysqli_query($kon, $query_hari_libur);
                                        $data_hari_libur = mysqli_fetch_assoc($result_hari_libur);
                                        $status_hari_libur = $data_hari_libur['status'];

                                        // Ambil data mulai_magang dan akhir_magang dari tbl_mahasiswa
                                        $query_magang = "SELECT mulai_magang, akhir_magang FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
                                        $result_magang = mysqli_query($kon, $query_magang);
                                        $data_magang = mysqli_fetch_assoc($result_magang);

                                        // Cek apakah hari ini berada di dalam periode magang
                                        if ($tanggal_sekarang >= $data_magang['mulai_magang'] && $tanggal_sekarang <= $data_magang['akhir_magang']) {
                                            // Periksa jika hari ini adalah hari libur
                                            if ($status_hari_libur == 'Libur' || $is_tanggal_libur) {
                                                echo '<span class="label label-warning">Hari Libur</span>';
                                            } else {
                                                // Periksa apakah mahasiswa sudah absen hari ini
                                                $query_absensi = "SELECT status FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal_sekarang'";
                                                $result_absensi = mysqli_query($kon, $query_absensi);

                                                // Memeriksa apakah ada hasil yang ditemukan
                                                if (mysqli_num_rows($result_absensi) > 0) {
                                                    $data_absensi = mysqli_fetch_array($result_absensi);
                                                    $status = $data_absensi['status'];

                                                    if ($status == 1) {
                                                        echo '<span class="label label-success">Hadir</span>';
                                                    } elseif ($status == 2) {
                                                        echo '<span class="label label-info">Izin</span>';
                                                    } elseif ($status == 3) {
                                                        echo '<span class="label label-warning">Terlambat</span>';
                                                    } elseif ($status == 4) {
                                                        echo '<span class="label label-danger">Tidak Hadir</span>';
                                                    } elseif ($status == 5) {
                                                        echo '<span class="label label-primary">WFA</span>'; // 🟦 Status 5 jadi Primary
                                                    }
                                                } else {
                                                    echo '<span class="label label-default">Belum Presensi<span>';
                                                }
                                            }
                                        } else {
                                            // Jika di luar periode magang, tampilkan pesan
                                            echo '<span class="label label-danger">Periode Magang Habis!</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i style="font-size: 1.5em;" class="bi bi-camera-fill"></i></td>
                                    <td width="85%">:
                                        <?php
                                        if ($is_presensi_tanpa_foto || $kamera_perangkat_nonaktif) {
                                            echo '<span><i class="bi bi-camera-video-off-fill"></i> Presensi tidak menggunakan kamera</span>';
                                        } else {
                                            // Gunakan $status yang sudah diambil di atas
                                            if ($status == 5) {
                                                // Jika WFA, ambil file dari tbl_bukti_wfa
                                                $query_bukti = "SELECT bukti_wfa FROM tbl_bukti_wfa WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal_sekarang' LIMIT 1";
                                                $result_bukti = mysqli_query($kon, $query_bukti);
                                                $data_bukti = $result_bukti ? mysqli_fetch_assoc($result_bukti) : null;

                                                if ($data_bukti && !empty($data_bukti['bukti_wfa'])) {
                                                    $file_bukti = 'apps/data_absensi/file_wfa/' . $data_bukti['bukti_wfa'];
                                                    $ext = strtolower(pathinfo($file_bukti, PATHINFO_EXTENSION));

                                                    if (!file_exists($file_bukti)) {
                                                        echo '<span class="text-danger">Bukti WFA tidak ditemukan (file hilang)</span>';
                                                    } else {
                                                        // Jika file adalah gambar, tampilkan thumbnail gambar.
                                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                            echo '<a href="#" data-toggle="modal" data-target="#modalGambarPresensi" data-img="' . $file_bukti . '" data-ext="' . $ext . '">
                                                                    <img src="' . $file_bukti . '" alt="Bukti WFA" class="img-thumbnail"
                                                                        style="width:100px; height:100px; object-fit:cover; border:1px solid #ccc; padding:5px;">
                                                                </a>';
                                                        } else {
                                                            // Non-image (pdf/doc/docx): tampilkan ikon + nama file, download via download.php
                                                            $basename = htmlspecialchars(basename($file_bukti));
                                                            $iconPath = "apps/data_absensi/extensi_file/" . $ext . ".png";

                                                            // fallback icon kalau tidak ada icon sesuai ekstensi
                                                            if (!file_exists($iconPath)) {
                                                                $iconPath = "apps/data_absensi/extensi_file/file.png";
                                                            }

                                                            // cek apakah file bukti benar-benar ada di server
                                                            if (file_exists($file_bukti)) {
                                                                echo '<a href="apps/data_absensi/download.php?file=' . urlencode($data_bukti['bukti_wfa']) . '" 
                                                                        class="text-decoration-none">
                                                                        <img src="' . $iconPath . '" alt="' . $ext . '" 
                                                                            style="width:20px; height:20px; vertical-align:middle; margin-right:5px;">
                                                                        <span>' . $basename . '</span>
                                                                    </a>';
                                                            } else {
                                                                echo '<span class="text-danger">File ' . $basename . ' tidak tersedia</span>';
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    echo '<span class="text-danger">Bukti WFA tidak ditemukan</span>';
                                                }
                                            } else {
                                                // Jika bukan WFA, ambil file dari $lokasi_file (kamera / bukti alasan)
                                                echo '<a href="#" data-toggle="modal" data-target="#modalGambarPresensi" data-img="' . $lokasi_file . '" data-ext="' . strtolower(pathinfo($lokasi_file, PATHINFO_EXTENSION)) . '">
                                                        <img src="' . $lokasi_file . '" alt="Foto Presensi" class="img-thumbnail"
                                                            style="width:100px; height:100px; object-fit:cover; border:1px solid #ccc; padding:5px;">
                                                    </a>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php
                                        // Cek apakah masih dalam periode magang
                                        if ($tanggal_sekarang >= $data_magang['mulai_magang'] && $tanggal_sekarang <= $data_magang['akhir_magang']) {
                                            // Cek apakah hari ini adalah hari libur
                                            if ($status_hari_libur == 'Libur' || $is_tanggal_libur) {
                                                echo '<button class="btn btn-success btn-circle" disabled><i class="fa fa-clock-o"></i> Presensi</button>';
                                            } else {
                                                echo '<button id_mahasiswa="' . $id_mahasiswa . '" id="tombol_absensi" class="tombol_periode mulai_absensi btn btn-success btn-circle"><i class="fa fa-clock-o"></i> Presensi</button>';
                                            }
                                        } else {
                                            echo '<button class="btn btn-success btn-circle" disabled><i class="fa fa-clock-o"></i> Presensi</button>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Mengimpor file konfigurasi database
    include 'config/database.php';
    $query = "SELECT mulai_absen, akhir_absen FROM tbl_setting_absensi";
    $result = mysqli_query($kon, $query);
    $data = mysqli_fetch_assoc($result);
    $mulai_absen = date("H:i:s", strtotime($data['mulai_absen']));
    $akhir_absen = date("H:i:s", strtotime($data['akhir_absen']));
    ?>
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

<!-- Modal Gambar Besar -->
<div class="modal fade" id="modalGambarPresensi" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 800px;">
        <div class="modal-content" style="background-color: #fff;">
            <div class="modal-header">
                <h4 class="modal-title" id="gambarModalLabel">File Presensi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="modalBodyPresensi">
                <!-- Image preview -->
                <img id="gambarModalContent" src="" alt="Foto Presensi" class="img-fluid"
                    style="display:none; width:100%; object-fit:contain; border:2px solid #444; border-radius:10px;">
                <!-- PDF preview -->
                <iframe id="gambarModalIframe" src=""
                    style="display:none; width:100%; height:600px; border:none;"></iframe>
                <!-- Download link for non-previewable files -->
                <div id="gambarModalDownload" style="display:none; margin-top:10px;">
                    <a id="gambarModalDownloadLink" href="#" class="btn btn-primary" target="_blank">Download file</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Model AJAX -->
<?php if (!empty($lokasi_presensi)): ?>
    <div class="row" style="margin-bottom: 50px;">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                    Lokasi
                    <span class="pull-right clickable panel-toggle panel-button-tab-left">
                        <em class="fa fa-toggle-up"></em>
                    </span>
                </div>
                <div class="panel-body" id="panel-body-map" style="background-color: rgb(24, 18, 92);">
                    <div id="map" style="height: 550px; z-index: 1; border: 5px solid white"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('click', function (e) {
        var target = e.target.closest("[data-toggle='modal'][data-target='#modalGambarPresensi']");
        if (!target) return;

        var imgSrc = target.getAttribute('data-img') || '';
        var ext = (target.getAttribute('data-ext') || '').toLowerCase();

        var $img = document.getElementById('gambarModalContent');
        var $iframe = document.getElementById('gambarModalIframe');
        var $downloadBlock = document.getElementById('gambarModalDownload');
        var $downloadLink = document.getElementById('gambarModalDownloadLink');

        // hide all
        $img.style.display = 'none';
        $iframe.style.display = 'none';
        $downloadBlock.style.display = 'none';

        if (!imgSrc) {
            // fallback: show default image
            $img.src = 'apps/pengguna/kamera/foto_default.png';
            $img.style.display = 'block';
            return;
        }

        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (imageExts.indexOf(ext) !== -1) {
            $img.src = imgSrc;
            $img.style.display = 'block';
        } else if (ext === 'pdf') {
            $iframe.src = imgSrc;
            $iframe.style.display = 'block';
            $downloadLink.href = imgSrc;
            $downloadBlock.style.display = 'block';
        } else {
            // doc/docx/other: offer download (can't reliably preview)
            $downloadLink.href = imgSrc;
            $downloadLink.innerText = 'Download file';
            $downloadBlock.style.display = 'block';
        }
    });
</script>

<script>
    // Kode di bawah ini dijalankan saat elemen dengan class "mulai_absensi" diklik
    $('.mulai_absensi').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/pengguna/mulai_absensi.php',
            method: 'post',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Mulai Absensi';
            }
        });

        // Membuka modal dengan ID "modal"
        $('#modal').modal('show');
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var locations = <?php echo json_encode($locations); ?>;
        var lokasiPresensi = <?php echo json_encode($lokasi_presensi); ?>;

        if (locations.length === 0) {
            document.getElementById('panel-body-map').innerHTML = `
            <div style="text-align: center; padding: 20px; display: flex; justify-content: center;">
                <div>
                    <dotlottie-player
                        src="https://lottie.host/055d8991-5e4c-40d9-b0ec-37188f267bd1/UOIC3latF6.lottie"
                        background="transparent"
                        speed="1"
                        style="width: 300px; height: 300px;"
                        loop
                        autoplay>
                    </dotlottie-player>
                    <p style="font-size: 1.4em; color: #fff;">Titik lokasi belum ditemukan.</p>
                </div>
            </div>
        `;
        } else {
            var map = L.map('map', {
                center: [-6.1754, 106.8272],
                zoom: 10,
                scrollWheelZoom: false
            });

            var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            });

            var googleSat = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                attribution: '© Google Satellite'
            });

            var googleRoadmap = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '© Google Roadmap'
            });

            var googleHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                attribution: '© Google Hybrid'
            });

            var googleTerrain = L.tileLayer('https://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
                attribution: '© Google Terrain'
            });

            osm.addTo(map);

            var baseMaps = {
                "OpenStreetMap": osm,
                "Google Earth (Satellite)": googleSat,
                "Google Roadmap": googleRoadmap,
                "Google Hybrid": googleHybrid,
                "Google Terrain": googleTerrain
            };

            L.control.layers(baseMaps).addTo(map);

            locations.forEach(function (loc) {
                if (loc.latitude && loc.longitude) {
                    L.marker([loc.latitude, loc.longitude])
                        .addTo(map)
                        .bindPopup(`<b>Latitude:</b> ${loc.latitude}<br><b>Longitude:</b> ${loc.longitude}`)
                        .openPopup(); // bisa dihapus jika tidak mau auto buka popup
                }
            });

            lokasiPresensi.forEach(function (lokasi) {
                if (lokasi.latitude && lokasi.longitude && lokasi.radius) {
                    L.circle([lokasi.latitude, lokasi.longitude], {
                        color: 'blue',
                        fillColor: 'blue',
                        fillOpacity: 0.3,
                        radius: lokasi.radius
                    }).addTo(map)
                        .bindPopup(`<b>Lokasi Presensi:</b><br>Latitude: ${lokasi.latitude}<br>Longitude: ${lokasi.longitude}<br>Radius: ${lokasi.radius} meter`);
                }
            });

            if (locations.length > 0) {
                var firstLoc = locations[0];
                map.setView([firstLoc.latitude, firstLoc.longitude], 15);
            }

            // --------- ZOOM INFO CTRL + SCROLL ----------
            var zoomInfoControl = null;

            function updateZoomInfoControl() {
                if (window.innerWidth > 768) {
                    if (!zoomInfoControl) {
                        zoomInfoControl = L.control({ position: "topright" });
                        zoomInfoControl.onAdd = function () {
                            var div = L.DomUtil.create("div", "zoom-info");
                            div.innerHTML = "<div style='background: white; padding: 5px; border-radius: 5px; font-size: 12px; box-shadow: 0 0 5px rgba(0,0,0,0.3);'>Klik <b>CTRL</b> + Scroll untuk zoom</div>";
                            return div;
                        };
                        zoomInfoControl.addTo(map);
                    }
                } else {
                    if (zoomInfoControl) {
                        map.removeControl(zoomInfoControl);
                        zoomInfoControl = null;
                    }
                }
            }

            updateZoomInfoControl();
            window.addEventListener('resize', updateZoomInfoControl);

            const mapContainer = document.getElementById("map");

            mapContainer.addEventListener("wheel", function (e) {
                if (e.ctrlKey) {
                    e.preventDefault(); // Cegah browser zoom
                    map.scrollWheelZoom.enable();
                } else {
                    map.scrollWheelZoom.disable();
                }
            }, { passive: false });

            map.on("zoomend", function () {
                map.scrollWheelZoom.disable();
            });
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