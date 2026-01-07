<?php
include '../../config/database.php';
session_start();

// Pastikan user yang login adalah Admin
$kode_pengguna = $_SESSION['kode_pengguna']; // Dari session login
$query_user = mysqli_query($kon, "SELECT * FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
$user = mysqli_fetch_assoc($query_user);

// Cek jika level user adalah Admin
if ($user['level'] != 'Admin') {
    die('Akses ditolak. Hanya admin yang bisa melakukan backup database.');
}

// Mendapatkan nama admin dari tbl_admin
$query_admin = mysqli_query($kon, "SELECT * FROM tbl_admin WHERE kode_admin = '{$user['kode_pengguna']}'");
$admin = mysqli_fetch_assoc($query_admin);
$nama_admin = $admin['nama'];

$tables = array();
$query = mysqli_query($kon, "SHOW TABLES");
while ($row = mysqli_fetch_row($query)) {
    $tables[] = $row[0];
}

$backup_sql = "";
$backup_status = 'berhasil';

foreach ($tables as $table) {
    // Mendapatkan struktur tabel
    $query_create = mysqli_query($kon, "SHOW CREATE TABLE $table");
    if (!$query_create) {
        $backup_status = 'gagal'; // Jika gagal mengambil struktur tabel
        break;
    }
    $row_create = mysqli_fetch_row($query_create);
    $backup_sql .= "\n\n" . $row_create[1] . ";\n\n";

    // Mendapatkan data tabel
    $query_data = mysqli_query($kon, "SELECT * FROM $table");
    if (!$query_data) {
        $backup_status = 'gagal'; // Jika gagal mengambil data tabel
        break;
    }
    $num_fields = mysqli_num_fields($query_data);

    while ($row_data = mysqli_fetch_row($query_data)) {
        $backup_sql .= "INSERT INTO $table VALUES(";
        for ($i = 0; $i < $num_fields; $i++) {
            $row_data[$i] = addslashes($row_data[$i]);
            $row_data[$i] = preg_replace("/\n/", "\\n", $row_data[$i]);
            if (isset($row_data[$i])) {
                $backup_sql .= '"' . $row_data[$i] . '"';
            } else {
                $backup_sql .= '""';
            }
            if ($i < ($num_fields - 1)) {
                $backup_sql .= ',';
            }
        }
        $backup_sql .= ");\n";
    }
    $backup_sql .= "\n\n\n";
}

// Nama file backup
$backup_filename = 'backup_database_' . date('Y-m-d_H-i-s') . '.sql';

// Menetapkan header untuk download jika backup berhasil
if ($backup_status == 'berhasil') {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=' . $backup_filename);
    echo $backup_sql;
}

// Catat aktivitas ke dalam tabel log
date_default_timezone_set('Asia/Jakarta');
$tanggal_sekarang = date('Y-m-d H:i:s'); // Mendapatkan tanggal dan waktu saat ini (WIB)
$aktivitas = 'Backup keseluruhan database';
$log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
              VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', '$backup_status')";

mysqli_query($kon, $log_query);

?>
