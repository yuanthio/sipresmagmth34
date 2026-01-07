<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">

<?php
// Memulai session
session_start();

// Jika terdeteksi ada variabel id_pengguna dalam session maka langsung arahkan ke halaman dashboard
if (isset($_SESSION["id_pengguna"])) {
    session_unset();
    session_destroy();
}

// Variable pesan untuk menampilkan validasi login
$pesan = "";

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
    // Menghubungkan database
    include "config/database.php";

    // Mengambil input username dan password dari form login
    $username = input($_POST["username"]);
    $password = input($_POST["password"]);

    // Simpan input username dan password dalam session untuk mengingat input sebelumnya
    $_SESSION["input_username"] = $username;
    $_SESSION["input_password"] = $password;

    // Encrypt password menggunakan MD5
    $password_hashed = md5($password);

    // Query untuk cek apakah username ada
    $cek_username = mysqli_query($kon, "SELECT * FROM tbl_user WHERE username='" . $username . "' LIMIT 1");
    $user_data = mysqli_fetch_assoc($cek_username);

    // Mengambil tanggal saat ini dengan timezone Indonesia
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date("Y-m-d H:i:s");

    // Variabel untuk menyimpan status
    $status = 'gagal'; // Default status
    $admin = null;
    $mentor = null;
    $mahasiswa = null;
    $nama = '';

    if ($user_data) {
        // Username ditemukan, lanjutkan ke pengecekan password
        if ($user_data['password'] == $password_hashed) {
            // Query untuk cek tbl_user yang dijoin dengan tabel tbl_admin
            $tabel_admin = "SELECT * FROM tbl_user p
                INNER JOIN tbl_admin k ON k.kode_admin=p.kode_pengguna
                WHERE username='" . $username . "' and password='" . $password_hashed . "' LIMIT 1";
            $cek_tabel_admin = mysqli_query($kon, $tabel_admin);
            $admin = mysqli_fetch_assoc($cek_tabel_admin);

            // Query untuk cek tbl_user yang dijoin dengan tabel tbl_mentor
            $tabel_mentor = "SELECT * FROM tbl_user p
                INNER JOIN tbl_mentor k ON k.kode_mentor=p.kode_pengguna
                WHERE username='" . $username . "' and password='" . $password_hashed . "' LIMIT 1";
            $cek_tabel_mentor = mysqli_query($kon, $tabel_mentor);
            $mentor = mysqli_fetch_assoc($cek_tabel_mentor);

            // Query untuk cek pada tbl_user yang dijoin dengan tabel tbl_mahasiswa
            $tabel_mahasiswa = "SELECT * FROM tbl_user p
                INNER JOIN tbl_mahasiswa m ON m.kode_mahasiswa=p.kode_pengguna
                WHERE username='" . $username . "' and password='" . $password_hashed . "' LIMIT 1";
            $cek_tabel_mahasiswa = mysqli_query($kon, $tabel_mahasiswa);
            $mahasiswa = mysqli_fetch_assoc($cek_tabel_mahasiswa);

            if ($admin && strtoupper($admin["level"]) == 'ADMIN') {
                $_SESSION["id_pengguna"] = $admin["id_user"];
                $_SESSION["kode_pengguna"] = $admin["kode_pengguna"];
                $_SESSION["nama_admin"] = $admin["nama"];
                $_SESSION["username"] = $admin["username"];
                $_SESSION["level"] = $admin["level"];
                $_SESSION["nip"] = $admin["nip"];

                // Set status menjadi berhasil
                $status = 'berhasil';

                // Ambil nama dari admin
                $nama = $admin['nama'];

                $pesan = "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Login Berhasil',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = 'index.php?page=beranda';
                            });
                          </script>";
            } elseif ($mentor && strtoupper($mentor["level"]) == 'MENTOR') {
                $_SESSION["id_pengguna"] = $mentor["id_user"];
                $_SESSION["kode_mentor"] = $mentor["kode_mentor"];
                $_SESSION["kode_pengguna"] = $mentor["kode_pengguna"];
                $_SESSION["nama_mentor"] = $mentor["nama"];
                $_SESSION["username"] = $mentor["username"];
                $_SESSION["level"] = $mentor["level"];
                $_SESSION["nip"] = $mentor["nip"];
                $_SESSION['show_overlay'] = true;

                // Set status menjadi berhasil
                $status = 'berhasil';

                // Ambil nama dari mentor
                $nama = $mentor['nama'];

                $pesan = "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Login Berhasil',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = 'index.php?page=beranda';
                            });
                          </script>";
            } elseif ($mahasiswa && strtoupper($mahasiswa["level"]) == 'MAHASISWA') {
                $_SESSION["id_pengguna"] = $mahasiswa["id_user"];
                $_SESSION["kode_pengguna"] = $mahasiswa["kode_pengguna"];
                $_SESSION["id_mahasiswa"] = $mahasiswa["id_mahasiswa"];
                $_SESSION["nama_mahasiswa"] = $mahasiswa["nama"]; // Make sure this key exists
                $_SESSION["username"] = $mahasiswa["username"];
                $_SESSION["universitas"] = $mahasiswa["universitas"];
                $_SESSION["level"] = $mahasiswa["level"];
                $_SESSION["foto"] = $mahasiswa["foto"];
                $_SESSION["nim"] = $mahasiswa["nim"];
                $_SESSION['show_overlay'] = true;

                // Set status menjadi berhasil
                $status = 'berhasil';

                // Ambil nama dari mahasiswa
                $nama = $mahasiswa['nama'];

                $pesan = "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Login Berhasil',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = 'index.php?page=beranda';
                            });
                          </script>";
            }
        } else {
            // Password salah
            $pesan = "<script>Swal.fire({
                        icon: 'error',
                        title: 'Password Salah',
                        text: 'Silakan coba lagi!',
                        showConfirmButton: true
                      });</script>";
        }
    } else {
        // Username tidak ditemukan
        $pesan = "<script>Swal.fire({
                    icon: 'error',
                    title: 'Username Salah',
                    text: 'Silakan coba lagi!',
                    showConfirmButton: true
                  });</script>";
    }

    // Menyimpan aktivitas ke dalam tabel log hanya jika status berhasil
    if ($status === 'berhasil') {
        // Mengambil kode pengguna
        $kode_pengguna = $user_data['kode_pengguna'];

        // Menyimpan log aktivitas
        $query_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal', '$nama', '{$user_data['level']}', '$kode_pengguna', 'login', '$status')";
        mysqli_query($kon, $query_log);
    }
}
?>

<!-- Mengambil Profil Aplikasi -->
<?php
include 'config/database.php';
$query = mysqli_query($kon, "select * from tbl_site limit 1");
$row = mysqli_fetch_array($query);
$nama_instansi = $row['nama_instansi'];
$logo = $row['logo'];
?>
<!-- Mengambil Profil Aplikasi -->

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Favicon -->
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo $logo; ?>">
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Google Font Roboto -->
    <link href="template/login/font/" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Saira+Semi+Condensed:wght@700&display=swap');

        body {
            font-family: "Roboto Condensed", sans-serif;
            background-image: url(source/img/bg-login.jpg);
            background-size: cover;
            width: 100vw;
            overflow-x: hidden;
        }

        .alert-fade-in {
            opacity: 0;
            animation: fadeIn 0.5s ease-in forwards;
        }

        .alert-fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        .judul-apk {
            text-shadow: 3px 3px 0 #fff;
            border-bottom: 3px solid black;
            padding-bottom: 7px;
            font-family: 'Saira Semi Condensed', sans-serif;
            letter-spacing: 1px;
            font-weight: bold;
            font-size: 1.8em;
            word-wrap: break-word;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        h5 {
            font-weight: bold;
        }

        .login {
            background-color: rgba(255, 255, 255, .7);
            backdrop-filter: blur(5px);
            padding: 20px;
            border-radius: 10px;
        }

        .login input,
        .btn-light {
            box-shadow: 0 0 5px 1px rgba(0, 0, 0, 0.245);
        }

        .swal-title {
            font-size: 1.5em;
        }

        .button-64 {
            align-items: center;
            background-image: linear-gradient(144deg, #AF40FF, #5B42F3 50%, #00DDEB);
            border: 0;
            border-radius: 8px;
            box-shadow: rgba(151, 65, 252, 0.2) 0 15px 30px -5px;
            box-sizing: border-box;
            color: #FFFFFF;
            display: flex;
            font-family: Phantomsans, sans-serif;
            justify-content: center;
            line-height: 1em;
            max-width: 100%;
            min-width: 140px;
            padding: 3px;
            text-decoration: none;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            white-space: nowrap;
            cursor: pointer;
        }

        .button-64:active,
        .button-64:hover {
            outline: 0;
        }

        .button-64 span {
            background-color: rgb(5, 6, 45);
            padding: 16px 24px;
            border-radius: 6px;
            width: 100%;
            height: 100%;
            transition: 300ms;
        }

        .button-64:hover span {
            background: none;
        }

        #animatedText span {
            transition: color 0.4s ease-in-out, transform 0.4s ease-in-out, text-shadow 0.4s ease-in-out;
            color: black;
            display: inline-block;
        }

        #animatedText span.active {
            color: rgb(193, 168, 7);
            transform: translateY(-3px);
            text-shadow: 0 10px 15px rgba(49, 49, 49, 0.65);
            /* bayangan oranye di bawah huruf */
        }

        @media (min-width: 768px) {
            .button-64 {
                font-size: 24px;
                min-width: 196px;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 0 20px;
            }
        }
    </style>

    <!-- Title Website -->
    <title>Login | SIPRESMAGMTH34</title>
</head>

<body>
    <div class="row" style="height: 100vh; display: flex; justify-content: center; align-items: center;">
        <div class="col-lg-3 col-sm-7">
            <form class="login" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
                <div class="logo d-flex justify-content-center">
                    <img src="source/img/<?php echo $logo; ?>" width="80" alt="">
                </div>
                <h5 class="judul-apk text-center mt-3" id="animatedText">
                    <span>S</span><span>I</span><span>P</span><span>R</span><span>E</span><span>S</span><span>M</span><span>A</span><span>G</span><span>M</span><span>T</span><span>H</span><span>3</span><span>4</span>
                </h5>
                <h5 class="text-center fs-5 mx-4">BPK Perwakilan Provinsi DKI Jakarta</h5>
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (empty($_POST["username"]) || empty($_POST["password"])) {
                        echo "<script>Swal.fire({
                                icon: 'warning',
                                title: 'Masukkan username dan password terlebih dahulu',
                                text: 'Pastikan untuk mengisi kedua kolom!',
                                showConfirmButton: true,
                                customClass: {
                                    title: 'swal-title'
                                }
                              });</script>";
                    } else {
                        echo $pesan;
                    }
                } ?>
                <div class="form-group mb-4">
                    <label for="username"><i class="bi bi-person-circle"></i> Username</label>
                    <input type="text" class="form-control" name="username" id="username"
                        value="<?php echo isset($_SESSION['input_username']) ? $_SESSION['input_username'] : ''; ?>"
                        placeholder="Masukan username" />
                </div>
                <div class="form-group">
                    <label for="password"><img width="20" src="source/img/kunci.png" alt="key"> Password</label>
                    <div class="input-group">
                        <input style="margin-right: 5px;" type="password" class="form-control" name="password"
                            id="password" placeholder="Masukkan password"
                            value="<?php echo isset($_SESSION['input_password']) ? $_SESSION['input_password'] : ''; ?>" />
                        <button class="btn btn-light" type="button" id="togglePassword">
                            <i class="bi-eye-fill" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="button d-flex justify-content-center mt-3">
                    <button type="submit" name="submit" class="button-64 btn btn-primary w-100 btn-block mx-auto"
                        role="button" style="font-size: 1.1em;"><span class="text"><i
                                class="bi bi-box-arrow-in-right"></i> Masuk</span></button>
                </div>
            </form>
        </div>
    </div>

    <!-- js Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/4752e5dd73.js" crossorigin="anonymous"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const togglePasswordButton = document.getElementById("togglePassword");
            const eyeIcon = document.getElementById("eyeIcon");

            togglePasswordButton.addEventListener("click", function () {
                const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                passwordInput.setAttribute("type", type);

                // Update ikon mata
                if (type === "password") {
                    eyeIcon.classList.remove("bi-eye-slash-fill");
                    eyeIcon.classList.add("bi-eye-fill");
                } else {
                    eyeIcon.classList.remove("bi-eye-fill");
                    eyeIcon.classList.add("bi-eye-slash-fill");
                }
            });
        });
    </script>

    <script>
        const spans = document.querySelectorAll('#animatedText span');
        let index = 0;

        function animateText() {
            spans.forEach((span, i) => {
                span.classList.remove('active');
                if (i === index) {
                    span.classList.add('active');
                }
            });

            // Simpan index saat ini lalu naikkan
            let currentIndex = index;
            index++;

            // Jika sudah di karakter terakhir, tunggu sampai animasi selesai dulu (200ms), lalu delay 4 detik, lalu reset ke awal
            if (currentIndex === spans.length - 1) {
                setTimeout(() => {
                    spans[currentIndex].classList.remove('active'); // hilangkan warna oranye
                    setTimeout(() => {
                        index = 0;
                        animateText();
                    }, 4000); // delay 4 detik setelah oranye hilang
                }, 200); // tunggu 200ms dulu biar oranye muncul sebentar
            } else {
                setTimeout(animateText, 400);
            }
        }

        animateText();
    </script>
</body>

</html>