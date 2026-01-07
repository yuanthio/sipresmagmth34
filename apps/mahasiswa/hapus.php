<?php
session_start();
include '../../config/database.php';

// Fungsi untuk mendapatkan waktu sekarang dengan timezone WIB
date_default_timezone_set('Asia/Jakarta');
$tanggal_sekarang = date('Y-m-d H:i:s');

// Mendapatkan data mahasiswa yang akan dihapus
$id_mahasiswa = $_GET['id_mahasiswa'];
$kode_mahasiswa = $_GET['kode_mahasiswa'];

// Ambil nama mahasiswa yang akan dihapus
$query_mahasiswa = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");
$data_mahasiswa = mysqli_fetch_assoc($query_mahasiswa);
$nama_mahasiswa = $data_mahasiswa['nama']; // Nama mahasiswa yang akan dihapus

// Mendapatkan informasi pengguna yang sedang login (level Admin)
$kode_pengguna_login = $_SESSION['kode_pengguna']; // Kode pengguna yang login dari session
$query_user = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna_login'");
$data_user = mysqli_fetch_assoc($query_user);
$level_login = $data_user['level']; // Level pengguna yang login

// Mendapatkan nama admin yang sedang login dari tbl_admin
$query_admin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna_login'");
$data_admin = mysqli_fetch_assoc($query_admin);
$nama_admin = $data_admin['nama']; // Nama admin yang sedang login

mysqli_query($kon, "START TRANSACTION");

// Hapus data mahasiswa terlebih dahulu
$hapus_mahasiswa = mysqli_query($kon, "DELETE FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");

if ($hapus_mahasiswa) {
    // Jika mahasiswa berhasil dihapus, baru hapus pengguna
    $hapus_pengguna = mysqli_query($kon, "DELETE FROM tbl_user WHERE kode_pengguna = '$kode_mahasiswa'");
    
    if ($hapus_pengguna) {
        // Jika pengguna juga berhasil dihapus, lakukan COMMIT
        mysqli_query($kon, "COMMIT");

        // Catat aktivitas ke dalam tabel log
        $status = "berhasil";
        $aktivitas = "Hapus data laporan magang mahasiswa ($nama_mahasiswa)";
        $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal_sekarang', '$nama_admin', '$level_login', '$kode_pengguna_login', '$aktivitas', '$status')";
        mysqli_query($kon, $sql_log);

        header("Location:../../index.php?page=mahasiswa&hapus=berhasil");
    } else {
        // Jika gagal menghapus pengguna, lakukan ROLLBACK
        mysqli_query($kon, "ROLLBACK");

        // Catat aktivitas gagal ke dalam tabel log
        $status = "gagal";
        $aktivitas = "Hapus data laporan magang mahasiswa ($nama_mahasiswa)";
        $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal_sekarang', '$nama_admin', '$level_login', '$kode_pengguna_login', '$aktivitas', '$status')";
        mysqli_query($kon, $sql_log);

        header("Location:../../index.php?page=mahasiswa&hapus=gagal");
    }
} else {
    // Jika gagal menghapus mahasiswa, lakukan ROLLBACK
    mysqli_query($kon, "ROLLBACK");

    // Catat aktivitas gagal ke dalam tabel log
    $status = "gagal";
    $aktivitas = "Hapus data laporan magang mahasiswa ($nama_mahasiswa)";
    $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                VALUES ('$tanggal_sekarang', '$nama_admin', '$level_login', '$kode_pengguna_login', '$aktivitas', '$status')";
    mysqli_query($kon, $sql_log);

    header("Location:../../index.php?page=mahasiswa&hapus=gagal");
}
?>
