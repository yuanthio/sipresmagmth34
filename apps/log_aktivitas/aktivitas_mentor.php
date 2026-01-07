<?php
// Memasukkan file konfigurasi database
include 'config/database.php';

// Ambil kode pengguna dari session
$kode_pengguna = isset($_SESSION['kode_pengguna']) ? $_SESSION['kode_pengguna'] : '';

// Mengambil level pengguna
$queryUser = "SELECT level FROM tbl_user WHERE kode_pengguna = ?";
$stmtUser = $kon->prepare($queryUser);
$stmtUser->bind_param("s", $kode_pengguna);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userInfo = $resultUser->fetch_assoc();
$level = $userInfo['level'];

// Hanya mencatat log jika level adalah Mentor
if ($level === 'Mentor') {
    // Mengambil nama mentor
    $queryMentor = "SELECT nama FROM tbl_mentor WHERE kode_mentor = ?";
    $stmtMentor = $kon->prepare($queryMentor);
    $stmtMentor->bind_param("s", $kode_pengguna);
    $stmtMentor->execute();
    $resultMentor = $stmtMentor->get_result();
    $mentorInfo = $resultMentor->fetch_assoc();
    $nama_mentor = $mentorInfo['nama'];

    // Mendapatkan tanggal dan waktu sekarang dalam format yang diinginkan
    date_default_timezone_set('Asia/Jakarta'); // Set timezone Indonesia
    $tanggal_sekarang = date("Y-m-d H:i:s");

    // Menyimpan aktivitas ke tabel log
    $aktivitas = "Melihat catatan aktivitas"; // Aktivitas yang ingin dicatat
    $status = "berhasil"; // Status log

    $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtLog = $kon->prepare($queryLog);
    $stmtLog->bind_param("ssssss", $tanggal_sekarang, $nama_mentor, $level, $kode_pengguna, $aktivitas, $status);
    $stmtLog->execute();
}

// Ambil parameter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mengambil data aktivitas
$query = "SELECT * FROM tbl_log_aktivitas WHERE 
          (tanggal LIKE ? OR 
          aktivitas LIKE ?) AND 
          kode_pengguna = ? 
          ORDER BY tanggal DESC";

// Menyiapkan pernyataan
$stmt = $kon->prepare($query);
$likeSearch = "%" . $search . "%";
$stmt->bind_param("sss", $likeSearch, $likeSearch, $kode_pengguna);
$stmt->execute();
$result = $stmt->get_result();
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
        <li class="active">Catatan Aktivitas</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Catatan Aktivitas
                <span class="pull-right clickable panel-toggle panel-button-tab-left">
                    <em class="fa fa-toggle-up"></em>
                </span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="log_aktivitas" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                    placeholder="Pencarian">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="200">Tanggal</th>
                                <th>Aktivitas</th>
                                <th width="100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengecek apakah ada data
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                        <td><?php echo htmlspecialchars($row['aktivitas']); ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'berhasil'): ?>
                                                <span class="label label-success">Berhasil</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Gagal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada data ditemukan</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Menutup pernyataan dan koneksi
$stmt->close();
$kon->close();
?>

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
