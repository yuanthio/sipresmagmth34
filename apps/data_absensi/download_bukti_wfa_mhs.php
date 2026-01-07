<?php
// apps/data_absensi/download_bukti_wfa_mhs.php
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("Akses tidak valid.");
}

$file = basename($_GET['file']); // amankan nama file
$path = __DIR__ . "/file_wfa/" . $file; // path lengkap file

if (!file_exists($path)) {
    die("File tidak ditemukan.");
}

// Tentukan header sesuai jenis file
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
switch ($ext) {
    case 'pdf':
        header("Content-Type: application/pdf");
        break;
    case 'doc':
        header("Content-Type: application/msword");
        break;
    case 'docx':
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        break;
    default:
        header("Content-Type: application/octet-stream");
}

header("Content-Disposition: attachment; filename=\"" . $file . "\"");
header("Content-Length: " . filesize($path));

readfile($path);
exit;
?>
