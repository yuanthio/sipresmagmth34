<?php
session_start();
if (isset($_POST['tambah_unit_kerja'])) {
    include '../../config/database.php';

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama_unit = input($_POST["nama"]);

        // Mengecek apakah nama unit kerja sudah ada
        $cekUnitKerja = mysqli_query($kon, "SELECT * FROM tbl_unit_kerja WHERE nama = '$nama_unit'");
        if (mysqli_num_rows($cekUnitKerja) > 0) {
            // Logging aktivitas gagal karena nama sudah ada
            $kode_pengguna = $_SESSION['kode_pengguna'];
            $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'");
            $user = mysqli_fetch_assoc($resultUser);
            $level = $user['level'];

            $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
            $admin = mysqli_fetch_assoc($resultAdmin);
            $nama_admin = $admin['nama'];

            date_default_timezone_set('Asia/Jakarta');
            $tanggal = date("Y-m-d H:i:s");

            $aktivitas = "Gagal tambah unit kerja ($nama_unit)";
            $status = "gagal";

            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $sqlLog);

            header("Location: ../../index.php?page=data_unit_kerja&tambah=nama_sudah_ada");
            exit();
        }

        mysqli_query($kon, "START TRANSACTION");

        // Menyimpan data unit kerja
        $sql = "INSERT INTO tbl_unit_kerja (nama) VALUES ('$nama_unit')";
        $simpan = mysqli_query($kon, $sql);

        // Logging
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        $aktivitas = "Tambah unit kerja ($nama_unit)";
        if ($simpan) {
            mysqli_query($kon, "COMMIT");
            $status = "berhasil";
        } else {
            mysqli_query($kon, "ROLLBACK");
            $status = "gagal";
        }

        $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $sqlLog);

        header("Location: ../../index.php?page=data_unit_kerja&tambah=$status");
    }
}
?>

<form action="apps/data_unit_kerja/tambah.php" method="post" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;" enctype="multipart/form-data">
    <div class="form-group">
        <label>Nama Unit Kerja:</label>
        <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Unit Kerja" required>
    </div>
    <button type="submit" name="tambah_unit_kerja" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
    <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
</form>