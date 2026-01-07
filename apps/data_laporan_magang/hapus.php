<?php
// Memulai sesi
session_start();
date_default_timezone_set('Asia/Jakarta');

// Memasukkan file konfigurasi database
include '../../config/database.php';

// Mulai transaksi database
mysqli_query($kon, "START TRANSACTION");

// Mengambil ID laporan yang akan dihapus dari parameter URL
$id_laporan = $_GET['id_laporan'];

// Mengambil informasi file yang akan dihapus
$queryGetFile = "SELECT file_laporan, nama FROM tbl_laporan WHERE id_laporan = '$id_laporan'";
$resultGetFile = mysqli_query($kon, $queryGetFile);
$dataFile = mysqli_fetch_assoc($resultGetFile);
$fileLaporan = $dataFile['file_laporan'];
$nama_mahasiswa = $dataFile['nama'];

// Mengecek apakah file ada di dalam folder 'upload'
$file_path = '../../apps/data_laporan_magang/upload/' . $fileLaporan; // Pastikan path benar
if (file_exists($file_path)) {
    // Menghapus file di dalam folder 'upload'
    unlink($file_path);
}

// Menghapus data laporan dari tabel tbl_laporan
$hapus_laporan = mysqli_query($kon, "DELETE FROM tbl_laporan WHERE id_laporan = '$id_laporan'");

// Mengecek apakah penghapusan laporan berhasil
if ($hapus_laporan) {
    // Jika berhasil, lakukan COMMIT (simpan perubahan)
    mysqli_query($kon, "COMMIT");
    
    // Pencatatan aktivitas ke tabel tbl_log_aktivitas
    $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil kode pengguna dari session
    $level = $_SESSION['level']; // Ambil level dari session
    $nama_admin = ''; // Inisialisasi variabel nama admin

    // Ambil nama admin dari tbl_admin berdasarkan kode_admin
    $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_admin = mysqli_query($kon, $query_admin);
    if ($data_admin = mysqli_fetch_assoc($result_admin)) {
        $nama_admin = $data_admin['nama'];
    }

    // Format tanggal untuk log
    $tanggal_log = date('Y-m-d H:i:s'); // Format tanggal dan waktu sekarang

    // Insert log aktivitas dengan nama mahasiswa
    $aktivitas = "Hapus data laporan magang mahasiswa ($nama_mahasiswa)";
    $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_log', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
    mysqli_query($kon, $query_log);

    // Redirect kembali ke halaman data laporan dengan pesan "berhasil"
    header("Location:../../index.php?page=data_laporan_magang&hapus=berhasil");
} else {
    // Jika gagal menghapus laporan, lakukan ROLLBACK (batalkan perubahan)
    mysqli_query($kon, "ROLLBACK");

    // Pencatatan aktivitas gagal
    $kode_pengguna = $_SESSION['kode_pengguna'];
    $level = $_SESSION['level'];
    $nama_admin = ''; 

    // Ambil nama admin dari tbl_admin
    $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_admin = mysqli_query($kon, $query_admin);
    if ($data_admin = mysqli_fetch_assoc($result_admin)) {
        $nama_admin = $data_admin['nama'];
    }

    // Format tanggal untuk log
    $tanggal_log = date('Y-m-d H:i:s');

    // Insert log aktivitas dengan nama mahasiswa
    $aktivitas = "Hapus data laporan magang mahasiswa ($nama_mahasiswa)";
    $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_log', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
    mysqli_query($kon, $query_log);

    // Redirect kembali ke halaman data laporan dengan pesan "gagal"
    header("Location:../../index.php?page=data_laporan_magang&hapus=gagal");
}
?>
