<?php
// Lokasi folder file
$baseDir = __DIR__ . "/file_wfa/";

// Pastikan parameter file ada
if (!isset($_GET['file'])) {
    die("Tidak ada file yang dipilih.");
}

$file = basename($_GET['file']); // cegah path traversal
$filePath = $baseDir . $file;

// Cek apakah file ada
if (!file_exists($filePath)) {
    die("File tidak ditemukan.");
}

// Tentukan MIME type berdasarkan ekstensi
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
switch ($ext) {
    case 'pdf':
        $mime = "application/pdf";
        break;
    case 'doc':
        $mime = "application/msword";
        break;
    case 'docx':
        $mime = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        break;
    default:
        $mime = "application/octet-stream";
        break;
}

// Header untuk download
header("Content-Type: " . $mime);
header("Content-Disposition: attachment; filename=\"" . $file . "\"");
header("Content-Length: " . filesize($filePath));

// Baca file dan kirim ke browser
readfile($filePath);
exit;
