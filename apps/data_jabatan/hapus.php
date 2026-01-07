<?php
session_start();
include '../../config/database.php';

// Fungsi untuk mencegah inputan karakter yang tidak sesuai
function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (isset($_GET['id'])) {
    $id_jabatan = input($_GET['id']); // Mengambil ID jabatan untuk dihapus

    // Ambil informasi admin yang sedang login
    $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan nama variabel session Anda
    $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user = mysqli_fetch_assoc($resultUser);
    $level = $user['level'];

    // Ambil nama admin
    $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
    $admin = mysqli_fetch_assoc($resultAdmin);
    $nama_admin = $admin['nama'];

    // Dapatkan tanggal sekarang dalam format yang diinginkan
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date("Y-m-d H:i:s");

    // Ambil nama jabatan dan unit kerja berdasarkan id_jabatan
    $resultJabatan = mysqli_query($kon, "SELECT nama, unit_kerja FROM tbl_jabatan WHERE id_jabatan = '$id_jabatan'");
    $jabatan = mysqli_fetch_assoc($resultJabatan);
    $nama_jabatan = $jabatan['nama'];
    $unit_kerja = $jabatan['unit_kerja'];

    // Log aktivitas (Hapus data jabatan)
    $aktivitas = "Hapus data jabatan $nama_jabatan ($unit_kerja)";

    // Memulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Query untuk menghapus data jabatan berdasarkan id_jabatan
    $delete_query = mysqli_query($kon, "DELETE FROM tbl_jabatan WHERE id_jabatan = '$id_jabatan'");

    if ($delete_query) {
        // Commit jika berhasil
        mysqli_query($kon, "COMMIT");

        // Simpan aktivitas ke tbl_log_aktivitas
        $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                    VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
        mysqli_query($kon, $sqlLog);

        header("Location: ../../index.php?page=data_jabatan&hapus=berhasil");
    } else {
        // Rollback jika terjadi kesalahan
        mysqli_query($kon, "ROLLBACK");

        // Log aktivitas gagal
        $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                    VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
        mysqli_query($kon, $sqlLog);

        header("Location: ../../index.php?page=data_jabatan&hapus=gagal");
    }
}
?>