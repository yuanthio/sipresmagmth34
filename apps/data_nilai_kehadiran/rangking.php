<?php
if ($_SESSION["level"] != 'Mahasiswa' and $_SESSION["level"] != 'mahasiswa') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<?php
// Mengatur timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Ambil data mahasiswa yang sedang login
$id_mahasiswa = $_SESSION["id_mahasiswa"];

// Query untuk mengambil data nama dari tbl_mahasiswa
$query_mahasiswa = "SELECT nama, kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
$result_mahasiswa = mysqli_query($kon, $query_mahasiswa);
$data_mahasiswa = mysqli_fetch_assoc($result_mahasiswa);
$nama_mahasiswa = $data_mahasiswa['nama'];
$kode_mahasiswa = $data_mahasiswa['kode_mahasiswa'];

// Query untuk mengambil data kode_pengguna dari tbl_user
$query_user = "SELECT kode_pengguna, level FROM tbl_user WHERE kode_pengguna = '$kode_mahasiswa' AND level = 'Mahasiswa'";
$result_user = mysqli_query($kon, $query_user);
$data_user = mysqli_fetch_assoc($result_user);
$kode_pengguna = $data_user['kode_pengguna'];
$level = $data_user['level'];
$tanggal_sekarang = date('Y-m-d H:i:s');
$aktivitas = "Melihat peringkat kehadiran";
$status_log = (mysqli_num_rows($hasil) > 0) ? "berhasil" : "gagal";

// Masukkan log ke tabel tbl_log_aktivitas
$insert_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
               VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level', '$kode_pengguna', '$aktivitas', '$status_log')";

mysqli_query($kon, $insert_log);
?>

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

    .sticky-row td {
        position: sticky;
        background-color: #eaeaea;
        font-weight: bold;
        z-index: 1;
        /* Supaya selalu di atas elemen lainnya */
    }

    /* Menentukan posisi sticky */
    .sticky-row td:nth-child(1) {
        top: 0;
        bottom: 0;
    }

    .sticky-row td:nth-child(2) {
        top: 0;
        bottom: 0;
    }

    .sticky-row td:nth-child(3) {
        top: 0;
        bottom: 0;
    }

    .sticky-row td:nth-child(4) {
        top: 0;
        bottom: 0;
    }

    .sticky-row td:nth-child(5) {
        top: 0;
        bottom: 0;
    }

    .sticky-row td:nth-child(6) {
        top: 0;
        bottom: 0;
    }

    .sticky-row td:nth-child(7) {
        top: 0;
        bottom: 0;
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
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb" style="background-color: #eaeaea">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Peringkat Kehadiran</li>
    </ol>
</div><!--/.row-->

<div class="row" style="margin-top: 20px; margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Peringkat Kedisiplinan
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="table-responsive">
                    <table class="table table-bordered yuan" id="dataTable" width="100%" cellspacing="0">
                        <thead style="z-index: 1;">
                            <tr>
                                <th width="50">No</th>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Universitas / Sekolah</th>
                                <th>Status (✓)</th>
                                <th>Status (X)</th>
                                <th>Nilai Kedisiplinan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil data untuk peringkat nilai kehadiran
                            $queryRanking = mysqli_query($kon, "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, tbl_mahasiswa.universitas, tbl_mahasiswa.foto, COUNT(CASE WHEN tbl_absensi.konfirmasi_status = '✓' THEN 1 END) as status_hadir, COUNT(CASE WHEN tbl_absensi.konfirmasi_status = 'X' THEN 1 END) as status_tidak_hadir, tbl_mahasiswa.nilai_kehadiran FROM tbl_mahasiswa LEFT JOIN tbl_absensi ON tbl_mahasiswa.id_mahasiswa = tbl_absensi.id_mahasiswa WHERE tbl_mahasiswa.status_magang = 'Aktif' GROUP BY tbl_mahasiswa.id_mahasiswa ORDER BY tbl_mahasiswa.nilai_kehadiran DESC");

                            // Inisialisasi nomor urut
                            $nomor = 1;

                            // Ambil informasi pengguna yang sedang login
                            $id_mahasiswa_login = $_SESSION['id_mahasiswa'];

                            while ($rowRanking = mysqli_fetch_assoc($queryRanking)) {
                                // Cek apakah akun yang sedang login
                                $isLoggedIn = ($rowRanking['id_mahasiswa'] == $id_mahasiswa_login);
                                ?>
                                <tr class="<?= $isLoggedIn ? 'sticky-row' : ''; ?>">
                                    <?php
                                    // Menerapkan gaya khusus untuk peringkat 1, 2, dan 3
                                    if ($nomor == 1) {
                                        ?>
                                        <td class="text-center"
                                            style="font-weight: bold; color: white; background-color: gold;"><?= $nomor; ?></td>
                                        <?php
                                    } elseif ($nomor == 2) {
                                        ?>
                                        <td class="text-center"
                                            style="font-weight: bold; color: white; background-color: silver;"><?= $nomor; ?>
                                        </td>
                                        <?php
                                    } elseif ($nomor == 3) {
                                        ?>
                                        <td class="text-center"
                                            style="font-weight: bold; color: white; background-color: #cd7f32;"><?= $nomor; ?>
                                        </td>
                                        <?php
                                    } else {
                                        ?>
                                        <td class="text-center"><?= $nomor; ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td class="text-center">
                                        <?php if (!empty($rowRanking['foto'])) { ?>
                                            <?php if ($isLoggedIn) { ?>
                                                <div
                                                    style="width: 100%; text-align: center; margin-bottom: 10px; position: relative; display: inline-block;">
                                                    <img src="apps/mahasiswa/foto/<?= $rowRanking['foto']; ?>" alt="Foto"
                                                        style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; display: block;">
                                                </div>
                                            <?php } else { ?>
                                                <a href="#" data-toggle="modal"
                                                    data-target="#modalFoto<?= $rowRanking['id_mahasiswa']; ?>">
                                                    <div
                                                        style="width: 100%; text-align: center; margin-bottom: 10px; position: relative; display: inline-block;">
                                                        <img src="apps/mahasiswa/foto/<?= $rowRanking['foto']; ?>" alt="Foto"
                                                            style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; display: block;">
                                                    </div>
                                                </a>

                                                <!-- Modal untuk menampilkan foto lebih besar -->
                                                <div class="modal fade" id="modalFoto<?= $rowRanking['id_mahasiswa']; ?>"
                                                    tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title" id="exampleModalLabel">
                                                                    <?= $rowRanking['nama']; ?></h4>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center" style="width: 100%; text-align: center; margin-bottom: 10px;">
                                                                <img src="apps/mahasiswa/foto/<?= $rowRanking['foto']; ?>"
                                                                    alt="Foto" style="width: 350px; height: 350px; border-radius: 50%; object-fit: cover; border: 4px solid rgb(13, 10, 44);">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <div
                                                style="width: 100%; text-align: center; margin-bottom: 10px; position: relative; display: inline-block;">
                                                <img src="apps/mahasiswa/foto/foto_default.png" alt="Foto"
                                                    style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; display: block;">
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td><?= $rowRanking['nama']; ?>     <?= $isLoggedIn ? '(Anda)' : ''; ?></td>
                                    <td><?= $rowRanking['universitas']; ?></td>
                                    <td><?= $rowRanking['status_hadir']; ?></td>
                                    <td><?= $rowRanking['status_tidak_hadir']; ?></td>
                                    <td><?= $rowRanking['nilai_kehadiran']; ?></td>
                                </tr>
                                <?php
                                // Increment nomor urut
                                $nomor++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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