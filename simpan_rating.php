<?php
header('Content-Type: application/json');
session_start();
include 'config/database.php';

if (!isset($_POST['rating']) || !isset($_POST['pesan'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// Ambil nilai rating dan pesan dari POST
$rating = mysqli_real_escape_string($kon, $_POST['rating']);
$pesan = mysqli_real_escape_string($kon, $_POST['pesan']);

// Periksa session
if (!isset($_SESSION['kode_pengguna'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
    exit;
}

$kode_pengguna = $_SESSION['kode_pengguna'];

// Cek user login
$sql_user = "SELECT * FROM tbl_user WHERE kode_pengguna='$kode_pengguna' LIMIT 1";
$hasil_user = mysqli_query($kon, $sql_user);
$data_user = mysqli_fetch_array($hasil_user);

if (!$data_user) {
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    exit;
}

$level = $data_user['level'];
$nama = '';

// Ambil nama dan kode berdasarkan level
switch ($level) {
    case 'Mahasiswa':
        $result_nama = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE kode_mahasiswa = '$kode_pengguna'");
        break;
    case 'Mentor':
        $result_nama = mysqli_query($kon, "SELECT nama FROM tbl_mentor WHERE kode_mentor = '$kode_pengguna'");
        break;
    case 'Admin':
        $result_nama = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Level pengguna tidak dikenali']);
        exit;
}

$data_nama = mysqli_fetch_assoc($result_nama);
$nama = $data_nama['nama'] ?? 'Tidak Diketahui';

// Tanggal saat ini
$tanggal = date('Y-m-d H:i:s');

// Simpan ke tabel
$queryInsert = "INSERT INTO tbl_rating (kode_pengguna, nama, level, rating, pesan, tanggal)
                VALUES ('$kode_pengguna', '$nama', '$level', '$rating', '$pesan', '$tanggal')";

if (mysqli_query($kon, $queryInsert)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . mysqli_error($kon)]);
}
?>
