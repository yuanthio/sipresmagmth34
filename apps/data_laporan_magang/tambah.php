<?php
include "../../config/database.php";
session_start();

function getHariIndonesia($day) {
    $hari = array(
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );
    return $hari[$day];
}

function formatSizeUnits($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $bytes /= 1024, $i++);
    return round($bytes, 2) . ' ' . $units[$i];
}

// Fungsi untuk mencatat log aktivitas
function catatLogAktivitas($kon, $kode_pengguna, $level, $aktivitas, $status) {
    // Ambil nama admin dari tbl_admin berdasarkan kode_pengguna
    $nama_admin = '';
    $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_admin = mysqli_query($kon, $query_admin);
    if ($data_admin = mysqli_fetch_assoc($result_admin)) {
        $nama_admin = $data_admin['nama'];
    }

    // Format tanggal untuk log
    date_default_timezone_set('Asia/Jakarta');
    $tanggal_log = date('Y-m-d H:i:s'); // Format tanggal dan waktu sekarang

    // Insert log aktivitas
    $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_log', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $query_log);
}

if (isset($_POST['tambah_laporan'])) {
    $kode_mahasiswa = $_POST['nama'];
    $file_laporan = $_FILES['file_laporan'];
    $kode_pengguna = $_SESSION['kode_pengguna']; // Pastikan ini sudah diset saat login
    $level = $_SESSION['level']; // Ambil level dari session

    // Ambil nama mahasiswa
    $sql_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$kode_mahasiswa'";
    $result_mahasiswa = mysqli_query($kon, $sql_mahasiswa);
    $mahasiswa_data = mysqli_fetch_assoc($result_mahasiswa);
    $nama_mahasiswa = $mahasiswa_data['nama'];

    $aktivitas = "Tambah data laporan magang mahasiswa ($nama_mahasiswa)";
    $target_dir = "../../apps/data_laporan_magang/upload/";
    $target_file = $target_dir . basename($file_laporan["name"]);

    // Cek ukuran file
    if ($file_laporan["size"] > 1048576) {
        $_SESSION['alert'] = array('type' => 'error', 'title' => 'Gagal!', 'message' => 'Ukuran file harus kurang dari 1MB');
        
        // Catat log aktivitas gagal karena ukuran file
        catatLogAktivitas($kon, $kode_pengguna, $level, "$aktivitas Ukuran file terlalu besar", 'gagal');
        
        header("Location: ../../index.php?page=data_laporan_magang");
        exit;
    }

    // Cek format file
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = array("doc", "docx", "pdf");
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['alert'] = array('type' => 'error', 'title' => 'Gagal!', 'message' => 'Format file harus doc, docx, atau pdf');
        
        // Catat log aktivitas gagal karena format file tidak sesuai
        catatLogAktivitas($kon, $kode_pengguna, $level, "$aktivitas Format file tidak sesuai", 'gagal');
        
        header("Location: ../../index.php?page=data_laporan_magang");
        exit;
    }

    // Jika file berhasil diunggah
    if (move_uploaded_file($file_laporan["tmp_name"], $target_file)) {
        $query_mahasiswa = "SELECT kode_mahasiswa, nama, universitas FROM tbl_mahasiswa WHERE id_mahasiswa = '$kode_mahasiswa'";
        $result_mahasiswa = mysqli_query($kon, $query_mahasiswa);
        $mahasiswa = mysqli_fetch_assoc($result_mahasiswa);

        if ($mahasiswa) {
            $kode_mahasiswa = $mahasiswa['kode_mahasiswa'];
            $nama = $mahasiswa['nama'];
            $universitas = $mahasiswa['universitas'];
            $hari = getHariIndonesia(date('l'));
            $tanggal = date('Y-m-d');
            $ukuran_file = formatSizeUnits($file_laporan["size"]);
            $nama_file = basename($file_laporan["name"]);

            // Cek apakah laporan sudah ada
            $query_check = "SELECT * FROM tbl_laporan WHERE kode_mahasiswa = '$kode_mahasiswa'";
            $result_check = mysqli_query($kon, $query_check);

            if (mysqli_num_rows($result_check) > 0) {
                $_SESSION['alert'] = array('type' => 'error', 'title' => 'Gagal!', 'message' => 'Laporan karyawan magang terkait sudah ada');
                
                // Catat log aktivitas gagal karena laporan sudah ada
                catatLogAktivitas($kon, $kode_pengguna, $level, "$aktivitas Laporan sudah ada", 'gagal');
                
                header("Location: ../../index.php?page=data_laporan_magang");
                exit;
            }

            // Query untuk menambahkan laporan
            $query_insert = "INSERT INTO tbl_laporan (nama, universitas, hari, tanggal, ukuran_file, file_laporan, kode_mahasiswa) 
                             VALUES ('$nama', '$universitas', '$hari', '$tanggal', '$ukuran_file', '$nama_file', '$kode_mahasiswa')";
            if (mysqli_query($kon, $query_insert)) {
                $_SESSION['alert'] = array('type' => 'success', 'title' => 'Berhasil!', 'message' => 'Menambah data laporan magang berhasil');
                
                // Catat log aktivitas berhasil
                catatLogAktivitas($kon, $kode_pengguna, $level, $aktivitas, 'berhasil');
            } else {
                $_SESSION['alert'] = array('type' => 'error', 'title' => 'Gagal!', 'message' => 'Menambah data laporan magang gagal');
                
                // Catat log aktivitas gagal karena kegagalan query
                catatLogAktivitas($kon, $kode_pengguna, $level, "$aktivitas Kegagalan query", 'gagal');
            }
        } else {
            $_SESSION['alert'] = array('type' => 'error', 'title' => 'Gagal!', 'message' => 'Mahasiswa tidak ditemukan');
            
            // Catat log aktivitas gagal karena mahasiswa tidak ditemukan
            catatLogAktivitas($kon, $kode_pengguna, $level, "$aktivitas Mahasiswa tidak ditemukan", 'gagal');
        }
    } else {
        $_SESSION['alert'] = array('type' => 'error', 'title' => 'Gagal!', 'message' => 'Mengunggah file gagal');
        
        // Catat log aktivitas gagal karena kegagalan upload file
        catatLogAktivitas($kon, $kode_pengguna, $level, "$aktivitas Kegagalan upload file", 'gagal');
    }

    header("Location: ../../index.php?page=data_laporan_magang");
    exit;
}
?>

<form action="apps/data_laporan_magang/tambah.php" method="post" enctype="multipart/form-data" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="form-group">
        <label for="nama">Nama Karyawan Magang:</label>
        <select name="nama" class="form-control" id="nama" required>
            <option selected disabled>Pilih Karyawan Magang</option>
            <?php
            include "../../config/database.php";
            $result = mysqli_query($kon, "SELECT id_mahasiswa, nama FROM tbl_mahasiswa");
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row['id_mahasiswa'] . '">' . $row['nama'] . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="file_laporan">Unggah File (Maksimal 1MB):</label>
        <input type="file" name="file_laporan" id="file_laporan" class="form-control" required>
    </div>
    <button class="btn btn-success" name="tambah_laporan" type="submit"><i class="fa fa-upload"></i> Upload</button>
    <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
</form>
