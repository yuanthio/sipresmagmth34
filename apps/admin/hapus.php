<?php
session_start();

// Koneksi database
include '../../config/database.php';

// Memulai transaksi
mysqli_query($kon, "START TRANSACTION");

$id_admin = $_GET['id_admin'];
$kode_admin = $_GET['kode_admin'];

// Ambil nama administrator yang akan dihapus untuk keperluan log
$resultAdminToDelete = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE id_admin='$id_admin'");
$adminToDelete = mysqli_fetch_assoc($resultAdminToDelete);
$nama_admin_dihapus = $adminToDelete['nama'];

// Menghapus data dalam tabel admin
$hapus_admin = mysqli_query($kon, "DELETE FROM tbl_admin WHERE id_admin='$id_admin'");
// Menghapus data dalam tabel pengguna
$hapus_pengguna = mysqli_query($kon, "DELETE FROM tbl_user WHERE kode_pengguna='$kode_admin'");

// Ambil informasi admin yang sedang login
$kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan session yang Anda gunakan
$resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
$user = mysqli_fetch_assoc($resultUser);
$level = $user['level'];

// Ambil nama administrator yang sedang login
$resultAdminLogin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
$adminLogin = mysqli_fetch_assoc($resultAdminLogin);
$nama_admin_login = $adminLogin['nama'];

// Dapatkan tanggal sekarang dalam format waktu Indonesia
date_default_timezone_set('Asia/Jakarta');
$tanggal = date("Y-m-d H:i:s");

// Jika berhasil menghapus data admin dan pengguna
if ($hapus_admin && $hapus_pengguna) {
    mysqli_query($kon, "COMMIT");

    // Simpan aktivitas ke tbl_log_aktivitas
    $aktivitas = "Hapus data administrator ($nama_admin_dihapus)";
    $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
               VALUES ('$tanggal', '$nama_admin_login', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
    mysqli_query($kon, $sqlLog);

    header("Location:../../index.php?page=admin&hapus=berhasil");
} else {
    mysqli_query($kon, "ROLLBACK");

    // Log aktivitas gagal
    $aktivitas = "Hapus data administrator ($nama_admin_dihapus)";
    $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
               VALUES ('$tanggal', '$nama_admin_login', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
    mysqli_query($kon, $sqlLog);

    header("Location:../../index.php?page=admin&hapus=gagal");
}
?>
