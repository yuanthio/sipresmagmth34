<?php
// Include koneksi ke database
include '../../config/database.php';

// Pastikan id_log_aktivitas diterima
if (isset($_GET['id_log_aktivitas'])) {
    // Ambil id_log_aktivitas dari URL
    $id_log_aktivitas = $_GET['id_log_aktivitas'];

    // Query untuk menghapus data log aktivitas berdasarkan id
    $query = "DELETE FROM tbl_log_aktivitas WHERE id_log_aktivitas = ?";
    $stmt = $kon->prepare($query);
    $stmt->bind_param("i", $id_log_aktivitas); // 'i' untuk integer

    // Eksekusi query
    if ($stmt->execute()) {
        // Redirect kembali jika penghapusan berhasil
        header("Location: ../../index.php?page=log_aktivitas&hapus_log_aktivitas=berhasil");
    } else {
        // Redirect kembali jika penghapusan gagal
        header("Location: ../../index.php?page=log_aktivitas&hapus_log_aktivitas=gagal");
    }

    // Tutup statement
    $stmt->close();
}

// Tutup koneksi database
$kon->close();
?>
