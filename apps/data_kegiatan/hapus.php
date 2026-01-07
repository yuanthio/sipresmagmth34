<?php
// Memulai sesi
session_start();
include '../../config/database.php';

// Ambil informasi pengguna yang sedang login
$kode_pengguna = $_SESSION['kode_pengguna']; // diasumsikan kode_pengguna sudah disimpan dalam sesi
$query_user = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
$data_user = mysqli_fetch_assoc($query_user);
$level = $data_user['level'];

// Ambil nama admin berdasarkan kode_pengguna
$query_admin = mysqli_query($kon, "SELECT nama, kode_admin FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
$data_admin = mysqli_fetch_assoc($query_admin);
$nama_admin = $data_admin['nama'];
$kode_admin = $data_admin['kode_admin'];

// Waktu saat ini (timezone Indonesia WIB)
date_default_timezone_set('Asia/Jakarta');
$tanggal = date('Y-m-d H:i:s');

// Mulai transaksi
mysqli_query($kon, "START TRANSACTION");

$id_kegiatan = $_GET['id_kegiatan'];

// Ambil informasi terkait kegiatan, termasuk foto dan nama mahasiswa
$query_kegiatan = mysqli_query($kon, "SELECT foto, id_mahasiswa FROM tbl_kegiatan WHERE id_kegiatan = '$id_kegiatan'");
$data_kegiatan = mysqli_fetch_assoc($query_kegiatan);
$foto = $data_kegiatan['foto'];
$id_mahasiswa = $data_kegiatan['id_mahasiswa'];

// Ambil nama mahasiswa
$query_mahasiswa = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");
$data_mahasiswa = mysqli_fetch_assoc($query_mahasiswa);
$nama_mahasiswa = $data_mahasiswa['nama'];

// Hapus kegiatan
$hapus_kegiatan = mysqli_query($kon, "DELETE FROM tbl_kegiatan WHERE id_kegiatan = '$id_kegiatan'");

if ($hapus_kegiatan) {
    // Jika penghapusan berhasil, hapus file foto
    $foto_array = explode(",", $foto);
    foreach ($foto_array as $foto_item) {
        $foto_item = trim($foto_item); // Membersihkan spasi
        if ($foto_item && file_exists("../../apps/data_kegiatan/foto_kegiatan/" . $foto_item)) {
            unlink("../../apps/data_kegiatan/foto_kegiatan/" . $foto_item);
        }
    }

    // Log aktivitas berhasil
    $aktivitas = "Hapus data kegiatan mahasiswa ($nama_mahasiswa)";
    $status = "berhasil";
    $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                VALUES ('$tanggal', '$nama_admin', '$level', '$kode_admin', '$aktivitas', '$status')";
    mysqli_query($kon, $sql_log);

    // Commit transaksi
    mysqli_query($kon, "COMMIT");
    header("Location:../../index.php?page=data_kegiatan&hapus=berhasil");
} else {
    // Jika penghapusan gagal, rollback transaksi
    mysqli_query($kon, "ROLLBACK");

    // Log aktivitas gagal
    $aktivitas = "Hapus data kegiatan mahasiswa ($nama_mahasiswa)";
    $status = "gagal";
    $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                VALUES ('$tanggal', '$nama_admin', '$level', '$kode_admin', '$aktivitas', '$status')";
    mysqli_query($kon, $sql_log);

    header("Location:../../index.php?page=data_kegiatan&hapus=gagal");
}
?>
