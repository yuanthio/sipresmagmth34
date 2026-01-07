<?php
include "../../config/database.php"; // Sertakan konfigurasi database
session_start();

// Set timezone Indonesia
date_default_timezone_set("Asia/Jakarta");

// Mendapatkan detail admin yang login
$kode_pengguna = $_SESSION['kode_pengguna']; // Misal kode_pengguna disimpan di session setelah login
$query_admin = "SELECT * FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
$result_admin = mysqli_query($kon, $query_admin);
$data_admin = mysqli_fetch_assoc($result_admin);

// Mendapatkan informasi admin yang login
$nama_admin = $data_admin['nama'];
$level = 'Admin'; // Level pengguna yang login adalah admin

if (isset($_GET['id_panduan'])) {
    $id_panduan = $_GET['id_panduan'];

    // Query untuk mengambil nama file sebelum penghapusan
    $query = "SELECT file_panduan, level FROM tbl_panduan WHERE id_panduan = '$id_panduan'";
    $result = mysqli_query($kon, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $file_panduan = $row['file_panduan'];
        $file_path = "../../apps/panduan/upload/" . $file_panduan;

        // Hapus catatan dari database
        $query_delete = "DELETE FROM tbl_panduan WHERE id_panduan = '$id_panduan'";
        if (mysqli_query($kon, $query_delete)) {
            // Jika penghapusan catatan berhasil, periksa dan hapus file
            if (file_exists($file_path)) {
                unlink($file_path); // Hapus file
            }

            // Catat aktivitas penghapusan berhasil
            $tanggal = date("Y-m-d H:i:s");
            $aktivitas = "Hapus data panduan (" . $row['level'] . ")";
            $status = "berhasil";

            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_query);

            header("Location: ../../index.php?page=panduan&add=berhasil");
        } else {
            // Catat aktivitas penghapusan gagal
            $tanggal = date("Y-m-d H:i:s");
            $aktivitas = "Hapus data panduan (" . $row['level'] . ") gagal menghapus dari database";
            $status = "gagal";

            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_query);

            header("Location: ../../index.php?page=panduan&add=gagal");
        }
    } else {
        // Catat aktivitas penghapusan gagal karena data tidak ditemukan
        $tanggal = date("Y-m-d H:i:s");
        $aktivitas = "Hapus data panduan (level tidak diketahui) data tidak ditemukan";
        $status = "gagal";

        $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $log_query);

        header("Location: ../../index.php?page=panduan&delete=gagal&reason=data_tidak_ditemukan");
    }
} else {
    // Catat aktivitas penghapusan gagal karena parameter tidak valid
    $tanggal = date("Y-m-d H:i:s");
    $aktivitas = "Hapus data panduan (level tidak diketahui) parameter tidak valid";
    $status = "gagal";

    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $log_query);

    header("Location: ../../index.php?page=panduan&add=gagal");
}
?>

