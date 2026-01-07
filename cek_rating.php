<?php
session_start();
include 'config/database.php';

$response = [
    'tampil' => false,
    'hide_button' => false
];

// Cek session login
if (!isset($_SESSION['kode_pengguna'])) {
    echo json_encode($response);
    exit;
}

$kode_pengguna = $_SESSION['kode_pengguna'];

// Cek apakah sudah mengisi kritik dan saran
$query_rating = "SELECT id_rating FROM tbl_rating WHERE kode_pengguna = '$kode_pengguna' LIMIT 1";
$result_rating = mysqli_query($kon, $query_rating);

if (mysqli_num_rows($result_rating) > 0) {
    // Sudah mengisi kritik dan saran, maka sembunyikan tombol
    $response['hide_button'] = true;
    echo json_encode($response);
    exit;
}

// Cek level user
$query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna' LIMIT 1";
$result_user = mysqli_query($kon, $query_user);
$data_user = mysqli_fetch_assoc($result_user);

if (!$data_user || $data_user['level'] !== 'Mahasiswa') {
    echo json_encode($response);
    exit;
}

// Cek apakah alert sudah ditampilkan di sesi ini
if (isset($_SESSION['rating_alert_shown'])) {
    echo json_encode($response);
    exit;
}

// Cek tanggal akhir magang
$query_mhs = "SELECT akhir_magang FROM tbl_mahasiswa WHERE kode_mahasiswa = '$kode_pengguna' LIMIT 1";
$result_mhs = mysqli_query($kon, $query_mhs);
$data_mhs = mysqli_fetch_assoc($result_mhs);

if (!$data_mhs) {
    echo json_encode($response);
    exit;
}

$tanggal_akhir = $data_mhs['akhir_magang'];
$tanggal_sekarang = date('Y-m-d');
$selisih_hari = (strtotime($tanggal_akhir) - strtotime($tanggal_sekarang)) / (60 * 60 * 24);

// Jika tinggal <= 2 hari dari akhir magang, tampilkan alert
if ($selisih_hari <= 2 && $selisih_hari >= 0) {
    $_SESSION['rating_alert_shown'] = true;
    $response['tampil'] = true;
}

echo json_encode($response);
?>
