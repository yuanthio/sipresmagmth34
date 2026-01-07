<?php
include '../../config/database.php';
session_start();

if (isset($_GET['table'])) {
    $table = $_GET['table'];
    $backup_sql = "";
    $backup_status = 'berhasil'; // Status awal dianggap berhasil

    // Mengambil informasi pengguna yang login
    $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil kode_pengguna dari session login
    $query_user = mysqli_query($kon, "SELECT * FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user = mysqli_fetch_assoc($query_user);

    // Cek jika level user adalah Admin
    if ($user['level'] != 'Admin') {
        die('Akses ditolak. Hanya admin yang bisa melakukan backup tabel.');
    }

    // Mendapatkan nama admin dari tbl_admin
    $query_admin = mysqli_query($kon, "SELECT * FROM tbl_admin WHERE kode_admin = '{$user['kode_pengguna']}'");
    $admin = mysqli_fetch_assoc($query_admin);
    $nama_admin = $admin['nama'];

    // Query untuk mengambil struktur tabel
    $query_create = mysqli_query($kon, "SHOW CREATE TABLE $table");
    if (!$query_create) {
        $backup_status = 'gagal'; // Jika gagal mengambil struktur tabel
    } else {
        $row_create = mysqli_fetch_row($query_create);
        $backup_sql .= $row_create[1] . ";\n\n";
    }

    // Mengambil data tabel
    if ($backup_status != 'gagal') {
        $query_data = mysqli_query($kon, "SELECT * FROM $table");
        if (!$query_data) {
            $backup_status = 'gagal'; // Jika gagal mengambil data tabel
        } else {
            while ($row_data = mysqli_fetch_assoc($query_data)) {
                $backup_sql .= "INSERT INTO $table VALUES(";
                $backup_sql .= "'" . implode("','", array_values($row_data)) . "'";
                $backup_sql .= ");\n";
            }
        }
    }

    // Nama file backup
    $backup_filename = $table . "_backup_" . date('Y-m-d_H-i-s') . ".sql";

    // Menyimpan aktivitas log ke database
    date_default_timezone_set("Asia/Jakarta");
    $tanggal_sekarang = date('Y-m-d H:i:s'); // Mendapatkan tanggal dan waktu saat ini (WIB)
    $aktivitas = "backup database ($table)";
    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', '$backup_status')";

    mysqli_query($kon, $log_query);

    // Mengatur header untuk download file jika backup berhasil
    if ($backup_status == 'berhasil') {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename=' . $backup_filename);
        echo $backup_sql;
    } else {
        echo "Gagal melakukan backup tabel.";
    }
}
?>
