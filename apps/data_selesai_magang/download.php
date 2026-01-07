<?php
// Mengimpor file konfigurasi database
include '../../config/database.php';

// Periksa apakah ada parameter ID yang dikirimkan melalui URL
if (isset($_GET['id_suket'])) {
    // Dapatkan ID dari parameter URL
    $id_suket = $_GET['id_suket'];

    // Query untuk mendapatkan informasi file berdasarkan ID
    $query = "SELECT * FROM tbl_suket WHERE id_suket = '$id_suket'";
    $result = mysqli_query($kon, $query);

    // Pastikan data ditemukan
    if ($data = mysqli_fetch_assoc($result)) {
        // Lokasi file yang akan diunduh
        $file_path = 'uploads/' . $data['file_suket'];

        // Periksa apakah file ada
        if (file_exists($file_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($file_path));

            // Atur header untuk mendownload file dengan nama yang spesifik
            header('Content-Disposition: attachment; filename="' . $data['file_suket'] . '"');

            // Baca dan kirimkan file ke output
            readfile($file_path);
            exit;
        } else {
            echo 'File tidak ditemukan.';
        }
    } else {
        echo 'Data tidak ditemukan.';
    }
} else {
    echo 'ID tidak valid.';
}
?>
