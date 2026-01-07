<?php
session_start();
include '../../config/database.php';

// Fungsi untuk mencegah inputan karakter yang tidak sesuai
function input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (isset($_POST['id_unit_kerja'])) {
    $id_unit_kerja = $_POST['id_unit_kerja'];
    
    // Proses simpan data
    if (isset($_POST['simpan'])) {
        $nama = input($_POST['nama']);

        // Mengecek apakah nama unit kerja sudah ada
        $cekNamaQuery = mysqli_query($kon, "SELECT * FROM tbl_unit_kerja WHERE nama = '$nama' AND id_unit_kerja != '$id_unit_kerja'");
        if (mysqli_num_rows($cekNamaQuery) > 0) {
            // Jika nama sudah ada, log aktivitas dan beri pesan error
            $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan nama variabel session Anda
            $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
            $user = mysqli_fetch_assoc($resultUser);
            $level = $user['level'];

            // Ambil nama admin
            $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
            $admin = mysqli_fetch_assoc($resultAdmin);
            $nama_admin = $admin['nama'];

            // Dapatkan tanggal sekarang dalam format yang diinginkan
            date_default_timezone_set('Asia/Jakarta');
            $tanggal = date("Y-m-d H:i:s");

            // Log aktivitas ketika nama unit kerja sudah ada
            $aktivitas = "Gagal edit data unit kerja ($nama)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location: ../../index.php?page=data_unit_kerja&edit=nama_sudah_ada");
            exit();
        }

        // Memulai transaksi
        mysqli_query($kon, "START TRANSACTION");

        // Query untuk update data unit kerja
        $update_query = mysqli_query($kon, "UPDATE tbl_unit_kerja SET nama = '$nama' WHERE id_unit_kerja = '$id_unit_kerja'");

        // Ambil informasi admin yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan nama variabel session Anda
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        // Ambil nama admin
        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        // Dapatkan tanggal sekarang dalam format yang diinginkan
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Log aktivitas (Edit data unit kerja)
        $aktivitas = "Edit data unit kerja ($nama)";

        if ($update_query) {
            // Commit jika berhasil
            mysqli_query($kon, "COMMIT");

            // Simpan aktivitas ke tbl_log_aktivitas
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sqlLog);

            header("Location: ../../index.php?page=data_unit_kerja&edit=berhasil");
        } else {
            // Rollback jika terjadi kesalahan
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location: ../../index.php?page=data_unit_kerja&edit=gagal");
        }
    }

    // Query untuk mengambil data berdasarkan id_unit_kerja
    $query = mysqli_query($kon, "SELECT * FROM tbl_unit_kerja WHERE id_unit_kerja = '$id_unit_kerja'");
    $data = mysqli_fetch_array($query);
}
?>

<form action="apps/data_unit_kerja/edit.php" method="POST" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nama">Nama Unit Kerja</label>
        <input type="hidden" name="id_unit_kerja" value="<?php echo $data['id_unit_kerja']; ?>">
        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
    </div>
    <div class="form-group">
        <button type="submit" name="simpan" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
    </div>
</form>
