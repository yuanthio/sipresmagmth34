<?php
include '../../config/database.php';
session_start();

// Cek apakah form disubmit
if (isset($_POST['ubah_lokasi'])) {
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $radius = $_POST['radius'];
    $status_aktif = isset($_POST['aktifkan_lokasi']) ? 1 : 0;

    // Ambil data pengguna dari session
    $kode_pengguna = $_SESSION['kode_pengguna'];

    // Ambil level dari tbl_user
    $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
    $result_user = mysqli_query($kon, $query_user);
    $row_user = mysqli_fetch_assoc($result_user);
    $level = $row_user['level'];

    // Ambil nama dari tbl_admin
    $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_admin = mysqli_query($kon, $query_admin);
    $row_admin = mysqli_fetch_assoc($result_admin);
    $nama = $row_admin['nama'];

    // Tanggal dan waktu saat ini
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date('Y-m-d H:i:s');

    // Cek apakah sudah ada data lokasi
    $cek = mysqli_query($kon, "SELECT * FROM tbl_lokasi_presensi LIMIT 1");

    if (mysqli_num_rows($cek) > 0) {
        // Update
        $row = mysqli_fetch_assoc($cek);
        $id_lokasi = $row['id_lokasi_presensi'];
        $query = "UPDATE tbl_lokasi_presensi 
                  SET latitude = '$latitude', longitude = '$longitude', radius = '$radius', status_aktif = '$status_aktif' 
                  WHERE id_lokasi_presensi = '$id_lokasi'";
    } else {
        // Insert
        $query = "INSERT INTO tbl_lokasi_presensi (id_lokasi_presensi, latitude, longitude, radius, status_aktif)
                  VALUES (1, '$latitude', '$longitude', '$radius', '$status_aktif')";
    }

    // Eksekusi query lokasi dan simpan status
    $berhasil = mysqli_query($kon, $query);

    // Catat ke log aktivitas
    $aktivitas = "Mengubah lokasi presensi";
    $status_log = $berhasil ? "berhasil" : "gagal";
    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                  VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status_log')";
    mysqli_query($kon, $log_query);

    // Redirect dengan notifikasi
    if ($berhasil) {
        header("Location: ../../index.php?page=pengaturan&edit_lokasi=berhasil");
    } else {
        header("Location: ../../index.php?page=pengaturan&edit_lokasi=gagal");
    }
    exit;
}
?>
