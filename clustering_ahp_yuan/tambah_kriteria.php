<?php
include 'db/koneksi.php';

if (isset($_POST['add'])) {
    $insert_query = "INSERT INTO tb_kriteria (jenis_kriteria) VALUES ('$_POST[jenis_kriteria]')";
    if ($koneksi->query($insert_query)) {
        $status = 'success';
        $message = 'Data berhasil tersimpan.';
    } else {
        $status = 'danger';
        $message = 'Gagal menyimpan data.';
    }

    header("Location: kriteria.php?status=$status&message=$message");
    exit();
}
?>
