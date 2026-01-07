<?php
include '../../config/database.php';
session_start();
date_default_timezone_set('Asia/Jakarta');
// Menambah batas waktu eksekusi maksimal, misalnya 500 detik (lebih dari 5 menit)
set_time_limit(500);

if (isset($_GET['format'])) {
    $format = $_GET['format'];
    $folder_path = '../../../absensi_magang_coba'; // Path ke folder 'apps'
    $archive_name = 'sipresmagmth34_' . date('Ymd_His') . '.' . $format; // Nama file arsip
    $archive_path = '../../../' . $archive_name;
    $backup_status = 'berhasil'; // Asumsi status berhasil awalnya

    // Mengambil informasi pengguna yang login
    $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil kode_pengguna dari session
    $query_user = mysqli_query($kon, "SELECT * FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user = mysqli_fetch_assoc($query_user);

    // Cek jika level user adalah Admin
    if ($user['level'] != 'Admin') {
        die('Akses ditolak. Hanya admin yang bisa melakukan backup.');
    }

    // Mendapatkan nama admin dari tbl_admin
    $query_admin = mysqli_query($kon, "SELECT * FROM tbl_admin WHERE kode_admin = '{$user['kode_pengguna']}'");
    $admin = mysqli_fetch_assoc($query_admin);
    $nama_admin = $admin['nama'];

    // Jika file arsip sudah ada, hapus terlebih dahulu
    if (file_exists($archive_path)) {
        unlink($archive_path); // Hapus file arsip lama
    }

    // Buat arsip berdasarkan format yang dipilih
    $command = '';
    switch ($format) {
        case 'zip':
            $command = "zip -r " . escapeshellarg($archive_path) . " " . escapeshellarg($folder_path);
            break;
        case 'tar':
            $command = "tar -cf " . escapeshellarg($archive_path) . " -C " . escapeshellarg(dirname($folder_path)) . " " . escapeshellarg(basename($folder_path));
            break;
        case 'tar.gz':
            $command = "tar -czf " . escapeshellarg($archive_path) . " -C " . escapeshellarg(dirname($folder_path)) . " " . escapeshellarg(basename($folder_path));
            break;
        case 'tar.bz2':
            $command = "tar -cjf " . escapeshellarg($archive_path) . " -C " . escapeshellarg(dirname($folder_path)) . " " . escapeshellarg(basename($folder_path));
            break;
        case 'tar.xz':
            $command = "tar -cJf " . escapeshellarg($archive_path) . " -C " . escapeshellarg(dirname($folder_path)) . " " . escapeshellarg(basename($folder_path));
            break;
        case 'tar.lz4':
            $command = "tar --use-compress-program=lz4 -cf " . escapeshellarg($archive_path) . " -C " . escapeshellarg(dirname($folder_path)) . " " . escapeshellarg(basename($folder_path));
            break;
        case 'tar.sz':
            $command = "tar --use-compress-program=szip -cf " . escapeshellarg($archive_path) . " -C " . escapeshellarg(dirname($folder_path)) . " " . escapeshellarg(basename($folder_path));
            break;
    }

    // Eksekusi perintah untuk membuat arsip
    if ($command) {
        shell_exec($command);
    }

    // Cek apakah file arsip berhasil dibuat
    if (file_exists($archive_path)) {
        // Cek jika log dengan aktivitas dan waktu yang sama sudah ada dalam database
        $tanggal_sekarang = date('Y-m-d H:i:s'); // Mendapatkan tanggal dan waktu saat ini (WIB)
        $aktivitas = "Backup keseluruhan aplikasi";
        
        $log_check_query = "SELECT COUNT(*) AS jumlah FROM tbl_log_aktivitas 
                            WHERE kode_pengguna = '$kode_pengguna' 
                            AND aktivitas = '$aktivitas' 
                            AND tanggal >= (NOW() - INTERVAL 5 MINUTE)";
        $log_check_result = mysqli_query($kon, $log_check_query);
        $log_check = mysqli_fetch_assoc($log_check_result);

        if ($log_check['jumlah'] == 0) { // Jika tidak ada log dalam 5 menit terakhir
            // Kirim file arsip untuk diunduh
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $archive_name);
            header('Content-Length: ' . filesize($archive_path));
            readfile($archive_path);

            // Hapus file arsip setelah diunduh
            unlink($archive_path);

            // Menyimpan log aktivitas
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $log_query);
        }

        exit; // Menghentikan eksekusi setelah file dikirim
    } else {
        // Jika gagal membuat arsip
        $backup_status = 'gagal';
        echo "Gagal membuat arsip.";

        // Menyimpan log dengan status gagal
        $tanggal_sekarang = date('Y-m-d H:i:s'); 
        $aktivitas = "Backup keseluruhan aplikasi"; // Aktivitas yang sama
        $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                      VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'gagal')";
        mysqli_query($kon, $log_query);
    }

    exit; // Menghentikan eksekusi di akhir proses backup
} else {
    // Jika format tidak diberikan
    echo "Format tidak diberikan.";

    // Menyimpan log dengan status gagal
    $tanggal_sekarang = date('Y-m-d H:i:s'); 
    $aktivitas = "Backup keseluruhan aplikasi"; // Aktivitas yang sama
    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'gagal')";
    mysqli_query($kon, $log_query);
    
    exit; // Menghentikan eksekusi
}
?>
