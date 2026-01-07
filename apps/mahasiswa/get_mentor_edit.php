<?php
// Sambungkan ke database
include '../../config/database.php';

// Ambil nilai unit kerja dari POST
$unitKerja = $_POST['unit_kerja'];

// Query untuk mengambil daftar mentor berdasarkan unit kerja
$query = "SELECT id_mentor, nama FROM tbl_mentor WHERE unit_kerja = '$unitKerja'";
$result = mysqli_query($kon, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($kon));
}

// Siapkan array untuk menyimpan data mentor
$mentors = array();

// Ambil data mentor dan tambahkan ke array
while ($row = mysqli_fetch_assoc($result)) {
    $mentors[] = $row;
}

// Mengembalikan data mentor dalam format JSON
echo json_encode($mentors);

// Tutup koneksi database
mysqli_close($kon);
?>
