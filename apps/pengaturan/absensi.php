<?php
// Memulai sesi PHP
session_start();

// Memeriksa apakah tombol "ubah_absen" sudah ditekan
if (isset($_POST['ubah_absen'])) {
    // Include file konfigurasi database
    include '../../config/database.php';

    // Fungsi untuk membersihkan dan melindungi data dari input
    function input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Periksa apakah metode HTTP adalah POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Memulai transaksi database
        mysqli_query($kon, "START TRANSACTION");

        // Ambil nilai ID waktu dari form
        $id_waktu = $_POST["id_waktu"];

        // Ambil dan bersihkan nilai "mulai_absen" dari input form
        $mulai_absen = input($_POST["mulai_absen"]);

        // Ambil dan bersihkan nilai "akhir_absen" dari input form
        $akhir_absen = input($_POST["akhir_absen"]);

        // Query SQL untuk mengupdate data pada tabel "tbl_setting_absensi"
        $sql = "UPDATE tbl_setting_absensi SET
            mulai_absen='$mulai_absen',
            akhir_absen='$akhir_absen'
            WHERE id_waktu=$id_waktu";

        // Mengeksekusi query SQL di atas
        $update_profil_aplikasi = mysqli_query($kon, $sql);

        // Mendapatkan data pengguna yang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil kode pengguna dari session
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

        // Memeriksa apakah query berhasil dieksekusi
        if ($update_profil_aplikasi) {
            // Jika berhasil, commit (simpan) transaksi database
            mysqli_query($kon, "COMMIT");

            // Catat aktivitas ke tabel log
            $aktivitas = "Setting waktu presensi aplikasi";
            $status = "berhasil";
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_query);

            // Redirect ke halaman dengan pesan sukses
            header("Location:../../index.php?page=pengaturan&absen=berhasil");
        } else {
            // Jika gagal, rollback (batalkan) transaksi database
            mysqli_query($kon, "ROLLBACK");

            // Catat aktivitas ke tabel log
            $aktivitas = "Setting Pengaturan waktu presensi";
            $status = "gagal";
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_query);

            // Redirect ke halaman dengan pesan gagal
            header("Location:../../index.php?page=pengaturan&absen=gagal");
        }
    }
}
?>
