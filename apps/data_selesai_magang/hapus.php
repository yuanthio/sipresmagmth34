<?php
session_start();
include "../../config/database.php";

// Fungsi untuk mendapatkan waktu sekarang dalam format 'Y-m-d H:i:s' sesuai zona WIB
date_default_timezone_set("Asia/Jakarta");
$tanggal_sekarang = date("Y-m-d H:i:s");

if (isset($_GET['id_suket'])) {
    $id_suket = $_GET['id_suket'];

    // Ambil nama file dan nama mahasiswa dari database sebelum menghapus data
    $query_file = "SELECT file_suket, nama FROM tbl_suket WHERE id_suket = $id_suket";
    $result_file = mysqli_query($kon, $query_file);
    $row_file = mysqli_fetch_assoc($result_file);

    // Pastikan row_file tidak null sebelum mengakses data
    if ($row_file) {
        $file_suket = $row_file['file_suket'];
        $nama_mahasiswa = $row_file['nama']; // Ambil nama mahasiswa

        // Query hapus data berdasarkan id_suket
        $query_hapus = "DELETE FROM tbl_suket WHERE id_suket = $id_suket";
        $result_hapus = mysqli_query($kon, $query_hapus);

        // Dapatkan data pengguna yang sedang login dari tabel tbl_user
        $kode_pengguna = $_SESSION['kode_pengguna']; // Asumsikan session ini menyimpan kode_pengguna admin yang login
        $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
        $result_user = mysqli_query($kon, $query_user);
        $row_user = mysqli_fetch_assoc($result_user);
        $level = $row_user['level'];

        // Dapatkan nama admin dari tabel tbl_admin
        $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
        $result_admin = mysqli_query($kon, $query_admin);
        $row_admin = mysqli_fetch_assoc($result_admin);
        $nama_admin = $row_admin['nama'];

        if ($result_hapus) {
            // Hapus file dari folder uploads/
            $file_path = "uploads/" . $file_suket;
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Commit transaksi
            mysqli_query($kon, "COMMIT");

            // Log aktivitas: berhasil hapus
            $status = "berhasil";
            $aktivitas = "Hapus data akhir magang mahasiswa ($nama_mahasiswa)"; // Menambahkan nama mahasiswa
            $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                          VALUES ('$tanggal_sekarang', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $query_log);

            // Redirect dengan pesan berhasil
            header("Location:../../index.php?page=data_selesai_magang&hapus=berhasil");
        } else {
            // Rollback transaksi
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas: gagal hapus
            $status = "gagal";
            $aktivitas = "Hapus data akhir magang mahasiswa ($nama_mahasiswa)"; // Menambahkan nama mahasiswa
            $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                          VALUES ('$tanggal_sekarang', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $query_log);

            // Redirect dengan pesan gagal
            header("Location:../../index.php?page=data_selesai_magang&hapus=gagal");
        }
    } else {
        // Tangani kasus ketika tidak ditemukan id_suket
        // Redirect dengan pesan gagal
        header("Location:../../index.php?page=data_selesai_magang&hapus=gagal");
    }
} else {
    // Rollback transaksi
    mysqli_query($kon, "ROLLBACK");

    // Log aktivitas: gagal (tidak ada id_suket)
    $status = "gagal";
    $aktivitas = "Hapus data akhir magang mahasiswa"; // Tanpa nama mahasiswa
    $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                  VALUES ('$tanggal_sekarang', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $query_log);

    // Redirect dengan pesan gagal
    header("Location:../../index.php?page=data_selesai_magang&hapus=gagal");
}
?>
