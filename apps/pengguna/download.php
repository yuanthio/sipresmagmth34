<?php
// apps/data_absensi/download.php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // amankan hanya nama file
    $filePath = __DIR__ . '/file_wfa/' . $file;

    // Validasi hanya file dengan ekstensi tertentu
    $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        echo "Tipe file tidak diizinkan.";
        exit;
    }

    if (file_exists($filePath)) {
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
