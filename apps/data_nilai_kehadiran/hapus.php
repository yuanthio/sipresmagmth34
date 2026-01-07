<?php
include '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mahasiswa = $_POST['id_mahasiswa'];
    
    // Ambil kode pengguna dari session
    $kode_pengguna = $_SESSION['kode_pengguna'];
    
    // Ambil level pengguna
    $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
    $result_user = mysqli_query($kon, $query_user);
    $row_user = mysqli_fetch_assoc($result_user);
    $level = $row_user['level'];

    // Ambil nama pengguna
    $nama_pengguna = '';
    if ($level == 'Admin') {
        $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
        $result_admin = mysqli_query($kon, $query_admin);
        $row_admin = mysqli_fetch_assoc($result_admin);
        $nama_pengguna = $row_admin['nama'];
    } elseif ($level == 'Mentor') {
        $query_mentor = "SELECT nama FROM tbl_mentor WHERE kode_mentor = '$kode_pengguna'";
        $result_mentor = mysqli_query($kon, $query_mentor);
        $row_mentor = mysqli_fetch_assoc($result_mentor);
        $nama_pengguna = $row_mentor['nama'];
    }

    // Ambil nama mahasiswa
    $query_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
    $result_mahasiswa = mysqli_query($kon, $query_mahasiswa);
    $row_mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
    $nama_mahasiswa = $row_mahasiswa['nama'];

    date_default_timezone_set("Asia/Jakarta");
    $tanggal_sekarang = date("Y-m-d H:i:s");

    // Hapus data dari tbl_nilai
    $query = "DELETE FROM tbl_nilai WHERE id_mahasiswa = $id_mahasiswa";
    $result = mysqli_query($kon, $query);

    if ($result) {
        // Update nilai_kehadiran dan konfirmasi_nilai di tbl_mahasiswa jadi NULL atau kosong
        $query_update_mahasiswa = "UPDATE tbl_mahasiswa 
                                   SET nilai_kehadiran = '0', konfirmasi_nilai = '' 
                                   WHERE id_mahasiswa = '$id_mahasiswa'";
        mysqli_query($kon, $query_update_mahasiswa);

        // Log berhasil
        $status = "berhasil";
        $aktivitas = "Hapus data penilaian kinerja mahasiswa ($nama_mahasiswa)";
        $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                      VALUES ('$tanggal_sekarang', '$nama_pengguna', '$level', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $query_log);

        echo json_encode(['status' => 'success', 'message' => 'Data nilai berhasil dihapus']);
    } else {
        // Log gagal
        $status = "gagal";
        $aktivitas = "Hapus data penilaian kinerja mahasiswa ($nama_mahasiswa)";
        $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                      VALUES ('$tanggal_sekarang', '$nama_pengguna', '$level', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $query_log);

        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data nilai']);
    }

    mysqli_close($kon);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid']);
}
?>
