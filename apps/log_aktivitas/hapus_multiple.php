<?php
include '../../config/database.php';

if (isset($_POST['id_log_aktivitas'])) {
    $ids = $_POST['id_log_aktivitas'];
    
    // Buat format string ID yang aman
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $query = "DELETE FROM tbl_log_aktivitas WHERE id_log_aktivitas IN ($placeholders)";
    $stmt = $kon->prepare($query);
    
    // Binding parameter secara dinamis
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    
    if ($stmt->execute()) {
        header("Location: ../../index.php?page=log_aktivitas&hapus_log_aktivitas=berhasil");
    } else {
        header("Location: ../../index.php?page=log_aktivitas&hapus_log_aktivitas=gagal");
    }

    $stmt->close();
}
$kon->close();
?>
