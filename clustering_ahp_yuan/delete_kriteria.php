<?php
include 'db/koneksi.php';

if (isset($_GET['id'])) {
    $id_kriteria = $_GET['id'];

    $delete_query = "DELETE FROM tb_kriteria WHERE id_kriteria = $id_kriteria";
    if ($koneksi->query($delete_query)) {
        $status = 'success';
        $message = 'Data berhasil dihapus.';
    } else {
        $status = 'danger';
        $message = 'Gagal menghapus data.';
    }

    header("Location: kriteria.php?status=$status&message=$message");
    exit();
} else {
    // Redirect to kriteria.php if id is not set
    header("Location: kriteria.php");
    exit();
}
?>
