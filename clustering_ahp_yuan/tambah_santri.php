<?php
include 'db/koneksi.php';

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama'];
    $tempat = $_POST['tempat'];
    $tgl = $_POST['tgl'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];

    $result = $koneksi->query("INSERT INTO tb_santri_2
        (nama_santri,tempat_lahir,tanggal_lahir,alamat,no_hp) 
        VALUES ('$nama','$tempat','$tgl','$alamat','$no_hp')");

    if ($result) {
        $status = "success";
        $message = "Data berhasil disimpan";
    } else {
        $status = "danger";
        $message = "Gagal menyimpan data: " . $koneksi->error;
    }

    echo "<script type='text/javascript'>
        setTimeout(function () { 
            swal({
                title: '$message',
                type: '$status',
                timer: 3200,
                showConfirmButton: true
            });   
        }, 10);  
        window.setTimeout(function(){ 
            window.location.replace('santri.php');
        }, 3200); 
        </script>";
}

// Redirect ke santri.php dengan status dan pesan
header("Location: santri.php?status=$status&message=$message");
exit();
?>