<?php
session_start();
if (isset($_POST['tambah_admin'])) {

    //Menghubungkan ke database
    include '../../config/database.php';

    //Fungsi untuk mencegah inputan karakter yang tidak sesuai
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //Cek apakah ada kiriman form dari method post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //Memulai transaksi
        mysqli_query($kon, "START TRANSACTION");

        //Menyimpan input dari form tambah admin
        $nip = input($_POST["nip"]);
        $nama = input($_POST["nama"]);
        $email = input($_POST["email"]);

        //Membuat kode admin otomatis berdasarkan nomor terakhir dari kolom kode_pengguna
        $query = mysqli_query($kon, "SELECT max(id_admin) AS id_terbesar FROM tbl_admin");
        $ambil = mysqli_fetch_array($query);
        $id_admin = $ambil['id_terbesar'];
        $id_admin++;
        $huruf = "A";
        $kode_admin = $huruf . sprintf("%03s", $id_admin);

        $sql = "INSERT INTO tbl_user (kode_pengguna) VALUES ('$kode_admin')";

        //Menyimpan ke tabel pengguna
        $simpan_pengguna = mysqli_query($kon, $sql);

        // Menyimpan ke tabel admin
        $sql = "INSERT INTO tbl_admin (kode_admin, nama, nip, email) VALUES ('$kode_admin', '$nama', '$nip', '$email')";
        //Menyimpan ke tabel admin
        $simpan_admin = mysqli_query($kon, $sql);

        // Ambil informasi admin yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan nama variabel session Anda
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        // Ambil nama admin yang sedang login
        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        // Dapatkan tanggal sekarang dalam format yang diinginkan
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        //validasi jika berhasil menambahkan data admin dan data pengguna 
        if ($simpan_pengguna && $simpan_admin) {
            mysqli_query($kon, "COMMIT");

            // Simpan aktivitas ke tbl_log_aktivitas
            $aktivitas = "Tambah data administrator ($nama)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=admin&add=berhasil");
        }
        //validasi jika gagal menambahkan data admin dan data pengguna
        else {
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal
            $aktivitas = "Tambah data administrator ($nama)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=admin&add=gagal");
        }
    }
}
?>

<form action="apps/admin/tambah.php" method="post" enctype="multipart/form-data" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukan Nama Lengkap" required autofocus oninvalid="this.setCustomValidity('Harap nama lengkap di isi terlebih dahulu')" oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nomor Induk Pegawai (NIP) :</label>
                <input type="text" name="nip" class="form-control" value="" placeholder="Masukan Nomor Induk Pegawai" required autofocus oninvalid="this.setCustomValidity('Harap NIP di isi terlebih dahulu')" oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" placeholder="Masukan Email" required autofocus oninvalid="this.setCustomValidity('Harap email di isi terlebih dahulu')" oninput="this.setCustomValidity('')">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="tambah_admin" id="Submit" class="btn btn-success"><i class="fa fa-plus"></i> Daftar</button>
            <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
        </div>
    </div>
</form>

<style>
    .file {
        visibility: hidden;
        position: absolute;
    }
</style>