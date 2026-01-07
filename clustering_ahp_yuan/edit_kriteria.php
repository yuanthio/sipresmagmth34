<?php
include 'db/koneksi.php';
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $koneksi->query("UPDATE tb_kriteria SET jenis_kriteria='$_POST[nama]' WHERE id_kriteria='$id'");

    if ($result) {
        $status = "success";
        $message = "Data berhasil diedit";
    } else {
        $status = "danger";
        $message = "Gagal mengedit data";
    }
}

// Redirect ke santri.php dengan status dan pesan
header("Location: kriteria.php?status=$status&message=$message");
exit();
?>