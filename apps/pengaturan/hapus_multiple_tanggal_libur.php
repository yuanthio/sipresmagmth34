<?php
session_start();
include '../../config/database.php';

// Cek apakah ada parameter 'ids'
if (isset($_GET['ids'])) {
    $ids = $_GET['ids'];
    
    // Pisahkan ID yang dipilih menjadi array
    $idArray = explode(',', $ids);
    $idArray = array_map('intval', $idArray); // Sanitasi angka

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

    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date("Y-m-d H:i:s");

    // Mulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Ambil data libur yang akan dihapus untuk log
    $dataLibur = mysqli_query($kon, "SELECT tanggal_awal, tanggal_akhir, alasan_libur FROM tbl_tanggal_libur WHERE id IN (" . implode(',', $idArray) . ")");
    $detailLibur = [];
    while ($row = mysqli_fetch_assoc($dataLibur)) {
        $detailLibur[] = $row['alasan_libur'] . " (" . $row['tanggal_awal'] . " s/d " . $row['tanggal_akhir'] . ")";
    }
    $aktivitas = "Menghapus beberapa tanggal libur: " . implode("; ", $detailLibur);

    // Hapus data
    $query = "DELETE FROM tbl_tanggal_libur WHERE id IN (" . implode(',', $idArray) . ")";
    $delete = mysqli_query($kon, $query);

    // Logging + Commit/Rollback
    if ($delete) {
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
    // Jika tidak ada ID yang dipilih
    header("Location: ../../index.php?page=pengaturan&hapus_tanggal_libur=gagal");
}
