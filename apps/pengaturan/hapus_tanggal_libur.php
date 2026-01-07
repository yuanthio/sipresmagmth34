<?php
session_start();
include '../../config/database.php';

// Cek apakah ID dikirim
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data tanggal libur sebelum dihapus
    $data = mysqli_fetch_assoc(mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur WHERE id = '$id'"));
    $tanggal_awal  = $data['tanggal_awal'];
    $tanggal_akhir = $data['tanggal_akhir'];
    $alasan_libur  = $data['alasan_libur'];

    // Mulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Hapus data dari database
    $query = mysqli_query($kon, "DELETE FROM tbl_tanggal_libur WHERE id = '$id'");

    // Ambil info user dari session
    $kode_pengguna = $_SESSION['kode_pengguna'];

    // Ambil data user
    $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user = mysqli_fetch_assoc($resultUser);
    $level = $user['level'];

    // Ambil nama admin
    $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
    $admin = mysqli_fetch_assoc($resultAdmin);
    $nama_admin = $admin['nama'];

    // Tanggal dan aktivitas
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date("Y-m-d H:i:s");
    $aktivitas = "Menghapus tanggal libur $alasan_libur dari $tanggal_awal sampai $tanggal_akhir";

    // Logging + Commit/Rollback
    if ($query) {
        mysqli_query($kon, "COMMIT");
        $status_aktivitas = "berhasil";
        header("Location: ../../index.php?page=pengaturan&hapus_tanggal_libur=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        $status_aktivitas = "gagal";
        header("Location: ../../index.php?page=pengaturan&hapus_tanggal_libur=gagal");
    }

    // Simpan log aktivitas
    $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
            VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status_aktivitas')";
    mysqli_query($kon, $log);

} else {
    // Jika tidak ada ID, langsung redirect
    header("Location: ../../index.php?page=pengaturan&hapus_tanggal_libur=gagal");
}
