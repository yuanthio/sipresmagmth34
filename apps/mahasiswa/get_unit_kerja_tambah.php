<?php
// Koneksi database dan lainnya
include '../../config/database.php';

if(isset($_POST['mentor'])) {
    $mentor = $_POST['mentor'];
    
    // Query untuk mendapatkan unit kerja berdasarkan nama mentor
    $query = "SELECT unit_kerja FROM tbl_mentor WHERE nama = '$mentor'";
    $result = mysqli_query($kon, $query);

    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $unit_kerja = $row['unit_kerja'];

        // Mengembalikan unit kerja sebagai response JSON
        echo json_encode(array('unit_kerja' => $unit_kerja));
    } else {
        // Jika tidak ada hasil, kembalikan response kosong atau sesuaikan kebutuhan Anda
        echo json_encode(array('unit_kerja' => ''));
    }
}
?>
