<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");

    .card {
        color: rgb(24, 18, 92);
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
    }

    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
    }

    .jumlah-karyawan,
    .jumlah-data-kegiatan,
    .jumlah-data-administrator,
    .jumlah-riwayat-presensi,
    .jumlah-kegiatan-harian,
    .jumlah-data-laporan,
    .jumlah-data-mentor,
    .jumlah-data-terlambat,
    .jumlah-data-hadir,
    .jumlah-data-izin,
    .jumlah-data-akhir-magang {
        margin-bottom: 20px;
        font-size: 2.5em;
        font-weight: bold;
        background-color: rgb(24, 18, 92);
        color: white;
    }

    h3 {
        color: rgb(24, 18, 92);
        font-size: 1.5em;
    }

    .sisa-periode-magang {
        margin-bottom: 20px;
        font-size: 2em;
        font-weight: bold;
        background-color: rgb(24, 18, 92);
        color: white;
        padding: 5px;
    }

    .bg-success,
    .bg-danger,
    .bg-info {
        font-size: 1em;
    }

    .chart-container canvas {
        height: 500px;
    }

    .chart-container-karyawan {
        position: relative;
        width: 100%;
        height: 450px;
    }

    /* Overlay */
    #loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(24, 18, 92, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;

        opacity: 1;
        visibility: visible;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    /* Hidden state */
    #loader-overlay.fade-out {
        opacity: 0;
        visibility: hidden;
    }

    .loader {
        width: 50px;
        aspect-ratio: 1;
        display: grid;
    }

    .loader::before,
    .loader::after {
        content: "";
        grid-area: 1/1;
        --c: no-repeat radial-gradient(farthest-side, #25b09b 92%, #0000);
        background:
            var(--c) 50% 0,
            var(--c) 50% 100%,
            var(--c) 100% 50%,
            var(--c) 0 50%;
        background-size: 12px 12px;
        animation: l12 1s infinite;
    }

    .loader::before {
        margin: 4px;
        filter: hue-rotate(45deg);
        background-size: 8px 8px;
        animation-timing-function: linear
    }

    @keyframes l12 {
        100% {
            transform: rotate(.5turn)
        }
    }

    @media (max-width: 576px) {
        .tables p {
            font-size: 1em;
        }

        .chart-container canvas {
            height: 350px;
        }

        .chart-container-karyawan {
            height: 350px;
        }
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Beranda</li>
    </ol>
</div>
<marquee style="color: white; font-size: 1.5em; margin-top: 20px;">Aplikasi Presensi Magang Berbasis Website Badan
    Pemeriksa Keuangan Perwakilan Provinsi DKI Jakarta</marquee>
<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default" style="background-color: rgb(24, 18, 92);">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: white;">
                Beranda
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <!--Menampilkan Nama Pengguna Sesuai Level -->
                <?php if ($_SESSION['level'] == 'Admin' or $_SESSION['level'] == 'Admin'): ?>
                    <h3 style="color: white;">Selamat Datang,
                        <?php echo $_SESSION["nama_admin"]; ?>.
                    </h3>
                <?php endif; ?>
                <?php if ($_SESSION['level'] == 'Mentor' or $_SESSION['level'] == 'mentor'): ?>
                    <h3 style="color: white;">Selamat Datang,
                        <?php echo $_SESSION["nama_mentor"]; ?>.
                    </h3>
                <?php endif; ?>
                <?php if ($_SESSION['level'] == 'Mahasiswa' or $_SESSION['level'] == 'mahasiswa'): ?>
                    <h3 style="color: white;">Selamat Datang,
                        <?php echo $_SESSION["nama_mahasiswa"]; ?>.
                    </h3>
                <?php endif; ?>

                <!-- Mengambil data table tbl_site -->
                <?php
                include 'config/database.php';

                $query = mysqli_query($kon, "select * from tbl_site limit 1");
                $row = mysqli_fetch_array($query);
                ?>
                <p style="color: white;">Selamat Datang di Aplikasi Presensi dan Kegiatan Harian Karyawan Magang
                    berbasis Website. Sebuah sistem yang memungkinkan para Karyawan Magang PKL di
                    <?php echo $row['nama_instansi']; ?> untuk melalukan Presensi dan mencatat kegiatan harian dari
                    website. Sistem ini diharapkan dapat memberi kemudahan setiap Karyawan Magang PKL untuk melakukan
                    Presensi dan mencatat kegiatan harian.
                </p>
            </div>
        </div>
    </div>

    <!-- tampilan untuk administrator -->
    <?php
    include 'config/database.php';
    include 'config/function.php';

    date_default_timezone_set('Asia/Jakarta');
    function ubahNamaHari($hariInggris)
    {
        $namaHariIndonesia = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        return $namaHariIndonesia[$hariInggris];
    }

    // Menambahkan kondisi untuk mengecek level login
    if ($_SESSION['level'] == 'Admin' || $_SESSION['level'] == 'admin') {
        // Menghitung jumlah data di tbl_mahasiswa
        $queryCount = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_mahasiswa");
        $rowCount = mysqli_fetch_assoc($queryCount);
        $totalMahasiswa = $rowCount['total'];

        // Menghitung jumlah data di tbl_kegiatan
        $queryCountKegiatan = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_kegiatan");
        $rowCountKegiatan = mysqli_fetch_assoc($queryCountKegiatan);
        $totalKegiatan = $rowCountKegiatan['total'];

        // Menghitung jumlah data di tbl_admin
        $queryCountAdmin = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_admin");
        $rowCountAdmin = mysqli_fetch_assoc($queryCountAdmin);
        $totalAdmin = $rowCountAdmin['total'];

        // Menghitung jumlah data di tbl_mentor
        $queryCountMentor = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_mentor");
        $rowCountMentor = mysqli_fetch_assoc($queryCountMentor);
        $totalMentor = $rowCountMentor['total'];

        // Menghitung jumlah data di tbl_laporan
        $queryCountLaporan = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_laporan");
        $rowCountLaporan = mysqli_fetch_assoc($queryCountLaporan);
        $totalLaporan = $rowCountLaporan['total'];

        // Menghitung jumlah data di tbl_suket
        $queryCountAkhirMagang = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_suket");
        $rowCountAkhirMagang = mysqli_fetch_assoc($queryCountAkhirMagang);
        $totalAkhirMagang = $rowCountAkhirMagang['total'];

        // Mengambil data konfirmasi_status dari tbl_absensi
        $queryAbsensi = mysqli_query($kon, "SELECT konfirmasi_status, COUNT(*) as jumlah FROM tbl_absensi GROUP BY konfirmasi_status");
        $dataAbsensi = array();
        while ($rowAbsensi = mysqli_fetch_assoc($queryAbsensi)) {
            $dataAbsensi[$rowAbsensi['konfirmasi_status']] = $rowAbsensi['jumlah'];
        }

        // Menghitung jumlah mahasiswa aktif
        $queryAktif = mysqli_query($kon, "SELECT COUNT(*) as totalAktif FROM tbl_mahasiswa WHERE status_magang = 'Aktif'");
        $rowAktif = mysqli_fetch_assoc($queryAktif);
        $totalAktif = $rowAktif['totalAktif'];

        // Menghitung jumlah mahasiswa tidak aktif
        $queryTidakAktif = mysqli_query($kon, "SELECT COUNT(*) as totalTidakAktif FROM tbl_mahasiswa WHERE status_magang = 'Tidak Aktif'");
        $rowTidakAktif = mysqli_fetch_assoc($queryTidakAktif);
        $totalTidakAktif = $rowTidakAktif['totalTidakAktif'];

        // Menghitung jumlah data absensi terlambat hari ini
        $today = date('Y-m-d');
        $queryTerlambatHariIni = mysqli_query($kon, "SELECT COUNT(*) as totalTerlambat FROM tbl_absensi WHERE status = 3 AND DATE(tanggal) = '$today'");
        $rowTerlambatHariIni = mysqli_fetch_assoc($queryTerlambatHariIni);
        $totalTerlambatHariIni = $rowTerlambatHariIni['totalTerlambat'];

        // Menghitung jumlah data absensi hadir hari ini
        $today = date('Y-m-d');
        $queryHadirHariIni = mysqli_query($kon, "SELECT COUNT(*) as totalHadir FROM tbl_absensi WHERE status = 1 AND DATE(tanggal) = '$today'");
        $rowHadirHariIni = mysqli_fetch_assoc($queryHadirHariIni);
        $totalHadirHariIni = $rowHadirHariIni['totalHadir'];

        // Menghitung jumlah data absensi izin hari ini
        $today = date('Y-m-d');
        $queryIzinHariIni = mysqli_query($kon, "SELECT COUNT(*) as totalIzin FROM tbl_absensi WHERE status = 2 AND DATE(tanggal) = '$today'");
        $rowIzinHariIni = mysqli_fetch_assoc($queryIzinHariIni);
        $totalIzinHariIni = $rowIzinHariIni['totalIzin'];

        // Query untuk mengambil data mahasiswa yang terlambat
        $queryTerlambat = mysqli_query($kon, "SELECT a.id_absensi, a.id_mahasiswa, a.status, a.waktu, a.tanggal, a.konfirmasi_status, 
        m.nama, m.universitas 
        FROM tbl_absensi a 
        JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
        WHERE a.status = 3 AND DATE(a.tanggal) = '$today'");

        // Query untuk mengambil data mahasiswa yang hadir
        $queryHadir = mysqli_query($kon, "SELECT a.id_absensi, a.id_mahasiswa, a.status, a.waktu, a.tanggal, a.konfirmasi_status, 
        m.nama, m.universitas 
        FROM tbl_absensi a 
        JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
        WHERE a.status = 1 AND DATE(a.tanggal) = '$today'");

        // Query untuk mengambil data mahasiswa yang izin
        $queryIzin = mysqli_query($kon, "SELECT a.id_absensi, a.id_mahasiswa, a.status, a.waktu, a.tanggal, a.konfirmasi_status, 
        m.nama, m.universitas 
        FROM tbl_absensi a 
        JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa 
        WHERE a.status = 2 AND DATE(a.tanggal) = '$today'");

        // Mengecek apakah alert sudah pernah ditampilkan dalam sesi ini
        if (!isset($_SESSION['alert_shown'])) {
            $today = date('Y-m-d');
            $queryMahasiswaHampirHabis = mysqli_query($kon, "SELECT m.id_mahasiswa, m.nama, m.akhir_magang 
                                                         FROM tbl_mahasiswa m 
                                                         LEFT JOIN tbl_suket s ON m.id_mahasiswa = s.id_mahasiswa 
                                                         WHERE DATEDIFF(m.akhir_magang, '$today') <= 3 
                                                           AND m.akhir_magang >= CURDATE() 
                                                           AND s.id_mahasiswa IS NULL");

            // Memasukkan data mahasiswa yang memenuhi kriteria ke dalam array
            $mahasiswaHampirHabis = array();
            while ($row = mysqli_fetch_assoc($queryMahasiswaHampirHabis)) {
                $mahasiswaHampirHabis[] = $row;
            }

            // Jika ada mahasiswa yang memenuhi kriteria, tampilkan SweetAlert
            if (!empty($mahasiswaHampirHabis)) {
                $namaMahasiswa = array_column($mahasiswaHampirHabis, 'nama');
                $tanggalAkhirMagang = array_unique(array_column($mahasiswaHampirHabis, 'akhir_magang'));
                $tanggalAkhirMagangString = '';
                $countTanggal = count($tanggalAkhirMagang);

                foreach ($tanggalAkhirMagang as $index => $tanggal) {
                    $tanggal_parts = explode('-', $tanggal);
                    $tanggalAkhirMagangString .= $tanggal_parts[2] . ' ' . MendapatkanBulan((int) $tanggal_parts[1]) . ' ' . $tanggal_parts[0];

                    if ($index < $countTanggal - 1) {
                        if ($countTanggal > 2 && $index == $countTanggal - 2) {
                            $tanggalAkhirMagangString .= ' dan ';
                        } else {
                            $tanggalAkhirMagangString .= ', ';
                        }
                    }
                }

                // Menggabungkan nama mahasiswa menjadi string dengan "dan" sebelum nama terakhir
                if (count($namaMahasiswa) > 1) {
                    $lastMahasiswa = array_pop($namaMahasiswa);
                    $namaMahasiswaString = implode(', ', $namaMahasiswa) . ' dan ' . $lastMahasiswa;
                } else {
                    $namaMahasiswaString = $namaMahasiswa[0];
                }

                echo "<script>
                    $(document).ready(function () {
                        Swal.fire({
                            title: '<span style=\"font-size: 1.5em;\">Informasi!</span>',
                            html: '<span style=\"font-size: 1.5em;\">Karyawan magang atas nama <b>" . $namaMahasiswaString . "</b> periode magangnya akan segera berakhir pada tanggal <b>" . $tanggalAkhirMagangString . "</b>, harap unggah data akhir magang.</span>',
                            icon: 'info',
                            confirmButtonText: '<span style=\"font-size: 1.5em;\">Ok</span>'
                        });
                    });
                  </script>";

                // Menandai bahwa alert sudah ditampilkan dalam sesi ini
                $_SESSION['alert_shown'] = true;
            }
        }

        // Mendapatkan data absensi dari database dan mengelompokkannya berdasarkan bulan dan status
        $query = "SELECT 
        MONTH(tanggal) as bulan, 
        YEAR(tanggal) as tahun,
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as terlambat,
        SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as tidak_hadir
        FROM tbl_absensi
        GROUP BY tahun, bulan
        ORDER BY tahun, bulan";
        $result = mysqli_query($kon, $query);

        $dataAbsensi = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $bulanTahun = $row['bulan'] . '-' . $row['tahun'];
            $dataAbsensi[$bulanTahun] = [
                'bulan' => $row['bulan'],
                'tahun' => $row['tahun'],
                'hadir' => $row['hadir'],
                'izin' => $row['izin'],
                'terlambat' => $row['terlambat'],
                'tidak_hadir' => $row['tidak_hadir']
            ];
        }
        ?>
        <div class="col-md-12">
            <div class="panel panel-default" style="background-color: rgb(24, 18, 92);">
                <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: white;">
                    <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                            class="fa fa-toggle-up"></em></span>
                </div>
                <div class="panel-body">
                    <div class="row card-admin">
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-karyawan">
                                    <?php echo $totalMahasiswa; ?>
                                </div>
                                <i class="fa fa-users" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Karyawan Magang</h3>
                                <div class="row">
                                    <div class="col-lg-6" style="margin-bottom: 20px;">
                                        <div class="bg-success" style="padding: 10px;">
                                            Aktif :
                                            <?php echo $totalAktif; ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" style="margin-bottom: 20px;">
                                        <div class="bg-danger" style="padding: 10px;">
                                            Tidak Aktif :
                                            <?php echo $totalTidakAktif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=mahasiswa" class="btn btn-primary"><i
                                            class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-terlambat">
                                    <?php echo $totalTerlambatHariIni; ?>
                                </div>
                                <img src="apps/beranda/expired.png" width="50" alt="icons">
                                <h3 style="margin-bottom: 20px;">Terlambat Hari Ini</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_absensi"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-hadir">
                                    <?php echo $totalHadirHariIni ?>
                                </div>
                                <img src="apps/beranda/user.png" width="50" alt="icons">
                                <h3 style="margin-bottom: 20px;">Hadir Hari Ini</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_absensi"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-izin">
                                    <?php echo $totalIzinHariIni ?>
                                </div>
                                <img src="apps/beranda/key.png" width="50" alt="icons">
                                <h3 style="margin-bottom: 20px;">Izin Hari Ini</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_absensi"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row tables">
                        <div class="col-md-6 bottom-40px" style="margin-bottom: 30px;">
                            <div class="enkapsulasi-table-hadir"
                                style="background-color: white; padding: 10px; border-radius: 5px;">
                                <p style="color: #000; font-size: 1.1em;">Daftar Karyawan Magang Hadir [Hari Ini]</p>
                                <div class="table-responsive table-hadir">
                                    <table class="table table-bordered table-light" id="dataTable" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>Universitas / Sekolah</th>
                                                <th>Status</th>
                                                <th>Waktu</th>
                                                <th>Hari</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            $liburSedangBerlangsung = false;

                                            // Cek libur dari tabel tanggal libur
                                            $queryLibur = mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur 
                                            WHERE status = 'Sedang berlangsung' 
                                            AND CURDATE() BETWEEN tanggal_awal AND tanggal_akhir");

                                            if (mysqli_num_rows($queryLibur) > 0) {
                                                $liburSedangBerlangsung = true;
                                                while ($rowLibur = mysqli_fetch_assoc($queryLibur)) {
                                                    $tanggalAwal = $rowLibur['tanggal_awal'];
                                                    $tanggalAkhir = $rowLibur['tanggal_akhir'];
                                                    $alasan = $rowLibur['alasan_libur'];

                                                    $tanggalTampil = ($tanggalAwal == $tanggalAkhir)
                                                        ? date('d-m-Y', strtotime($tanggalAwal))
                                                        : date('d-m-Y', strtotime($tanggalAwal)) . ' - ' . date('d-m-Y', strtotime($tanggalAkhir));
                                                    echo "<tr><td colspan='7' class='text-center'>Libur $alasan ($tanggalTampil)</td></tr>";
                                                }
                                            }

                                            // Cek libur mingguan dari tbl_hari_libur
                                            $hariID = date('N');
                                            $queryHariLibur = mysqli_query($kon, "SELECT status FROM tbl_hari_libur WHERE id = '$hariID' LIMIT 1");
                                            $dataHariLibur = mysqli_fetch_assoc($queryHariLibur);

                                            if ($dataHariLibur && $dataHariLibur['status'] === 'Libur') {
                                                $liburSedangBerlangsung = true;
                                                echo "<tr><td colspan='7' class='text-center'>Data tidak ditampilkan karena hari libur</td></tr>";
                                            }

                                            // Jika tidak sedang libur
                                            if (!$liburSedangBerlangsung) {
                                                if (mysqli_num_rows($queryHadir) > 0) {
                                                    while ($rowHadir = mysqli_fetch_assoc($queryHadir)) {
                                                        $hariIndonesia = ubahNamaHari(date('l', strtotime($rowHadir['tanggal'])));
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $rowHadir['nama'] ?></td>
                                                            <td><?= $rowHadir['universitas'] ?></td>
                                                            <td><span class="label label-success">Hadir</span></td>
                                                            <td><?= $rowHadir['waktu'] ?></td>
                                                            <td><?= $hariIndonesia ?></td>
                                                            <td><?= $rowHadir['tanggal'] ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>Data masih kosong</td></tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" style="margin-bottom: 30px;">
                            <div class="enkapsulasi-table-izin"
                                style="background-color: white; padding: 10px; border-radius: 5px;">
                                <p style="color: #000; font-size: 1.1em;">Daftar Karyawan Magang Izin [Hari Ini]</p>
                                <div class="table-responsive table-izin">
                                    <table class="table table-bordered table-light" id="dataTable" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>Universitas / Sekolah</th>
                                                <th>Status</th>
                                                <th>Waktu</th>
                                                <th>Hari</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            $liburSedangBerlangsung = false;

                                            // Cek libur berdasarkan tanggal rentang di tbl_tanggal_libur
                                            $queryLibur = mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur 
                                            WHERE status = 'Sedang berlangsung' 
                                            AND CURDATE() BETWEEN tanggal_awal AND tanggal_akhir");

                                            if (mysqli_num_rows($queryLibur) > 0) {
                                                $liburSedangBerlangsung = true;
                                                while ($rowLibur = mysqli_fetch_assoc($queryLibur)) {
                                                    $tanggalAwal = $rowLibur['tanggal_awal'];
                                                    $tanggalAkhir = $rowLibur['tanggal_akhir'];
                                                    $alasan = $rowLibur['alasan_libur'];

                                                    $tanggalTampil = ($tanggalAwal == $tanggalAkhir)
                                                        ? date('d-m-Y', strtotime($tanggalAwal))
                                                        : date('d-m-Y', strtotime($tanggalAwal)) . ' - ' . date('d-m-Y', strtotime($tanggalAkhir));
                                                    ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">
                                                            Libur <?= $alasan ?> (<?= $tanggalTampil ?>)
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }

                                            // Cek hari libur mingguan dari tbl_hari_libur
                                            $hariID = date('N'); // 1=Senin ... 7=Minggu
                                            $queryHariLibur = mysqli_query($kon, "SELECT status FROM tbl_hari_libur WHERE id = '$hariID' LIMIT 1");
                                            $dataHariLibur = mysqli_fetch_assoc($queryHariLibur);

                                            if ($dataHariLibur && $dataHariLibur['status'] === 'Libur') {
                                                $liburSedangBerlangsung = true;
                                                echo "<tr><td colspan='7' class='text-center'>Data tidak ditampilkan karena hari libur</td></tr>";
                                            }

                                            // Tampilkan data izin jika tidak sedang libur
                                            if (!$liburSedangBerlangsung) {
                                                if (mysqli_num_rows($queryIzin) > 0) {
                                                    while ($rowIzin = mysqli_fetch_assoc($queryIzin)) {
                                                        $hariIndonesia = ubahNamaHari(date('l', strtotime($rowIzin['tanggal'])));
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $rowIzin['nama'] ?></td>
                                                            <td><?= $rowIzin['universitas'] ?></td>
                                                            <td><span class="label label-info">Izin</span></td>
                                                            <td><?= $rowIzin['waktu'] ?></td>
                                                            <td><?= $hariIndonesia ?></td>
                                                            <td><?= $rowIzin['tanggal'] ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>Data masih kosong</td></tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row tables">
                        <div class="col-md-6" style="margin-bottom: 30px;">
                            <div class="enkapsulasi-table-terlambat"
                                style="background-color: white; padding: 10px; border-radius: 5px;">
                                <p style="color: #000; font-size: 1.1em;">Daftar Karyawan Magang Terlambat [Hari Ini]</p>
                                <div class="table-responsive table-terlambat">
                                    <table class="table table-bordered table-light" id="dataTable" width="100%"
                                        cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>Universitas / Sekolah</th>
                                                <th>Status</th>
                                                <th>Waktu</th>
                                                <th>Hari</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            $liburSedangBerlangsung = false;

                                            // Cek libur dari tabel tanggal libur
                                            $queryLibur = mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur 
                                            WHERE status = 'Sedang berlangsung' 
                                            AND CURDATE() BETWEEN tanggal_awal AND tanggal_akhir");

                                            if (mysqli_num_rows($queryLibur) > 0) {
                                                $liburSedangBerlangsung = true;
                                                while ($rowLibur = mysqli_fetch_assoc($queryLibur)) {
                                                    $tanggalAwal = $rowLibur['tanggal_awal'];
                                                    $tanggalAkhir = $rowLibur['tanggal_akhir'];
                                                    $alasan = $rowLibur['alasan_libur'];

                                                    $tanggalTampil = ($tanggalAwal == $tanggalAkhir)
                                                        ? date('d-m-Y', strtotime($tanggalAwal))
                                                        : date('d-m-Y', strtotime($tanggalAwal)) . ' - ' . date('d-m-Y', strtotime($tanggalAkhir));
                                                    echo "<tr><td colspan='7' class='text-center'>Libur $alasan ($tanggalTampil)</td></tr>";
                                                }
                                            }

                                            // Cek libur mingguan dari tabel hari libur
                                            $hariID = date('N'); // 1 = Senin, ..., 7 = Minggu
                                            $queryHariLibur = mysqli_query($kon, "SELECT status FROM tbl_hari_libur WHERE id = '$hariID' LIMIT 1");
                                            $dataHariLibur = mysqli_fetch_assoc($queryHariLibur);

                                            if ($dataHariLibur && $dataHariLibur['status'] === 'Libur') {
                                                $liburSedangBerlangsung = true;
                                                echo "<tr><td colspan='7' class='text-center'>Data tidak ditampilkan karena hari libur</td></tr>";
                                            }

                                            // Tampilkan data Terlambat jika tidak sedang libur
                                            if (!$liburSedangBerlangsung) {
                                                if (mysqli_num_rows($queryTerlambat) > 0) {
                                                    while ($rowTerlambat = mysqli_fetch_assoc($queryTerlambat)) {
                                                        $hariIndonesia = ubahNamaHari(date('l', strtotime($rowTerlambat['tanggal'])));
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $rowTerlambat['nama'] ?></td>
                                                            <td><?= $rowTerlambat['universitas'] ?></td>
                                                            <td><span class="label label-warning">Terlambat</span></td>
                                                            <td><?= $rowTerlambat['waktu'] ?></td>
                                                            <td><?= $hariIndonesia ?></td>
                                                            <td><?= $rowTerlambat['tanggal'] ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>Data masih kosong</td></tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $tahunTersedia = [];

        foreach ($dataAbsensi as $key => $value) {
            $parts = explode('-', $key);
            $tahun = $parts[1];
            if (!in_array($tahun, $tahunTersedia)) {
                $tahunTersedia[] = $tahun;
            }
        }

        sort($tahunTersedia); // urutkan tahun naik
        ?>

        <?php
        include "config/database.php";

        // Ambil semua data magang per bulan dan tahun
        $query = "SELECT 
            YEAR(mulai_magang) AS tahun, 
            MONTH(mulai_magang) AS bulan, 
            COUNT(*) AS jumlah 
          FROM tbl_mahasiswa 
          GROUP BY YEAR(mulai_magang), MONTH(mulai_magang) 
          ORDER BY tahun, bulan";

        $result = mysqli_query($kon, $query);

        // Siapkan array tahun dan data magang
        $dataMagangSemuaTahun = [];
        $tahunTersedia = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $tahun = $row['tahun'];
            $bulan = $row['bulan'];
            $jumlah = $row['jumlah'];
            $key = $bulan . '-' . $tahun;

            $dataMagangSemuaTahun[$tahun][$bulan] = $jumlah;

            if (!in_array($tahun, $tahunTersedia)) {
                $tahunTersedia[] = $tahun;
            }
        }

        // Sort tahun secara descending
        rsort($tahunTersedia);
        ?>

        <div class="col-md-12">
            <div class="panel panel-default" style="background-color: rgb(255, 255, 255);">
                <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: white;">
                    Statistik Presensi
                    <span class="pull-right clickable panel-toggle panel-button-tab-left">
                        <em class="fa fa-toggle-up"></em>
                    </span>
                </div>
                <div class="panel-body">
                    <h5 class="text-center" style="font-weight: bold;">Statistik presensi perbulan</h5>
                    <div class="text-center mb-3">
                        <label for="selectTahun"><strong>Pilih Tahun:</strong></label>
                        <select id="selectTahun" class="form-control" style="width: 200px; display: inline-block;">
                            <?php foreach ($tahunTersedia as $tahun): ?>
                                <option value="<?= $tahun ?>" <?= $tahun == date('Y') ? 'selected' : '' ?>><?= $tahun ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="chartAbsensi"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default" style="background-color: rgb(255, 255, 255);">
                <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: white;">
                    Statistik Jumlah Karyawan Magang
                    <span class="pull-right clickable panel-toggle panel-button-tab-left">
                        <em class="fa fa-toggle-up"></em>
                    </span>
                </div>
                <div class="panel-body">
                    <h5 class="text-center" style="font-weight: bold;">Jumlah Karyawan Magang yang masuk setiap bulan</h5>
                    <div class="text-center mb-3">
                        <label for="tahunSelect">Pilih Tahun:</label>
                        <select id="tahunSelect" class="form-control" style="width: 200px; display: inline-block;">
                            <?php foreach ($tahunTersedia as $tahun): ?>
                                <option value="<?= $tahun ?>" <?= $tahun == date('Y') ? 'selected' : '' ?>><?= $tahun ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="chartMagang"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="margin-bottom: 50px;">
            <div class="panel panel-default" style="background-color: rgb(24, 18, 92);">
                <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: white;">
                    <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                            class="fa fa-toggle-up"></em></span>
                </div>
                <div class="panel-body">
                    <div class="row tables">
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-kegiatan">
                                    <?php echo $totalKegiatan; ?>
                                </div>
                                <i class="fa fa-book" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Data Kegiatan</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_kegiatan"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-akhir-magang">
                                    <?php echo $totalAkhirMagang; ?>
                                </div>
                                <i class="bi bi-file-earmark-fill" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Data Akhir Magang</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_selesai_magang"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-mentor">
                                    <?php echo $totalMentor; ?>
                                </div>
                                <i class="fa fa-users" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Mentor</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_mentor"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-laporan">
                                    <?php echo $totalLaporan; ?>
                                </div>
                                <i class="bi bi-file-earmark-fill" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Data Laporan Magang</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_laporan_magang"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-administrator">
                                    <?php echo $totalAdmin; ?>
                                </div>
                                <i class="fa fa-user" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Administrator</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=admin" class="btn btn-primary"><i
                                            class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://kit.fontawesome.com/4752e5dd73.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            var dataAbsensi = <?php echo json_encode($dataAbsensi); ?>;
            var namaBulan = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            function getFilteredAbsensiData(tahun) {
                var filtered = {};
                for (var key in dataAbsensi) {
                    var parts = key.split('-');
                    if (parseInt(parts[1]) === parseInt(tahun)) {
                        filtered[key] = dataAbsensi[key];
                    }
                }
                return filtered;
            }

            function generateChartData(filteredData) {
                var labels = [];
                var hadir = [], izin = [], terlambat = [], tidakHadir = [];

                for (var i = 0; i < 12; i++) {
                    var key = (i + 1) + '-' + selectedTahun;
                    var data = filteredData[key] || { hadir: 0, izin: 0, terlambat: 0, tidak_hadir: 0 };
                    labels.push(namaBulan[i]);
                    hadir.push(data.hadir);
                    izin.push(data.izin);
                    terlambat.push(data.terlambat);
                    tidakHadir.push(data.tidak_hadir);
                }

                return {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Hadir',
                            data: hadir,
                            backgroundColor: 'rgba(75, 192, 87, 0.7)',
                            borderWidth: 1
                        },
                        {
                            label: 'Izin',
                            data: izin,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderWidth: 1
                        },
                        {
                            label: 'Terlambat',
                            data: terlambat,
                            backgroundColor: 'rgba(255, 206, 86, 0.7)',
                            borderWidth: 1
                        },
                        {
                            label: 'Tidak Hadir',
                            data: tidakHadir,
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderWidth: 1
                        }
                    ]
                };
            }

            var selectedTahun = document.getElementById('selectTahun').value;
            var ctx = document.getElementById('chartAbsensi').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: generateChartData(getFilteredAbsensiData(selectedTahun)),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            document.getElementById('selectTahun').addEventListener('change', function () {
                selectedTahun = this.value;
                var newFiltered = getFilteredAbsensiData(selectedTahun);
                var newChartData = generateChartData(newFiltered);

                chart.data = newChartData;
                chart.update();
            });
        </script>

        <script>
            var semuaDataMagang = <?php echo json_encode($dataMagangSemuaTahun); ?>;
            var namaBulan = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            var ctx2 = document.getElementById('chartMagang').getContext('2d');
            var chartMagang;

            function updateChartMagang(tahun) {
                var dataTahun = semuaDataMagang[tahun] || {};
                var dataPerBulan = Array(12).fill(0);

                for (var bulan = 1; bulan <= 12; bulan++) {
                    if (dataTahun[bulan]) {
                        dataPerBulan[bulan - 1] = dataTahun[bulan];
                    }
                }

                if (chartMagang) {
                    chartMagang.data.datasets[0].data = dataPerBulan;
                    chartMagang.update();
                } else {
                    chartMagang = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: namaBulan,
                            datasets: [{
                                label: 'Jumlah',
                                data: dataPerBulan,
                                backgroundColor: 'rgba(153, 102, 255, 0.7)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }

            // Inisialisasi dengan tahun sekarang
            var tahunDefault = document.getElementById('tahunSelect').value;
            updateChartMagang(tahunDefault);

            // Update saat tahun diganti
            document.getElementById('tahunSelect').addEventListener('change', function () {
                updateChartMagang(this.value);
            });
        </script>

        <script>
            $(document).ready(function () {
                var maxCardHeight = 0;

                $(".card").each(function () {
                    var cardHeight = $(this).outerHeight();
                    if (cardHeight > maxCardHeight) {
                        maxCardHeight = cardHeight;
                    }
                });

                $(".card").height(maxCardHeight);
            });
        </script>
        <?php
    }
    ?>

    <!-- tampilan untuk mentor -->
    <?php
    if ($_SESSION['level'] == 'Mentor' || $_SESSION['level'] == 'mentor') {
        date_default_timezone_set('Asia/Jakarta');
        // Mendapatkan kode mentor dari sesi
        $kodeMentor = $_SESSION['kode_mentor'];

        // Menghitung jumlah mahasiswa yang di bimbing oleh mentor
        $queryCountMahasiswa = "SELECT COUNT(*) AS totalMahasiswa FROM tbl_mahasiswa WHERE kode_mentor = '$kodeMentor'";
        $resultCountMahasiswa = mysqli_query($kon, $queryCountMahasiswa);
        $totalMahasiswa = mysqli_fetch_assoc($resultCountMahasiswa)['totalMahasiswa'];

        // Menghitung jumlah data kegiatan mahasiswa yang di bimbing oleh mentor
        $queryCountKegiatan = "SELECT COUNT(*) AS totalKegiatan FROM tbl_kegiatan WHERE id_mahasiswa IN (SELECT id_mahasiswa FROM tbl_mahasiswa WHERE kode_mentor = '$kodeMentor')";
        $resultCountKegiatan = mysqli_query($kon, $queryCountKegiatan);
        $totalKegiatan = mysqli_fetch_assoc($resultCountKegiatan)['totalKegiatan'];
        ?>
        <div class="col-md-12" style="margin-bottom: 50px;">
            <div class="panel panel-default" style="background-color: rgb(24, 18, 92);">
                <div class="panel-body">
                    <div class="row card-admin">
                        <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-karyawan">
                                    <?php echo $totalMahasiswa; ?>
                                </div>
                                <i class="fa fa-users" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Karyawan Magang</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=mahasiswa" class="btn btn-primary"><i
                                            class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                            <div class="card text-center">
                                <div class="jumlah-data-kegiatan">
                                    <?php echo $totalKegiatan; ?>
                                </div>
                                <i class="fa fa-book" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Data Kegiatan</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=data_kegiatan"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                var maxCardHeight = Math.max.apply(null, $(".card").map(function () {
                    return $(this).height();
                }).get());

                $(".card").height(maxCardHeight);
            });
        </script>
        <?php
    }
    ?>

    <!-- tampilan untuk karyawan magang -->
    <?php
    if ($_SESSION['level'] == 'Mahasiswa' || $_SESSION['level'] == 'mahasiswa') {
        date_default_timezone_set('Asia/Jakarta');
        // Menghitung jumlah riwayat presensi
        $idMahasiswa = $_SESSION['id_mahasiswa'];
        $namaMahasiswa = $_SESSION['nama_mahasiswa'];
        $queryPresensi = mysqli_query($kon, "SELECT COUNT(*) as totalPresensi FROM tbl_absensi WHERE id_mahasiswa = '$idMahasiswa'");
        $rowPresensi = mysqli_fetch_assoc($queryPresensi);
        $totalPresensi = $rowPresensi['totalPresensi'];

        // Menghitung jumlah riwayat presensi
        $idMahasiswaKegiatan = $_SESSION['id_mahasiswa'];
        $queryKegiatan = mysqli_query($kon, "SELECT COUNT(*) as totalKegiatan FROM tbl_kegiatan WHERE id_mahasiswa = '$idMahasiswaKegiatan'");
        $rowKegiatan = mysqli_fetch_assoc($queryKegiatan);
        $totalKegiatan = $rowKegiatan['totalKegiatan'];

        // Menghitung jumlah status ✓
        $queryStatusHadir = mysqli_query($kon, "SELECT COUNT(*) as totalStatusHadir FROM tbl_absensi WHERE id_mahasiswa = '$idMahasiswa' AND konfirmasi_status = '✓'");
        $rowStatusHadir = mysqli_fetch_assoc($queryStatusHadir);
        $totalStatusHadir = $rowStatusHadir['totalStatusHadir'];

        // Menghitung jumlah status X
        $queryStatusTidakHadir = mysqli_query($kon, "SELECT COUNT(*) as totalStatusTidakHadir FROM tbl_absensi WHERE id_mahasiswa = '$idMahasiswa' AND konfirmasi_status = 'X'");
        $rowStatusTidakHadir = mysqli_fetch_assoc($queryStatusTidakHadir);
        $totalStatusTidakHadir = $rowStatusTidakHadir['totalStatusTidakHadir'];

        // Menghitung jumlah data di tbl_suket hanya untuk akun yang sedang login
        $queryCountAkhirMagang = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_suket WHERE id_mahasiswa = '$idMahasiswa'");
        $rowCountAkhirMagang = mysqli_fetch_assoc($queryCountAkhirMagang);
        $totalAkhirMagang = $rowCountAkhirMagang['total'];

        // Sesuaikan kueri SQL dengan kondisi sesuai kebutuhan
        $queryNilaiKehadiran = mysqli_query($kon, "SELECT nilai_kehadiran FROM tbl_mahasiswa WHERE id_mahasiswa = '$idMahasiswa'");
        $rowNilaiKehadiran = mysqli_fetch_assoc($queryNilaiKehadiran);
        $nilaiKehadiran = $rowNilaiKehadiran['nilai_kehadiran'];

        // Mendapatkan data periode magang dari database
        $queryPeriodeMagang = mysqli_query($kon, "SELECT mulai_magang, akhir_magang FROM tbl_mahasiswa WHERE id_mahasiswa = '$idMahasiswa'");
        $dataPeriodeMagang = mysqli_fetch_assoc($queryPeriodeMagang);
        $mulaiMagang = strtotime($dataPeriodeMagang['mulai_magang']);
        $akhirMagang = strtotime($dataPeriodeMagang['akhir_magang']);

        function bulanIndonesia($tanggal)
        {
            $bulanInggris = array(
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            );
            $bulanInggrisLower = array(
                'january' => 'Januari',
                'february' => 'Februari',
                'march' => 'Maret',
                'april' => 'April',
                'may' => 'Mei',
                'june' => 'Juni',
                'july' => 'Juli',
                'august' => 'Agustus',
                'september' => 'September',
                'october' => 'Oktober',
                'november' => 'November',
                'december' => 'Desember'
            );

            $tanggalBaru = date('d F Y', strtotime($tanggal));
            $namaBulanInggris = date('F', strtotime($tanggal));
            return str_replace(array_keys($bulanInggris), $bulanInggris, $tanggalBaru);
        }

        // Menghitung jumlah minggu dari mulai hingga akhir magang
        $mulaiTanggal = new DateTime(date('Y-m-d', $mulaiMagang));
        $akhirTanggal = new DateTime(date('Y-m-d', $akhirMagang));
        $interval = $mulaiTanggal->diff($akhirTanggal);
        $jumlahMinggu = ceil($interval->days / 7);

        // Inisialisasi array untuk menyimpan jumlah presensi per minggu
        $dataPresensiPerMinggu = array();

        // Query untuk mengambil jumlah presensi per minggu dari tbl_absensi
        $queryPresensiPerMinggu = mysqli_query($kon, "SELECT WEEKOFYEAR(tanggal) as minggu, 
                                                        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as jumlahHadir,
                                                        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as jumlahIzin,
                                                        SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as jumlahTerlambat,
                                                        SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as jumlahTidakHadir
                                                 FROM tbl_absensi
                                                 WHERE id_mahasiswa = '$idMahasiswa'
                                                 GROUP BY minggu");

        while ($rowPresensi = mysqli_fetch_assoc($queryPresensiPerMinggu)) {
            $minggu = $rowPresensi['minggu'];
            $dataPresensiPerMinggu[$minggu] = array(
                'jumlahHadir' => $rowPresensi['jumlahHadir'],
                'jumlahIzin' => $rowPresensi['jumlahIzin'],
                'jumlahTerlambat' => $rowPresensi['jumlahTerlambat'],
                'jumlahTidakHadir' => $rowPresensi['jumlahTidakHadir']
            );
        }

        // Membuat label minggu seperti "Minggu ke-1", "Minggu ke-2", dst.
        $labelMinggu = array();
        for ($i = 1; $i <= $jumlahMinggu; $i++) {
            $labelMinggu[] = "Minggu ke-$i";
        }

        // Menghitung sisa waktu
        $sisaWaktu = $akhirMagang - time();

        // Menghitung hari, jam, menit, dan detik
        $sisaHari = floor($sisaWaktu / (60 * 60 * 24));
        $sisaJam = floor(($sisaWaktu % (60 * 60 * 24)) / (60 * 60));
        $sisaMenit = floor(($sisaWaktu % (60 * 60)) / 60);
        $sisaDetik = $sisaWaktu % 60;

        $sisaPeriodeMagangText = "<span style='color: white;'>" . $sisaHari . " hari, " . $sisaJam . " : " . $sisaMenit . " : " . $sisaDetik . "</span>";
        $alertShown = isset($_SESSION['alert_shown']) ? $_SESSION['alert_shown'] : false;

        if (!$alertShown && $sisaHari <= 3 && $sisaWaktu > 0) {
            $_SESSION['alert_shown'] = true;
        }
        ?>

        <?php
        include 'config/database.php'; // Pastikan koneksi database sudah tersedia
    
        $kodePengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan session yang menyimpan kode_mahasiswa atau kode_pengguna
        $laporanSudahDiunggah = false;

        // Cek apakah ada laporan magang yang sudah diunggah oleh mahasiswa ini
        $queryLaporan = mysqli_query($kon, "SELECT COUNT(*) as total FROM tbl_laporan WHERE kode_mahasiswa = '$kodePengguna'");
        $dataLaporan = mysqli_fetch_assoc($queryLaporan);

        if ($dataLaporan['total'] > 0) {
            $laporanSudahDiunggah = true;
        }
        ?>

        <div class="col-md-12">
            <div class="panel panel-default" style="background-color: rgb(24, 18, 92);">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6" style="margin-bottom: 30px;">
                            <div class="card text-center">
                                <div class="sisa-periode-magang">
                                    <?php echo $sisaPeriodeMagangText; ?>
                                </div>
                                <i class="fa-solid fa-timeline" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Sisa Periode Magang</h3>
                                <div class="progress" style="height: 20px;">
                                    <?php
                                    if ($sisaWaktu <= 0) {
                                        echo '<div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%;">100%</div>';
                                    } else {
                                        // Jika masih ada waktu, hitung persentase seberapa banyak waktu yang telah berlalu
                                        $persentaseWaktuBerlalu = 100 - ($sisaWaktu / ($akhirMagang - $mulaiMagang) * 100);

                                        // Tampilkan nilai lebar (width) sesuai dengan persentase waktu yang telah berlalu
                                        echo '<div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: ' . $persentaseWaktuBerlalu . '%;">' . round($persentaseWaktuBerlalu) . '%</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6" style="margin-bottom: 30px;">
                            <div class="card text-center">
                                <div class="jumlah-riwayat-presensi">
                                    <?php echo $totalPresensi; ?>
                                </div>
                                <i class="fa fa-history" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Riwayat Presensi</h3>
                                <div class="row">
                                    <div class="col-lg-6" style="margin-bottom: 10px;">
                                        <div class="bg-success" style="padding: 10px;">
                                            Status ✓ :
                                            <?php echo $totalStatusHadir; ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" style="margin-bottom: 10px;">
                                        <div class="bg-danger" style="padding: 10px;">
                                            Status X :
                                            <?php echo $totalStatusTidakHadir; ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="bg-info" style="padding: 10px;">
                                            <?php
                                            // Mendapatkan data nilai kehadiran
                                            $nilaiKehadiran = $rowNilaiKehadiran['nilai_kehadiran'];

                                            // Menentukan kategori nilai berdasarkan rentang
                                            $kategoriNilai = "";
                                            if ($nilaiKehadiran >= 90) {
                                                $kategoriNilai = "Sangat Rajin";
                                            } elseif ($nilaiKehadiran >= 80) {
                                                $kategoriNilai = "Rajin";
                                            } elseif ($nilaiKehadiran >= 70) {
                                                $kategoriNilai = "Cukup Rajin";
                                            } elseif ($nilaiKehadiran >= 60) {
                                                $kategoriNilai = "Kurang Rajin";
                                            } else {
                                                $kategoriNilai = "Tidak Rajin";
                                            }

                                            // Menampilkan kategori nilai kehadiran
                                            echo "Nilai kehadiran saat ini : " . $nilaiKehadiran . " (" . $kategoriNilai . ")";
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 10px;">
                                    <a style="font-weight: bold;" href="index.php?page=riwayat" class="btn btn-primary"><i
                                            class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6" style="margin-bottom: 30px;">
                            <div class="card text-center">
                                <div class="jumlah-kegiatan-harian">
                                    <?php echo $totalKegiatan; ?>
                                </div>
                                <i class="fa fa-book" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Kegiatan Harian</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=kegiatan" class="btn btn-primary"><i
                                            class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card text-center">
                                <div class="jumlah-data-akhir-magang">
                                    <?php echo $totalAkhirMagang; ?>
                                </div>
                                <i class="bi bi-file-earmark-fill" style="font-size: 3em;"></i>
                                <h3 style="margin-bottom: 20px;">Data Akhir Magang</h3>
                                <div class="row">
                                    <a style="font-weight: bold;" href="index.php?page=suket_nilai_sertifikat"
                                        class="btn btn-primary"><i class="bi bi-arrow-bar-right"></i> Lihat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="margin-bottom: 50px;">
            <div class="panel panel-default" style="background-color: rgb(255, 255, 255);">
                <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: white;">
                    Statistik Presensi
                    <span class="pull-right clickable panel-toggle panel-button-tab-left">
                        <em class="fa fa-toggle-up"></em>
                    </span>
                </div>
                <div class="panel-body">
                    <h5 style="font-weight: bold; text-align: center;">
                        Statistik presensi perminggu
                        <?php echo $_SESSION['nama_mahasiswa']; ?><br><?php echo bulanIndonesia($dataPeriodeMagang['mulai_magang']); ?>
                        -
                        <?php echo bulanIndonesia($dataPeriodeMagang['akhir_magang']); ?>
                    </h5>
                    <div class="chart-container-karyawan">
                        <canvas id="chartAbsensi"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://kit.fontawesome.com/4752e5dd73.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
        <script>
            var ctx = document.getElementById('chartAbsensi').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar', // Ubah jenis chart sesuai kebutuhan
                data: {
                    labels: <?php echo json_encode($labelMinggu); ?>,
                    datasets: [{
                        label: 'Hadir',
                        backgroundColor: 'green',
                        data: <?php echo json_encode(array_column($dataPresensiPerMinggu, 'jumlahHadir')); ?>
                    }, {
                        label: 'Izin',
                        backgroundColor: 'skyblue',
                        data: <?php echo json_encode(array_column($dataPresensiPerMinggu, 'jumlahIzin')); ?>
                    }, {
                        label: 'Terlambat',
                        backgroundColor: 'orange',
                        data: <?php echo json_encode(array_column($dataPresensiPerMinggu, 'jumlahTerlambat')); ?>
                    }, {
                        label: 'Tidak Hadir',
                        backgroundColor: 'red',
                        data: <?php echo json_encode(array_column($dataPresensiPerMinggu, 'jumlahTidakHadir')); ?>
                    }]
                },
                options: {
                    responsive: true, // Aktifkan responsivitas chart
                    maintainAspectRatio: false, // Biarkan chart menyesuaikan aspek rasio
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Minggu'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Jumlah'
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        zoom: {
                            zoom: {
                                wheel: {
                                    enabled: true // Aktifkan zoom dengan mouse wheel
                                },
                                pinch: {
                                    enabled: true // Aktifkan zoom dengan pinch gesture
                                },
                                mode: 'x' // Arah zooming
                            },
                            pan: {
                                enabled: true,
                                mode: 'x',
                                speed: 10
                            }
                        }
                    }
                }
            });
        </script>

        <!-- Script untuk mengatur tinggi card secara dinamis -->
        <script>
            $(document).ready(function () {
                var maxCardHeight = 0;

                $(".card").each(function () {
                    var cardHeight = $(this).outerHeight();
                    if (cardHeight > maxCardHeight) {
                        maxCardHeight = cardHeight;
                    }
                });

                $(".card").height(maxCardHeight);
            });

            let alertShown = <?php echo $alertShown ? 'true' : 'false'; ?>; // Variabel untuk mengecek apakah alert sudah ditampilkan

            // Fungsi untuk menghitung sisa waktu periode magang
            function updateRemainingInternshipTime() {
                let startDate = new Date("<?php echo date('Y-m-d', strtotime($dataPeriodeMagang['mulai_magang'])); ?>");
                let endDate = new Date("<?php echo date('Y-m-d', strtotime($dataPeriodeMagang['akhir_magang'])); ?>");
                let remainingTime = endDate.getTime() - new Date().getTime();

                if (remainingTime <= 0) {
                    remainingTime = 0;
                }

                let endDateReached = new Date() >= endDate;

                if (endDateReached) {
                    remainingTime = 0;
                }

                let remainingDays = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
                let remainingHours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let remainingMinutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
                let remainingSeconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

                remainingHours = remainingHours < 10 ? '0' + remainingHours : remainingHours;
                remainingMinutes = remainingMinutes < 10 ? '0' + remainingMinutes : remainingMinutes;
                remainingSeconds = remainingSeconds < 10 ? '0' + remainingSeconds : remainingSeconds;

                let remainingTimeText = '';

                if (endDateReached) {
                    remainingTimeText = '<span style="color: red;">00:00:00</span>';
                } else {
                    if (remainingDays > 0) {
                        remainingTimeText = '<span style="color: ' + (remainingDays <= 3 ? "red" : "white") + ';">' + remainingDays + ' hari ' + remainingHours + ' : ' + remainingMinutes + ' : ' + remainingSeconds + '</span>';
                    } else {
                        remainingTimeText = '<span style="color: ' + (remainingHours > 0 || remainingMinutes > 0 || remainingSeconds > 0 ? "red" : "white") + ';">' + remainingHours + ' : ' + remainingMinutes + ' : ' + remainingSeconds + '</span>';
                    }
                }

                document.querySelector('.sisa-periode-magang').innerHTML = remainingTimeText;

                let laporanSudahDiunggah = <?php echo $laporanSudahDiunggah ? 'true' : 'false'; ?>;
                // Tampilkan alert hanya sekali jika belum ditampilkan sebelumnya
                if (!alertShown && remainingDays <= 5 && remainingTime > 0 && !laporanSudahDiunggah) {
                    let alertMessage = '';

                    if (remainingDays > 0) {
                        alertMessage = 'Periode magang anda tersisa <span style="color: red;">' + remainingDays + ' hari : ' + remainingHours + ' Jam : ' + remainingMinutes + ' Menit</span> lagi, Jangan lupa untuk mengunggah laporan magang anda di menu Unggah Laporan Magang <img src="source/img/gestures.png" width="25" alt="Smile">';
                    } else {
                        alertMessage = 'Periode magang anda tersisa <span style="color: red;">' + remainingHours + ' Jam : ' + remainingMinutes + ' Menit</span> lagi, Jangan lupa untuk mengunggah laporan magang anda di menu Unggah Laporan Magang <img src="source/img/gestures.png" width="25" alt="Smile">';
                    }

                    Swal.fire({
                        title: '<span style="font-size: 1.5em;">Peringatan!</span>',
                        html: '<span style="font-size: 1.5em;">' + alertMessage + '</span>',
                        icon: 'warning',
                        confirmButtonText: '<span style="font-size: 1.5em;">Ok</span>'
                    });

                    alertShown = true;
                    <?php $_SESSION['alert_shown'] = true; ?>
                }
            }

            updateRemainingInternshipTime();
            setInterval(updateRemainingInternshipTime, 1000);
        </script>
        <?php
    }
    ?>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loader = document.getElementById("loader-overlay");

        if (sessionStorage.getItem("sudah_load")) {
            // Kalau sudah pernah load, langsung sembunyikan
            loader.classList.add("fade-out");
        } else {
            // Kalau pertama kali load, kasih delay sebelum fade out
            setTimeout(function () {
                loader.classList.add("fade-out");
                sessionStorage.setItem("sudah_load", "true");
            }, 1000); // spinner muncul 1 detik lalu fade out
        }
    });
</script>