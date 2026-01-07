<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<style>
    .swal2-confirm {
        font-size: 1.5em !important;
    }

    @media (max-width: 576px) {
        #camera-container .video-container {
            height: 400px !important;
        }

        #loadingIndicator {
            width: 70%;
            text-align: center;
        }
    }
</style>

<?php
session_start();
date_default_timezone_set("Asia/Jakarta");
include '../../config/database.php';

function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Mendapatkan tanggal dan waktu saat ini
$date_now = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$waktu_akhir_hari = $date_now . ' 23:00:00';

// Cek apakah pengguna sudah login
if (isset($_SESSION['id_mahasiswa'])) {
    $id_mahasiswa = $_SESSION["id_mahasiswa"];

    // Cek apakah sudah absen hari ini
    $cek_absen_hari_ini = "SELECT COUNT(*) FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$date_now'";
    $result = mysqli_query($kon, $cek_absen_hari_ini);

    if ($result) {
        $data = mysqli_fetch_assoc($result);

        // Jika belum absen hari ini dan sudah diluar rentang waktu yang diizinkan
        if ($data['COUNT(*)'] == 0 && $waktu_sekarang > $waktu_akhir_hari) {
            $hari_sekarang = date("N");
            if ($hari_sekarang != 6 && $hari_sekarang != 7) {
                $sql_otomatis = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, konfirmasi_status) VALUES 
                    ('$id_mahasiswa', 4, '-', '$date_now', 'X')";
                mysqli_query($kon, $sql_otomatis);
            }
        }
    }
}

if (isset($_POST['submit'])) {
    date_default_timezone_set("Asia/Jakarta");
    $id_mahasiswa = $_SESSION["id_mahasiswa"];
    $status = input($_POST["status"]);
    $tanggal = date("Y-m-d");
    $waktu = date("H:i:s");
    $alasan = input($_POST["alasan"]);
    $latitude = input($_POST["latitude"]);
    $longitude = input($_POST["longitude"]);

    $kamera_filename = null;
    if (!empty($_POST['kamera_data'])) {
        $base64_image = $_POST['kamera_data'];

        // Ekstrak data base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
            $data = substr($base64_image, strpos($base64_image, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc.

            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                header("Location: ../../index.php?page=absen&error=tipe_gambar_tidak_didukung");
                exit;
            }

            $data = base64_decode($data);
            if ($data === false) {
                header("Location: ../../index.php?page=absen&error=gagal_decode_gambar");
                exit;
            }

            // Simpan file
            $kamera_filename = "kamera_" . time() . "_" . rand(100, 999) . ".$type";
            $filepath = "../../apps/pengguna/kamera/" . $kamera_filename;
            file_put_contents($filepath, $data);
        }
    }

    // Upload bukti foto jika ada
    if ($_FILES['bukti_foto']['name']) {
        $bukti_foto = $_FILES['bukti_foto']['name'];
        $target_dir = "../../apps/pengguna/bukti_alasan/";
        $target_file = $target_dir . basename($bukti_foto);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file gambar adalah gambar yang valid
        $check = getimagesize($_FILES['bukti_foto']['tmp_name']);
        if ($check === false) {
            $uploadOk = 0;
            header("Location: ../../index.php?page=absen&error=file_bukan_gambar");
            exit;
        }

        // Cek ukuran file (maksimal 5MB)
        if ($_FILES['bukti_foto']['size'] > 1000000) {
            $uploadOk = 0;
            header("Location: ../../index.php?page=absen&error=file_terlalu_besar");
            exit;
        }

        // Cek ekstensi file
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $uploadOk = 0;
            header("Location: ../../index.php?page=absen&error=ekstensi_tidak_valid");
            exit;
        }

        // Jika semua aman, upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $target_file)) {
                // Berhasil upload, lanjut proses
                // (tidak perlu redirect di sini, kecuali mau kasih notifikasi sukses)
            } else {
                header("Location: ../../index.php?page=absen&error=gagal_upload");
                exit;
            }
        }
    } else {
        $bukti_foto = null; // Jika tidak ada foto
    }

    // Mendapatkan data lokasi presensi
    $query_lokasi = "SELECT latitude, longitude, radius, status_aktif FROM tbl_lokasi_presensi WHERE id_lokasi_presensi = 1";
    $result_lokasi = mysqli_query($kon, $query_lokasi);
    $lokasi_data = mysqli_fetch_assoc($result_lokasi);

    $lat_lokasi = $lokasi_data['latitude'];
    $long_lokasi = $lokasi_data['longitude'];
    $radius = $lokasi_data['radius'];
    $status_aktif = $lokasi_data['status_aktif'];  // ambil status_aktif

    // Mendapatkan latitude dan longitude mahasiswa yang absen
    $lat_mahasiswa = input($_POST['latitude']);
    $long_mahasiswa = input($_POST['longitude']);

    // Kalau status_aktif = 1, baru cek jarak
    if ($status_aktif == 1) {
        // Fungsi untuk menghitung jarak menggunakan rumus Haversine
        function jarak_haversine($lat1, $long1, $lat2, $long2)
        {
            $earth_radius = 6371000;  // dalam meter

            $lat1 = deg2rad($lat1);
            $long1 = deg2rad($long1);
            $lat2 = deg2rad($lat2);
            $long2 = deg2rad($long2);

            $dlat = $lat2 - $lat1;
            $dlon = $long2 - $long1;

            $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $jarak = $earth_radius * $c;
            return $jarak;
        }

        // Menghitung jarak antara lokasi mahasiswa dan lokasi presensi
        $jarak = jarak_haversine($lat_mahasiswa, $long_mahasiswa, $lat_lokasi, $long_lokasi);

        // Mengecek apakah jarak dalam batas radius yang ditentukan
        if ($status != "2" && $status != "5" && $jarak > $radius) {
            if ($jarak >= 1000) {
                $jarak_text = number_format($jarak / 1000, 2, ',', '.') . " km";
            } else {
                $jarak_text = round($jarak) . " m";
            }

            // Tambahkan bagian log di sini:
            $query_nama = "SELECT nama, kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
            $result_nama = mysqli_query($kon, $query_nama);
            $data_nama = mysqli_fetch_assoc($result_nama);
            $nama = $data_nama['nama'];
            $kode_pengguna = $data_nama['kode_mahasiswa'];
            $aktivitas = "Melakukan presensi (Jarak terlalu jauh: $jarak_text dari titik lokasi presensi)";
            $status_log = "gagal";

            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES (NOW(), '$nama', 'Mahasiswa', '$kode_pengguna', '$aktivitas', '$status_log')";
            mysqli_query($kon, $log_query);

            header("Location: ../../index.php?page=absen&error=jarak_terlalu_jauh&jarak=" . urlencode($jarak_text));
            exit();
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $cek_waktu = "SELECT CONCAT(CURDATE(), ' ', mulai_absen) as mulai_absen, CONCAT(CURDATE(), ' ', akhir_absen) as akhir_absen, NOW() as waktu_sekarang FROM tbl_setting_absensi LIMIT 1;";
        $query = mysqli_query($kon, $cek_waktu);
        $setting = mysqli_fetch_array($query);
        $mulai_absen = $setting["mulai_absen"];
        $akhir_absen = $setting["akhir_absen"];
        $waktu_sekarang = $setting["waktu_sekarang"];

        // Mendapatkan nama mahasiswa
        $query_nama = "SELECT nama, kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
        $result_nama = mysqli_query($kon, $query_nama);
        $data_nama = mysqli_fetch_assoc($result_nama);
        $nama = $data_nama['nama'];
        $kode_pengguna = $data_nama['kode_mahasiswa'];

        // Tentukan aktivitas berdasarkan status
        if ($status == "2") {
            $aktivitas = "Presensi izin";
        } else if ($status == "5") {
            $aktivitas = "Presensi WFA";
        } else {
            $aktivitas = "Melakukan presensi";
        }

        if ($status == "2") {
            // Simpan presensi dengan izin dan alasan (termasuk foto)
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, latitude, longitude) 
            VALUES ('$id_mahasiswa', $status, NOW(), '$tanggal', '$latitude', '$longitude')";
            $simpan_absensi = mysqli_query($kon, $sql);

            // Simpan alasan dan foto bukti ke tbl_alasan
            $sql_alasan = "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal, foto) VALUES ('$id_mahasiswa', '$alasan', '$tanggal', '$bukti_foto')";
            mysqli_query($kon, $sql_alasan);
        } else if ($status == "5") {
            // WFA harus dalam rentang waktu absensi
            if ($waktu_sekarang >= $mulai_absen && $waktu_sekarang <= $akhir_absen) {
                // --- Upload bukti WFA hanya di sini ---
                $bukti_wfa = null;
                if (isset($_FILES['bukti_wfa']['name']) && $_FILES['bukti_wfa']['name'] != '') {
                    $target_dir = "../../apps/data_absensi/file_wfa/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }

                    $file_name = $_FILES['bukti_wfa']['name'];
                    $file_tmp = $_FILES['bukti_wfa']['tmp_name'];
                    $file_size = $_FILES['bukti_wfa']['size'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    $allowed = ["jpg", "jpeg", "png", "pdf", "doc", "docx"];

                    if (!in_array($file_ext, $allowed)) {
                        header("Location: ../../index.php?page=absen&error=ekstensi_tidak_valid_wfa");
                        exit;
                    }

                    if ($file_size > 1000000) {
                        header("Location: ../../index.php?page=absen&error=file_terlalu_besar_wfa");
                        exit;
                    }

                    $new_file_name = "wfa_" . time() . "_" . rand(100, 999) . "." . $file_ext;
                    $target_file = $target_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $bukti_wfa = $new_file_name;

                        // Simpan ke tabel bukti_wfa
                        $sql_wfa = "INSERT INTO tbl_bukti_wfa (id_mahasiswa, bukti_wfa, tanggal) 
                            VALUES ('$id_mahasiswa', '$bukti_wfa', '$tanggal')";
                        mysqli_query($kon, $sql_wfa);
                    } else {
                        header("Location: ../../index.php?page=absen&error=gagal_upload_wfa");
                        exit;
                    }
                }

                // Simpan presensi WFA
                $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, latitude, longitude, input_admin) 
                VALUES ('$id_mahasiswa', $status, NOW(), '$tanggal', '$latitude', '$longitude', 'input_mahasiswa')";
                $simpan_absensi = mysqli_query($kon, $sql);

            } else if ($waktu_sekarang < $mulai_absen) {
                $status_log = "gagal";
                $aktivitas = "Presensi WFA (Rentang waktu presensi belum dimulai)";
                $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                      VALUES (NOW(), '$nama', 'Mahasiswa', '$kode_pengguna', '$aktivitas', '$status_log')";
                mysqli_query($kon, $log_query);
                header("Location: ../../index.php?page=absen&mulai=belum_dimulai");
                exit();
            } else {
                $status_log = "gagal";
                $aktivitas = "Presensi WFA (Rentang waktu presensi lewat)";
                $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                      VALUES (NOW(), '$nama', 'Mahasiswa', '$kode_pengguna', '$aktivitas', '$status_log')";
                mysqli_query($kon, $log_query);
                header("Location: ../../index.php?page=absen&mulai=lewat");
                exit();
            }
        } else if ($waktu_sekarang >= $mulai_absen && $waktu_sekarang <= $akhir_absen) {
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, latitude, longitude, kamera, input_admin) 
            VALUES ('$id_mahasiswa', $status, NOW(), '$tanggal', '$latitude', '$longitude', '$kamera_filename', 'input_mahasiswa')";
        } else if ($waktu_sekarang < $mulai_absen) {
            $status_log = "gagal";
            $aktivitas = "Melakukan presensi (Rentang waktu presensi belum dimulai)";
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES (NOW(), '$nama', 'Mahasiswa', '$kode_pengguna', '$aktivitas', '$status_log')";
            mysqli_query($kon, $log_query);
            header("Location: ../../index.php?page=absen&mulai=belum_dimulai");
            exit();
        } else {
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, konfirmasi_status, latitude, longitude, kamera, input_admin) VALUES ('$id_mahasiswa', 3, NOW(), '$tanggal', 'X', '$latitude', '$longitude', '$kamera_filename', 'input_mahasiswa')";
            $simpan_absensi = mysqli_query($kon, $sql);
            $status_log = "gagal";
            $aktivitas = "Melakukan presensi (Rentang waktu presensi lewat)";
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES (NOW(), '$nama', 'Mahasiswa', '$kode_pengguna', '$aktivitas', '$status_log')";
            mysqli_query($kon, $log_query);
            header("Location: ../../index.php?page=absen&mulai=lewat");
            exit();
        }

        if (!isset($simpan_absensi)) {
            $simpan_absensi = mysqli_query($kon, $sql);
        }

        // Log aktivitas
        $status_log = isset($simpan_absensi) && $simpan_absensi ? "berhasil" : "gagal";
        $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                      VALUES (NOW(), '$nama', 'Mahasiswa', '$kode_pengguna', '$aktivitas', '$status_log')";
        mysqli_query($kon, $log_query);

        if ($simpan_absensi) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=absen&mulai=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=absen&mulai=gagal");
        }
    }
}
?>

<?php
$id_mahasiswa = $_SESSION["id_mahasiswa"];
$nama_mahasiswa = $_SESSION["nama_mahasiswa"];
$tanggal = date(format: "Y-m-d");
include '../../config/database.php';
$query = mysqli_query(
    $kon,
    "SELECT mulai_magang, akhir_magang FROM tbl_mahasiswa WHERE id_mahasiswa=$id_mahasiswa;"
);
$periode = mysqli_fetch_array($query);
$tanggal_masuk = $periode["mulai_magang"];
$tanggal_keluar = $periode["akhir_magang"];
?>

<?php
$tanggal_sekarang = date("Y-m-d");
$query = "SELECT COUNT(*) FROM tbl_absensi WHERE tanggal = '$tanggal_sekarang' AND id_mahasiswa = '$id_mahasiswa'";
$result = mysqli_query($kon, $query);
$data = mysqli_fetch_assoc($result);
if ($data['COUNT(*)'] > 0) {
    $absensi_sudah = "disabled";
} else {
    $absensi_sudah = "";
}
?>

<?php
// Ambil status_aktif dari tbl_lokasi_presensi
$query_lokasi = "SELECT status_aktif FROM tbl_lokasi_presensi WHERE id_lokasi_presensi = 1";
$result_lokasi = mysqli_query($kon, $query_lokasi);
$lokasi_data = mysqli_fetch_assoc($result_lokasi);
$status_aktif = $lokasi_data['status_aktif']; // Ambil status_aktif
?>

<?php
include '../../config/database.php';
$kamera_perangkat = 1;
$deteksi_wajah = 1;

$hasil = mysqli_query($kon, "SELECT kamera_perangkat, deteksi_wajah FROM tbl_kamera LIMIT 1");
if ($row = mysqli_fetch_assoc($hasil)) {
    $kamera_perangkat = $row['kamera_perangkat'];
    $deteksi_wajah = $row['deteksi_wajah'];
}
?>

<?php
include '../../config/database.php';
$id_mahasiswa = $_SESSION['id_mahasiswa'];
$wajah_terakhir = null;

$query_wajah = mysqli_query($kon, "
    SELECT kamera FROM tbl_absensi 
    WHERE id_mahasiswa='$id_mahasiswa' AND kamera IS NOT NULL AND kamera != ''
    ORDER BY tanggal DESC, waktu DESC LIMIT 1
");

if ($data = mysqli_fetch_assoc($query_wajah)) {
    $wajah_terakhir = $data['kamera'];
}
?>

<?php
$id_mahasiswa = $_SESSION['id_mahasiswa'];
$q = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa'");
$data = mysqli_fetch_assoc($q);
$nama_mahasiswa = $data['nama'];
?>

<form action="apps/pengguna/mulai_absensi.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="alert alert-danger" role="alert" id="alertMessage" style="display: none;">
        Pilih status sebelum melakukan presensi!
    </div>

    <?php if ($absensi_sudah): ?>
        <!-- Alert untuk sudah presensi -->
        <div class="alert alert-warning" role="alert" id="alert_sudah_presensi">
            <i class="bi bi-exclamation-triangle-fill"></i> Anda sudah presensi!
        </div>
    <?php else: ?>
        <!-- Alert untuk status HADIR -->
        <div class="alert alert-success" role="alert" id="alert_hadir" style="display: none;">
            <i class="fa fa-info-circle"></i> Disarankan gunakan perangkat mobile saat presensi agar lebih akurat!
        </div>

        <!-- Alert untuk status IZIN -->
        <div class="alert alert-info" role="alert" id="alert_izin" style="display: none;">
            <i class="fa fa-info-circle"></i> Izin dapat dilakukan di luar rentang waktu presensi pada hari yang sama dan
            boleh diluar radius dari titik lokasi presensi!
        </div>
    <?php endif; ?>

    <input type="hidden" name="kamera_data" id="kamera_data">
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">
    <input type="hidden" id="kamera_status" value="<?= $kamera_perangkat ?>">
    <input type="hidden" id="deteksi_wajah_status" value="<?= $deteksi_wajah ?>">
    <input type="hidden" id="foto_lama" value="<?= $wajah_terakhir ? 'apps/pengguna/kamera/' . $wajah_terakhir : '' ?>">
    <input type="hidden" id="nama_mahasiswa" value="<?= $nama_mahasiswa ?>">
    <audio id="audio_success" src="/absensi_magang_coba/audio/success.mp3"></audio>
    <audio id="audio_fail" src="/absensi_magang_coba/audio/failed.mp3"></audio>

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label>Status :</label>
                <select class="form-control" id="status" name="status" required>
                    <option selected disabled>Pilih</option>
                    <option value="1">Hadir</option>
                    <option value="2">Izin</option>
                    <option value="5">WFA</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12" id="text_wfa" style="display:none;">
            <div class="form-group">
                <label>Bukti Arahan Atasan (jpg, jpeg, png, docx, doc, pdf):</label>
                <input type="file" name="bukti_wfa" id="bukti_wfa" class="form-control" accept="*/*">
                <!-- Tempat untuk pratinjau gambar -->
                <div id="preview-container-wfa" style="margin-top: 10px;">
                    <img id="preview-image-wfa" src="" alt="Preview Gambar" class="img-thumbnail"
                        style="display: none; width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; padding: 5px;">
                </div>
            </div>
        </div>
        <div class="col-sm-12" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" value=""
                    placeholder="Masukkan Alasan Kenapa Izin?">
            </div>
            <div class="form-group">
                <label>Bukti Alasan:</label>
                <input type="file" name="bukti_foto" id="bukti_foto" class="form-control" accept="image/*">
                <!-- Tempat untuk pratinjau gambar -->
                <div id="preview-container" style="margin-top: 10px;">
                    <img id="preview-image" src="" alt="Preview Gambar" class="img-thumbnail"
                        style="display: none; width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; padding: 5px;">
                </div>
            </div>
        </div>
    </div>
    <!-- Preview kamera saat HADIR -->
    <?php if ($kamera_perangkat == 1): ?>
        <div class="row" id="camera-container" style="display: none;">
            <div class="col-sm-12">
                <label>Kamera:</label>
                <div class="video-container"
                    style="position: relative; border: 3px solid #ffffff; border-radius: 8px; overflow: hidden; width: 100%; max-width: 100%; height: 300px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);">
                    <video id="video" autoplay playsinline
                        style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                    <canvas id="overlay" style="position: absolute; top: 0; left: 0;"></canvas>

                    <!-- Alert jika tidak ada kamera -->
                    <div id="alert_no_camera" style="
                    display: none;
                    position: absolute;
                    top: 10px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(220, 53, 69, 0.7);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-weight: bold;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                    z-index: 10;
                ">
                        <i class="fa fa-times-circle"></i> Kamera tidak aktif.
                    </div>

                    <!-- Loading saat kamera menyala dan wajah belum terdeteksi -->
                    <div id="loadingIndicator" style="
                        display: none;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background: rgba(0, 0, 0, 0.7);
                        color: white;
                        padding: 12px 20px;
                        border-radius: 10px;
                        font-weight: bold;
                        font-size: 16px;
                        z-index: 15;
                    ">
                        <i class="fa fa-spinner fa-spin"></i> Mendeteksi wajah...
                    </div>

                    <!-- Alert dalam kamera -->
                    <?php if ($deteksi_wajah == 1): ?>
                        <div id="alert_no_face" style="
                            display: none;
                            position: absolute;
                            bottom: 10px;
                            left: 50%;
                            transform: translateX(-50%);
                            background: rgba(255, 193, 7, 0.7);
                            color: black;
                            padding: 8px 8px;
                            border-radius: 8px;
                            font-weight: bold;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                            z-index: 10;
                        ">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit" id="tombol_hari" class="simpan_absensi btn btn-primary" <?php echo $absensi_sudah; ?> data-absensi="<?php echo $absensi_sudah ? 'sudah' : 'belum'; ?>">
                    <i class="fa fa-clock-o"></i> Presensi
                </button>

            </div>
        </div>
    </div>
</form>

<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    let descriptorLama = null;
    let tombolPresensi = document.getElementById("tombol_hari");
    let lastStatus = null;  // Untuk melacak perubahan status

    async function loadModels() {
        await faceapi.nets.faceRecognitionNet.loadFromUri('/absensi_magang_coba/models/face_recognition');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/absensi_magang_coba/models/face_recognition');
        await faceapi.nets.tinyFaceDetector.loadFromUri('/absensi_magang_coba/models/tiny_face_detector');
        await faceapi.nets.faceExpressionNet.loadFromUri('/absensi_magang_coba/models/face_expression'); // <- Tambahan untuk senyum
    }

    async function loadOldFaceDescriptor() {
        const pathFotoLama = document.getElementById('foto_lama').value;
        if (!pathFotoLama) {
            descriptorLama = null; // Tidak ada wajah lama
            return;
        }

        const img = await faceapi.fetchImage(pathFotoLama);
        const detection = await faceapi
            .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (detection) {
            descriptorLama = detection.descriptor;
        }
    }

    async function startFaceComparison(video) {
        const canvas = document.getElementById('overlay');
        const displaySize = { width: video.clientWidth, height: video.clientHeight };
        faceapi.matchDimensions(canvas, displaySize);

        const namaMahasiswa = document.getElementById("nama_mahasiswa").value;
        const audioSuccess = document.getElementById("audio_success");
        const audioFail = document.getElementById("audio_fail");

        setInterval(async () => {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor()
                .withFaceExpressions();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const resizedDetection = detection ? faceapi.resizeResults(detection, displaySize) : null;

            // Mirror canvas agar seperti kamera depan
            ctx.save();
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);

            let boxInfo = null;
            let boxColor = "red";

            if (resizedDetection) {
                const { x, y, width, height } = resizedDetection.detection.box;
                boxInfo = { x, y, width, height }; // Simpan info posisi untuk label

                // Default warna kotak (akan diperbarui nanti)
                ctx.strokeStyle = boxColor;
                ctx.lineWidth = 3;
                ctx.strokeRect(x, y, width, height);
            }

            ctx.restore(); // Kembali normal untuk teks dan label

            if (detection && descriptorLama && detection.descriptor) {
                const distance = faceapi.euclideanDistance(descriptorLama, detection.descriptor);
                const percentMatch = ((1 - distance) * 100).toFixed(2);

                ctx.font = "bold 14px Arial";
                const matchText = `Kecocokan: ${percentMatch}%`;
                const textMetrics = ctx.measureText(matchText);
                const textWidth = textMetrics.width + 20;

                ctx.fillStyle = "rgba(0, 0, 0, 0.6)";
                ctx.fillRect(10, 10, textWidth, 20);

                ctx.fillStyle = "#00FF00";
                ctx.fillText(matchText, 15, 25);
            }

            if (detection) {
                const distance = descriptorLama ? faceapi.euclideanDistance(descriptorLama, detection.descriptor) : null;
                document.getElementById("loadingIndicator").style.display = "none";

                let boxLabel = "";
                let statusSekarang = "fail";

                if (!descriptorLama || distance < 0.5) {
                    const ekspresi = detection.expressions;
                    const ekspresiUtama = Object.entries(ekspresi).sort((a, b) => b[1] - a[1])[0];

                    if (ekspresiUtama[0] === "happy" && ekspresiUtama[1] > 0.8) {
                        boxColor = "green";
                        boxLabel = `${namaMahasiswa}`;
                        tombolPresensi.disabled = false;
                        document.getElementById("alert_no_face").style.display = "none";
                        statusSekarang = "success";

                        if (lastStatus !== statusSekarang) {
                            audioSuccess.play();
                            lastStatus = statusSekarang;
                        }
                    } else {
                        boxColor = "yellow";
                        boxLabel = "Silakan SENYUM 😊";
                        tombolPresensi.disabled = true;
                        document.getElementById("alert_no_face").style.display = "block";
                        document.getElementById("alert_no_face").innerHTML = `
                            <div class="text-center"><i class="fa fa-smile"></i><br>Silakan SENYUM agar tombol presensi aktif.</div>
                        `;

                        if (lastStatus !== "not-smiling") {
                            audioFail.play();
                            speak("Silakan senyum terlebih dahulu");
                            lastStatus = "not-smiling";
                        }
                    }
                } else {
                    boxColor = "red";
                    boxLabel = "Wajah tidak cocok!";
                    tombolPresensi.disabled = true;
                    document.getElementById("alert_no_face").style.display = "block";
                    document.getElementById("alert_no_face").innerHTML = `
                        <div class="text-center"><i class="fa fa-exclamation-triangle"></i><br>Wajah tidak cocok! pastikan yang menghadap kamera adalah wajah anda!</div>
                    `;

                    if (lastStatus !== "not-match") {
                        audioFail.play();
                        speak("Wajah tidak cocok");
                        lastStatus = "not-match";
                    }
                }

                if (boxInfo) {
                    const realX = canvas.width - boxInfo.x - boxInfo.width;
                    const realY = boxInfo.y;

                    // Gambar ulang kotak dengan warna status
                    ctx.save();
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.strokeStyle = boxColor;
                    ctx.lineWidth = 3;
                    ctx.strokeRect(boxInfo.x, boxInfo.y, boxInfo.width, boxInfo.height);
                    ctx.restore();

                    ctx.font = "14px Arial";
                    const textMetrics = ctx.measureText(boxLabel);
                    const textWidth = textMetrics.width + 10;

                    // Gambar background label
                    ctx.fillStyle = boxColor;
                    ctx.fillRect(realX, realY - 25, textWidth, 20);

                    // Tentukan warna teks berdasarkan warna box
                    if (boxColor === "green" || boxColor === "red") {
                        ctx.fillStyle = "white";
                    } else if (boxColor === "yellow") {
                        ctx.fillStyle = "black";
                    } else {
                        ctx.fillStyle = "white"; // default
                    }

                    ctx.fillText(boxLabel, realX + 5, realY - 10);
                }

            } else {
                tombolPresensi.disabled = true;
                document.getElementById("loadingIndicator").style.display = "none"; // Tambahkan baris ini
                document.getElementById("alert_no_face").style.display = "block";
                document.getElementById("alert_no_face").innerHTML = `
                    <div class="text-center"><i class="fa fa-exclamation-triangle"></i><br>Wajah tidak terdeteksi! Pastikan wajah terlihat jelas, pencahayaan cukup dan wajah menghadap kamera.</div>
                    `;

                if (lastStatus !== "no-face") {
                    audioFail.play();
                    speak("Wajah tidak terdeteksi");
                    lastStatus = "no-face";
                }
            }
        }, 500);
    }

    document.getElementById("status").addEventListener("change", function () {
        const status = this.value;
        const alasanDiv = document.getElementById("text_alasan");
        const alertHadir = document.getElementById("alert_hadir");
        const alertIzin = document.getElementById("alert_izin");
        const cameraContainer = document.getElementById("camera-container");
        const kameraStatus = document.getElementById("kamera_status").value;

        alertHadir.style.display = "none";
        alertIzin.style.display = "none";
        alasanDiv.style.display = "none";
        cameraContainer.style.display = "none";

        function stopKamera() {
            const video = document.getElementById('video');
            const stream = video.srcObject;
            if (stream) {
                const tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
                video.srcObject = null;
            }

            // Bersihkan canvas overlay
            const canvas = document.getElementById('overlay');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        if (status === "1") {
            if (kameraStatus != "1") return;

            alertHadir.style.display = "block";
            cameraContainer.style.display = "block";

            tombolPresensi.disabled = true;

            const sudahPresensi = tombolPresensi.getAttribute('data-absensi') === 'sudah';
            if (sudahPresensi) {
                document.getElementById("alert_no_camera").style.display = "block";
                document.getElementById("video").style.display = "none";
                return;
            }

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(function (stream) {
                        const video = document.getElementById('video');
                        video.srcObject = stream;
                        video.onloadedmetadata = function () {
                            video.play();
                            const deteksiWajah = document.getElementById("deteksi_wajah_status").value;

                            if (deteksiWajah == "1") {
                                document.getElementById("loadingIndicator").style.display = "block"; // <-- Tampilkan loading

                                loadModels().then(() => {
                                    loadOldFaceDescriptor().then(() => {
                                        startFaceComparison(video);
                                    });
                                });
                            } else {
                                tombolPresensi.disabled = false;
                            }
                        };
                    })
                    .catch(function (error) {
                        console.error("Gagal mengakses kamera:", error);
                        document.getElementById("alert_no_camera").style.display = "block";
                        tombolPresensi.disabled = true;
                    });
            }
        } else if (status === "2") {
            alertIzin.style.display = "block";
            alasanDiv.style.display = "block";
            tombolPresensi.disabled = false;

            stopKamera(); // <-- Tambahkan ini untuk mematikan kamera saat pilih izin
        }
    });
</script>

<script>
    const form = document.querySelector('form');
    const video = document.getElementById('video');
    const kameraData = document.getElementById('kamera_data');

    form.addEventListener('submit', function (e) {
        // Ambil snapshot dari video dan simpan sebagai base64
        const canvasSnap = document.createElement('canvas');
        canvasSnap.width = video.videoWidth;
        canvasSnap.height = video.videoHeight;
        const ctx = canvasSnap.getContext('2d');

        // Terapkan mirror seperti kamera depan
        ctx.translate(canvasSnap.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvasSnap.width, canvasSnap.height);

        // Konversi ke base64 dan simpan ke input hidden
        const base64Image = canvasSnap.toDataURL('image/jpeg');
        kameraData.value = base64Image;

        // Stop kamera setelah presensi
        const stream = video.srcObject;
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
            video.srcObject = null;
        }
    });
</script>

<script>
    function speak(text) {
        const synth = window.speechSynthesis;

        if (!synth) return;

        // Cek apakah sedang berbicara, jika ya jangan bicara lagi
        if (synth.speaking) return;

        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = 'id-ID'; // Bahasa Indonesia
        utter.rate = 1;       // Kecepatan bicara

        synth.speak(utter);
    }
</script>

<script>
    $('#modal').on('hidden.bs.modal', function () {
        const video = document.getElementById('video');
        const stream = video?.srcObject;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }

        // Bersihkan canvas overlay
        const canvas = document.getElementById('overlay');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // Reset tombol presensi agar disabled saat modal dibuka lagi
        const tombolPresensi = document.getElementById("tombol_hari");
        if (tombolPresensi) tombolPresensi.disabled = true;

        // Reset alert wajah
        const alertWajah = document.getElementById("alert_no_face");
        if (alertWajah) {
            alertWajah.style.display = "none";
            alertWajah.innerHTML = "";
        }

        // Reset status terakhir
        lastStatus = null;
    });
</script>

<?php if ($status_aktif == 1): ?>
    <script>
        let lokasiTersedia = false;

        function ambilLokasi() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        $("#latitude").val(position.coords.latitude);
                        $("#longitude").val(position.coords.longitude);
                        lokasiTersedia = true;
                        console.log("Lokasi didapat:", position.coords.latitude, position.coords.longitude);
                    },
                    function (error) {
                        console.warn(`ERROR(${error.code}): ${error.message}`);
                        Swal.fire({
                            icon: 'warning',
                            title: '<span style="font-size: 1.5em;">Lokasi tidak diizinkan!</span>',
                            html: '<span style="font-size: 1.5em;"></span> Silakan izinkan akses lokasi untuk melakukan presensi.',
                            confirmButtonColor: '#3085d6'
                        });
                        lokasiTersedia = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000, // perpanjang timeout jadi 15 detik
                        maximumAge: 0
                    }
                );
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-size: 1.5em;">Geolocation tidak didukung!</span>',
                    html: '<span style="font-size: 1.5em;">Browser kamu tidak mendukung fitur lokasi.</span>',
                    confirmButtonColor: '#3085d6'
                });
                lokasiTersedia = false;
            }
        }

        // ambil lokasi saat halaman load
        ambilLokasi();

        // cek lokasi saat klik tombol
        document.querySelector(".simpan_absensi").addEventListener("click", function (e) {
            if (!lokasiTersedia) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-size: 1.5em;">Presensi gagal!</span>',
                    html: '<span style="font-size: 1.5em;">Presensi tidak bisa dilakukan jika lokasi belum diaktifkan.</span>',
                    confirmButtonColor: '#d33'
                });
            }
        });
    </script>
<?php endif; ?>

<script>
    $(document).ready(function () {
        // Tampilkan alasan dan bukti foto jika status dipilih sebagai izin
        $("#status").change(function () {
            if ($(this).val() == "2") {
                $("#text_alasan").show();
                $("#upload_bukti").show();
                $("#alasan").attr("required", true);
                $("#bukti_foto").attr("required", true);
            } else {
                $("#text_alasan").hide();
                $("#upload_bukti").hide();
                $("#alasan").attr("required", false);
                $("#bukti_foto").attr("required", false);
            }

            // 👉 Tambahkan ini untuk WFA
            if ($(this).val() == "5") {
                $("#text_wfa").show();
                $("#bukti_wfa").attr("required", true);
            } else {
                $("#text_wfa").hide();
                $("#bukti_wfa").attr("required", false);
            }
        });

        // Validasi status sebelum submit
        $('.simpan_absensi').on('click', function () {
            if ($("#status").val() === null) {
                Swal.fire({
                    icon: 'error',
                    title: '<span style="font-size: 1.5em;">Oops...</span>',
                    html: '<span style="font-size: 1.5em;">Pilih status sebelum melakukan presensi!</span>',
                    confirmButtonColor: '#3085d6',
                    showConfirmButton: 'true',
                    customClass: {
                        confirmButton: 'swal2-confirm'
                    }
                });
                return false;
            }
        });

        // Pratinjau gambar
        $("#bukti_foto").change(function (event) {
            const file = event.target.files[0]; // Ambil file yang dipilih
            const previewImage = document.getElementById("preview-image");
            const reader = new FileReader();

            if (file) {
                reader.onload = function (e) {
                    previewImage.src = e.target.result; // Set sumber gambar
                    previewImage.style.display = "block"; // Tampilkan gambar
                };
                reader.readAsDataURL(file); // Baca file sebagai DataURL
            } else {
                previewImage.src = ""; // Reset sumber gambar
                previewImage.style.display = "none"; // Sembunyikan gambar
            }
        });

        $("#bukti_wfa").change(function (event) {
            const file = event.target.files[0];
            const previewImage = document.getElementById("preview-image-wfa");
            const allowedImageTypes = ["image/jpeg", "image/png", "image/jpg"];

            if (file) {
                if (allowedImageTypes.includes(file.type)) {
                    // Jika file tipe gambar, tampilkan preview
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Bukan gambar, hide preview
                    previewImage.src = "";
                    previewImage.style.display = "none";
                }
            } else {
                previewImage.src = "";
                previewImage.style.display = "none";
            }
        });
    });
</script>

<script>
    document.getElementById('status').addEventListener('change', function () {
        var status = this.value;
        var absensiSudah = document.getElementById('tombol_hari').getAttribute('data-absensi') === 'sudah';

        if (absensiSudah) return; // Kalau sudah presensi, jangan ubah apapun

        var alertHadir = document.getElementById('alert_hadir');
        var alertIzin = document.getElementById('alert_izin');
        var textAlasan = document.getElementById('text_alasan');
        var textWfa = document.getElementById('text_wfa');

        if (status === "1") { // Hadir
            alertHadir.style.display = "block";
            alertIzin.style.display = "none";
            textAlasan.style.display = "none";
            textWfa.style.display = "none";
        } else if (status === "2") { // Izin
            alertHadir.style.display = "none";
            alertIzin.style.display = "block";
            textAlasan.style.display = "block";
            textWfa.style.display = "none";
        } else if (status === "5") { // WFA
            alertHadir.style.display = "none";
            alertIzin.style.display = "none";
            textAlasan.style.display = "none";
            textWfa.style.display = "block";
        } else {
            alertHadir.style.display = "none";
            alertIzin.style.display = "none";
            textAlasan.style.display = "none";
            textWfa.style.display = "none";
        }
    });
</script>

<?php if ($absensi_sudah): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('alert_sudah_presensi').style.display = 'block';
        });
    </script>
<?php endif; ?>