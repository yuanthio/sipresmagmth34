<?php
session_start();
if (isset($_POST['submit'])) {

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

        $kode_admin = input($_POST["kode_admin"]);
        $username = input($_POST["username"]);
        $password = md5(input($_POST["password"]));
        $level = "Admin";

        // Ambil nama administrator yang akan diubah
        $resultAdminToUpdate = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_admin'");
        $adminToUpdate = mysqli_fetch_assoc($resultAdminToUpdate);
        $nama_admin_diubah = $adminToUpdate['nama'];

        $sql = "UPDATE tbl_user SET 
            username='$username',
            password='$password',
            level='$level'
            WHERE kode_pengguna='$kode_admin'";

        // Menyimpan ke tabel pengguna
        $setting_pengguna = mysqli_query($kon, $sql);

        // Ambil informasi admin yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan session yang Anda gunakan
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level_admin = $user['level'];

        // Ambil nama administrator yang sedang login
        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin_login = $admin['nama'];

        // Dapatkan tanggal sekarang dalam format waktu Indonesia (WIB)
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Jika berhasil menyimpan data pengguna
        if ($setting_pengguna) {
            mysqli_query($kon, "COMMIT");

            // Simpan aktivitas ke tbl_log_aktivitas
            $aktivitas = "Setting data administrator ($nama_admin_diubah)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                       VALUES ('$tanggal', '$nama_admin_login', '$level_admin', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=admin&pengguna=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal
            $aktivitas = "Setting data administrator ($nama_admin_diubah)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                       VALUES ('$tanggal', '$nama_admin_login', '$level_admin', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=admin&pengguna=gagal");
        }
    }
}
?>

<form action="apps/admin/pengguna.php" method="post" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <?php
    include '../../config/database.php';
    $kode_pengguna = $_POST['kode_admin'];
    $query = mysqli_query($kon, "SELECT * FROM tbl_user where kode_pengguna='$kode_pengguna'");
    $data = mysqli_fetch_array($query);
    $username = $data['username'];
    $password = $data['password'];
    ?>

    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <input name="kode_admin" type="hidden" id="kode_admin" class="form-control" value="<?php echo $_POST['kode_admin']; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Username :</label>
                <input name="username" type="text" id="username" class="form-control" value="<?php echo $username; ?>" 
                <?php
                    //Mencegah admin lain mengubah username jika password sudah dibuat
                    if ($username == $_SESSION["username"]) {
                        echo "";
                    } else if (empty($username)) {
                        echo "";
                    } else
                        echo "disabled";
                ?> 
                placeholder="Buat Username" required>
                <div id="info_username"> </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password :</label>
                <input style="margin-bottom: 10px;" name="password" type="password" id="password" class="form-control" value="" 
                <?php
                    //Mencegah admin lain mengubah username jika password sudah dibuat
                    if ($username == $_SESSION["username"]) {
                        echo "";
                    } else if (empty($username)) {
                        echo "";
                    } else
                        echo "disabled";
                ?> 
                placeholder="Buat Password" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="checkbox" id="showPassword" style="margin-bottom: 5px;"> Lihat Password
                    </div>
                </div>
                <div id="info_password"> </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="submit" id="submit" class="btn-setting btn btn-primary"><i class="bi bi-floppy-fill"></i> Simpan</button>
            <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
        </div>
    </div>
</form>

<script>
    // Fungsi untuk memeriksa apakah tombol simpan dapat diaktifkan
    function checkPassword() {
        var password = $('#password').val();
        var hasUppercase = /^(?=.*[A-Z])/.test(password); // Pemeriksaan huruf kapital
        var hasLowercase = /^(?=.*[a-z])/.test(password); // Pemeriksaan huruf kecil
        var hasNumber = /\d/.test(password); // Pemeriksaan angka
        var hasMinLength = password.length >= 8; // Pemeriksaan panjang minimal
        var hasSymbol = /[-!$%^&*()_+|~=`{}\[\]:";'<>?,./]/.test(password); // Pemeriksaan simbol

        // Menentukan pesan validasi untuk huruf kapital
        var uppercaseMessage = hasUppercase ? '<span style="color: green; margin-right: 15px;">&#10004; Gunakan huruf kapital</span>' :
            '<span style="color: red; margin-right: 15px;">&#10008; Gunakan huruf kapital</span>';

        // Menentukan pesan validasi untuk huruf kecil
        var lowercaseMessage = hasLowercase ? '<span style="color: green;">&#10004; Gunakan huruf kecil</span><br>' :
            '<span style="color: red;">&#10008; Gunakan huruf kecil</span><br>';

        // Menentukan pesan validasi untuk angka
        var numberMessage = hasNumber ? '<span style="color: green; margin-right: 15px;">&#10004; Gunakan angka</span>' :
            '<span style="color: red; margin-right: 15px;">&#10008; Gunakan angka</span>';

        // Menentukan pesan validasi untuk panjang minimal
        var minLengthMessage = hasMinLength ? '<span style="color: green;">&#10004; Karakter minimal 8</span><br>' :
            '<span style="color: red;">&#10008; Karakter minimal 8</span><br>';

        // Menentukan pesan validasi untuk simbol
        var symbolMessage = hasSymbol ? '<span style="color: green; margin-right: 15px;">&#10004; Gunakan simbol</span>' :
            '<span style="color: red; margin-right: 15px;">&#10008; Gunakan simbol</span>';

        // Menampilkan pesan validasi
        $('#info_password').html(uppercaseMessage + ' ' + lowercaseMessage + ' ' + numberMessage + ' ' + minLengthMessage + ' ' + symbolMessage);

        // Menonaktifkan atau mengaktifkan tombol simpan berdasarkan kriteria
        $('#submit').prop('disabled', !(hasUppercase && hasLowercase && hasNumber && hasMinLength && hasSymbol));
    }

    // Memanggil checkPassword saat pengguna mengetikkan password
    $('#password').on('input', function() {
        checkPassword();
    });

    // Fungsi mengubah password
    $('.btn-setting').on('click', function() {
        konfirmasi = confirm("Konfirmasi Menyimpan Username dan Password?");
        if (konfirmasi) {
            return checkPassword(); // Panggil fungsi checkPassword
        } else {
            return false;
        }
    });
</script>

<script>
    // Fungsi untuk menangani perubahan pada checkbox showPassword
    $('#showPassword').change(function() {
        var passwordInput = $('#password');
        var isChecked = $(this).is(':checked');
        passwordInput.attr('type', isChecked ? 'text' : 'password');
    });
</script>