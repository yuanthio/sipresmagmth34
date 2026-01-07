<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<?php
// Cek level akses pengguna
if ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<style>
    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
        color: black;
    }

    .table tbody td {
        color: black;
        text-align: center;
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
        .button {
            margin-left: 15px;
        }

        .responsive-table {
            overflow-x: auto;
        }

        .besar-mobile {
            width: 115px;
        }
    }

    @media (max-width: 380px) {
        .besar-mobile {
            width: 90px;
        }
    }
</style>

<script>
    function showAlert(type, title, text) {
        Swal.fire({
            icon: type,
            title: `<span style="font-size: 1.5em;">${title}</span>`,
            html: `<span style="font-size: 1.5em;">${text}</span>`,
            timer: (type === 'error' || type === 'warning') ? null : 1700,
            showConfirmButton: (type === 'error' || type === 'warning'),
            confirmButtonText: '<span style="font-size: 1.5em;">Ok</span>'
        }).then(() => {
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('edit');
                url.searchParams.delete('absen');
                url.searchParams.delete('edit_hari_libur');
                url.searchParams.delete('menu_edit');
                url.searchParams.delete('tambah_tanggal_libur');
                url.searchParams.delete('edit_tanggal_libur');
                url.searchParams.delete('bentrok');
                url.searchParams.delete('hapus_tanggal_libur');
                url.searchParams.delete('edit_maintenance');
                url.searchParams.delete('edit_lokasi');
                url.searchParams.delete('hapus_lokasi');
                url.searchParams.delete('edit_kamera');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Pengaturan Website</li>
    </ol>
</div>
<!--/.row-->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Profil Instansi
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <?php
                // Mengganti setiap penampilan alert dengan panggilan showAlert
                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan Website Telah Diupdate');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        $error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "Pengaturan Website Gagal Diupdate";
                        echo "<script>showAlert('error', 'Gagal!', '$error_message');</script>";
                    }
                }

                if (isset($_GET['absen'])) {
                    if ($_GET['absen'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan Presensi Telah Diupdate');</script>";
                    } else if ($_GET['absen'] == 'gagal') {
                        echo "<script>showAlert('error', 'Gagal!', 'Pengaturan Absensi Gagal Diupdate');</script>";
                    }
                }

                if (isset($_GET['edit_hari_libur'])) {
                    if ($_GET['edit_hari_libur'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan Hari Libur Telah Diupdate');</script>";
                    } else {
                        echo "<script>showAlert('error', 'Gagal!', 'Pengaturan Hari Libur Gagal Diupdate');</script>";
                    }
                }

                if (isset($_GET['edit_kamera'])) {
                    if ($_GET['edit_kamera'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan Kamera Telah Diupdate');</script>";
                    } else {
                        echo "<script>showAlert('error', 'Gagal!', 'Pengaturan Kamera Gagal Diupdate');</script>";
                    }
                }

                if (isset($_GET['tambah_tanggal_libur'])) {
                    if ($_GET['tambah_tanggal_libur'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Tanggal Libur Telah Ditambah');</script>";
                    } else if ($_GET['tambah_tanggal_libur'] == 'bentrok') {
                        echo "<script>showAlert('warning', 'Tanggal Bentrok!', 'Tanggal yang Anda masukkan bertabrakan dengan tanggal libur yang sudah ada.');</script>";
                    } else {
                        echo "<script>showAlert('error', 'Gagal!', 'Tanggal Libur Gagal Ditambah');</script>";
                    }
                }

                if (isset($_GET['edit_tanggal_libur'])) {
                    if ($_GET['edit_tanggal_libur'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Tanggal Libur Telah Diupdate');</script>";
                    } else if ($_GET['edit_tanggal_libur'] == 'bentrok') {
                        echo "<script>showAlert('warning', 'Tanggal Bentrok!', 'Tanggal yang Anda masukkan bertabrakan dengan tanggal libur lain.');</script>";
                    } else {
                        echo "<script>showAlert('error', 'Gagal!', 'Tanggal Libur Gagal Diupdate');</script>";
                    }
                }

                if (isset($_GET['hapus_tanggal_libur'])) {
                    if ($_GET['hapus_tanggal_libur'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Tanggal Hari Libur Telah Dihapus');</script>";
                    } else {
                        echo "<script>showAlert('error', 'Gagal!', 'Tanggal Hari Libur Gagal Dihapus');</script>";
                    }
                }

                if (isset($_GET['menu_edit'])) {
                    if ($_GET['menu_edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan Menu Telah Diupdate');</script>";
                    } else if ($_GET['menu_edit'] == 'gagal') {
                        $error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "Pengaturan Menu Gagal Diupdate";
                        echo "<script>showAlert('error', 'Gagal!', '$error_message');</script>";
                    }
                }

                if (isset($_GET['edit_maintenance'])) {
                    if ($_GET['edit_maintenance'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pesan maintenance telah diatur');</script>";
                    } else if ($_GET['edit_maintenance'] == 'gagal') {
                        $error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "Pesan maintenance gagal diatur";
                        echo "<script>showAlert('error', 'Gagal!', '$error_message');</script>";
                    }
                }

                if (isset($_GET['edit_lokasi'])) {
                    if ($_GET['edit_lokasi'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan lokasi presensi telah diatur');</script>";
                    } else if ($_GET['edit_lokasi'] == 'gagal') {
                        $error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "Pengaturan lokasi presensi gagal diatur";
                        echo "<script>showAlert('error', 'Gagal!', '$error_message');</script>";
                    }
                }

                if (isset($_GET['hapus_lokasi'])) {
                    if ($_GET['hapus_lokasi'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Pengaturan lokasi presensi telah dihapus');</script>";
                    } else if ($_GET['hapus_lokasi'] == 'gagal') {
                        $error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "Pengaturan lokasi presensi gagal dihapus";
                        echo "<script>showAlert('error', 'Gagal!', '$error_message');</script>";
                    }
                }
                ?>

                <?php
                //Include database
                include 'config/database.php';
                //Mengambil data profil aplikasi
                $hasil = mysqli_query($kon, "select * from tbl_site order by nama_instansi desc limit 1");
                $data = mysqli_fetch_array($hasil);
                ?>

                <form action="apps/pengaturan/edit.php" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="form-group">
                            <input type="hidden" class="form-control" value="<?php echo $data['id_site']; ?>" name="id">
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Nama Instansi :</label>
                                <input type="text" class="form-control" value="<?php echo $data['nama_instansi']; ?>"
                                    name="nama_instansi" placeholder="Masukan Nama Instansi" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Nama Ketua (Pimpinan) :</label>
                                <input type="text" class="form-control" value="<?php echo $data['pimpinan']; ?>"
                                    name="pimpinan" placeholder="Masukan Nama Ketua" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Kepala Sekretariat :</label>
                                <input type="text" class="form-control" value="<?php echo $data['kep_sekretariat']; ?>"
                                    name="kep_sekretariat" placeholder="Masukan Nama Kepala Sekretariat" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>NIP Kepala Sekretariat :</label>
                                <input type="number" class="form-control"
                                    value="<?php echo $data['nip_kep_sekretariat']; ?>" name="nip_kep_sekretariat"
                                    placeholder="Masukan NIP Kepala Sekretariat" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Alamat :</label>
                                <input type="text" class="form-control" value="<?php echo $data['alamat']; ?>"
                                    placeholder="Masukan Alamat Instansi" name="alamat">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>No Telp :</label>
                                <input type="text" class="form-control" value="<?php echo $data['no_telp']; ?>"
                                    placeholder="Masukan Nomor Telp" name="no_telp">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Website :</label>
                                <input type="text" class="form-control" value="<?php echo $data['website']; ?>"
                                    placeholder="Masukan Alamat Website" name="website">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div id="msg"></div>
                                <label>Logo :</label>
                                <input type="file" name="logo" class="file">
                                <div class="input-group my-3">
                                    <input type="text" style="margin-bottom: 20px;" class="form-control" disabled
                                        placeholder="Upload Gambar" id="file">
                                    <img src="apps/pengaturan/logo/<?php echo $data['logo']; ?>" id="preview"
                                        width="20%" class="img-thumbnail">
                                    <div class="input-group-append">
                                        <button type="button" id="pilih_logo" class="browse btn btn-info"
                                            style="margin: 20px 0;"><i class="fa fa-search"></i> Pilih Logo</button>
                                    </div>
                                </div>
                                <input type="hidden" name="logo_sebelumnya" value="<?php echo $data['logo']; ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info" name="ubah_aplikasi"><i class="fa fa-save"></i>
                            Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Sertakan file konfigurasi database
include 'config/database.php';
date_default_timezone_set("Asia/Jakarta");
// Ambil status hari libur dari database atau sumber data lainnya
$query_hari_libur = "SELECT hari, status FROM tbl_hari_libur";
$result_hari_libur = mysqli_query($kon, $query_hari_libur);

// Inisialisasi array $hari_libur
$hari_libur = [];
while ($row = mysqli_fetch_assoc($result_hari_libur)) {
    $hari_libur[$row['hari']] = $row['status'];
}
?>

<div class="panel panel-default">
    <?php
    include 'config/database.php'; // sesuaikan path kalau perlu
    date_default_timezone_set("Asia/Jakarta");

    // Ambil tanggal hari ini
    $hari_ini = date("Y-m-d");

    // Update status otomatis berdasarkan tanggal hari ini
    mysqli_query($kon, "
        UPDATE tbl_tanggal_libur 
        SET status = CASE
            WHEN '$hari_ini' < tanggal_awal THEN 'Belum dimulai'
            WHEN '$hari_ini' BETWEEN tanggal_awal AND tanggal_akhir THEN 'Sedang berlangsung'
            WHEN '$hari_ini' > tanggal_akhir THEN 'Selesai'
            ELSE 'Status tidak diketahui'
        END
    ");

    // Ambil tanggal libur terbaru
    $query = "SELECT tanggal_awal, tanggal_akhir FROM tbl_tanggal_libur ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($kon, $query);
    $data = mysqli_fetch_assoc($result);

    // Default kosong jika belum ada
    $tanggal_awal = isset($data['tanggal_awal']) ? $data['tanggal_awal'] : '';
    $tanggal_akhir = isset($data['tanggal_akhir']) ? $data['tanggal_akhir'] : '';
    ?>
    <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
        Pengaturan Tanggal Libur
        <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
    </div>
    <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
        <div class="form-group">
            <button type="button" class="btn btn-success" id="tombol_tambah"><i class="fa fa-plus"></i>
                Tambah</button>
            <button class="btn btn-danger" id="deleteSelected" onclick="deleteSelected()" disabled><i
                    class="fa fa-trash"></i> Hapus Data Terpilih</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Mulai Libur</th>
                        <th>Akhir Libur</th>
                        <th>Alasan Libur</th>
                        <th>Status</th>
                        <th>Tanggal Input</th>
                        <th width="100">Aksi</th>
                        <th>
                            <input type="checkbox" id="selectAll"> Pilih Semua
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'config/database.php';
                    include 'config/function.php';
                    $no = 1;
                    $query = mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur ORDER BY tanggal_awal DESC");
                    $jumlah_data = mysqli_num_rows($query);

                    if ($jumlah_data == 0) {
                        echo '<tr><td colspan="8" class="text-center">Data masih kosong</td></tr>';
                    } else {
                        while ($data = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++ ?></td>
                                <td style="text-align: left;">
                                    <?php
                                    $tanggal_awal = strtotime($data['tanggal_awal']);
                                    echo date('d', $tanggal_awal) . ' ' . MendapatkanBulan((int) date('m', $tanggal_awal)) . ' ' . date('Y', $tanggal_awal);
                                    ?>
                                </td>
                                <td style="text-align: left;">
                                    <?php
                                    $tanggal_akhir = strtotime($data['tanggal_akhir']);
                                    echo date('d', $tanggal_akhir) . ' ' . MendapatkanBulan((int) date('m', $tanggal_akhir)) . ' ' . date('Y', $tanggal_akhir);
                                    ?>
                                </td>
                                <td style="text-align: left;"><?= $data['alasan_libur'] ?></td>
                                <td class="text-center">
                                    <?php
                                    $sekarang = date("Y-m-d");
                                    $awal = $data['tanggal_awal'];
                                    $akhir = $data['tanggal_akhir'];

                                    if ($sekarang < $awal) {
                                        $status = 'Belum dimulai';
                                        $label = '<span class="label label-default">Belum dimulai</span>';
                                    } elseif ($sekarang >= $awal && $sekarang <= $akhir) {
                                        $status = 'Sedang berlangsung';
                                        $label = '<span class="label label-warning">Sedang berlangsung</span>';
                                    } elseif ($sekarang > $akhir) {
                                        $status = 'Selesai';
                                        $label = '<span class="label label-info">Selesai</span>';
                                    } else {
                                        $status = 'Status tidak diketahui';
                                        $label = '<span class="label label-warning">Status tidak diketahui</span>';
                                    }

                                    echo $label;
                                    ?>
                                </td>
                                <td style="text-align: left;">
                                    <?php
                                    $tanggal_input = strtotime($data['tanggal_input']);
                                    echo date('d', $tanggal_input) . ' ' . MendapatkanBulan((int) date('m', $tanggal_input)) . ' ' . date('Y', $tanggal_input);
                                    ?>
                                </td>
                                <td style="text-align: left;">
                                    <button class="btn btn-warning btn-circle tombol_edit" data-id="<?= $data['id'] ?>"
                                        title="Edit Jadwal libur">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a href="apps/pengaturan/hapus_tanggal_libur.php?id=<?= $data['id'] ?>"
                                        class="btn btn-danger btn-circle btn-hapus" title="Hapus Jadwal Libur">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="selectItem" value="<?= $data['id'] ?>">
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <?php
    include 'config/database.php';

    // Ambil pengaturan dari tbl_kamera
    $kamera_perangkat = 0;
    $deteksi_wajah = 0;

    $query = mysqli_query($kon, "SELECT * FROM tbl_kamera LIMIT 1");
    if ($data = mysqli_fetch_assoc($query)) {
        $kamera_perangkat = $data['kamera_perangkat'];
        $deteksi_wajah = $data['deteksi_wajah'];
    }
    ?>
    <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
        Pengaturan Kamera Presensi
        <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
    </div>
    <div class="panel-body" id="panel-body-map" style="background-color: rgb(24, 18, 92); color: #fff;">
        <form action="apps/pengaturan/edit_deteksi_wajah.php" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label>Kamera :</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kamera_perangkat" value="1" <?php echo ($kamera_perangkat == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="kamera_perangkat">
                                Ya, aktifkan
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="form-group">
                        <label>Pengenalan Wajah :</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="deteksi_wajah" value="1" <?php echo ($deteksi_wajah == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="deteksi_wajah">
                                Ya, aktifkan
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-info" name="ubah_pengaturan_kamera">
                    <i class="fa fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
        Pengaturan Lokasi Presensi
        <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
    </div>

    <?php
    include 'config/database.php'; // Koneksi ke database
    
    // Ambil data lokasi presensi
    $lokasi_query = mysqli_query($kon, "SELECT * FROM tbl_lokasi_presensi");
    $locations = [];
    while ($row = mysqli_fetch_assoc($lokasi_query)) {
        $locations[] = [
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude']
        ];
    }

    // Data untuk form (ambil yang pertama saja)
    $data = isset($locations[0]) ? $locations[0] : ['latitude' => '', 'longitude' => ''];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];

    // Ambil radius dan status_aktif juga
    $lokasi_single = mysqli_query($kon, "SELECT * FROM tbl_lokasi_presensi LIMIT 1");
    $data_single = mysqli_fetch_assoc($lokasi_single);
    $radius = isset($data_single['radius']) ? $data_single['radius'] : '';
    $status_aktif = isset($data_single['status_aktif']) ? $data_single['status_aktif'] : 0;
    ?>

    <div class="panel-body" id="panel-body-map" style="background-color: rgb(24, 18, 92); color: #fff;">
        <form action="apps/pengaturan/edit_lokasi.php" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Latitude :</label>
                        <input type="text" class="form-control" value="<?= $latitude ?>" name="latitude"
                            placeholder="Masukan Latitude" required>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Longitude :</label>
                        <input type="text" class="form-control" value="<?= $longitude ?>" name="longitude"
                            placeholder="Masukan Longitude" required>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Radius :</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="radiusInput" name="radius"
                                value="<?= $radius ?>" placeholder="Masukan Radius" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="radiusUnit">m</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label>Aktifkan Lokasi :</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="aktifkan_lokasi" id="aktifkanLokasi"
                                value="1" <?= ($status_aktif == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="aktifkanLokasi">
                                Ya, aktifkan lokasi
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-info" name="ubah_lokasi">
                    <i class="fa fa-save"></i> Simpan Perubahan
                </button>

                <a href="apps/pengaturan/hapus_lokasi.php" class="btn btn-danger btn-hapus-lokasi">
                    <i class="fa fa-trash"></i> Hapus Lokasi
                </a>
            </div>
        </form>

        <!-- Tambahkan MAP di bawah form -->
        <?php if (count($locations) === 0): ?>
            <div style="text-align: center; padding: 20px; display: flex; justify-content: center;">
                <div>
                    <dotlottie-player src="https://lottie.host/055d8991-5e4c-40d9-b0ec-37188f267bd1/UOIC3latF6.lottie"
                        background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay>
                    </dotlottie-player>
                    <p style="font-size: 1.4em; color: #fff;">Titik lokasi presensi belum ditentukan.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilkan Map hanya jika ada data lokasi -->
            <div id="map" style="height: 400px; margin-top:20px; z-index: 1; border: 5px solid white;"></div>
        <?php endif; ?>
    </div>
</div>
<div class="row" style="margin-bottom: 50px;">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Pengaturan Menu
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div id="alert-container"></div>
                <div class="table-responsive">
                    <form id="menuForm" action="apps/pengaturan/edit_menu.php" method="post">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Menu</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center besar-mobile">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query untuk mengambil data menu dari database
                                $query_menu = "SELECT * FROM tbl_setting_menu";
                                $result_menu = mysqli_query($kon, $query_menu);
                                $index = 1;

                                while ($row_menu = mysqli_fetch_assoc($result_menu)) {
                                    $id_menu = $row_menu['id_menu'];
                                    $menu = $row_menu['menu'];
                                    $status = $row_menu['status'];

                                    // Tentukan apakah select harus disabled
                                    $select_disabled = '';
                                    if ($id_menu == 16 && $status == 'Aktif') {
                                        $select_disabled = 'disabled';
                                    }
                                    ?>

                                    <tr>
                                        <td class='text-center'><?php echo $index; ?></td>
                                        <td style='position: relative;'>
                                            <input type='text' name='menu[<?php echo $id_menu; ?>]'
                                                value='<?php echo $menu; ?>' class='form-control input-menu'
                                                data-id='<?php echo $id_menu; ?>' required>
                                            <div class='icon-pen'
                                                style='position: absolute; top: 50%; transform: translateY(-50%); right: 15px;'>
                                                <i style='background-color: white; padding: 5px;' class='fa fa-pen'></i>
                                            </div>
                                        </td>
                                        <td class='text-center'>
                                            <span
                                                class='label <?php echo ($status == 'Aktif') ? 'label-success' : 'label-danger'; ?>'><?php echo $status; ?></span>
                                        </td>
                                        <td>
                                            <div class='form-group'>
                                                <select class='form-control' name='status[<?php echo $id_menu; ?>]' required
                                                    <?php echo $select_disabled; ?>>
                                                    <option value='Aktif' <?php echo ($status == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                                    <option value='Nonaktif' <?php echo ($status == 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                                </select>
                                                <?php
                                                // Tambahkan input hidden untuk id_menu 9 jika select disabled
                                                if ($id_menu == 16 && $status == 'Aktif') {
                                                    echo "<input type='hidden' name='status[$id_menu]' value='$status'>";
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php
                                    $index++;
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="form-group">
                            <button id="btnSubmit" type="submit" class="button btn btn-info" name="ubah_absen"><i
                                    class="fa fa-save"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-default" style="margin-bottom: 30px;">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Pengaturan Hari Libur
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="table-responsive">
                    <form action="apps/pengaturan/edit_hari_libur.php" method="post">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Hari</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                foreach ($days as $index => $day) {
                                    $status = isset($hari_libur[$day]) ? $hari_libur[$day] : 'Masuk';
                                    $label_class = ($status == 'Masuk') ? 'label label-success' : 'label label-danger';
                                    $status_label = ($status == 'Masuk') ? '<span class="label label-success">Masuk Kantor</span>' : '<span class="label label-danger">Libur</span>';
                                    ?>
                                    <tr>
                                        <td class='text-center'><?php echo $index + 1; ?></td>
                                        <td><?php echo $day; ?></td>
                                        <td class='text-center'><?php echo $status_label; ?></td>
                                        <td>
                                            <div class='form-group'>
                                                <select class='form-control' name='status[<?php echo $day; ?>]' required>
                                                    <option value='Masuk' <?php echo ($status == 'Masuk') ? 'selected' : ''; ?>>Masuk Kantor</option>
                                                    <option value='Libur' <?php echo ($status == 'Libur') ? 'selected' : ''; ?>>Libur</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="form-group">
                            <button type="submit" class="button btn btn-info"><i class="fa fa-save"></i> Simpan
                                Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel panel-default" style="margin-bottom: 30px;">
            <?php
            $maintenance_file = 'config/maintenance.json';
            $maintenance_data = json_decode(file_get_contents($maintenance_file), true);
            $catatan_login = isset($maintenance_data['catatan']) ? $maintenance_data['catatan'] : '';
            $status_login = isset($maintenance_data['status']) && $maintenance_data['status'] === 'on' ? true : false;
            ?>
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Maintenance
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="table-responsive">
                    <form action="apps/pengaturan/edit_maintenance.php" method="post">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">Pesan</th>
                                    <th width="70" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">
                                        <input type="text" name="catatan_login" class="form-control"
                                            placeholder="Masukkan catatan..."
                                            value="<?= htmlspecialchars($catatan_login) ?>" required
                                            oninvalid="this.setCustomValidity('Harap catatan di isi terlebih dahulu')"
                                            oninput="this.setCustomValidity('')">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="status_login" value="1" <?= $status_login ? 'checked' : '' ?>>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="form-group">
                            <button type="submit" class="button btn btn-info"><i class="fa fa-save"></i> Simpan
                                Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Pengaturan Presensi
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">

                <?php
                // Profil Aplikasi
                include 'config/database.php';
                date_default_timezone_set("Asia/Jakarta");
                $query = mysqli_query($kon, "select * from tbl_setting_absensi limit 1");
                $row = mysqli_fetch_array($query);
                $id_waktu = $row['id_waktu'];
                $mulai_absen = $row['mulai_absen'];
                $akhir_absen = $row['akhir_absen'];
                ?>

                <form action="apps/pengaturan/absensi.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="hidden" class="form-control" value="<?php echo $id_waktu ?>" name="id_waktu">
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Mulai Absensi :</label>
                                <input type="time" class="form-control" value="<?php echo $mulai_absen ?>"
                                    name="mulai_absen" placeholder="Masukan Mulai Absensi" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Akhir Absensi:</label>
                                <input type="time" class="form-control" value="<?php echo $akhir_absen ?>"
                                    name="akhir_absen" placeholder="Masukan Akhir Absensi" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info" name="ubah_absen"><i class="fa fa-save"></i>
                            Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div id="tampil_data">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                    Close</button>
            </div>

        </div>
    </div>
</div>

<style>
    .file {
        visibility: hidden;
        position: absolute;
    }
</style>

<script>
    $(document).ready(function () {
        $('[title]').tooltip();
    });
</script>

<script>
    // Tambah Tanggal Libur
    $('#tombol_tambah').on('click', function () {
        $.ajax({
            url: 'apps/pengaturan/tambah_tanggal_libur.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Tanggal Libur';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Edit Tanggal Libur
    $('.tombol_edit').on('click', function () {
        var id = $(this).data('id');
        $.ajax({
            url: 'apps/pengaturan/edit_tanggal_libur.php',
            method: 'post',
            data: { id: id },
            success: function (data) {
                $('#tampil_data').html(data);
                $('#judul').text('Edit Tanggal Libur');
                $('#modal').modal('show');
            }
        });
    });
</script>

<script>
    $(document).on("click", "#pilih_logo", function () {
        var file = $(this).parents().find(".file");
        file.trigger("click");
    });

    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);

        var reader = new FileReader();
        reader.onload = function (e) {
            // get loaded data and render thumbnail.
            document.getElementById("preview").src = e.target.result;
        };
        // read the image file as a data URL.
        reader.readAsDataURL(this.files[0]);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kameraCheckbox = document.querySelector('input[name="kamera_perangkat"]');
        const deteksiCheckbox = document.querySelector('input[name="deteksi_wajah"]');

        kameraCheckbox.addEventListener('change', function () {
            if (!this.checked) {
                // Jika kamera dinonaktifkan, deteksi wajah ikut dinonaktifkan
                deteksiCheckbox.checked = false;
                deteksiCheckbox.disabled = true;
            } else {
                // Jika kamera diaktifkan, deteksi wajah bisa diubah
                deteksiCheckbox.disabled = false;
            }
        });

        // Inisialisasi: jika kamera tidak aktif saat halaman pertama kali dimuat
        if (!kameraCheckbox.checked) {
            deteksiCheckbox.disabled = true;
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const inputs = document.querySelectorAll('.input-menu');
        inputs.forEach(input => {
            const id = input.getAttribute('data-id');
            const icon = input.parentElement.querySelector('.icon-pen');

            input.addEventListener('focus', () => {
                icon.style.display = 'none';
            });

            input.addEventListener('blur', () => {
                if (input.value.trim() === '') {
                    icon.style.display = 'block';
                } else {
                    icon.style.display = 'block';
                }
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var inputs = document.querySelectorAll('.input-menu');
        var btnSubmit = document.getElementById('btnSubmit');

        inputs.forEach(function (input) {
            input.addEventListener('input', function () {
                var alertContainer = document.getElementById('alert-container');
                var valueWithoutSpaces = input.value.replace(/\s+/g, '');
                if (valueWithoutSpaces.length > 20) {
                    alertContainer.innerHTML = "<div class='alert alert-warning'><strong>Gagal!</strong> Huruf tidak boleh lebih dari 20 karakter</div>";
                    btnSubmit.disabled = true;
                    input.value = input.value.slice(0, 20 + input.value.split(' ').length - 1); // allow space characters
                } else {
                    alertContainer.innerHTML = "";
                    btnSubmit.disabled = false;
                }
            });
        });
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-tanggal-libur').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            Swal.fire({
                title: "<span style='font-size: 1.5em;'>Apa anda yakin??</span>",
                html: "<span style='font-size: 1.5em;'>Anda tidak akan dapat mengembalikan data ini!!</span>",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "<span style='font-size: 1.5em;'>Ya, hapus!</span>",
                cancelButtonText: "<span style='font-size: 1.5em;'>Batal</span>"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
</script>

<script>
    // Ambil elemen input tanggal
    const tanggalAwal = document.getElementById('tanggal_awal');
    const tanggalAkhir = document.getElementById('tanggal_akhir');

    // Fungsi untuk memvalidasi tanggal akhir tidak boleh lebih kecil dari tanggal awal
    function validateDates() {
        const tanggalAwalValue = new Date(tanggalAwal.value);
        const tanggalAkhirValue = new Date(tanggalAkhir.value);

        if (tanggalAwalValue && tanggalAkhirValue) {
            if (tanggalAkhirValue < tanggalAwalValue) {
                alert("Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal");
                tanggalAkhir.value = ""; // Clear the invalid date
            }
        }

        // Menonaktifkan tanggal yang sudah dipilih
        if (tanggalAwal.value) {
            tanggalAkhir.setAttribute('min', tanggalAwal.value);
        } else {
            tanggalAkhir.removeAttribute('min');
        }

        if (tanggalAkhir.value) {
            tanggalAwal.setAttribute('max', tanggalAkhir.value);
        } else {
            tanggalAwal.removeAttribute('max');
        }
    }

    // Menambahkan event listener untuk perubahan tanggal
    tanggalAwal.addEventListener('change', validateDates);
    tanggalAkhir.addEventListener('change', validateDates);

    // Inisialisasi pada load halaman (jika ada nilai yang sudah terisi)
    validateDates();
</script>

<script>
    // Fungsi untuk memilih semua checkbox
    document.getElementById('selectAll').onclick = function () {
        var checkboxes = document.querySelectorAll('.selectItem');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
        toggleDeleteButton(); // Cek status checkbox setelah memilih semua
    };

    // Fungsi untuk mengaktifkan atau menonaktifkan tombol Hapus Data Terpilih
    function toggleDeleteButton() {
        var selectedIds = document.querySelectorAll('.selectItem:checked').length;
        var deleteButton = document.getElementById('deleteSelected');
        if (selectedIds > 0) {
            deleteButton.disabled = false; // Mengaktifkan tombol
        } else {
            deleteButton.disabled = true; // Menonaktifkan tombol
        }
    }

    // Fungsi untuk menghapus data yang dipilih
    function deleteSelected() {
        var selectedIds = [];
        var checkboxes = document.querySelectorAll('.selectItem:checked');

        checkboxes.forEach(function (checkbox) {
            selectedIds.push(checkbox.value);
        });

        if (selectedIds.length > 0) {
            Swal.fire({
                title: '<span style="font-size: 1.5em;">Apa anda yakin??</span>',
                html: '<span style="font-size: 1.5em;">Anda tidak akan dapat mengembalikan data ini!!</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Ya, hapus!</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request untuk menghapus data yang terpilih
                    window.location.href = 'apps/pengaturan/hapus_multiple_tanggal_libur.php?ids=' + selectedIds.join(',');
                }
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Tidak ada data yang dipilih',
                text: 'Harap pilih data terlebih dahulu.'
            });
        }
    }

    // Event listener untuk tombol hapus individu
    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            Swal.fire({
                title: '<span style="font-size: 1.5em;">Apa anda yakin??</span>',
                html: '<span style="font-size: 1.5em;">Anda tidak akan dapat mengembalikan data ini!!</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Ya, hapus!</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect untuk menghapus data yang dipilih
                    window.location.href = href;
                }
            });
        });
    });

    // Menambahkan event listener untuk mengubah status tombol ketika ada perubahan checkbox
    document.querySelectorAll('.selectItem').forEach(function (checkbox) {
        checkbox.addEventListener('change', toggleDeleteButton);
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var locations = <?php echo json_encode($locations); ?>;

        // Run only if locations are available and #map element exists
        if (locations.length > 0 && document.getElementById('map')) {
            var map = L.map('map', {
                center: [locations[0].latitude, locations[0].longitude],
                zoom: 15,
                scrollWheelZoom: false
            });

            var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var baseMaps = {
                "OpenStreetMap": osm,
                "Google Earth (Satellite)": L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}'),
                "Google Roadmap": L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}'),
                "Google Hybrid": L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}'),
                "Google Terrain": L.tileLayer('https://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}')
            };

            L.control.layers(baseMaps).addTo(map);

            locations.forEach(function (loc) {
                if (loc.latitude && loc.longitude) {
                    // Marker
                    L.marker([loc.latitude, loc.longitude]).addTo(map)
                        .bindPopup(
                            `<b>Titik lokasi presensi</b><br>
                            <b>Latitude:</b> ${loc.latitude}<br>
                            <b>Longitude:</b> ${loc.longitude}`
                        );

                    // Circle
                    L.circle([loc.latitude, loc.longitude], {
                        color: 'blue',
                        fillColor: 'blue',
                        fillOpacity: 0.3,
                        radius: <?= $radius ?>
                    }).addTo(map);
                }
            });

            // Add zoom info for desktop
            var zoomInfoControl = null;

            function updateZoomInfoControl() {
                if (window.innerWidth > 768) {
                    if (!zoomInfoControl) {
                        zoomInfoControl = L.control({ position: "topright" });
                        zoomInfoControl.onAdd = function () {
                            var div = L.DomUtil.create("div", "zoom-info");
                            div.innerHTML = "<div style='background: white; padding: 5px; border-radius: 5px; color: #000; font-size: 12px; box-shadow: 0 0 5px rgba(0,0,0,0.3);'>Klik <b>CTRL</b> + Scroll untuk zoom</div>";
                            return div;
                        };
                        zoomInfoControl.addTo(map);
                    }
                } else {
                    if (zoomInfoControl) {
                        map.removeControl(zoomInfoControl);
                        zoomInfoControl = null;
                    }
                }
            }

            updateZoomInfoControl();
            window.addEventListener('resize', updateZoomInfoControl);

            // Handle CTRL + ScrollZoom
            const mapContainer = document.getElementById("map");
            mapContainer.addEventListener("wheel", function (e) {
                if (e.ctrlKey) {
                    e.preventDefault(); // Prevent browser zoom
                    map.scrollWheelZoom.enable();
                } else {
                    map.scrollWheelZoom.disable();
                }
            }, { passive: false });

            // Disable zoom after scroll (optional for better UX)
            map.on("zoomend", function () {
                map.scrollWheelZoom.disable();
            });
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var radiusInput = document.getElementById('radiusInput');
        var radiusUnit = document.getElementById('radiusUnit');

        function updateUnit() {
            var value = parseFloat(radiusInput.value);
            if (!isNaN(value)) {
                if (value >= 1000) {
                    radiusUnit.innerText = 'km';
                } else {
                    radiusUnit.innerText = 'm';
                }
            } else {
                radiusUnit.innerText = 'm'; // default
            }
        }

        radiusInput.addEventListener('input', updateUnit);

        // Panggil sekali saat halaman load
        updateUnit();
    });
</script>

<script>
    // Event listener untuk tombol hapus individu
    document.querySelectorAll('.btn-hapus-lokasi').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            Swal.fire({
                title: '<span style="font-size: 1.5em;">Apa anda yakin??</span>',
                html: '<span style="font-size: 1.5em;">Anda tidak akan dapat mengembalikan data ini!!</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Ya, hapus!</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect untuk menghapus data yang dipilih
                    window.location.href = href;
                }
            });
        });
    });
</script>

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