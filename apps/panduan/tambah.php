<?php
include "../../config/database.php";
session_start();

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    return number_format($bytes / pow(1024, $pow), $precision, ',', '.') . ' ' . $units[$pow];
}

function getFileExtension($filename) {
    return pathinfo($filename, PATHINFO_EXTENSION);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['jenis_data'], $_POST['level'])) {
        $jenis_data = $_POST['jenis_data'];
        $level = $_POST['level'];

        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][date('N') - 1];
        $tanggal = date('Y-m-d');

        // Dapatkan informasi pengguna yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Kode admin dari sesi
        $query_admin = "SELECT nama, kode_admin FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
        $result_admin = mysqli_query($kon, $query_admin);
        $row_admin = mysqli_fetch_assoc($result_admin);
        $nama = $row_admin['nama'];

        if (isset($_FILES['file_panduan'])) {
            $ukuran_file = $_FILES['file_panduan']['size'];
            $nama_file = $_FILES['file_panduan']['name'];
            $lokasi_file = $_FILES['file_panduan']['tmp_name'];

            $allowed_extensions = ['doc', 'docx', 'pdf'];
            $file_extension = strtolower(getFileExtension($nama_file));

            // Waktu log aktivitas
            date_default_timezone_set("Asia/Jakarta");
            $tanggal_log = date('Y-m-d H:i:s');

            if (!in_array($file_extension, $allowed_extensions)) {
                $aktivitas = "Tambah data panduan ($level) jenis file tidak diizinkan";
                $status = "gagal";

                // Catat aktivitas gagal ke tbl_log_aktivitas
                $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                              VALUES ('$tanggal_log', '$nama', 'Admin', '$kode_pengguna', '$aktivitas', '$status')";
                mysqli_query($kon, $query_log);

                header("Location:../../index.php?page=panduan&add=gagal&reason=jenis_file_tidak_diizinkan");
                exit();
            }

            $ukuran_file_formatted = formatBytes($ukuran_file);
            $folder_upload = "../../apps/panduan/upload/";
            $path_file = $folder_upload . $nama_file;

            $query_cek_data = "SELECT COUNT(*) as jumlah_data FROM tbl_panduan WHERE jenis_data = '$jenis_data' AND level = '$level'";
            $result_cek_data = mysqli_query($kon, $query_cek_data);
            $row_cek_data = mysqli_fetch_assoc($result_cek_data);
            if ($row_cek_data['jumlah_data'] > 0) {
                $aktivitas = "Tambah data panduan ($level) data ganda";
                $status = "gagal";

                // Catat aktivitas gagal ke tbl_log_aktivitas
                $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                              VALUES ('$tanggal_log', '$nama', 'Admin', '$kode_pengguna', '$aktivitas', '$status')";
                mysqli_query($kon, $query_log);

                header("Location:../../index.php?page=panduan&add=gagal&reason=data_ganda");
                exit(); 
            }            

            move_uploaded_file($lokasi_file, $path_file);

            $query_insert = "INSERT INTO tbl_panduan (jenis_data, level, hari, tanggal, ukuran_file, file_panduan) 
                             VALUES ('$jenis_data', '$level', '$hari', '$tanggal', '$ukuran_file_formatted', '$nama_file')";
            $result_insert = mysqli_query($kon, $query_insert);

            if ($result_insert) {
                mysqli_query($kon, "COMMIT");
                $aktivitas = "Tambah data panduan ($level)";
                $status = "berhasil";

                // Catat aktivitas berhasil ke tbl_log_aktivitas
                $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                              VALUES ('$tanggal_log', '$nama', 'Admin', '$kode_pengguna', '$aktivitas', '$status')";
                mysqli_query($kon, $query_log);

                header("Location:../../index.php?page=panduan&add=berhasil");
            } else {
                mysqli_query($kon, "ROLLBACK");
                $aktivitas = "Tambah data panduan ($level) kesalahan saat menyimpan data";
                $status = "gagal";

                // Catat aktivitas gagal ke tbl_log_aktivitas
                $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                              VALUES ('$tanggal_log', '$nama', 'Admin', '$kode_pengguna', '$aktivitas', '$status')";
                mysqli_query($kon, $query_log);

                header("Location:../../index.php?page=panduan&add=gagal");
            }
        }
    }
}
?>

<form action="apps/panduan/tambah.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="form-group">
        <label for="jenis_data">Jenis Data</label>
        <select name="jenis_data" class="form-control" id="jenis_data" required autofocus
            oninvalid="this.setCustomValidity('Harap jenis data di isi terlebih dahulu')"
            oninput="this.setCustomValidity('')">
            <option selected disabled>Pilih Jenis Data</option>
            <option value="Panduan Aplikasi untuk Admin">Panduan Aplikasi untuk Admin</option>
            <option value="Panduan Aplikasi untuk Mentor">Panduan Aplikasi untuk Mentor</option>
            <option value="Panduan Aplikasi untuk Karyawan Magang">Panduan Aplikasi untuk Karyawan Magang</option>
        </select>
    </div>
    <div class="form-group">
        <label for="level">Level :</label>
        <select name="level" class="form-control" id="level" required autofocus
            oninvalid="this.setCustomValidity('Harap level di isi terlebih dahulu')"
            oninput="this.setCustomValidity('')">
            <option selected disabled>Pilih Level</option>
            <option value="Admin">Admin</option>
            <option value="Mentor">Mentor</option>
            <option value="Karyawan Magang">Karyawan Magang</option>
        </select>
    </div>
    <div class="form-group">
        <label for="file_panduan">Unggah File</label>
        <input type="file" name="file_panduan" id="file_panduan" class="form-control" value="" required autofocus
            oninvalid="this.setCustomValidity('Harap file di unggah terlebih dahulu')"
            oninput="this.setCustomValidity('')">
    </div>
    <div class="row">
        <div class="col-sm-12">
            <button class="btn btn-primary" name="tambah_panduan" type="submit"><i class="fa fa-upload"></i>
                Unggah</button>
        </div>
    </div>
</form>

<script>
    const jenisDataSelect = document.getElementById('jenis_data');
    const levelSelect = document.getElementById('level');

    jenisDataSelect.addEventListener('change', function () {
        switch (jenisDataSelect.value) {
            case 'Panduan Aplikasi untuk Admin':
                levelSelect.innerHTML = '<option value="Admin" selected>Admin</option>';
                break;
            case 'Panduan Aplikasi untuk Mentor':
                levelSelect.innerHTML = '<option value="Mentor" selected>Mentor</option>';
                break;
            case 'Panduan Aplikasi untuk Karyawan Magang':
                levelSelect.innerHTML = '<option value="Karyawan Magang" selected>Karyawan Magang</option>';
                break;
            default:
                levelSelect.innerHTML = '<option selected disabled>Pilih Level</option>';
        }
    });

    levelSelect.addEventListener('change', function () {
        switch (levelSelect.value) {
            case 'Admin':
                jenisDataSelect.innerHTML = '<option value="Panduan Aplikasi untuk Admin" selected>Panduan Aplikasi untuk Admin</option>';
                break;
            case 'Mentor':
                jenisDataSelect.innerHTML = '<option value="Panduan Aplikasi untuk Mentor" selected>Panduan Aplikasi untuk Mentor</option>';
                break;
            case 'Karyawan Magang':
                jenisDataSelect.innerHTML = '<option value="Panduan Aplikasi untuk Karyawan Magang" selected>Panduan Aplikasi untuk Karyawan Magang</option>';
                break;
            default:
                jenisDataSelect.innerHTML = '<option selected disabled>Pilih Jenis Data</option>';
        }
    });
</script>
