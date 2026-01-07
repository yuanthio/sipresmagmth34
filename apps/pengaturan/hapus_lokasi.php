<?php
include '../../config/database.php';

// Hapus semua data dari tabel lokasi presensi
$query = mysqli_query($kon, "DELETE FROM tbl_lokasi_presensi");

if ($query) {
    header("Location: ../../index.php?page=pengaturan&hapus_lokasi=berhasil");
} else {
    echo "Gagal menghapus data lokasi.";
}
?>
