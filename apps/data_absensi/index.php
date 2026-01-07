<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

<?php
if ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'Mentor' && $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

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
            // Menghapus parameter URL setelah menampilkan alert
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('mulai');
                url.searchParams.delete('edit');
                url.searchParams.delete('hapus');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<style>
    .table-responsive {
        overflow-y: auto;
        max-height: 500px;
    }

    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
    }

    /* Spinner CSS */
    .spinner-container {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .spinner-border {
        width: 2rem;
        height: 2rem;
        border: 0.25em solid currentColor;
        border-right: 0.25em solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
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

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb" style="background-color: #eaeaea">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Absensi</li>
    </ol>
</div><!--/.row-->

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Presensi
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_absensi" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Nama Karyawan Magang :</label>
                                <input type="text" name="nama" id="nama" class="form-control" value=""
                                    placeholder="Cari Mahasiswa">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Awal : <span style="color: red">*</span></label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tanggal Akhir : <span style="color: red">*</span></label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                </br>
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div id="alert-container"></div>
                <?php
                // Validasi untuk menampilkan pesan pemberitahuan saat user update pengaturan aplikasi
                if (isset($_GET['mulai'])) {
                    if ($_GET['mulai'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Absensi Berhasil Ditambah');</script>";
                    } else if ($_GET['mulai'] == 'gagal') {
                        echo "<script>showAlert('warning', 'Maaf!', 'Data Absensi Sudah Ada');</script>";
                    } else if ($_GET['mulai'] == 'gagal_ukuran_file') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file melebihi 1MB. Harap upload ulang kembali.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_tipe_file') {
                        echo "<script>showAlert('error', 'Gagal!', 'Tipe file tidak didukung. Harap upload file JPG, JPEG, PNG, atau GIF.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_upload') {
                        echo "<script>showAlert('error', 'Gagal!', 'Upload bukti alasan gagal. Silakan coba lagi.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_tipe_file_kamera') {
                        echo "<script>showAlert('error', 'Gagal!', 'Tipe file kamera tidak didukung. Harap upload file JPG, JPEG, atau PNG.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_ukuran_file_kamera') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file kamera melebihi 1MB. Harap upload ulang.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_upload_kamera') {
                        echo "<script>showAlert('error', 'Gagal!', 'Upload foto kamera gagal. Silakan coba lagi.');</script>";
                    }

                    // ✅ Tambahan khusus bukti WFA
                    else if ($_GET['mulai'] == 'gagal_tipe_file_wfa') {
                        echo "<script>showAlert('error', 'Gagal!', 'Tipe file bukti WFA tidak valid. Harap upload JPG, JPEG, PNG, PDF, DOC, atau DOCX.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_ukuran_file_wfa') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file bukti WFA melebihi 1MB. Harap upload ulang.');</script>";
                    } else if ($_GET['mulai'] == 'gagal_upload_wfa') {
                        echo "<script>showAlert('error', 'Gagal!', 'Upload bukti WFA gagal. Silakan coba lagi.');</script>";
                    }
                }

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Absensi Berhasil Diedit');</script>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<script>showAlert('warning', 'Maaf!', 'Data Absensi Gagal Diedit');</script>";
                    } else if ($_GET['edit'] == 'gagal_ukuran_file') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file melebihi 1MB. Harap upload ulang kembali.');</script>";
                    } else if ($_GET['edit'] == 'gagal_tipe_file') {
                        echo "<script>showAlert('error', 'Gagal!', 'Tipe file tidak didukung. Harap upload file JPG, JPEG, PNG, atau GIF.');</script>";
                    } else if ($_GET['edit'] == 'gagal_ukuran_kamera') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file kamera melebihi 1MB. Harap upload ulang kembali.');</script>";
                    } else if ($_GET['edit'] == 'gagal_tipe_kamera') {
                        echo "<script>showAlert('error', 'Gagal!', 'Tipe file kamera tidak didukung. Harap upload file JPG, JPEG, atau PNG.');</script>";
                    } else if ($_GET['edit'] == 'gagal_upload_kamera') {
                        echo "<script>showAlert('error', 'Gagal!', 'Upload foto kamera gagal. Silakan coba lagi.');</script>";
                    }

                    // ✅ Tambahan khusus untuk Bukti WFA
                    else if ($_GET['edit'] == 'gagal_tipe_wfa') {
                        echo "<script>showAlert('error', 'Gagal!', 'Tipe file bukti WFA tidak valid. Harap upload JPG, JPEG, PNG, PDF, DOC, atau DOCX.');</script>";
                    } else if ($_GET['edit'] == 'gagal_ukuran_wfa') {
                        echo "<script>showAlert('error', 'Gagal!', 'Ukuran file bukti WFA melebihi 1MB. Harap upload ulang.');</script>";
                    } else if ($_GET['edit'] == 'gagal_upload_wfa') {
                        echo "<script>showAlert('error', 'Gagal!', 'Upload bukti WFA gagal. Silakan coba lagi.');</script>";
                    }
                }

                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Data Absensi Berhasil Dihapus');</script>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<script>showAlert('warning', 'Gagal!', 'Data Absensi Gagal Dihapus');</script>";
                    }
                }
                ?>

                <?php if ($_SESSION["level"] == 'Admin'): ?>
                    <div class="form-group">
                        <button type="button" class="btn btn-success" id="tambah_absensi"><i
                                class="tambah_absensi fa fa-plus"></i> Tambah</button>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-light" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama</th>
                                <th class="text-center">Universitas / Sekolah</th>
                                <th width="100" class="text-center">Status</th>
                                <th width="220" class="text-center">Keterangan</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-center">Hari</th>
                                <th class="text-center">Tanggal</th>
                                <th width="230" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/database.php';
                            include 'config/function.php';
                            date_default_timezone_set('Asia/Jakarta');

                            // Hari dan tanggal saat ini
                            $hari_ini = date('l');
                            $hari_id = date('N');
                            $tanggal_hari_ini = date('Y-m-d');

                            // Cek apakah hari ini termasuk tanggal libur berdasarkan rentang tanggal
                            $query_tanggal_libur = "SELECT * FROM tbl_tanggal_libur 
                            WHERE '$tanggal_hari_ini' BETWEEN tanggal_awal AND tanggal_akhir 
                            AND status = 'Sedang berlangsung' 
                            LIMIT 1";
                            $hasil_tanggal_libur = mysqli_query($kon, $query_tanggal_libur);
                            $data_tanggal_libur = mysqli_fetch_assoc($hasil_tanggal_libur);

                            // Cek apakah hari ini adalah hari libur mingguan (dari tbl_hari_libur)
                            $query_hari_libur = "SELECT status FROM tbl_hari_libur WHERE id = '$hari_id' LIMIT 1";
                            $hasil_hari_libur = mysqli_query($kon, $query_hari_libur);
                            $data_hari_libur = mysqli_fetch_assoc($hasil_hari_libur);

                            if ($data_tanggal_libur) {
                                $tglAwal = date('d-m-Y', strtotime($data_tanggal_libur['tanggal_awal']));
                                $tglAkhir = date('d-m-Y', strtotime($data_tanggal_libur['tanggal_akhir']));
                                $rentangTanggal = ($tglAwal == $tglAkhir) ? "($tglAwal)" : "($tglAwal - $tglAkhir)";
                                echo '<tr><td colspan="9" class="text-center">Libur ' . $data_tanggal_libur['alasan_libur'] . ' ' . $rentangTanggal . '</td></tr>';
                            } elseif ($data_hari_libur['status'] == 'Libur') {
                                echo '<tr><td colspan="9" class="text-center">Data tidak ditampilkan karena hari libur</td></tr>';
                            } else {
                                $tanggal_awal = $_GET["tanggal_awal"] ?? '';
                                $tanggal_akhir = $_GET["tanggal_akhir"] ?? '';
                                $nama = trim($_GET["nama"] ?? '');

                                if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
                                    $sql = PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir);
                                } else {
                                    $sql = AbsensiOtomatis('');
                                }

                                $hasil = mysqli_query($kon, $sql);
                                $no = 0;

                                if (mysqli_num_rows($hasil) == 0) {
                                    echo '<tr><td colspan="9" class="text-center">Data masih kosong</td></tr>';
                                } else {
                                    while ($data = mysqli_fetch_array($hasil)):
                                        $no++;
                                        ?>
                                        <tr>
                                            <td><?php echo $no; ?></td>
                                            <td><?php echo $data['nama']; ?></td>
                                            <td><?php echo $data['universitas']; ?></td>
                                            <td class="text-center">
                                                <?php
                                                $status = $data['status'];
                                                if ($status == 'Hadir') {
                                                    echo '<span class="label label-success">Hadir</span>';
                                                } elseif ($status == 'Izin') {
                                                    echo '<span class="label label-info">Izin</span>';
                                                } elseif ($status == 'Terlambat') {
                                                    echo '<span class="label label-warning">Terlambat</span>';
                                                } elseif ($status == 'Tidak Hadir') {
                                                    echo '<span class="label label-danger">Tidak Hadir</span>';
                                                } elseif ($status == 'WFA') {
                                                    echo '<span class="label label-primary">WFA</span>';
                                                } else {
                                                    echo '<span class="label label-default">Belum Absensi</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $id_mahasiswa = $data['id_mahasiswa'];
                                                $tanggal = $data['tanggal'];

                                                if ($status == 'Izin') {
                                                    // === Untuk status Izin ===
                                                    $query_alasan = "SELECT alasan FROM tbl_alasan 
                                                    WHERE id_mahasiswa = '$id_mahasiswa' 
                                                    AND tanggal = '$tanggal' LIMIT 1";
                                                    $hasil_alasan = mysqli_query($kon, $query_alasan);
                                                    $data_alasan = mysqli_fetch_assoc($hasil_alasan);
                                                    echo $data_alasan ? $data_alasan['alasan'] : '-';

                                                } elseif ($status == 'WFA') {
                                                    $query_bukti = "SELECT bukti_wfa FROM tbl_bukti_wfa 
                                                    WHERE id_mahasiswa = '$id_mahasiswa' 
                                                    AND tanggal = '$tanggal' LIMIT 1";
                                                    $hasil_bukti = mysqli_query($kon, $query_bukti);
                                                    $data_bukti = mysqli_fetch_assoc($hasil_bukti);

                                                    if ($data_bukti && !empty($data_bukti['bukti_wfa'])) {
                                                        $file_bukti = 'apps/data_absensi/file_wfa/' . $data_bukti['bukti_wfa'];
                                                        $ext = strtolower(pathinfo($file_bukti, PATHINFO_EXTENSION));

                                                        // hanya izinkan pdf/doc/docx
                                                        $ext_file = ['pdf', 'doc', 'docx'];

                                                        if (in_array($ext, $ext_file)) {
                                                            $icon = "apps/data_absensi/extensi_file/" . $ext . ".png";
                                                            $basename = htmlspecialchars(basename($file_bukti));
                                                            echo '<a href="apps/data_absensi/download.php?file=' . urlencode($data_bukti['bukti_wfa']) . '" 
                                                                style="text-decoration:none;">
                                                                <img src="' . $icon . '" width="20" style="margin-right:5px;">
                                                                ' . $basename . '
                                                            </a>';
                                                        } else {
                                                            echo '<div class="text-center">-</div>';
                                                        }
                                                    } else {
                                                        echo '<div class="text-center">-</div>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $waktu = $data['waktu'];
                                                echo $waktu == 'Belum' ? '<span class="label label-default">Belum</span>' : $waktu;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo MendapatkanHari($data["hari"]); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $tgl = date("d", strtotime($data['tanggal']));
                                                $bulan = date("m", strtotime($data['tanggal']));
                                                $tahun = date("Y", strtotime($data['tanggal']));
                                                echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                                                ?>
                                            </td>
                                            <td>
                                                <button style="margin-bottom: 5px;"
                                                    data-id_absensi="<?php echo $data['id_absensi']; ?>"
                                                    class="cek-foto btn btn-success btn-circle" title="Cek Foto">
                                                    <i class="bi bi-camera-fill"></i>
                                                </button>

                                                <button style="margin-bottom: 5px;" id_absensi="<?php echo $data['id_absensi']; ?>"
                                                    class="cek-lokasi btn btn-info btn-circle" title="Cek Lokasi">
                                                    <i class="fa fa-map-marker"></i>
                                                </button>

                                                <?php if ($_SESSION["level"] == 'Admin'): ?>
                                                    <button style="margin-bottom: 5px;"
                                                        id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>"
                                                        id_absensi="<?php echo $data['id_absensi']; ?>"
                                                        class="absensi btn btn-warning btn-circle" title="Edit Presensi">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <button style="margin-bottom: 5px;"
                                                    id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>"
                                                    class="cetak btn btn-primary btn-circle" title="Cetak Presensi">
                                                    <i class="fa fa-print"></i>
                                                </button>

                                                <?php if ($status != 'Belum Absensi' && $_SESSION["level"] == 'Admin'): ?>
                                                    <a style="margin-bottom: 5px;"
                                                        href="apps/data_absensi/hapus.php?id_absensi=<?php echo $data['id_absensi']; ?>"
                                                        class="btn-hapus-absensi btn btn-danger btn-circle"
                                                        title="Hapus Data Absensi Karyawan Magang">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->


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

<script>
    $(document).ready(function () {
        $('[title]').tooltip();
    });
</script>

<script>
    // Cek Lokasi - Ambil Latitude dan Longitude
    $('.cek-foto').on('click', function () {
        var id_absensi = $(this).data("id_absensi");

        $.ajax({
            url: 'apps/data_absensi/cek_foto.php',
            method: 'POST',
            data: { id_absensi: id_absensi },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Foto Presensi';
                $('#modal').modal('show');
            }
        });
    });
</script>

<script>
    // Cek Lokasi - Ambil Latitude dan Longitude
    $('.cek-lokasi').on('click', function () {
        var id_absensi = $(this).attr("id_absensi");

        $.ajax({
            url: 'apps/data_absensi/cek_lokasi.php',  // Buat file cek_lokasi.php untuk mengambil data lokasi
            method: 'POST',
            data: { id_absensi: id_absensi },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Lokasi Presensi';
            }
        });

        // Membuka modal
        $('#modal').modal('show');
    });

    // Setelah modal terbuka sepenuhnya, inisialisasi peta
    $('#modal').on('shown.bs.modal', function () {
        // Periksa apakah peta sudah ada
        if (typeof map === "undefined") {
            return; // Peta belum diinisialisasi
        }

        // Memastikan peta diperbarui setelah modal muncul
        map.invalidateSize(); // Memastikan peta dirender ulang setelah modal ditampilkan
    });
</script>

<script>
    //Menambahkan absensi oleh admin
    $('#tambah_absensi').on('click', function () {
        $.ajax({
            url: 'apps/data_absensi/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Presensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    //Mengubah absensi oleh admin
    $('.absensi').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        var id_absensi = $(this).attr("id_absensi");
        $.ajax({
            url: 'apps/data_absensi/absensi.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa,
                id_absensi: id_absensi
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Presensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    //Cetak Absensi
    $('.cetak').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_absensi/cetak.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Cetak Presensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    document.querySelectorAll('.btn-hapus-absensi').forEach(button => {
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