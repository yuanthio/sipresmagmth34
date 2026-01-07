<?php
session_start();
if (isset($_POST['submit'])) {

    include '../../config/database.php';
    date_default_timezone_set('Asia/Jakarta'); // Set timezone ke Indonesia (WIB)

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        // Data mahasiswa
        $kode_mahasiswa = input($_POST["kode_mahasiswa"]);
        $username = input($_POST["username"]);
        $level = "Mahasiswa";
        $password = md5(input($_POST["password"]));
        $confirm_password = md5(input($_POST["confirm_password"]));

        // Cek kecocokan password
        if ($password !== $confirm_password) {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=mahasiswa&pengguna=password_tidak_sesuai");
            exit();
        }

        // Ambil nama mahasiswa untuk log aktivitas
        $query_mahasiswa = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_mahasiswa'");
        $data_mahasiswa = mysqli_fetch_assoc($query_mahasiswa);
        $nama_mahasiswa = $data_mahasiswa['nama'];

        // Query untuk update akun mahasiswa
        $sql = "UPDATE tbl_user SET
            username='$username',
            password='$password',
            level='$level'
            WHERE kode_pengguna='$kode_mahasiswa'";
        $setting_pengguna = mysqli_query($kon, $sql);

        // Inisialisasi data untuk log aktivitas
        $tanggal = date("Y-m-d H:i:s"); // Tanggal dan waktu sekarang
        $aktivitas = "Setting data mahasiswa ($nama_mahasiswa)"; // Ubah pesan aktivitas
        $status = ($setting_pengguna) ? "berhasil" : "gagal";

        // Ambil data Admin yang sedang login
        $kode_admin = $_SESSION['kode_pengguna']; // Asumsi session berisi kode pengguna
        $query_admin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_admin'");
        $data_admin = mysqli_fetch_assoc($query_admin);
        $nama_admin = $data_admin['nama'];

        // Masukkan log aktivitas ke dalam database
        $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES 
                    ('$tanggal', '$nama_admin', 'Admin', '$kode_admin', '$aktivitas', '$status')";
        mysqli_query($kon, $log_sql);

        if ($setting_pengguna) {
            // Jika berhasil, commit transaksi
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=mahasiswa&pengguna=berhasil");
        } else {
            // Jika gagal, rollback transaksi
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=mahasiswa&pengguna=gagal");
        }
    }
}
?>

<form action="apps/mahasiswa/pengguna.php" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;"
    method="post">
    <?php
    include '../../config/database.php';
    $kode_pengguna = $_POST['kode_mahasiswa'];
    $query = mysqli_query($kon, "SELECT * FROM tbl_user where kode_pengguna='$kode_pengguna'");
    $data = mysqli_fetch_array($query);
    $username = $data['username'];
    ?>
    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <input name="kode_mahasiswa" type="hidden" id="kode_mahasiswa" class="form-control"
                    value="<?php echo $_POST['kode_mahasiswa']; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Username :</label>
                <input name="username" type="text" id="username" class="form-control" value="<?php echo $username; ?>"
                    placeholder="Buat Username" required>
                <div id="info_username"> </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password :</label>
                <input style="margin-bottom: 10px;" name="password" type="password" id="password" class="form-control"
                    value="" placeholder="Buat Password" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="checkbox" id="showPassword" style="margin-bottom: 5px;"> Lihat Password
                    </div>
                </div>
                <div id="info_password"> </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Konfirmasi Password :</label>
                <input style="margin-bottom: 10px;" name="confirm_password" type="password" id="confirm_password"
                    class="form-control" value="" placeholder="Konfirmasi Password" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="checkbox" id="showConfirmPassword" style="margin-bottom: 5px;"> Lihat Password
                    </div>
                </div>
                <div id="info_confirm_password"> </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="submit" id="submit" class="btn-setting btn btn-success"><i
                    class="bi bi-floppy-fill"></i> Simpan</button>
        </div>
    </div>
</form>

<script>
    $("#username").bind('keyup', function () {
        var username = $('#username').val();
        $.ajax({
            url: 'apps/pengguna/cek_username.php',
            method: 'POST',
            data: {
                username: username
            },
            success: function (data) {
                $('#info_username').show();
                $('#info_username').html(data);
            }
        });
    });

    // Fungsi untuk memeriksa apakah tombol simpan dapat diaktifkan
    function checkPassword() {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        var hasUppercase = /^(?=.*[A-Z])/.test(password); // Pemeriksaan huruf kapital
        var hasLowercase = /^(?=.*[a-z])/.test(password); // Pemeriksaan huruf kecil
        var hasNumber = /\d/.test(password); // Pemeriksaan angka
        var hasMinLength = password.length >= 8; // Pemeriksaan panjang minimal
        var hasSymbol = /[-!$%^&*()_+|~=`{}\[\]:";'<>?,./]/.test(password); // Pemeriksaan simbol
        var passwordsMatch = password === confirmPassword; // Pemeriksaan kesesuaian password

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

        // Menentukan pesan validasi untuk kesesuaian password
        var passwordsMatchMessage = passwordsMatch ? '<span style="color: green;">&#10004; Password sesuai</span><br>' :
            '<span style="color: red;">&#10008; Password tidak sesuai</span><br>';

        // Menampilkan pesan validasi
        $('#info_password').html(uppercaseMessage + ' ' + lowercaseMessage + ' ' + numberMessage + ' ' + minLengthMessage + ' ' + symbolMessage);
        $('#info_confirm_password').html(passwordsMatchMessage);

        // Menonaktifkan atau mengaktifkan tombol simpan berdasarkan kriteria
        $('#submit').prop('disabled', !(hasUppercase && hasLowercase && hasNumber && hasMinLength && hasSymbol && passwordsMatch));
    }

    // Memanggil checkPassword saat pengguna mengetikkan password atau konfirmasi password
    $('#password, #confirm_password').on('input', function () {
        checkPassword();
    });

    // Fungsi mengubah password
    $('.btn-setting').on('click', function () {
        konfirmasi = confirm("Konfirmasi Menyimpan Username dan Password?");
        if (konfirmasi) {
            return checkPassword(); // Panggil fungsi checkPassword
        } else {
            return false;
        }
    });

    // Fungsi untuk menangani perubahan pada checkbox showPassword
    $('#showPassword').change(function () {
        var passwordInput = $('#password');
        var isChecked = $(this).is(':checked');
        passwordInput.attr('type', isChecked ? 'text' : 'password');
    });

    // Fungsi untuk menangani perubahan pada checkbox showConfirmPassword
    $('#showConfirmPassword').change(function () {
        var confirmPasswordInput = $('#confirm_password');
        var isChecked = $(this).is(':checked');
        confirmPasswordInput.attr('type', isChecked ? 'text' : 'password');
    });
</script>