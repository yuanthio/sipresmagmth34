<?php
include 'db/koneksi.php';
$id = $_GET['id'];

$koneksi->query("UPDATE tb_santri_2 set kreatifitas='$_POST[kreatifitas]', loyalitas='$_POST[loyalitas]', kepribadian='$_POST[kepribadian]', tanggung_jawab='$_POST[kehadiran]', kehadiran='$_POST[tanggung_jawab]', keahlian='$_POST[keahlian]'  where id_santri='$id'");

// Set alert
$status = 'success'; // Ganti dengan warna alert yang diinginkan (primary, success, danger, dll.)
$message = 'Data Berhasil Diedit';

// Redirect ke nilai.php dengan parameter status dan message
header("Location: nilai.php?status=$status&message=$message");
exit();
?>