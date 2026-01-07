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

        $kode_mahasiswa = input($_POST["kode_mahasiswa"]);
        $password = md5(input($_POST["password"]));
        $confirm_password = md5(input($_POST["confirm_password"]));

        // Periksa apakah password dan konfirmasi password sesuai
        if ($password !== $confirm_password) {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=profil&password_tidak_sesuai");
            exit();
        }

        // Update password ke tbl_user
        $sql = "UPDATE tbl_user SET password='$password' WHERE kode_pengguna='$kode_mahasiswa'";
        $password_update = mysqli_query($kon, $sql);

        // Mendapatkan informasi mahasiswa dan user yang sedang login
        $query_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_mahasiswa'";
        $hasil_mahasiswa = mysqli_query($kon, $query_mahasiswa);
        $data_mahasiswa = mysqli_fetch_assoc($hasil_mahasiswa);
        $nama_mahasiswa = $data_mahasiswa['nama'];

        $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_mahasiswa'";
        $hasil_user = mysqli_query($kon, $query_user);
        $data_user = mysqli_fetch_assoc($hasil_user);
        $level = $data_user['level'];

        // Tanggal saat ini dalam timezone Indonesia (WIB)
        date_default_timezone_set('Asia/Jakarta');
        $tanggal_sekarang = date("Y-m-d H:i:s");

        // Menyimpan log aktivitas
        if ($password_update) {
            // Jika password berhasil di update, commit transaksi dan simpan log aktivitas
            $aktivitas = "Edit password";
            $status = "berhasil";
            mysqli_query($kon, "COMMIT");

            $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level', '$kode_mahasiswa', '$aktivitas', '$status')";
            mysqli_query($kon, $log_sql);

            // Arahkan ke halaman logout
            header("Location:../../logout.php");
        } else {
            // Jika password gagal di update, rollback transaksi dan simpan log aktivitas
            mysqli_query($kon, "ROLLBACK");
            $aktivitas = "Edit password";
            $status = "gagal";

            $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level', '$kode_mahasiswa', '$aktivitas', '$status')";
            mysqli_query($kon, $log_sql);

            // Arahkan ke halaman profil
            header("Location:../../index.php?page=profil&password=gagal");
        }
    }
}
?>

<form action="apps/pengguna/ubah_password.php" method="post"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <!-- Menyimpan kode_mahasiswa dari AJAX -->
                <input name="kode_mahasiswa" type="hidden" id="kode_mahasiswa" class="form-control"
                    value="<?php echo $_POST['kode_mahasiswa']; ?>" />
                <!-- Menyimpan kode_mahasiswa dari AJAX -->
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
                    class="form-control" value="" placeholder="Konfirmasi Password?" required>
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