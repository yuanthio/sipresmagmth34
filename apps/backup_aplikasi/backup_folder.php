<?php
set_time_limit(500); // Menambah batas waktu eksekusi
include_once '../../config/database.php'; // Pastikan file kon database di-include
date_default_timezone_set('Asia/Jakarta'); // Set timezone ke WIB

// Fungsi untuk mencatat log aktivitas
function catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, $status) {
    $tanggal = date('Y-m-d H:i:s'); // Format tanggal saat ini
    
    // Periksa apakah aktivitas serupa sudah dicatat dalam log sebelumnya
    $cek_query = "SELECT * FROM tbl_log_aktivitas 
                  WHERE kode_pengguna = '$kode_pengguna' AND aktivitas = '$aktivitas' AND status = '$status'
                  ORDER BY tanggal DESC LIMIT 1";
    $result = mysqli_query($kon, $cek_query);

    if (mysqli_num_rows($result) > 0) {
        // Jika sudah ada aktivitas serupa, jangan tambahkan log lagi
        return false;
    }

    // Jika belum ada, tambahkan log
    $query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
              VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    
    if (mysqli_query($kon, $query)) {
        return true;
    } else {
        // Untuk debugging, jika insert gagal
        echo "Error: " . mysqli_error($kon);
        return false;
    }
}

// Ambil data pengguna yang login
session_start(); // Pastikan session dimulai
$kode_pengguna = $_SESSION['kode_pengguna'] ?? ''; // Kode pengguna dari session
$level = ''; // Inisialisasi level
$nama = '';  // Inisialisasi nama

// Cek level pengguna dari tbl_user
$query_user = "SELECT level, kode_pengguna FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
$result_user = mysqli_query($kon, $query_user);
if ($row_user = mysqli_fetch_assoc($result_user)) {
    $level = $row_user['level'];
}

// Ambil nama admin dari tbl_admin
$query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
$result_admin = mysqli_query($kon, $query_admin);
if ($row_admin = mysqli_fetch_assoc($result_admin)) {
    $nama = $row_admin['nama'];
}

// Periksa apakah ada parameter folder dan format yang dikirim
if (isset($_GET['folder']) && isset($_GET['format'])) {
    $folder_name = $_GET['folder'];
    $format = $_GET['format'];
    $folder_path = '../../../absensi_magang_coba/' . $folder_name;

    $current_date = date('Ymd_His'); // Format: YYYYMMDD_HHMMSS
    $archive_name = $folder_name . '_' . $current_date . '.' . $format;
    $archive_path = '../../../' . $archive_name; // Path untuk arsip yang akan dibuat

    // Jika file arsip sudah ada, hapus terlebih dahulu
    if (file_exists($archive_path)) {
        unlink($archive_path); // Hapus file arsip yang sudah ada
    }

    // Cek apakah folder yang akan diarsipkan ada
    if (is_dir($folder_path)) {
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
            default:
                // Catat log aktivitas (Gagal) jika format tidak didukung
                $aktivitas = "Backup folder ($folder_name) aplikasi";
                catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'gagal');
                echo "Format tidak didukung.";
                exit;
        }

        // Eksekusi perintah untuk membuat arsip
        if ($command) {
            shell_exec($command);
        }

        // Cek apakah file arsip berhasil dibuat
        if (file_exists($archive_path)) {
            // Catat log aktivitas (Berhasil)
            $aktivitas = "Backup folder ($folder_name) aplikasi"; // Isi aktivitas tetap sama
            catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'berhasil');

            // Kirim file arsip untuk diunduh
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $archive_name);
            header('Content-Length: ' . filesize($archive_path));
            readfile($archive_path);

            // Hapus file arsip setelah diunduh
            unlink($archive_path);
            exit;
        } else {
            // Catat log aktivitas (Gagal)
            $aktivitas = "Backup folder ($folder_name) aplikasi"; // Isi aktivitas tetap sama
            catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'gagal');
            echo "Gagal membuat arsip.";
        }
    } else {
        // Catat log aktivitas (Gagal) jika folder tidak ditemukan
        $aktivitas = "Backup folder ($folder_name) aplikasi"; // Isi aktivitas tetap sama
        catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'gagal');
        echo "Folder tidak ditemukan.";
    }
} elseif (isset($_GET['file'])) {
    $file_name = $_GET['file'];
    $file_path = '../../apps/' . $file_name;

    if (file_exists($file_path)) {
        $aktivitas = "Download file $file_name"; // Isi aktivitas tetap sama
        catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'berhasil');

        // Kirim file untuk diunduh
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_path));
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        // Catat log aktivitas (Gagal) jika file tidak ditemukan
        $aktivitas = "Download file $file_name - File tidak ditemukan"; // Isi aktivitas tetap sama
        catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'gagal');
        echo "File tidak ditemukan.";
    }
} else {
    // Catat log aktivitas (Gagal) jika folder atau file tidak diberikan
    $aktivitas = "Operasi gagal - Folder atau file tidak diberikan."; // Isi aktivitas tetap sama
    catat_log_aktivitas($kon, $kode_pengguna, $nama, $level, $aktivitas, 'gagal');
    echo "Folder atau file tidak diberikan.";
}
?>
