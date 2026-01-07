<?php
// Include file koneksi ke database
include "../../config/database.php";

// Memulai sesi
session_start();

// Fungsi untuk memformat ukuran file
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Fungsi untuk mendapatkan ekstensi file
function getFileExtension($filename) {
    return pathinfo($filename, PATHINFO_EXTENSION);
}

// Query untuk mengambil data dari tabel tbl_mahasiswa
$query = "SELECT id_mahasiswa, nama, universitas FROM tbl_mahasiswa";
$result = mysqli_query($kon, $query);

// Periksa apakah query berhasil dijalankan
if (!$result) {
    die("Query error: " . mysqli_error($kon));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tangani data yang dikirim dari formulir
    if (isset($_POST['nama'], $_POST['jenis_data'])) {
        $id_mahasiswa = $_POST['nama'];
        $jenis_data = $_POST['jenis_data'];

        // Ambil data mahasiswa terpilih dari tabel tbl_mahasiswa
        $query_mahasiswa = "SELECT nama, universitas FROM tbl_mahasiswa WHERE id_mahasiswa = $id_mahasiswa";
        $result_mahasiswa = mysqli_query($kon, $query_mahasiswa);

        // Periksa apakah query berhasil dijalankan
        if (!$result_mahasiswa) {
            die("Query error: " . mysqli_error($kon));
        }

        $row_mahasiswa = mysqli_fetch_assoc($result_mahasiswa);

        // Variabel $nama dan $universitas diisi dengan data dari tabel tbl_mahasiswa
        $nama = $row_mahasiswa['nama'];
        $universitas = $row_mahasiswa['universitas'];

        // Alternatif untuk mendapatkan nama hari dalam bahasa Indonesia
        $nama_hari = date('N'); 
        $hari = [
            'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ][$nama_hari - 1];

        // Format tanggal sesuai dengan yang diinginkan
        $tanggal = date('Y-m-d');

        // Tangani unggahan file
        if (isset($_FILES['file_suket'])) {
            $ukuran_file = $_FILES['file_suket']['size'];
            $nama_file = $_FILES['file_suket']['name'];
            $lokasi_file = $_FILES['file_suket']['tmp_name'];

            // Validasi ukuran file (maksimal 1MB)
            $ukuran_maksimal = 1 * 1024 * 1024; // 1MB dalam byte

            // Simpan log aktivitas yang sama untuk semua kesalahan
            $aktivitas = "Unggah surat keterangan magang mahasiswa";

            if ($ukuran_file > $ukuran_maksimal) {
                // Simpan log aktivitas untuk status gagal
                $status = "gagal";
                $reason = "ukuran_terlalu_besar";
                $aktivitas = "Tambah data akhir magang mahasiswa ($nama) Ukuran file terlalu besar";
                logActivity($kon, $status, $aktivitas);
                header("Location:../../index.php?page=data_selesai_magang&add=gagal&reason=$reason");
                exit();
            }

            // Validasi jenis file (hanya izinkan doc, docx, atau pdf)
            $allowed_extensions = ['doc', 'docx', 'pdf'];
            $file_extension = strtolower(getFileExtension($nama_file));

            if (!in_array($file_extension, $allowed_extensions)) {
                // Simpan log aktivitas untuk status gagal
                $status = "gagal";
                $reason = "jenis_file_tidak_diizinkan";
                $aktivitas = "Tambah data akhir magang mahasiswa ($nama) Jenis file tidak diizinkan";
                logActivity($kon, $status, $aktivitas);
                header("Location:../../index.php?page=data_selesai_magang&add=gagal&reason=$reason");
                exit();
            }

            // Format ukuran file
            $ukuran_file_formatted = formatBytes($ukuran_file);

            // Proses penyimpanan file
            $folder_upload = "uploads/";  // Sesuaikan dengan folder tempat menyimpan file
            $path_file = $folder_upload . $nama_file;

            // Validasi data tidak boleh ganda
            $query_cek_data = "SELECT COUNT(*) as jumlah_data FROM tbl_suket WHERE nama = '$nama' AND jenis_data = '$jenis_data'";
            $result_cek_data = mysqli_query($kon, $query_cek_data);
            $row_cek_data = mysqli_fetch_assoc($result_cek_data);
            $jumlah_data = $row_cek_data['jumlah_data'];

            if ($jumlah_data > 0) {
                // Simpan log aktivitas untuk status gagal
                $status = "gagal";
                $reason = "data_ganda";
                $aktivitas = "Tambah data akhir magang mahasiswa ($nama) Data sudah ada";
                logActivity($kon, $status, $aktivitas);
                header("Location:../../index.php?page=data_selesai_magang&add=gagal&reason=$reason");
                exit(); 
            }

            move_uploaded_file($lokasi_file, $path_file);

            // Query untuk menyisipkan data ke dalam tabel tbl_suket
            $query_insert = "INSERT INTO tbl_suket (id_mahasiswa, nama, universitas, jenis_data, hari, tanggal, ukuran_file, file_suket) VALUES ('$id_mahasiswa', '$nama', '$universitas', '$jenis_data', '$hari', '$tanggal', '$ukuran_file_formatted', '$nama_file')";

            // Jalankan query
            $result_insert = mysqli_query($kon, $query_insert);

            // Siapkan data log aktivitas
            date_default_timezone_set('Asia/Jakarta');
            $tanggal_log = date('Y-m-d H:i:s'); // Tanggal sekarang
            $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil dari sesi
            $level = 'Admin'; // Level admin

            if ($result_insert) {
                // Jika penyimpanan berhasil
                $aktivitas = "Tambah data akhir magang mahasiswa ($nama) $jenis_data";
                $status = "berhasil";
                mysqli_query($kon, "COMMIT");
                header("Location:../../index.php?page=data_selesai_magang&add=berhasil");
            } else {
                // Jika penyimpanan gagal
                $status = "gagal";
                $aktivitas = "Tambah data akhir magang mahasiswa ($nama) Kesalahan penyimpanan data";
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=data_selesai_magang&add=gagal");
            }

            // Simpan log aktivitas ke dalam tbl_log_aktivitas
            logActivity($kon, $status, $aktivitas);
        }
    }
}

function logActivity($kon, $status, $aktivitas) {
    date_default_timezone_set('Asia/Jakarta');
    $tanggal_log = date('Y-m-d H:i:s'); // Tanggal sekarang
    $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil dari sesi
    $level = 'Admin'; // Level admin

    // Query untuk ambil nama admin
    $query_nama_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_nama_admin = mysqli_query($kon, $query_nama_admin);
    $row_admin = mysqli_fetch_assoc($result_nama_admin);
    $nama_admin = $row_admin['nama'];

    // Simpan log aktivitas ke dalam tbl_log_aktivitas
    $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_log', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $query_log);
}
?>

<!-- Formulir -->
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="form-group">
        <label for="nama">Nama Karyawan Magang :</label>
        <select name="nama" class="form-control" id="nama" required autofocus
            oninvalid="this.setCustomValidity('Harap unit nama di isi terlebih dahulu')"
            oninput="this.setCustomValidity('')">
            <option selected disabled>Pilih Karyawan Magang</option>

            <?php
            // Loop untuk menampilkan opsi dari hasil query
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row['id_mahasiswa'] . '">' . $row['nama'] . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="jenis_data">Jenis Data :</label>
        <select name="jenis_data" class="form-control" id="jenis_data" required autofocus
            oninvalid="this.setCustomValidity('Harap jenis data di isi terlebih dahulu')"
            oninput="this.setCustomValidity('')">
            <option selected disabled>Pilih Jenis Data</option>
            <option value="Surat Keterangan">Surat Keterangan</option>
            <option value="Sertifikat">Sertifikat</option>
            <option value="Penilaian Kinerja">Penilaian Kinerja</option>
        </select>
    </div>
    <div class="form-group">
        <label for="file_suket">Unggah File (Maksimal 1MB):</label>
        <input type="file" name="file_suket" id="file_suket" class="form-control" value="" required autofocus
            oninvalid="this.setCustomValidity('Harap file di unggah terlebih dahulu')"
            oninput="this.setCustomValidity('')">
    </div>
    <div class="row">
        <div class="col-sm-12">
            <button class="btn btn-primary" name="tambah_suket" type="submit"><i class="fa fa-upload"></i>
                Unggah</button>
        </div>
    </div>
</form>
