<?php
session_start();
if (isset($_POST['edit_admin'])) {

    // Include file koneksi, untuk koneksikan ke database
    include '../../config/database.php';

    // Fungsi untuk mencegah inputan karakter yang tidak sesuai
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Cek apakah ada kiriman form dari method post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Memulai transaksi
        mysqli_query($kon, "START TRANSACTION");

        $id_admin = input($_POST["id_admin"]);
        $nama = input($_POST["nama"]);
        $nip = input($_POST["nip"]);
        $email = input($_POST["email"]);

        // Ambil nama admin yang sedang diedit sebelum melakukan update
        $resultEditAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE id_admin = '$id_admin'");
        $adminDiedit = mysqli_fetch_assoc($resultEditAdmin);
        $nama_admin_diedit = $adminDiedit['nama'];

        // Query untuk update tbl_admin
        $sql = "UPDATE tbl_admin SET 
            nama='$nama', 
            nip='$nip', 
            email='$email'
            WHERE id_admin=$id_admin";

        // Mengeksekusi query 
        $edit_admin = mysqli_query($kon, $sql);

        // Ambil informasi admin yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan nama variabel session Anda
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        // Ambil nama admin yang sedang login
        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin_login = $admin['nama'];

        // Dapatkan tanggal sekarang dalam format yang diinginkan
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Validasi jika data admin berhasil di update
        if ($edit_admin) {
            mysqli_query($kon, "COMMIT");

            // Simpan aktivitas ke tbl_log_aktivitas
            $aktivitas = "Edit data administrator ($nama_admin_diedit)"; // Menggunakan nama admin yang diedit
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin_login', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=admin&edit=berhasil");
        } else {
            // Rollback jika terjadi kesalahan
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal
            $aktivitas = "Edit data administrator ($nama_admin_diedit)"; // Menggunakan nama admin yang diedit
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin_login', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=admin&edit=gagal");
        }
    }
}
?>

<?php
include '../../config/database.php';
$id_admin = $_POST["id_admin"];
$sql = "select * from tbl_admin where id_admin=$id_admin limit 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
?>

<form action="apps/admin/edit.php" method="post" enctype="multipart/form-data" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-7">
            <input type="hidden" name="id_admin" class="form-control" value="<?php echo $data['id_admin']; ?>">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" value="<?php echo $data['nama']; ?>" placeholder="Masukan Nama Lengkap" required>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="form-group">
                <label>Nomor Induk Pegawai (NIP) :</label>
                <input type="text" name="nip" class="form-control" value="<?php echo $data['nip']; ?>" placeholder="Masukan Nomor Induk Pegawai" required>
            </div>
        </div>
        <div class="col-sm-7">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" value="<?php echo $data['email']; ?>" placeholder="Masukan Email" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_admin" id="Submit" class="btn btn-warning"><i class="fa fa-edit"></i> Update</button>
            </div>
        </div>
    </div>
</form>

<style>
    .file {
        visibility: hidden;
        position: absolute;
    }
</style>