<?php
//Memulai sesi
session_start();
//Jika kode pengguna di session kosong maka kembali ke login
if (!$_SESSION["kode_pengguna"]) {
    header("Location:login.php");
    //Jika kode pengguna ada maka akan di proses masuk ke halaman utama
} else {
    //Menghubungkan database
    include 'config/database.php';
    $kode_pengguna = $_SESSION["kode_pengguna"];
    $username = $_SESSION["username"];
    $hasil = mysqli_query($kon, "select username from tbl_user where kode_pengguna='$kode_pengguna'");
    $data = mysqli_fetch_array($hasil);
    $username_db = $data['username'];
    if ($username != $username_db) {
        //Menghapus session
        session_unset();
        session_destroy();
        header("Location:login.php");
    }
}
?>

<?php
include 'config/database.php';
$query = mysqli_query($kon, "select * from tbl_site limit 1");
$row = mysqli_fetch_array($query);
$nama_instansi = $row['nama_instansi'];
$logo = $row['logo'];
?>

<?php
// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

// Proses download file panduan jika terdapat parameter "download_panduan" di URL
if (isset($_GET['download_panduan']) && isset($_SESSION["level"])) {
    $userLevel = $_SESSION["level"];
    $query = "";

    // Tentukan query berdasarkan level pengguna yang login
    if ($userLevel == "Mahasiswa" || $userLevel == "mahasiswa") {
        $query = "SELECT * FROM tbl_panduan WHERE level = 'Karyawan Magang' LIMIT 1";
    } elseif ($userLevel == "Mentor" || $userLevel == "mentor") {
        $query = "SELECT * FROM tbl_panduan WHERE level = 'Mentor' LIMIT 1";
    }

    if ($query != "") {
        $result = mysqli_query($kon, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $filePath = 'apps/panduan/upload/' . $row['file_panduan'];

            if (file_exists($filePath)) {
                // Mengatur header untuk memulai download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                ob_clean();
                flush();
                readfile($filePath);
                exit;
            } else {
                echo "<script>alert('File tidak ditemukan!');</script>";
            }
        } else {
            echo "<script>alert('Panduan tidak ditemukan untuk level " . $userLevel . ".');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo $logo; ?>">
    <!-- Title Website -->
    <title>SIPRESMAGMTH34</title>
    <!-- Bootstrap -->
    <link href="template/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="template/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Date Picker 3 -->
    <link href="template/css/datepicker3.css" rel="stylesheet">
    <!-- Local CSS -->
    <link href="template/css/styles.css" rel="stylesheet">
    <!-- jQuery -->
    <!-- <link rel="stylesheet" href="assets/css/jquery-ui.css"> -->
    <script src="template/js/jquery-2.2.3.min.js"></script>
    <script src="template/js/jquery-1.11.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom Font -->
    <!-- <link href="src/font/font.css" rel="stylesheet" type="text/css"> -->
    <!-- Custom CSS -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Saira+Semi+Condensed&display=swap');

        .no-js #loader {
            display: none;
        }

        .js #loader {
            display: block;
            position: absolute;
            left: 100px;
            top: 0;
        }

        .se-pre-con {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('loading.gif') center no-repeat #fff;
        }

        .sidebar .menu li a {
            transition: .2s all;
        }

        .sidebar .menu li a:hover {
            background-color: rgb(13, 10, 44);
        }

        .jam {
            color: white;
        }

        .brand {
            text-shadow: 3px 3px 0 #815e08;
            font-family: 'Saira Semi Condensed', sans-serif;
            letter-spacing: 1px;
        }

        #overlayText {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgb(24, 18, 92);
            color: white;
            font-size: 3rem;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            text-align: center;
            visibility: visible;
            flex-direction: column;
        }

        .sidebar {
            padding-bottom: 50px;
        }

        .chat-button {
            position: fixed;
            bottom: 60px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            font-size: 24px;
            z-index: 10;
            transition: .3s;
        }

        .chat-button:hover {
            background-color: #0056b3;
        }

        #rating i {
            color: gray;
            cursor: pointer;
            transition: color 0.2s;
        }

        #rating i.fa-solid {
            color: gold;
        }

        .chat-button {
            display: none;
            /* Tombol disembunyikan secara default */
        }

        @media (max-width: 768px) {
            #sidebar-collapse {
                position: fixed;
                top: 60px;
                /* atau setinggi navbar */
                left: 0;
                width: 100vw;
                height: calc(100vh - 50px);
                /* sisakan tinggi navbar */
                overflow-y: auto;
                z-index: 3;
                background-color: rgb(24, 18, 92);
                padding-bottom: 100px;
            }

            body.sidebar-open {
                overflow: hidden;
                height: 100vh;
                position: fixed;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            #overlayText {
                font-size: 1.9rem;
            }
        }

        @media (max-width: 692px) {
            .brand {
                display: none;
            }

            body .menu {
                margin-bottom: 0;
            }

            .jam {
                margin-right: 10px;
            }

            .sidebar {
                position: sticky;
                top: 0;
                z-index: 1;
            }
        }

        <?php if ($_SESSION['level'] == 'Admin' or $_SESSION['level'] == 'admin'): ?>
            .copyright {
                margin-bottom: 25px;
            }

        <?php endif; ?>
    </style>
</head>

<body style="background-color: rgb(13, 10, 44)">
    <?php
    $maintenance_file = __DIR__ . '/config/maintenance.json'; // atau sesuaikan path
    $maintenance_data = json_decode(file_get_contents($maintenance_file), true);
    $catatan = isset($maintenance_data['catatan']) ? $maintenance_data['catatan'] : '';
    $status = isset($maintenance_data['status']) && $maintenance_data['status'] === 'on';
    ?>

    <?php if (
        $status &&
        isset($_SESSION['level']) &&
        (strtoupper($_SESSION['level']) === 'MENTOR' || strtoupper($_SESSION['level']) === 'MAHASISWA')
    ): ?>
        <div id="overlayText">
            <div style="display: flex; flex-direction: column; align-items: center; margin: 0 20px;">
                <dotlottie-player src="https://lottie.host/49a407fc-402f-403c-adcd-d34f7a2675c7/2WFbVNM1GT.lottie"
                    background="transparent" speed="1" style="width: 75%; height: 75%" loop autoplay></dotlottie-player>
                <?= htmlspecialchars($catatan) ?>
                <a class="btn btn-danger" href="logout.php" id="keluar"
                    style="color: #fff; margin-top: 20px; z-index: 10000; position: relative;">
                    <em class="fa fa-sign-out">&nbsp;</em> Keluar
                </a>
            </div>
        </div>
        <script>
            document.getElementById('keluar').addEventListener('click', function (e) {
                e.preventDefault(); // jika href tidak jalan
                window.location.href = 'logout.php'; // arahkan manual
            });

            // Tambahan: pastikan html dan body tidak bisa scroll
            document.documentElement.style.maxWidth = "100%";
            document.documentElement.style.overflow = "hidden";
            document.body.style.maxWidth = "100%";
            document.body.style.overflow = "hidden";
        </script>
    <?php endif; ?>
    <nav class="navbar navbar-custom navbar-fixed-top" style="background-color: rgb(13, 10, 44)" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#sidebar-collapse"><span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img style="margin-top: -13px;" src="source/img/logo-bpk.png"
                        width="50" alt="Logo"></a>
                <a class="navbar-brand brand" style="font-weight: bold; font-size: 1.8em;" href="#">SIPRESMAGMTH34</a>
                <div class="jam" href="#" style="float: right; padding: 13px 0; font-size: 1.7em;">
                    <span id="jam"></span>:<span id="menit"></span>:<span id="detik"></span> <span id="am-pm"></span>
                </div>
            </div>
        </div>
    </nav>
    <div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar"
        style="background-color: rgb(24, 18, 92); color: white;">
        <?php
        // Pastikan level pengguna diatur dan benar
        if (isset($_SESSION['level'])) {
            // Menampilkan info nama dan level admin di navbar
            if ($_SESSION['level'] == 'Admin' or $_SESSION['level'] == 'admin'): ?>
                <div class="profile-sidebar" style="position: sticky; top: 0; z-index: 4; background-color: rgb(24, 18, 92);">
                    <div class="profile-userpic">
                        <img src="source/img/profile.png" class="img-responsive" alt=""
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                    </div>
                    <div class="profile-usertitle">
                        <?php echo substr($_SESSION['nama_admin'], 0, 20); ?>
                        <div class="profile-usertitle-name" style="font-size: 1.2em; font-weight: bold;">
                            <?php echo "Administrator"; ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>

                <?php if ($_SESSION["level"] == "Admin" || $_SESSION["level"] == "admin"): ?>
                    <div style="position: sticky; top: 81px; z-index: 3; background-color: rgb(24, 18, 92); padding: 10px 17px;">
                        <input type="text" class="form-control filter-menu-input" placeholder="Cari menu..."
                            onkeyup="filterMenuByClass()" style="border-radius: 30px; padding: 6px 15px;" />
                    </div>
                <?php endif; ?>
            <?php endif;

            // Menampilkan info nama dan level mentor di navbar
            if ($_SESSION['level'] == 'Mentor' or $_SESSION['level'] == 'mentor'):
                // Pastikan kode_mentor diatur
                if (isset($_SESSION['kode_mentor'])) {
                    include('config/database.php');

                    // Ambil data mentor dari database
                    $kode_mentor = $_SESSION['kode_mentor'];
                    $query = "SELECT foto FROM tbl_mentor WHERE kode_mentor = '$kode_mentor'";
                    $result = mysqli_query($kon, $query);
                    $data = mysqli_fetch_assoc($result);

                    // Tetapkan URL gambar
                    if (empty($data['foto'])) {
                        $foto_url = "source/img/profile.png";
                    } else {
                        $foto_url = "apps/pengguna/foto_mentor/" . $data['foto'];
                    }
                } else {
                    // Tetapkan URL gambar default jika kode_mentor tidak ada
                    $foto_url = "source/img/profile.png";
                }
                ?>
                <div class="profile-sidebar" style="position: sticky; top: 0; z-index: 4; background-color: rgb(24, 18, 92);">
                    <div class="profile-userpic">
                        <img src="<?php echo $foto_url; ?>" class="img-responsive" alt=""
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                    </div>
                    <div class="profile-usertitle">
                        <?php echo substr($_SESSION['nama_mentor'], 0, 20); ?>
                        <div class="profile-usertitle-name" style="font-size: 1.2em; font-weight: bold;">
                            <?php echo "Mentor"; ?>
                        </div>
                        <div></div>
                    </div>
                    <div class="clear"></div>
                </div>

                <?php if ($_SESSION["level"] == "Mentor" || $_SESSION["level"] == "mentor"): ?>
                    <div style="position: sticky; top: 81px; z-index: 3; background-color: rgb(24, 18, 92); padding: 10px 17px;">
                        <input type="text" class="form-control filter-mentor-input" placeholder="Cari menu..."
                            onkeyup="filterMentorMenu()" style="border-radius: 30px; padding: 6px 15px;" />
                    </div>
                <?php endif; ?>
            <?php endif;

            // Menampilkan info nama dan level mahasiswa di navbar
            if ($_SESSION['level'] == 'Mahasiswa' or $_SESSION['level'] == 'mahasiswa'): ?>
                <div class="profile-sidebar" style="position: sticky; top: 0; z-index: 4; background-color: rgb(24, 18, 92);">
                    <div class="profile-userpic">
                        <img src="apps/mahasiswa/foto/<?php echo !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'foto_default.png'; ?>"
                            class="img-responsive" alt=""
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                    </div>
                    <div class="profile-usertitle">
                        <?php echo substr($_SESSION['nama_mahasiswa'], 0, 20); ?>
                        <div class="profile-usertitle-name" style="font-size: 1.2em; font-weight: bold;">
                            <?php echo "Karyawan Magang"; ?>
                        </div>
                        <div></div>
                    </div>
                    <div class="clear"></div>
                </div>

                <?php if ($_SESSION["level"] == "Mahasiswa" || $_SESSION["level"] == "mahasiswa"): ?>
                    <div style="position: sticky; top: 81px; z-index: 3; background-color: rgb(24, 18, 92); padding: 10px 17px;">
                        <input type="text" class="form-control filter-mahasiswa-input" placeholder="Cari menu..."
                            onkeyup="filterMahasiswaMenu()" style="border-radius: 30px; padding: 6px 15px;" />
                    </div>
                <?php endif; ?>
            <?php endif;
        } else {
            echo "Level pengguna tidak diatur.";
        }
        ?>

        <!-- Side Bar Navigation -->
        <div class="divider"></div>

        <!-- Menu Beranda -->
        <ul class="nav menu admin-menu-list mentor-menu-list mahasiswa-menu-list">
            <?php if ($_SESSION["level"] == "Mahasiswa" or $_SESSION["level"] == "mahasiswa"): ?>
                <li class="mentor-menu-item mahasiswa-menu-item">
                    <a href="index.php?download_panduan=true"
                        style="margin: 0 35px; border-radius: 30px; margin-bottom: 10px;" class="btn btn-warning">
                        <i class="fa fa-download"></i> Panduan Aplikasi
                    </a>
                </li>
            <?php endif; ?>
            <?php if ($_SESSION["level"] == "Mentor" or $_SESSION["level"] == "mentor"): ?>
                <li class="mentor-menu-item mahasiswa-menu-item">
                    <a href="index.php?download_panduan=true"
                        style="margin: 0 35px; border-radius: 30px; margin-bottom: 10px;" class="btn btn-warning">
                        <i class="fa fa-download"></i> Panduan Aplikasi
                    </a>
                </li>
            <?php endif; ?>
            <li class="admin-menu-item mentor-menu-item mahasiswa-menu-item">
                <a href='index.php?page=beranda' style="color: #fff;"><em class='fa fa-home'>&nbsp;</em> Beranda</a>
            </li>
            <!-- Menu Beranda -->
            <!-- Menu Admin -->
            <?php
            include 'config/database.php';

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if ($_SESSION["level"] == "Admin" || $_SESSION["level"] == "admin") {
                $query = "SELECT id_menu, menu, status, icon FROM tbl_setting_menu";
                $result = mysqli_query($kon, $query);
                $menus = [];
                $icons = [];

                // Notifikasi berdasarkan nama page (hanya menghitung yang Belum Dibaca)
                $notifikasi = [
                    'log_aktivitas' => mysqli_num_rows(mysqli_query($kon, "SELECT id_log_aktivitas FROM tbl_log_aktivitas WHERE notif = 'Belum Dibaca'")),
                    'data_absensi' => mysqli_num_rows(mysqli_query($kon, "SELECT id_absensi FROM tbl_absensi WHERE notif = 'Belum Dibaca'")),
                    'data_kegiatan' => mysqli_num_rows(mysqli_query($kon, "SELECT id_kegiatan FROM tbl_kegiatan WHERE notif = 'Belum Dibaca'")),
                    'data_laporan_magang' => mysqli_num_rows(mysqli_query($kon, "SELECT id_laporan FROM tbl_laporan WHERE notif = 'Belum Dibaca'")),
                    'data_nilai_kehadiran' => mysqli_num_rows(mysqli_query($kon, "SELECT id_nilai FROM tbl_nilai WHERE notif = 'Belum Dibaca'")),
                    'data_rating' => mysqli_num_rows(mysqli_query($kon, "SELECT id_rating FROM tbl_rating WHERE notif = 'Belum Dibaca'")),
                ];

                // Pemetaan id_menu ke nama page
                $id_menu_page = [
                    1 => 'log_aktivitas',
                    2 => 'mahasiswa',
                    3 => 'data_absensi',
                    4 => 'data_kegiatan',
                    5 => 'data_laporan_magang',
                    6 => 'data_selesai_magang',
                    7 => 'data_nilai_kehadiran',
                    8 => 'data_unit_kerja',
                    9 => 'data_jabatan',
                    10 => 'data_mentor',
                    11 => 'admin',
                    12 => 'panduan',
                    13 => 'backup_database',
                    14 => 'backup_aplikasi',
                    15 => 'data_rating',
                    16 => 'pengaturan'
                ];

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id_menu = $row['id_menu'];
                        $menu = $row['menu'];
                        $status = $row['status'];
                        $icon = $row['icon'];

                        if ($status == 'Aktif') {
                            // Tentukan nama halaman berdasarkan id_menu
                            switch ($id_menu) {
                                case 1:
                                    $page = 'log_aktivitas';
                                    break;
                                case 2:
                                    $page = 'mahasiswa';
                                    break;
                                case 3:
                                    $page = 'data_absensi';
                                    break;
                                case 4:
                                    $page = 'data_kegiatan';
                                    break;
                                case 5:
                                    $page = 'data_laporan_magang';
                                    break;
                                case 6:
                                    $page = 'data_selesai_magang';
                                    break;
                                case 7:
                                    $page = 'data_nilai_kehadiran';
                                    break;
                                case 8:
                                    $page = 'data_unit_kerja';
                                    break;
                                case 9:
                                    $page = 'data_jabatan';
                                    break;
                                case 10:
                                    $page = 'data_mentor';
                                    break;
                                case 11:
                                    $page = 'admin';
                                    break;
                                case 12:
                                    $page = 'panduan';
                                    break;
                                case 13:
                                    $page = 'backup_database';
                                    break;
                                case 14:
                                    $page = 'backup_aplikasi';
                                    break;
                                case 15:
                                    $page = 'data_rating';
                                    break;
                                case 16:
                                    $page = 'pengaturan';
                                    break;
                                default:
                                    $page = strtolower(str_replace(' ', '_', $menu));
                                    break;
                            }

                            $url = "index.php?page=" . $page;

                            // Ambil jumlah notif hanya jika tersedia
                            $jumlah = isset($notifikasi[$page]) ? $notifikasi[$page] : 0;

                            // Tampilkan badge hanya jika jumlah > 0
                            $notif_badge = ($jumlah > 0)
                                ? '<span style="background:red; color:white; border-radius:5px; padding:2px 7px; font-size:0.8em; margin-left:10px;">' . $jumlah . '</span>'
                                : '';

                            echo '<li class="admin-menu-item"><a href="' . $url . '" id="' . $page . '" style="color: #fff;">
                                <em class="' . $icon . '">&nbsp;</em> ' . $menu . $notif_badge . '
                            </a></li>';
                        }

                        $icons[$id_menu] = $icon;
                    }
                }

                // Update notif jadi 'Sudah Dibaca' ketika halaman dikunjungi
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                    switch ($page) {
                        case 'log_aktivitas':
                            mysqli_query($kon, "UPDATE tbl_log_aktivitas SET notif = 'Sudah Dibaca' WHERE notif = 'Belum Dibaca'");
                            break;
                        case 'data_absensi':
                            mysqli_query($kon, "UPDATE tbl_absensi SET notif = 'Sudah Dibaca' WHERE notif = 'Belum Dibaca'");
                            break;
                        case 'data_kegiatan':
                            mysqli_query($kon, "UPDATE tbl_kegiatan SET notif = 'Sudah Dibaca' WHERE notif = 'Belum Dibaca'");
                            break;
                        case 'data_laporan_magang':
                            mysqli_query($kon, "UPDATE tbl_laporan SET notif = 'Sudah Dibaca' WHERE notif = 'Belum Dibaca'");
                            break;
                        case 'data_nilai_kehadiran':
                            mysqli_query($kon, "UPDATE tbl_nilai SET notif = 'Sudah Dibaca' WHERE notif = 'Belum Dibaca'");
                            break;
                        case 'data_rating':
                            mysqli_query($kon, "UPDATE tbl_rating SET notif = 'Sudah Dibaca' WHERE notif = 'Belum Dibaca'");
                            break;
                    }
                }
            }
            ?>
            <!-- Menu Admin -->
            <!-- Menu Mentor -->
            <?php
            if ($_SESSION["level"] == "Mentor" || $_SESSION["level"] == "mentor"):
                include 'config/database.php';

                // Ambil kode mentor dari session
                $kode_mentor = $_SESSION['kode_mentor'] ?? '';

                // Siapkan badge notifikasi
                $notif_badge_absensi = '';
                $notif_badge_kegiatan = '';

                if (!empty($kode_mentor)) {
                    // Ambil semua id_mahasiswa yang dibimbing oleh mentor ini
                    $mahasiswa_query = mysqli_query($kon, "SELECT id_mahasiswa FROM tbl_mahasiswa WHERE kode_mentor = '$kode_mentor'");
                    $id_mahasiswa_list = [];
                    while ($row = mysqli_fetch_assoc($mahasiswa_query)) {
                        $id_mahasiswa_list[] = $row['id_mahasiswa'];
                    }

                    if (count($id_mahasiswa_list) > 0) {
                        $id_mahasiswa_in = implode(',', $id_mahasiswa_list);

                        // Hitung notif absensi
                        $q_absensi = mysqli_query($kon, "SELECT COUNT(*) AS total FROM tbl_absensi WHERE id_mahasiswa IN ($id_mahasiswa_in) AND notif = 'Belum Dibaca'");
                        $absensi_result = mysqli_fetch_assoc($q_absensi);
                        $jumlah_notif_absensi = $absensi_result['total'] ?? 0;
                        if ($jumlah_notif_absensi > 0) {
                            $notif_badge_absensi = '<span style="background:red; color:white; border-radius:5px; padding:2px 7px; font-size:0.8em; margin-left:10px;">' . $jumlah_notif_absensi . '</span>';
                        }

                        // Hitung notif kegiatan
                        $q_kegiatan = mysqli_query($kon, "SELECT COUNT(*) AS total FROM tbl_kegiatan WHERE id_mahasiswa IN ($id_mahasiswa_in) AND notif = 'Belum Dibaca'");
                        $kegiatan_result = mysqli_fetch_assoc($q_kegiatan);
                        $jumlah_notif_kegiatan = $kegiatan_result['total'] ?? 0;
                        if ($jumlah_notif_kegiatan > 0) {
                            $notif_badge_kegiatan = '<span style="background:red; color:white; border-radius:5px; padding:2px 7px; font-size:0.8em; margin-left:10px;">' . $jumlah_notif_kegiatan . '</span>';
                        }

                        // Jika halaman diklik, tandai notif sebagai 'Sudah Dibaca'
                        if (isset($_GET['page'])) {
                            // Memperbarui status notif absensi jika halaman yang diklik adalah data_absensi
                            if ($_GET['page'] == 'data_absensi') {
                                mysqli_query($kon, "UPDATE tbl_absensi SET notif = 'Sudah Dibaca' WHERE id_mahasiswa IN ($id_mahasiswa_in) AND notif = 'Belum Dibaca'");
                                $notif_badge_absensi = '';  // Hapus notifikasi yang tampil di menu
                            }
                            // Memperbarui status notif kegiatan jika halaman yang diklik adalah data_kegiatan
                            elseif ($_GET['page'] == 'data_kegiatan') {
                                mysqli_query($kon, "UPDATE tbl_kegiatan SET notif = 'Sudah Dibaca' WHERE id_mahasiswa IN ($id_mahasiswa_in) AND notif = 'Belum Dibaca'");
                                $notif_badge_kegiatan = '';  // Hapus notifikasi yang tampil di menu
                            }
                        }
                    }
                }
                ?>
                <li class="mentor-menu-item"class="mentor-menu-item"><a href="index.php?page=aktivitas_mentor" style="color: #fff;"><em
                            class="bi bi-activity">&nbsp;</em>
                        Catatan Aktivitas</a></li>
                <li class="mentor-menu-item"><a href="index.php?page=mahasiswa" id="mahasiswa" style="color: #fff;"><em
                            class="fa fa-users">&nbsp;</em>
                        Data Karyawan Magang</a></li>
                <li class="mentor-menu-item"><a href="index.php?page=data_absensi" id="data_absensi"
                        style="color: #fff;"><em class="fa fa-calendar">&nbsp;</em>
                        Data Presensi<?= $notif_badge_absensi ?></a></li>
                <li class="mentor-menu-item"><a href="index.php?page=data_kegiatan" id="kegiatan" style="color: #fff;"><em
                            class="fa fa-book">&nbsp;</em>
                        Data Kegiatan<?= $notif_badge_kegiatan ?></a></li>
                <li class="mentor-menu-item"><a href="index.php?page=data_nilai_kehadiran" id="data_kehadiran"
                        style="color: #fff;"><em class="bi bi-clipboard2-data-fill">&nbsp;</em>
                        Data Penilaian Kinerja</a></li>
                <li class="mentor-menu-item"><a href="index.php?page=profil_mentor" style="color: #fff;"><em
                            class="fa fa-user-circle-o">&nbsp;</em>
                        Profil</a></li>
            <?php endif; ?>
            <!-- Menu Mentor -->
            <!-- Menu Mahasiswa -->
            <?php
            if ($_SESSION["level"] == "Mahasiswa" or $_SESSION["level"] == "mahasiswa"):
                include 'config/database.php';

                // Ambil id_mahasiswa dari session, tanpa gunakan nama
                $id_mahasiswa = $_SESSION["id_mahasiswa"] ?? '';

                // Jika halaman Data Akhir Magang dibuka, ubah notif jadi 'Sudah Dibaca'
                if (isset($_GET['page']) && $_GET['page'] == 'suket_nilai_sertifikat' && !empty($id_mahasiswa)) {
                    mysqli_query($kon, "UPDATE tbl_suket SET notif = 'Sudah Dibaca' WHERE id_mahasiswa = '$id_mahasiswa' AND notif = 'Belum Dibaca'");
                }

                // Hitung jumlah notif yang belum dibaca
                $jumlah_notif_suket = 0;
                if (!empty($id_mahasiswa)) {
                    $cek_notif = mysqli_query($kon, "SELECT COUNT(*) AS total FROM tbl_suket WHERE id_mahasiswa = '$id_mahasiswa' AND notif = 'Belum Dibaca'");
                    if ($row = mysqli_fetch_assoc($cek_notif)) {
                        $jumlah_notif_suket = $row['total'];
                    }
                }

                // Tampilkan badge jika ada notif
                $notif_badge_suket = ($jumlah_notif_suket > 0)
                    ? '<span style="background:red; color:white; border-radius:5px; padding:2px 7px; font-size:0.8em; margin-left:10px;">' . $jumlah_notif_suket . '</span>'
                    : '';
                ?>

                <li class="mahasiswa-menu-item"><a href="index.php?page=aktivitas_mahasiswa" style="color: #fff;"><em class="bi bi-activity">&nbsp;</em>
                        Catatan Aktivitas</a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=absen" style="color: #fff;"><em class="fa fa-calendar-check-o">&nbsp;</em>
                        Presensi</a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=riwayat" style="color: #fff;"><em class="fa fa-history">&nbsp;</em> Riwayat
                        Presensi</a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=kegiatan" style="color: #fff;"><em class="fa fa-book">&nbsp;</em> Kegiatan
                        Harian</a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=rangking" style="color: #fff;"><em class="bi bi-bar-chart-line-fill">&nbsp;</em>
                        Peringkat Kedisplinan</a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=suket_nilai_sertifikat" style="color: #fff;">
                        <em class="bi bi-file-earmark-fill">&nbsp;</em> Data Akhir Magang<?= $notif_badge_suket ?>
                    </a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=laporan_magang" style="color: #fff;"><em class="bi bi-upload">&nbsp;</em> Unggah
                        Laporan Magang</a></li>
                <li class="mahasiswa-menu-item"><a href="index.php?page=profil" style="color: #fff;"><em class="fa fa-user-circle-o">&nbsp;</em>
                        Profil</a></li>
            <?php endif; ?>
            <!-- Menu Mahasiswa -->
            <!-- Menu Keluar -->
            <li class="admin-menu-item mentor-menu-item mahasiswa-menu-item">
                <a href="logout.php" id="keluar" style="color: #fff;"><em class="fa fa-sign-out">&nbsp;</em> Keluar</a>
            </li>
        </ul>
        <!-- Menu Keluar -->
    </div>
    <button class="chat-button" id="chatButton" title="Kritik dan Saran">
        <i class="bi bi-chat-left-text-fill"></i>
    </button>
    <nav class="navbar navbar-fixed-bottom d-flex text-center align-items-center"
        style="background-color: rgb(13, 10, 44); padding-top: 15px; border-top: 1px solid white; border-bottom: 1px solid white;"
        role="navigation">
        <span style="color: #fff;">Designed by <a style="text-decoration: none;"
                href="https://yuanthio.github.io/">Yuanthio Virly</a>
            <?php echo date("Y"); ?>
        </span>
    </nav>
    <!-- Side Bar Navigation -->

    <!-- Page Penghubung -->
    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            switch ($page) {
                case 'beranda':
                    include "apps/beranda/index.php";
                    break;
                case 'admin':
                    include "apps/admin/index.php";
                    break;
                case 'data_unit_kerja':
                    include "apps/data_unit_kerja/index.php";
                    break;
                case 'data_jabatan':
                    include "apps/data_jabatan/index.php";
                    break;
                case 'data_mentor':
                    include "apps/data_mentor/index.php";
                    break;
                case 'mahasiswa':
                    include "apps/mahasiswa/index.php";
                    break;
                case 'data_absensi':
                    include "apps/data_absensi/index.php";
                    break;
                case 'data_kegiatan':
                    include "apps/data_kegiatan/index.php";
                    break;
                case 'data_laporan_magang':
                    include "apps/data_laporan_magang/index.php";
                    break;
                case 'data_selesai_magang':
                    include "apps/data_selesai_magang/index.php";
                    break;
                case 'data_nilai_kehadiran':
                    include "apps/data_nilai_kehadiran/index.php";
                    break;
                case 'pengaturan':
                    include "apps/pengaturan/index.php";
                    break;
                case 'panduan':
                    include "apps/panduan/index.php";
                    break;
                case 'backup_database':
                    include "apps/database/backup_database.php";
                    break;
                case 'backup_aplikasi':
                    include "apps/backup_aplikasi/index.php";
                    break;
                case 'data_rating':
                    include "apps/data_rating/index.php";
                    break;
                case 'log_aktivitas':
                    include "apps/log_aktivitas/index.php";
                    break;
                case 'aktivitas_mahasiswa':
                    include "apps/log_aktivitas/aktivitas_mahasiswa.php";
                    break;
                case 'aktivitas_mentor':
                    include "apps/log_aktivitas/aktivitas_mentor.php";
                    break;
                case 'absen':
                    include "apps/pengguna/absen.php";
                    break;
                case 'riwayat':
                    include "apps/data_absensi/riwayat.php";
                    break;
                case 'kegiatan':
                    include "apps/data_kegiatan/kegiatan.php";
                    break;
                case 'rangking':
                    include "apps/data_nilai_kehadiran/rangking.php";
                    break;
                case 'laporan_magang':
                    include "apps/data_laporan_magang/laporan_magang.php";
                    break;
                case 'suket_nilai_sertifikat':
                    include "apps/data_selesai_magang/suket_nilai_sertifikat.php";
                    break;
                case 'profil':
                    include "apps/pengguna/profil.php";
                    break;
                case 'profil_mentor':
                    include "apps/pengguna/profil_mentor.php";
                    break;
                default:
                    echo "<center><h3>Maaf. Halaman Tidak Di Temukan !</h3></center>";
                    break;
            }
        }
        ?>
        <!-- Function Page Penghubung -->
    </div>
    <!--/.main-->

    <!-- Java Script -->
    <script src="template/js/bootstrap.min.js"></script>
    <script src="template/js/chart.min.js"></script>
    <script src="template/js/chart-data.js"></script>
    <script src="template/js/easypiechart.js"></script>
    <script src="template/js/easypiechart-data.js"></script>
    <script src="template/js/bootstrap-datepicker.js"></script>
    <script src="template/js/custom.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <script src="https://kit.fontawesome.com/4752e5dd73.js" crossorigin="anonymous"></script>
    <!-- <script src="/assets/chart/chart.js"></script> -->
    <!-- Java Script -->

    <script>
        $(document).ready(function () {
            $('[title]').tooltip();
        });
    </script>

    <script>
        // Event listener untuk saat tombol "Keluar" diklik
        document.getElementById('keluar').addEventListener('click', function (event) {
            event.preventDefault(); // Mencegah aksi default dari link

            // Tampilkan alert dari SweetAlert2
            Swal.fire({
                title: '<span style="font-size: 1.3em;">Apakah Anda Yakin?</span>',
                html: '<span style="font-size: 1.5em;">Anda akan keluar dari aplikasi.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Ya, keluar!</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulasi logout berhasil (biasanya akan dilakukan dengan redirect ke logout.php)
                    // Di sini kita hanya menampilkan alert SweetAlert2 setelah logout berhasil
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-size: 1.5em;">Logout Berhasil</span>',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        window.location.href = 'login.php';
                    });
                }
            });
        });
    </script>

    <script>
        document.getElementById('chatButton').addEventListener('click', function () {
            Swal.fire({
                title: '<span style="font-size: 1.5em;">Kritik dan Saran</span>',
                html: `
            <span style="font-size: 1.5em;">Beri rating serta kritik dan saran pada aplikasi Sipresmagmth34.</span>
            <div id="rating" style="font-size: 2em; margin-top: 10px;">
                <i class="bi bi-star" data-value="1" style="cursor:pointer;"></i>
                <i class="bi bi-star" data-value="2" style="cursor:pointer;"></i>
                <i class="bi bi-star" data-value="3" style="cursor:pointer;"></i>
                <i class="bi bi-star" data-value="4" style="cursor:pointer;"></i>
                <i class="bi bi-star" data-value="5" style="cursor:pointer;"></i>
            </div>
        `,
                input: 'textarea',
                inputPlaceholder: 'Tulis di sini...',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Kirim</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>',
                didOpen: () => {
                    let selectedRating = 0;
                    const stars = Swal.getPopup().querySelectorAll('#rating i');

                    stars.forEach(star => {
                        star.addEventListener('click', () => {
                            selectedRating = parseInt(star.dataset.value);
                            Swal.getPopup().setAttribute('data-rating', selectedRating);

                            stars.forEach(s => {
                                const val = parseInt(s.dataset.value);
                                s.classList.remove('bi-star-fill');
                                s.classList.add('bi-star');
                                s.style.color = '';
                                if (val <= selectedRating) {
                                    s.classList.remove('bi-star');
                                    s.classList.add('bi-star-fill');
                                    s.style.color = 'rgb(243, 224, 9)';
                                }
                            });
                        });
                    });
                },
                preConfirm: () => {
                    const saran = Swal.getInput().value.trim();
                    const rating = parseInt(Swal.getPopup().getAttribute('data-rating')) || 0;

                    if (!saran) {
                        Swal.showValidationMessage('Kritik atau saran tidak boleh kosong!');
                        return false;
                    }

                    if (rating === 0) {
                        Swal.showValidationMessage('Silakan pilih rating terlebih dahulu!');
                        return false;
                    }

                    return { saran, rating };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { saran, rating } = result.value;

                    // Tampilkan loading
                    let dotsInterval;
                    Swal.fire({
                        title: '<span style="font-size: 1.5em;">Mengirim</span>',
                        html: '<span id="loading-text" style="font-size: 1.5em;">Silakan tunggu</span><span id="dots" style="font-size: 1.5em;">.</span>',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            let dotCount = 1;
                            const maxDots = 3;
                            const dotsEl = document.getElementById('dots');
                            dotsInterval = setInterval(() => {
                                dotCount = (dotCount % maxDots) + 1;
                                dotsEl.textContent = '.'.repeat(dotCount);
                            }, 500);
                        },
                        willClose: () => {
                            clearInterval(dotsInterval);
                        }
                    });

                    // Kirim data ke server
                    fetch('simpan_rating.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `rating=${rating}&pesan=${encodeURIComponent(saran)}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '<span style="font-size: 1.5em;">Terima kasih!</span>',
                                    html: '<span style="font-size: 1.5em;">Kritik dan saran Anda sudah kami terima.</span>',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: '<span style="font-size: 1.5em;">Tutup</span>'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message || 'Terjadi kesalahan!',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: '<span style="font-size: 1.5em;">Tutup</span>'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Terjadi kesalahan:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Gagal mengirim data!',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: '<span style="font-size: 1.5em;">Tutup</span>'
                            });
                        });
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch('cek_rating.php')
                .then(response => response.json())
                .then(data => {
                    const chatButton = document.getElementById('chatButton');

                    if (data.hide_button === true) {
                        // Jangan lakukan apa-apa, tetap tersembunyi
                    } else {
                        if (chatButton) chatButton.style.display = 'block'; // Tampilkan tombol
                        if (data.tampil === true && chatButton) {
                            chatButton.click();
                        }
                    }
                })
                .catch(error => {
                    console.error('Gagal mengecek data rating:', error);
                });
        });
    </script>

    <script>
        function perbaruiWaktu() {
            var sekarang = new Date();
            var jam = sekarang.getHours();
            var menit = sekarang.getMinutes().toString().padStart(2, '0');
            var detik = sekarang.getSeconds().toString().padStart(2, '0');
            var amPm = jam >= 12 ? 'PM' : 'AM';

            // Ubah format 12 jam
            if (jam > 12) {
                jam = jam - 12;
            }
            if (jam === 0) {
                jam = 12;
            }

            document.getElementById("jam").textContent = jam.toString().padStart(2, '0');
            document.getElementById("menit").textContent = menit;
            document.getElementById("detik").textContent = detik;
            document.getElementById("am-pm").textContent = amPm;

            // Simpan waktu saat ini di localStorage
            localStorage.setItem("waktu", JSON.stringify(sekarang));
        }

        // Periksa apakah waktu sebelumnya disimpan di localStorage
        var waktuSebelumnya = localStorage.getItem("waktu");
        if (waktuSebelumnya) {
            perbaruiWaktu(); // Memanggil fungsi perbaruiWaktu untuk menampilkan waktu sebelumnya
        } else {
            perbaruiWaktu(); // Jika tidak ada waktu sebelumnya, tampilkan waktu saat ini
        }

        // Panggil perbaruiWaktu setiap detik
        setInterval(perbaruiWaktu, 1000);
    </script>

    <script>
        function filterMenuByClass() {
            const input = document.querySelector('.filter-menu-input');
            const filter = input.value.toLowerCase();
            const menuItems = document.querySelectorAll('.admin-menu-list .admin-menu-item');

            menuItems.forEach(item => {
                const link = item.querySelector('a');
                const text = link.textContent || link.innerText;
                item.style.display = text.toLowerCase().includes(filter) ? '' : 'none';
            });
        }
    </script>

    <script>
        function filterMentorMenu() {
            const input = document.querySelector('.filter-mentor-input');
            const filter = input.value.toLowerCase();
            const menuItems = document.querySelectorAll('.mentor-menu-list .mentor-menu-item');

            menuItems.forEach(item => {
                const link = item.querySelector('a');
                const text = link.textContent || link.innerText;
                item.style.display = text.toLowerCase().includes(filter) ? '' : 'none';
            });
        }
    </script>

    <script>
        function filterMahasiswaMenu() {
            const input = document.querySelector('.filter-mahasiswa-input');
            const filter = input.value.toLowerCase();
            const menuItems = document.querySelectorAll('.mahasiswa-menu-list .mahasiswa-menu-item');

            menuItems.forEach(item => {
                const link = item.querySelector('a');
                const text = link.textContent || link.innerText;
                item.style.display = text.toLowerCase().includes(filter) ? '' : 'none';
            });
        }
    </script>
</body>

</html>