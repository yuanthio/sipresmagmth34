<?php
if (isset($_GET['file'])) {
    $file_name = $_GET['file'];
    $file_path = '../../../absensi_magang_coba/' . $file_name;

    // Cek apakah file ada
    if (file_exists($file_path)) {
        // Mengunduh file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_path));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        echo "File tidak ditemukan.";
    }
} else {
    echo "Parameter tidak valid.";
}
?>
