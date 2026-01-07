<?php
$mentorNama = $_POST['mentor_nama'];

include '../../config/database.php'; 
$query = "SELECT unit_kerja FROM tbl_mentor WHERE nama = '$mentorNama'";
$result = mysqli_query($kon, $query);

// Periksa apakah query berhasil
if (!$result) {
    die("Query gagal: " . mysqli_error($kon));
}

// Ambil data unit kerja jika tersedia
if ($row = mysqli_fetch_assoc($result)) {
    $unitKerja = $row['unit_kerja'];

    // Formatkan sebagai JSON untuk dikirimkan kembali ke JavaScript
    echo json_encode(array('unit_kerja' => $unitKerja));
} else {
    echo json_encode(array('unit_kerja' => null));
}

// Tutup koneksi database
mysqli_close($kon);
?>
