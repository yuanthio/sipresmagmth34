<?php
include '../../config/database.php';

if (isset($_POST['level'])) {
    $level = $_POST['level'];
    $names = [];

    if ($level == 'Admin') {
        $result = mysqli_query($kon, "SELECT kode_admin AS kode, nama FROM tbl_admin");
    } elseif ($level == 'Mahasiswa') {
        $result = mysqli_query($kon, "SELECT kode_mahasiswa AS kode, nama FROM tbl_mahasiswa");
    } elseif ($level == 'Mentor') {
        $result = mysqli_query($kon, "SELECT kode_mentor AS kode, nama FROM tbl_mentor");
    }

    while ($row = mysqli_fetch_array($result)) {
        $kode = $row['kode'];

        // Cek apakah sudah ada rating
        $cek = mysqli_query($kon, "SELECT * FROM tbl_rating WHERE kode_pengguna='$kode'");
        $sudah_ada = mysqli_num_rows($cek) > 0;

        $names[] = [
            'kode' => $kode,
            'nama' => $row['nama'],
            'disabled' => $sudah_ada
        ];
    }

    echo json_encode($names);
}
?>
