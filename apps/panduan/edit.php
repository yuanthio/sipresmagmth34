<?php
include "../../config/database.php";
session_start();

function getFileExtension($filename)
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_panduan = $_POST['id_panduan']; // ID panduan dari form
    $kode_pengguna = $_SESSION['kode_pengguna']; // Kode admin dari sesi

    // Mulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Ambil file lama dari database
    $query_get_old_file = "SELECT file_panduan, level FROM tbl_panduan WHERE id_panduan = '$id_panduan'";
    $result_old_file = mysqli_query($kon, $query_get_old_file);
    $data_old_file = mysqli_fetch_assoc($result_old_file);
    $file_lama = $data_old_file['file_panduan'];
    $level_diedit = $data_old_file['level']; // Mengambil level untuk pencatatan aktivitas

    if (isset($_FILES['file_panduan'])) {
        $nama_file = $_FILES['file_panduan']['name'];
        $ukuran_file = $_FILES['file_panduan']['size'];
        $lokasi_file = $_FILES['file_panduan']['tmp_name'];

        $allowed_extensions = ['doc', 'docx', 'pdf'];
        $file_extension = strtolower(getFileExtension($nama_file));

        // Validasi ekstensi file
        if (!in_array($file_extension, $allowed_extensions)) {
            mysqli_query($kon, "ROLLBACK"); // Rollback jika ada kesalahan
            // Pencatatan aktivitas gagal
            $status = 'gagal';
            $aktivitas = "Edit data panduan ($level_diedit) jenis file tidak diizinkan";
            $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES 
                          (NOW(), (SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'), 
                          (SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'), 
                          '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $query_log);
            header("Location:../../index.php?page=panduan&edit=gagal&reason=jenis_file_tidak_diizinkan");
            exit();
        }

        $folder_upload = "../../apps/panduan/upload/";
        $path_file_baru = $folder_upload . $nama_file;

        // Hapus file lama sebelum mengunggah file baru
        $path_file_lama = $folder_upload . $file_lama;
        if (file_exists($path_file_lama)) {
            unlink($path_file_lama); // Hapus file lama
        }

        // Upload file baru
        if (!move_uploaded_file($lokasi_file, $path_file_baru)) {
            mysqli_query($kon, "ROLLBACK"); // Rollback jika gagal upload
            // Pencatatan aktivitas gagal
            $status = 'gagal';
            $aktivitas = "Edit data panduan ($level_diedit) gagal upload file";
            $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES 
                          (NOW(), (SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'), 
                          (SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'), 
                          '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $query_log);
            header("Location:../../index.php?page=panduan&edit=gagal");
            exit();
        }

        // Update nama file baru di database
        $query_update = "UPDATE tbl_panduan SET file_panduan = '$nama_file' WHERE id_panduan = '$id_panduan'";
        $result_update = mysqli_query($kon, $query_update);

        if ($result_update) {
            mysqli_query($kon, "COMMIT"); // Commit jika semua berhasil
            // Pencatatan aktivitas berhasil
            $status = 'berhasil';
            $aktivitas = "Edit data panduan ($level_diedit)";
            $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES 
                          (NOW(), (SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'), 
                          (SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'), 
                          '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $query_log);
            header("Location:../../index.php?page=panduan&edit=berhasil"); 
        } else {
            mysqli_query($kon, "ROLLBACK"); // Rollback jika update gagal
            // Pencatatan aktivitas gagal
            $status = 'gagal';
            $aktivitas = "Edit data panduan ($level_diedit) gagal update";
            $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES 
                          (NOW(), (SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'), 
                          (SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'), 
                          '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $query_log);
            header("Location:../../index.php?page=panduan&edit=gagal");
        }
    }
}

// Ambil data panduan berdasarkan ID
if (isset($_POST['id_panduan'])) {
    $id_panduan = $_POST['id_panduan'];
    $query = "SELECT * FROM tbl_panduan WHERE id_panduan = '$id_panduan'";
    $result = mysqli_query($kon, $query);
    $data = mysqli_fetch_assoc($result);
}
?>

<form action="apps/panduan/edit.php" method="post" enctype="multipart/form-data" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <input type="hidden" name="id_panduan" value="<?php echo $data['id_panduan']; ?>">

    <div class="form-group">
        <label for="file_panduan">Unggah File Baru</label>
        <input type="file" name="file_panduan" id="file_panduan" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Unggah</button>
</form>