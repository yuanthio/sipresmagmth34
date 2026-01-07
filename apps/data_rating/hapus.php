<?php
session_start();
include '../../config/database.php';

if (isset($_GET['id_rating'])) {
    $id_rating = $_GET['id_rating'];

    // Ambil data rating sebelum dihapus
    $result = mysqli_query($kon, "SELECT * FROM tbl_rating WHERE id_rating='$id_rating'");
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        $kode_pengguna_dihapus = $data['kode_pengguna'];
        $nama_dihapus = $data['nama'];
        $level_dihapus = $data['level'];
        $rating_dihapus = $data['rating'];

        // Hapus dari tabel rating
        $hapus = mysqli_query($kon, "DELETE FROM tbl_rating WHERE id_rating='$id_rating'");

        // Log aktivitas
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d H:i:s');
        $kode_pengguna = $_SESSION['kode_pengguna'];

        // Ambil info user yang melakukan penghapusan
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level_user = $user['level'];

        $resultNama = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultNama);
        $nama_admin = $admin['nama'];

        $aktivitas = "Menghapus rating untuk $nama_dihapus dengan nilai $rating_dihapus ($level_dihapus)";
        $status = $hapus ? "berhasil" : "gagal";

        $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                   VALUES ('$tanggal', '$nama_admin', '$level_user', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $sqlLog);
    }

    // Redirect kembali ke halaman rating
    header("Location:../../index.php?page=data_rating&hapus=" . ($hapus ? "berhasil" : "gagal"));
    exit;
}
?>
