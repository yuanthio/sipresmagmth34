<?php
// Sertakan koneksi database
include '../../config/database.php';
session_start(); // Mulai session untuk mengambil data pengguna yang login

// Ambil data dari POST
$id_mahasiswa = $_POST['id_mahasiswa'];
$kehadiran = $_POST['kehadiran'];
$keaktifan = $_POST['keaktifan'];
$kreatifitas = $_POST['kreatifitas'];
$kepatuhan = $_POST['kepatuhan'];
$tingkah_laku = $_POST['tingkah_laku'];
$keahlian = $_POST['keahlian'];
$jumlah = $_POST['jumlah'];
$rata_rata = $_POST['rata_rata'];

// Ambil data pengguna yang sedang login
$kode_pengguna = $_SESSION['kode_pengguna']; // Pastikan session ini menyimpan kode_pengguna
$query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
$result_user = mysqli_query($kon, $query_user);
$row_user = mysqli_fetch_assoc($result_user);
$level = $row_user['level'];

// Tentukan nama pengguna yang login berdasarkan level
$nama_pengguna = "";
if ($level == "Admin") {
    $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $result_admin = mysqli_query($kon, $query_admin);
    $row_admin = mysqli_fetch_assoc($result_admin);
    $nama_pengguna = $row_admin['nama'];
} elseif ($level == "Mentor") {
    $query_mentor = "SELECT nama FROM tbl_mentor WHERE kode_mentor = '$kode_pengguna'";
    $result_mentor = mysqli_query($kon, $query_mentor);
    $row_mentor = mysqli_fetch_assoc($result_mentor);
    $nama_pengguna = $row_mentor['nama'];
}

// Ambil nama mahasiswa dari tabel tbl_mahasiswa
$query_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
$result_mahasiswa = mysqli_query($kon, $query_mahasiswa);
$row_mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
$nama_mahasiswa = $row_mahasiswa['nama'];

// Format tanggal untuk log
date_default_timezone_set("Asia/Jakarta");
$tanggal_sekarang = date("Y-m-d H:i:s");

// Simpan atau update data penilaian
$query = "INSERT INTO tbl_nilai (id_mahasiswa, kehadiran, keaktifan, kreatifitas, kepatuhan, tingkah_laku, keahlian, jumlah, rata_rata) 
          VALUES ('$id_mahasiswa', '$kehadiran', '$keaktifan', '$kreatifitas', '$kepatuhan', '$tingkah_laku', '$keahlian', '$jumlah', '$rata_rata')
          ON DUPLICATE KEY UPDATE kehadiran = VALUES(kehadiran), keaktifan = VALUES(keaktifan), kreatifitas = VALUES(kreatifitas),
          kepatuhan = VALUES(kepatuhan), tingkah_laku = VALUES(tingkah_laku), keahlian = VALUES(keahlian), jumlah = VALUES(jumlah),
          rata_rata = VALUES(rata_rata)";

// Eksekusi query
if (mysqli_query($kon, $query)) {
    // Update nilai_kehadiran dan set konfirmasi_nilai menjadi 'diubah'
    $update_mahasiswa = "UPDATE tbl_mahasiswa 
                         SET nilai_kehadiran = '$kehadiran', konfirmasi_nilai = 'diubah' 
                         WHERE id_mahasiswa = '$id_mahasiswa'";
    mysqli_query($kon, $update_mahasiswa);

    // Log aktivitas: berhasil input
    $status = "berhasil";
} else {
    // Log aktivitas: gagal input
    $status = "gagal";
}

// Aktivitas untuk log
$aktivitas = "Input data penilaian kinerja mahasiswa ($nama_mahasiswa)";

// Insert ke tabel log aktivitas
$query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
              VALUES ('$tanggal_sekarang', '$nama_pengguna', '$level', '$kode_pengguna', '$aktivitas', '$status')";
mysqli_query($kon, $query_log);

// Tutup koneksi
mysqli_close($kon);
?>
