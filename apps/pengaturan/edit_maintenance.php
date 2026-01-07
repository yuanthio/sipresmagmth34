<?php
// Ambil nilai dari form
$catatan = isset($_POST['catatan_login']) ? $_POST['catatan_login'] : '';

// Jika checkbox status_login tidak dicentang, set statusnya ke 'off' dan hapus catatan
$status = isset($_POST['status_login']) ? 'on' : 'off';

// Jika status adalah 'off', kosongkan catatan
if ($status === 'off') {
    $catatan = '';  // Hapus pesan jika status 'off'
}

// Simpan ke file
$data = [
    'catatan' => $catatan,
    'status' => $status
];

file_put_contents('../../config/maintenance.json', json_encode($data));

// Redirect kembali ke halaman pengaturan dengan pesan sukses
header('Location: ../../index.php?page=pengaturan&edit_maintenance=berhasil');
exit;
?>
