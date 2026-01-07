<?php
session_start();
if (isset($_POST['submit'])) {

    // Menghubungkan ke database
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

        // Mengambil kode mentor, password, dan konfirmasi password
        $kode_mentor = input($_POST["kode_mentor"]);
        $password = md5(input($_POST["password"]));
        $confirm_password = md5(input($_POST["confirm_password"]));

        // Mengatur timezone Indonesia (WIB)
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Mendapatkan informasi level dan kode_pengguna dari tbl_user
        $result_user = mysqli_query($kon, "SELECT level, kode_pengguna FROM tbl_user WHERE kode_pengguna = '$kode_mentor'");
        $user = mysqli_fetch_assoc($result_user);
        $level = $user['level'];  // Level pengguna (Mentor)
        $kode_pengguna = $user['kode_pengguna'];

        // Mendapatkan nama dari tbl_mentor berdasarkan kode_mentor
        $result_mentor = mysqli_query($kon, "SELECT nama FROM tbl_mentor WHERE kode_mentor = '$kode_mentor'");
        $mentor = mysqli_fetch_assoc($result_mentor);
        $nama = $mentor['nama'];

        // Periksa apakah password dan konfirmasi password sesuai
        if ($password !== $confirm_password) {
            // Log aktivitas gagal karena password tidak sesuai
            $aktivitas = "Edit password";
            $status = "gagal";
            $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                        VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_sql);

            // Rollback transaksi dan alihkan ke halaman profil dengan pesan error
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=profil&password_tidak_sesuai");
            exit();
        }

        // Update password di tbl_user
        $sql = "UPDATE tbl_user SET password='$password' WHERE kode_pengguna='$kode_mentor'";
        $password_update = mysqli_query($kon, $sql);

        // Jika password berhasil diupdate
        if ($password_update) {
            // Log aktivitas berhasil
            $aktivitas = "Edit password";
            $status = "berhasil";
            $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                        VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_sql);

            // Commit transaksi dan logout
            mysqli_query($kon, "COMMIT");
            header("Location:../../logout.php");
        } else {
            // Jika gagal update password, rollback transaksi dan log aktivitas
            $aktivitas = "Edit password";
            $status = "gagal";
            $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                        VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_sql);

            // Rollback transaksi dan alihkan ke halaman profil dengan pesan error
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=profil&password=gagal");
        }
    }
}
?>

<form action="apps/pengguna/ubah_password_mentor.php" method="post"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <input name="kode_mentor" type="hidden" id="kode_mentor" class="form-control"
                    value="<?php echo isset($_POST['kode_mentor']) ? $_POST['kode_mentor'] : ''; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password :</label>
                <input style="margin-bottom: 10px;" name="password" type="password" id="password" class="form-control"
                    value="" placeholder="Ganti Password?" required>
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
            <button type="submit" name="submit" id="submit" class="btn-password btn btn-primary"><i
                    class="fa fa-key"></i> Simpan</button>
        </div>
    </div>
</form>

<script>
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
            '<span style="color: red;">&#10008; Karakter kurang dari 8</span><br>';

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
    $('.btn-password').on('click', function () {
        konfirmasi = confirm("Konfirmasi Menyimpan Password?");
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