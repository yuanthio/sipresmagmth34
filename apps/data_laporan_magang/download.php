<?php
// Mengimpor file konfigurasi database
include '../../config/database.php';

// Periksa apakah ada parameter ID yang dikirimkan melalui URL
if (isset($_GET['id'])) {
    // Dapatkan ID dari parameter URL
    $id_laporan = $_GET['id'];

    // Query untuk mendapatkan informasi file berdasarkan ID
    $query = "SELECT * FROM tbl_laporan WHERE id_laporan = '$id_laporan'";
    $result = mysqli_query($kon, $query);

    // Pastikan data ditemukan
    if ($data = mysqli_fetch_assoc($result)) {
        // Lokasi file yang akan diunduh
        $file_path = '../../apps/data_laporan_magang/upload/' . basename($data['file_laporan']);

        // Periksa apakah file ada
        if (file_exists($file_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($file_path));

            // Atur header untuk mendownload file dengan nama yang spesifik
            header('Content-Disposition: attachment; filename="' . basename($data['file_laporan']) . '"');

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
