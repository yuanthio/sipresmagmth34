<?php
include '../../config/database.php';

if (isset($_POST['unit_kerja'])) {
    $unit_kerja = mysqli_real_escape_string($kon, $_POST['unit_kerja']);
    $jabatan_lama = isset($_POST['jabatan_lama']) ? $_POST['jabatan_lama'] : '';

    $query = mysqli_query($kon, "SELECT nama FROM tbl_jabatan WHERE unit_kerja='$unit_kerja'");
    $options = "<option selected disabled>Pilih Jabatan</option>";

    while ($data = mysqli_fetch_assoc($query)) {
        $nama_jabatan_array = explode(',', $data['nama']);
        foreach ($nama_jabatan_array as $jabatan) {
            $jabatan = trim($jabatan);
            $selected = ($jabatan == $jabatan_lama) ? "selected" : "";
            $options .= "<option value='$jabatan' $selected>$jabatan</option>";
        }
    }
    echo $options;
}
?>
