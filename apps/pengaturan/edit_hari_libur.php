<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mendapatkan kode pengguna dari session
    session_start();
    $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil kode pengguna dari session

    // Mendapatkan level admin
    $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
    $result_user = mysqli_query($kon, $query_user);
    $row_user = mysqli_fetch_assoc($result_user);
    $level = $row_user['level'];

    // Mendapatkan nama admin
    $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_admin = mysqli_query($kon, $query_admin);
    $row_admin = mysqli_fetch_assoc($result_admin);
    $nama = $row_admin['nama'];

    // Mendapatkan tanggal saat ini dengan timezone WIB
    date_default_timezone_set('Asia/Jakarta'); // Set timezone
    $tanggal = date('Y-m-d H:i:s'); // Format: YYYY-MM-DD HH:MM:SS

    // Memperbarui status hari libur
    $statuses = $_POST['status'];
    $berhasil = true; // Menandakan apakah update berhasil

    foreach ($statuses as $day => $status) {
        $query = "UPDATE tbl_hari_libur SET status = '$status' WHERE hari = '$day'";
        if (!mysqli_query($kon, $query)) {
            $berhasil = false; // Jika ada query yang gagal
        }
    }

    // Mencatat aktivitas ke tabel log
    $aktivitas = "Setting pengaturan hari libur";
    $status_log = $berhasil ? "berhasil" : "gagal";
    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status_log')";
    mysqli_query($kon, $log_query);

    // Redirect berdasarkan hasil update
    if ($berhasil) {
        header("Location: ../../index.php?page=pengaturan&edit_hari_libur=berhasil");
    } else {
        header("Location: ../../index.php?page=pengaturan&edit_hari_libur=gagal");
    }
    exit;
}
?>
