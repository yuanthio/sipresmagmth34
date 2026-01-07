<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<?php
// Sambungan ke database
include '../../config/database.php';
include '../../config/function.php';

// Mulai sesi untuk mendapatkan kode_pengguna
session_start();
$kode_pengguna = $_SESSION['kode_pengguna']; // Pastikan ini sesuai dengan sesi yang digunakan

// Mendapatkan level pengguna
$queryUser = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
$resultUser = mysqli_query($kon, $queryUser);
$userInfo = mysqli_fetch_array($resultUser);
$level = $userInfo['level'];

// Mengambil nama pengguna berdasarkan level
$nama_pengguna = "";
if ($level == 'Admin') {
    $queryAdmin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
    $resultAdmin = mysqli_query($kon, $queryAdmin);
    $adminInfo = mysqli_fetch_array($resultAdmin);
    $nama_pengguna = $adminInfo['nama'];
} elseif ($level == 'Mentor') {
    $queryMentor = "SELECT nama FROM tbl_mentor WHERE kode_mentor = '$kode_pengguna'";
    $resultMentor = mysqli_query($kon, $queryMentor);
    $mentorInfo = mysqli_fetch_array($resultMentor);
    $nama_pengguna = $mentorInfo['nama'];
}

if (isset($_POST['id_mahasiswa'])) {
    $id_mahasiswa = $_POST['id_mahasiswa'];

    // Query untuk mendapatkan nama mahasiswa
    $queryInfoMahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = $id_mahasiswa";
    $resultInfoMahasiswa = mysqli_query($kon, $queryInfoMahasiswa);
    $dataMahasiswa = mysqli_fetch_assoc($resultInfoMahasiswa);
    $nama_mahasiswa = $dataMahasiswa['nama'];

    // Menyimpan aktivitas log ke tabel log aktivitas
    date_default_timezone_set('Asia/Jakarta'); // Mengatur zona waktu ke Indonesia WIB
    $tanggal_sekarang = date('Y-m-d H:i:s');
    $aktivitas = "Melihat riwayat presensi karyawan magang ($nama_mahasiswa)";
    $status = "berhasil"; // Status "berhasil" karena halaman berhasil dibuka

    $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                 VALUES ('$tanggal_sekarang', '$nama_pengguna', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $queryLog);
}
?>

<style>
    th {
        text-align: center;
    }

    .nilai-kehadiran {
        margin-top: 20px;
    }
</style>

<div class="table-responsive" style="overflow-x: auto;">
    <table class="table table-bordered table-center" id="dataTable" style="min-width: 1800px;" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Hari</th>
                <th width="150">Tanggal</th>
                <th width="150">Waktu</th>
                <th width="150">Kehadiran</th>
                <th width="200">Kegiatan</th>
                <th width="220">Keterangan</th>
                <th width="200">Foto</th>
                <th width="200">Foto Presensi</th>
                <th width="200">Foto Bukti WFA</th>
                <th width="250">Lokasi</th>
                <th width="70">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($_POST['id_mahasiswa'])) {
                $id_mahasiswa = $_POST['id_mahasiswa'];

                // Query untuk mengambil data riwayat absensi dari tbl_absensi, tbl_kegiatan, dan tbl_alasan
                $query = "SELECT
                    tbl_absensi.id_absensi,
                    tbl_absensi.id_mahasiswa,
                    tbl_absensi.latitude,
                    tbl_absensi.longitude,
                    DAYNAME(tbl_absensi.tanggal) AS hari,
                    tbl_absensi.tanggal,
                    tbl_absensi.waktu,
                    tbl_absensi.kamera,
                    (CASE
                        WHEN tbl_absensi.status = 1 THEN 'Hadir'
                        WHEN tbl_absensi.status = 2 THEN 'Izin'
                        WHEN tbl_absensi.status = 3 THEN 'Terlambat'
                        WHEN tbl_absensi.status = 4 THEN 'Tidak Hadir'
                        WHEN tbl_absensi.status = 5 THEN 'WFA'
                        ELSE 'Belum Absensi'
                    END) AS kehadiran,
                    IFNULL(tbl_kegiatan.kegiatan, ' - ') AS kegiatan,
                    IFNULL(tbl_alasan.alasan, '-') AS keterangan_izin,
                    IFNULL(tbl_kegiatan.foto, '-') AS foto_kegiatan,
                    IFNULL(tbl_alasan.foto, '-') AS foto_alasan,
                    (CASE
                        WHEN tbl_absensi.konfirmasi_status = '✓' THEN 'Disetujui'
                        WHEN tbl_absensi.konfirmasi_status = 'X' THEN 'Ditolak'
                        WHEN tbl_absensi.status = 2 AND tbl_alasan.alasan IS NOT NULL THEN 'Disetujui'
                        ELSE '-'
                    END) AS status,
                    GROUP_CONCAT(tbl_bukti_wfa.bukti_wfa SEPARATOR ', ') AS file_bukti_wfa
                FROM tbl_absensi
                LEFT JOIN tbl_kegiatan 
                    ON tbl_absensi.tanggal = tbl_kegiatan.tanggal 
                    AND tbl_absensi.id_mahasiswa = tbl_kegiatan.id_mahasiswa
                LEFT JOIN tbl_alasan 
                    ON tbl_absensi.tanggal = tbl_alasan.tanggal 
                    AND tbl_absensi.id_mahasiswa = tbl_alasan.id_mahasiswa
                LEFT JOIN tbl_bukti_wfa 
                    ON tbl_absensi.tanggal = tbl_bukti_wfa.tanggal 
                    AND tbl_absensi.id_mahasiswa = tbl_bukti_wfa.id_mahasiswa
                WHERE tbl_absensi.id_mahasiswa = $id_mahasiswa
                GROUP BY tbl_absensi.id_absensi
                ORDER BY tbl_absensi.tanggal DESC";

                $result = mysqli_query($kon, $query);

                // Ambil lokasi presensi aktif
                $lokasi_presensi = mysqli_query($kon, "SELECT latitude, longitude, radius FROM tbl_lokasi_presensi WHERE status_aktif = 1 LIMIT 1");
                $data_lokasi = mysqli_fetch_assoc($lokasi_presensi);
                $lat_presensi = $data_lokasi['latitude'] ?? null;
                $lng_presensi = $data_lokasi['longitude'] ?? null;
                $radius_presensi = $data_lokasi['radius'] ?? null;

                // Output data ke dalam tabel
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $no; ?></td>
                        <td class="text-center"><?php echo MendapatkanHari($row['hari']); ?></td>
                        <?php
                        // Ubah format tanggal ke dalam bahasa Indonesia
                        $formattedDate = date_create_from_format('Y-m-d', $row['tanggal']);
                        $tgl = date_format($formattedDate, 'd');
                        $bulan = date_format($formattedDate, 'm');
                        $tahun = date_format($formattedDate, 'Y');
                        ?>
                        <td class="text-center"><?php echo "{$tgl} " . MendapatkanBulan($bulan) . " {$tahun}"; ?></td>
                        <td class="text-center"><?php echo ($row['waktu'] == '00:00:00') ? '-' : $row['waktu']; ?></td>
                        <td class="text-center">
                            <?php
                            if ($row['kehadiran'] == 'Hadir') {
                                echo '<span class="label label-success">Hadir</span>';
                            } elseif ($row['kehadiran'] == 'Izin') {
                                echo '<span class="label label-info">Izin</span>';
                            } elseif ($row['kehadiran'] == 'Terlambat') {
                                echo '<span class="label label-warning">Terlambat</span>';
                            } elseif ($row['kehadiran'] == 'Tidak Hadir') {
                                echo '<span class="label label-danger">Tidak Hadir</span>';
                            } elseif ($row['kehadiran'] == 'WFA') {
                                echo '<span class="label label-primary">WFA</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td <?php echo ($row['kegiatan'] == ' - ') ? 'class="text-center"' : ''; ?>>
                            <?php echo $row['kegiatan']; ?>
                        </td>
                        <td>
                            <?php
                            $alasan = ($row['keterangan_izin'] != '-' ? $row['keterangan_izin'] : '');
                            $buktiWfaHtml = '';

                            if (!empty($row['file_bukti_wfa'])) {
                                $files = explode(", ", $row['file_bukti_wfa']);
                                foreach ($files as $file) {
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    if (in_array($ext, ['doc', 'docx', 'pdf'])) {
                                        $iconPath = "apps/data_absensi/extensi_file/$ext.png";
                                        $filePath = "apps/data_absensi/file_wfa/$file";

                                        // arahkan ke download.php
                                        $buktiWfaHtml .= "<a href='apps/data_absensi/download.php?file=" . urlencode($file) . "' 
                                    style='display:inline-block;margin-bottom:3px;'>
                                    <img src='$iconPath' width='20' 
                                         style='margin-right:5px; vertical-align:middle;'>
                                    $file
                                  </a><br>";
                                    }
                                }
                            }

                            if (!empty($alasan) || !empty($buktiWfaHtml)) {
                                echo (!empty($alasan) ? $alasan . "<br>" : "");
                                echo $buktiWfaHtml;
                            } else {
                                echo " <div class='text-center'>-</div> ";
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <?php
                            if ($row['kehadiran'] == 'Izin' && $row['foto_alasan'] != '-') {
                                // Foto dari tbl_alasan jika statusnya Izin
                                $foto_array = explode(", ", $row['foto_alasan']);
                                foreach ($foto_array as $foto) {
                                    echo "<img src='apps/pengguna/bukti_alasan/$foto' width='100' height='100' style='margin-right: 5px; margin-bottom: 5px;'>";
                                }
                            } else {
                                // Foto dari tbl_kegiatan
                                $foto_array = explode(", ", $row['foto_kegiatan']);
                                if (count($foto_array) == 1 && ($foto_array[0] == '-' || empty($foto_array[0]))) {
                                    echo "<img src='apps/data_kegiatan/foto_kegiatan/gambar_default/No_gambar.jpg' width='100' height='100' style='margin-right: 5px; margin-bottom: 5px;'>";
                                } else {
                                    foreach ($foto_array as $foto) {
                                        if (!empty($foto)) {
                                            echo "<img src='apps/data_kegiatan/foto_kegiatan/$foto' width='100' height='100' style='margin-right: 5px; margin-bottom: 5px;'>";
                                        } else {
                                            echo "<img src='apps/data_kegiatan/foto_kegiatan/gambar_default/No_gambar.jpg' width='100' height='100' style='margin-right: 5px; margin-bottom: 5px;'>";
                                        }
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <?php
                            $foto_presensi = isset($row['kamera']) ? $row['kamera'] : '';
                            $path_file = $_SERVER['DOCUMENT_ROOT'] . "/absensi_magang_coba/apps/pengguna/kamera/$foto_presensi";
                            $url_file = "apps/pengguna/kamera/$foto_presensi";
                            $url_default = "apps/pengguna/kamera/foto_default.png";

                            if (!empty($foto_presensi) && file_exists($path_file)) {
                                echo "<img src='$url_file' width='100' height='100' style='margin-bottom: 5px; object-fit: cover;'>";
                            } else {
                                echo "<img src='$url_default' width='100' height='100' style='margin-bottom: 5px; object-fit: cover;'>";
                            }
                            ?>
                        </td>
                        <td class="text-center">
                            <?php
                            $buktiWfaFotoHtml = '';

                            if (!empty($row['file_bukti_wfa'])) {
                                $files = explode(", ", $row['file_bukti_wfa']);
                                foreach ($files as $file) {
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    // hanya ambil gambar (jpg, jpeg, png)
                                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                        $filePath = "apps/data_absensi/file_wfa/$file";
                                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/absensi_magang_coba/$filePath")) {
                                            $buktiWfaFotoHtml .= "<img src='$filePath' width='100' height='100' style='margin:5px; object-fit:cover;'>";
                                        } else {
                                            $buktiWfaFotoHtml .= "<img src='apps/data_absensi/file_wfa/gambar_default/No_gambar.jpg' width='100' height='100' style='margin:5px; object-fit:cover;'>";
                                        }
                                    }
                                }
                            }

                            // kalau kosong, tampilkan gambar default
                            if (empty($buktiWfaFotoHtml)) {
                                $buktiWfaFotoHtml = "<img src='apps/data_absensi/file_wfa/gambar_default/No_gambar.jpg' width='100' height='100' style='margin:5px; object-fit:cover;'>";
                            }

                            echo $buktiWfaFotoHtml;
                            ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                <div id="map_<?php echo $row['id_absensi']; ?>" style="height: 150px; width: 250px;"></div>
                                <script>
                                    (function () {
                                        var mapId = "map_<?php echo $row['id_absensi']; ?>";
                                        var lat = <?php echo $row['latitude']; ?>;
                                        var lng = <?php echo $row['longitude']; ?>;

                                        function initMap() {
                                            var map = L.map(mapId, {
                                                center: [lat, lng],
                                                zoom: 15,
                                                scrollWheelZoom: false
                                            });

                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '© OpenStreetMap contributors'
                                            }).addTo(map);

                                            L.marker([lat, lng])
                                                .addTo(map)
                                                .bindPopup("Latitude: " + lat + "<br>Longitude: " + lng)
                                                .openPopup();

                                            <?php if ($lat_presensi && $lng_presensi && $radius_presensi): ?>
                                                L.circle([<?php echo $lat_presensi; ?>, <?php echo $lng_presensi; ?>], {
                                                    color: 'blue',
                                                    fillColor: 'blue',
                                                    fillOpacity: 0.2,
                                                    radius: <?php echo $radius_presensi; ?>
                                                }).addTo(map)
                                                    .bindPopup("Radius Presensi Aktif:<br>Latitude: <?php echo $lat_presensi; ?><br>Longitude: <?php echo $lng_presensi; ?><br>Radius: <?php echo $radius_presensi; ?> m");
                                            <?php endif; ?>

                                            map.invalidateSize();

                                            const mapContainer = document.getElementById(mapId);
                                            mapContainer.addEventListener("wheel", function (e) {
                                                if (e.ctrlKey) {
                                                    e.preventDefault();
                                                    map.scrollWheelZoom.enable();
                                                } else {
                                                    map.scrollWheelZoom.disable();
                                                }
                                            }, { passive: false });

                                            map.on("zoomend", function () {
                                                map.scrollWheelZoom.disable();
                                            });
                                        }

                                        requestAnimationFrame(() => {
                                            setTimeout(initMap, 500);
                                        });
                                    })();
                                </script>
                            <?php else: ?>
                                <span class="text-muted">Lokasi tidak ditemukan</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php
                            if ($row['status'] == 'Disetujui') {
                                echo '<span class="label label-info">✓</span>';
                            } elseif ($row['status'] == 'Ditolak') {
                                echo '<span class="label label-danger">X</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    $no++;
                }
            } else {
                echo "<tr><td colspan='9'>ID Mahasiswa tidak ditemukan.</td></tr>";
            }

            // Tutup koneksi database
            mysqli_close($kon);
            ?>
        </tbody>
    </table>
</div>

<div class="nilai-kehadiran"
    style="position: sticky; bottom: 0; background-color: rgb(13, 10, 44); color: #fff; padding: 8px 0;">
    <?php
    // Sambungan ke database (gunakan sambungan yang sesuai)
    include '../../config/database.php';

    if (isset($_POST['id_mahasiswa'])) {
        $id_mahasiswa = $_POST['id_mahasiswa'];

        // Query untuk mengambil nilai kehadiran dan nama mahasiswa dari tbl_mahasiswa
        $queryInfoMahasiswa = "SELECT tbl_mahasiswa.nama, tbl_mahasiswa.nilai_kehadiran FROM tbl_mahasiswa WHERE tbl_mahasiswa.id_mahasiswa = $id_mahasiswa";
        $resultInfoMahasiswa = mysqli_query($kon, $queryInfoMahasiswa);
        $dataMahasiswa = mysqli_fetch_assoc($resultInfoMahasiswa);

        // Tampilkan nama dan nilai kehadiran
        echo "<span style='color: #fff; font-size: 1.3em; padding: 10px 0;' class='nilai'>Nilai Kehadiran {$dataMahasiswa['nama']} : {$dataMahasiswa['nilai_kehadiran']}</span>";
    }
    // Tutup koneksi database
    mysqli_close($kon);
    ?>
    <br>
    <span style="color: #fff;">Catatan: Nilai berdasarkan jumlah status (✓) dan (X).</span>
</div>