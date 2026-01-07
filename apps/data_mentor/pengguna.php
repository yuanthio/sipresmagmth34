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

        $kode_mentor = input($_POST["kode_mentor"]);
        $username = input($_POST["username"]);
        $password = md5(input($_POST["password"]));
        $level = "Mentor";

        // Ambil nama mentor dari tabel mentor
        $resultMentor = mysqli_query($kon, "SELECT nama FROM tbl_mentor WHERE kode_mentor = '$kode_mentor'");
        $mentor = mysqli_fetch_assoc($resultMentor);
        $nama_mentor = $mentor['nama'];

        // Update data mentor pada tabel pengguna
        $sql = "UPDATE tbl_user SET 
            username='$username',
            password='$password',
            level='$level'
            WHERE kode_pengguna='$kode_mentor'";

        // Menyimpan ke tabel pengguna
        $setting_pengguna = mysqli_query($kon, $sql);

        // Ambil informasi admin yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan session yang Anda gunakan
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level_admin = $user['level'];

        // Ambil nama admin dari tabel admin
        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        // Dapatkan tanggal sekarang dalam format waktu Indonesia (WIB)
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Jika berhasil menyimpan data pengguna
        if ($setting_pengguna) {
            mysqli_query($kon, "COMMIT");

            // Simpan aktivitas ke tbl_log_aktivitas
            $aktivitas = "Setting data mentor ($nama_mentor)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                       VALUES ('$tanggal', '$nama_admin', '$level_admin', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=data_mentor&pengguna=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal
            $aktivitas = "Setting data mentor ($nama_mentor)";
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                       VALUES ('$tanggal', '$nama_admin', '$level_admin', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location:../../index.php?page=data_mentor&pengguna=gagal");
        }
    }
}
?>

<form action="apps/data_mentor/pengguna.php" method="post" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <?php
    include '../../config/database.php';
    $kode_pengguna = $_POST['kode_mentor'];
    $query = mysqli_query($kon, "SELECT * FROM tbl_user where kode_pengguna='$kode_pengguna'");
    $data = mysqli_fetch_array($query);

    // Periksa apakah $data tidak null sebelum mengakses elemennya
    if ($data) {
        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
    } else {
        // Tangani kasus di mana $data bernilai null
        $username = '';
        $password = '';
    }
    ?>
    
    <?php 
    include '../../config/database.php';
    ?>

    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <input name="kode_mentor" type="hidden" id="kode_mentor" class="form-control" value="<?php echo $_POST['kode_mentor']; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Username :</label>
                <input name="username" type="text" id="username" class="form-control" value="<?php echo $username; ?>" placeholder="Buat Username" required>
                <div id="info_username"> </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password :</label>
                <input style="margin-bottom: 10px;" name="password" type="password" id="password" class="form-control" value="" placeholder="Buat Password" required>
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
            <button type="submit" name="submit" id="submit" class="btn-setting btn btn-success"><i class="bi bi-floppy-fill"></i> Simpan</button>
        </div>
    </div>
</form>

<script>
    $("#username").bind('keyup', function() {
        var username = $('#username').val();
        $.ajax({
            url: 'apps/pengguna/cek_username.php',
            method: 'POST',
            data: {
                username: username
            },
            success: function(data) {
                $('#info_username').show();
                $('#info_username').html(data);
            }
        });
    });
</script>

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