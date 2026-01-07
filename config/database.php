<?php
    $host="localhost";
    $user="root";
    $password="";
    $db="absensi_magang_coba";
    $kon = mysqli_connect($host,$user,$password,$db);
    if (!$kon){
        die("Koneksi gagal:".mysqli_connect_error());
    }
    // Menjalankan pernyataan SQL untuk mengatur zona waktu ke WIB (Jakarta)
    $sql = "SET time_zone = '+07:00'";
    mysqli_query($kon, $sql);
?>