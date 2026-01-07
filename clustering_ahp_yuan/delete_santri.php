<?php
include 'db/koneksi.php';

if (isset($_POST['delete'])) {
    $id = $_GET['id'];

    if ($koneksi->query("DELETE FROM tb_santri_2 WHERE id_santri='$id'")) {
        // Jika penghapusan berhasil, atur variabel status
        $status = "success";
        $message = "Data berhasil dihapus";
    } else {
        // Jika penghapusan gagal, atur variabel status dengan "danger"
        $status = "danger";
        $message = "Gagal menghapus data";
    }
}

// Redirect ke santri.php dengan status dan pesan
header("Location: santri.php?status=$status&message=$message");
exit();
?>
