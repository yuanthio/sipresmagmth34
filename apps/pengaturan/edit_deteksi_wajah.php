<?php
include '../../config/database.php';
session_start();

if (isset($_POST['ubah_pengaturan_kamera'])) {
    // Ambil nilai checkbox, default 0 jika tidak dicentang
    $kamera_perangkat = isset($_POST['kamera_perangkat']) ? 1 : 0;
    $deteksi_wajah = isset($_POST['deteksi_wajah']) ? 1 : 0;

    $berhasil = true;
    $kamera_lama = 0;
    $deteksi_lama = 0;

    // Cek apakah ada data sebelumnya di tbl_kamera
    $cek = mysqli_query($kon, "SELECT * FROM tbl_kamera LIMIT 1");
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        $id_kamera = $data['id_kamera'];
        $kamera_lama = $data['kamera_perangkat'];
        $deteksi_lama = $data['deteksi_wajah'];

        // Update data
        $update = mysqli_query($kon, "UPDATE tbl_kamera SET kamera_perangkat='$kamera_perangkat', deteksi_wajah='$deteksi_wajah' WHERE id_kamera='$id_kamera'");
        if (!$update) $berhasil = false;
    } else {
        // Insert data baru
        $insert = mysqli_query($kon, "INSERT INTO tbl_kamera (kamera_perangkat, deteksi_wajah) VALUES ('$kamera_perangkat', '$deteksi_wajah')");
        if (!$insert) $berhasil = false;
    }

    // Ambil info pengguna dari session
    $kode_pengguna = $_SESSION['kode_pengguna'];

    // Ambil level dari tbl_user
    $result_user = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $row_user = mysqli_fetch_assoc($result_user);
    $level = $row_user['level'];

    // Ambil nama dari tbl_admin
    $result_admin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
    $row_admin = mysqli_fetch_assoc($result_admin);
    $nama = $row_admin['nama'];

    // Tanggal saat ini
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date('Y-m-d H:i:s');

    // Tentukan log aktivitas berdasarkan perubahan
    $aktivitas = [];

    if ($kamera_perangkat != $kamera_lama) {
        $aktivitas[] = "Kamera " . ($kamera_perangkat == 1 ? "diaktifkan" : "dinonaktifkan");
    }

    if ($deteksi_wajah != $deteksi_lama) {
        $aktivitas[] = "Deteksi wajah " . ($deteksi_wajah == 1 ? "diaktifkan" : "dinonaktifkan");
    }

    if (count($aktivitas) == 0) {
        $aktivitas_log = "Tidak ada perubahan pengaturan kamera atau deteksi wajah";
    } else {
        $aktivitas_log = implode(', ', $aktivitas);
    }

    $status_log = $berhasil ? "berhasil" : "gagal";

    // Simpan log aktivitas
    mysqli_query($kon, "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas_log', '$status_log')");

    // Redirect
    if ($berhasil) {
        header("Location: ../../index.php?page=pengaturan&edit_kamera=berhasil");
    } else {
        header("Location: ../../index.php?page=pengaturan&edit_kamera=gagal");
    }
    exit;
} else {
    header("Location: ../../index.php?page=pengaturan");
    exit;
}
?>
