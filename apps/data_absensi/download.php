<?php
// apps/data_absensi/download.php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // amankan hanya nama file
    $filePath = __DIR__ . '/file_wfa/' . $file;

    // validasi file ada
    if (file_exists($filePath)) {
        // force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File tidak ditemukan.";
    }
} else {
    echo "Parameter file tidak valid.";
}
