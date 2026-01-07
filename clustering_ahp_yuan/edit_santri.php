<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
include 'db/koneksi.php';
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $koneksi->query("UPDATE tb_santri_2 SET 
        nama_santri='$_POST[nama]', 
        tempat_lahir='$_POST[tempat]', 
        tanggal_lahir='$_POST[tgl]', 
        alamat='$_POST[alamat]', 
        no_hp='$_POST[no_hp]' 
        WHERE id_santri='$id'");

    if ($result) {
        $status = "success";
        $message = "Data berhasil diedit";
    } else {
        $status = "danger";
        $message = "Gagal mengedit data";
    }
}

// Redirect ke santri.php dengan status dan pesan
header("Location: santri.php?status=$status&message=$message");
exit();
?>