<?php
// Memulai Session
session_start();

// Koneksi database
include '../../config/database.php';

// Memulai transaksi
mysqli_query($kon, "START TRANSACTION");

$id_mentor = $_GET['id_mentor'];
$kode_mentor = $_GET['kode_mentor'];

// Ambil informasi mentor sebelum dihapus (nama dan foto)
$resultMentor = mysqli_query($kon, "SELECT nama, foto FROM tbl_mentor WHERE id_mentor = '$id_mentor'");
$mentor = mysqli_fetch_assoc($resultMentor);
$nama_mentor = $mentor['nama'];
$foto = $mentor['foto'];

// Hapus file foto dari folder jika ada dan bukan default
if (!empty($foto)) {
    $path_foto = "../../apps/pengguna/foto_mentor/" . $foto;
    if (file_exists($path_foto)) {
        unlink($path_foto);
    }
}

// Hapus data dari tabel mentor
$hapus_mentor = mysqli_query($kon, "DELETE FROM tbl_mentor WHERE id_mentor='$id_mentor'");

// Hapus akun pengguna terkait
$hapus_pengguna = mysqli_query($kon, "DELETE FROM tbl_user WHERE kode_pengguna='$kode_mentor'");

// Ambil informasi admin yang sedang login
$kode_pengguna = $_SESSION['kode_pengguna'];
$resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
$user = mysqli_fetch_assoc($resultUser);
$level = $user['level'];

// Ambil nama admin dari tabel admin
$resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
$admin = mysqli_fetch_assoc($resultAdmin);
$nama_admin = $admin['nama'];

// Tanggal dan waktu
date_default_timezone_set('Asia/Jakarta');
$tanggal = date("Y-m-d H:i:s");

// Cek jika penghapusan berhasil
if ($hapus_mentor && $hapus_pengguna) {
    mysqli_query($kon, "COMMIT");

    $aktivitas = "Hapus data mentor ($nama_mentor)";
    mysqli_query($kon, "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')");

    header("Location:../../index.php?page=data_mentor&hapus=berhasil");
} else {
    mysqli_query($kon, "ROLLBACK");

    $aktivitas = "Hapus data mentor ($nama_mentor)";
    mysqli_query($kon, "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')");

    header("Location:../../index.php?page=data_mentor&hapus=gagal");
}
?>
