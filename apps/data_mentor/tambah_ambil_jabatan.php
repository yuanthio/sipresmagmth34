<?php
include '../../config/database.php';

if (isset($_POST['unit_kerja'])) {
    $unit_kerja = mysqli_real_escape_string($kon, $_POST['unit_kerja']);

    // Ambil semua jabatan dari tbl_jabatan untuk unit kerja tersebut
    $query = mysqli_query($kon, "SELECT nama FROM tbl_jabatan WHERE unit_kerja='$unit_kerja'");
    $jabatan_semua = [];
    while ($data = mysqli_fetch_assoc($query)) {
        $nama_jabatan_array = explode(',', $data['nama']);
        foreach ($nama_jabatan_array as $jabatan) {
            $jabatan_semua[] = trim($jabatan);
        }
    }

    // Ambil jabatan yang sudah terpakai di tbl_mentor untuk unit kerja ini
    $query_mentor = mysqli_query($kon, "SELECT jabatan FROM tbl_mentor WHERE unit_kerja='$unit_kerja'");
    $jabatan_terpakai = [];
    while ($data = mysqli_fetch_assoc($query_mentor)) {
        $jabatan_array = explode(',', $data['jabatan']);
        foreach ($jabatan_array as $jabatan) {
            $jabatan_terpakai[] = trim($jabatan);
        }
    }

    // Bangun opsi <option>
    $options = "<option selected disabled>Pilih Jabatan</option>";
    foreach ($jabatan_semua as $jabatan) {
        if (in_array($jabatan, $jabatan_terpakai)) {
            $options .= "<option disabled>$jabatan (sudah digunakan)</option>";
        } else {
            $options .= "<option value='$jabatan'>$jabatan</option>";
        }
    }

    echo $options;
}
