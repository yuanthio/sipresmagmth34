<?php
// Include file konfigurasi database
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data menu dan status dari POST
    $menus = $_POST['menu'];
    $statuses = $_POST['status'];

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

    $berhasil = true; // Menandakan apakah update berhasil

    foreach ($menus as $id_menu => $menu) {
        // Prevent SQL Injection
        $id_menu = mysqli_real_escape_string($kon, $id_menu);
        $menu = mysqli_real_escape_string($kon, htmlspecialchars($menu));
        $status = mysqli_real_escape_string($kon, htmlspecialchars($statuses[$id_menu]));

        // Update menu ke database
        $query_update = "UPDATE tbl_setting_menu SET menu = '$menu', status = '$status' WHERE id_menu = $id_menu";
        $result_update = mysqli_query($kon, $query_update);

        // Periksa hasil update
        if (!$result_update) {
            $berhasil = false; // Jika ada update yang gagal
            break; // Keluar dari loop jika ada kesalahan
        }
    }

    // Mencatat aktivitas ke tabel log
    $aktivitas = "Setting pengaturan menu aplikasi";
    $status_log = $berhasil ? "berhasil" : "gagal";
    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status_log')";
    mysqli_query($kon, $log_query);

    // Redirect berdasarkan hasil update
    if ($berhasil) {
        header("Location: ../../index.php?page=pengaturan&menu_edit=berhasil");
    } else {
        header("Location: ../../index.php?page=pengaturan&menu_edit=gagal");
    }
    exit;
} else {
    // Jika bukan POST request, redirect ke halaman pengaturan
    header("Location: ../../pengaturan.php");
    exit;
}
?>
