<?php
// Memasukkan file konfigurasi database
include 'config/database.php';

// Mengatur timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Ambil kode pengguna dari session
$kode_pengguna = isset($_SESSION['kode_pengguna']) ? $_SESSION['kode_pengguna'] : '';
$id_mahasiswa = $_SESSION["id_mahasiswa"];

// Ambil parameter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination setup
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25; // default 25 data per halaman
if (!in_array($limit, [25, 50, 75, 100])) {
    $limit = 25;
}
$page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Query untuk mengambil data mahasiswa yang sedang login
$query_mahasiswa = "SELECT nama, kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa = ?";
$stmt_mahasiswa = $kon->prepare($query_mahasiswa);
$stmt_mahasiswa->bind_param("s", $id_mahasiswa);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();
$data_mahasiswa = $result_mahasiswa->fetch_assoc();
$nama_mahasiswa = $data_mahasiswa['nama'];
$kode_mahasiswa = $data_mahasiswa['kode_mahasiswa'];

// Query untuk mengambil data kode_pengguna dari tbl_user
$query_user = "SELECT kode_pengguna, level FROM tbl_user WHERE kode_pengguna = ? AND level = 'Mahasiswa'";
$stmt_user = $kon->prepare($query_user);
$stmt_user->bind_param("s", $kode_mahasiswa);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$data_user = $result_user->fetch_assoc();

// Cek apakah user terdaftar
if ($data_user) {
    $kode_pengguna = $data_user['kode_pengguna'];
    $level = $data_user['level'];

    // Mendapatkan tanggal dan waktu sekarang
    $tanggal_sekarang = date('Y-m-d H:i:s');
    $aktivitas = "Melihat catatan aktivitas";

    // Hitung total data untuk pagination
    $query_count = "SELECT COUNT(*) as total FROM tbl_log_aktivitas 
                    WHERE (tanggal LIKE ? OR aktivitas LIKE ?) AND kode_pengguna = ?";
    $stmt_count = $kon->prepare($query_count);
    $likeSearch = "%" . $search . "%";
    $stmt_count->bind_param("sss", $likeSearch, $likeSearch, $kode_pengguna);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_data = $result_count->fetch_assoc()['total'];
    $total_halaman = ceil($total_data / $limit);

    // Query untuk mengambil data aktivitas dengan pagination
    $query = "SELECT * FROM tbl_log_aktivitas 
              WHERE (tanggal LIKE ? OR aktivitas LIKE ?) AND kode_pengguna = ? 
              ORDER BY tanggal DESC LIMIT ? OFFSET ?";
    $stmt = $kon->prepare($query);
    $stmt->bind_param("sssii", $likeSearch, $likeSearch, $kode_pengguna, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Status log
    $status_log = ($result->num_rows > 0) ? "berhasil" : "gagal";

    // Insert log hanya saat halaman pertama kali dibuka (tanpa parameter halaman)
    if (!isset($_GET['halaman'])) {
        $insert_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                   VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_log = $kon->prepare($insert_log);
        $stmt_log->bind_param("ssssss", $tanggal_sekarang, $nama_mahasiswa, $level, $kode_pengguna, $aktivitas, $status_log);
        $stmt_log->execute();
        $stmt_log->close();
    }
}
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

    #dataFilter {
        width: 70px;
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
                        <input type="hidden" name="page" value="aktivitas_mahasiswa" />
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
                <div class="form-group">
                    <select id="dataFilter" class="form-control" onchange="changeLimit(this.value)">
                        <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="75" <?= $limit == 75 ? 'selected' : '' ?>>75</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
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
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $aktivitas = htmlspecialchars($row['aktivitas']);
                                    if ($row['status'] == 'gagal') {
                                        $aktivitas = preg_replace('/\((.*?)\)/', '(<span style="color: red;">$1</span>)', $aktivitas);
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['tanggal']); ?></td>
                                        <td><?= $aktivitas; ?></td>
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

                <!-- Pagination -->
                <nav aria-label="Page navigation" style="display: flex; justify-content: center;">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li>
                                <a
                                    href="?page=aktivitas_mahasiswa&halaman=<?= $page - 1; ?>&limit=<?= $limit ?>&search=<?= urlencode($search); ?>">
                                    « Prev
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $range = 2; // jumlah halaman di kiri dan kanan halaman aktif
                        $start = max(1, $page - $range);
                        $end = min($total_halaman, $page + $range);

                        // tampilkan halaman pertama
                        if ($start > 1) {
                            echo '<li><a href="?page=aktivitas_mahasiswa&halaman=1&limit=' . $limit . '&search=' . urlencode($search) . '">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                        }

                        // halaman di sekitar halaman aktif
                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="' . $active . '"><a href="?page=aktivitas_mahasiswa&halaman=' . $i . '&limit=' . $limit . '&search=' . urlencode($search) . '">' . $i . '</a></li>';
                        }

                        // tampilkan halaman terakhir
                        if ($end < $total_halaman) {
                            if ($end < $total_halaman - 1) {
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                            echo '<li><a href="?page=aktivitas_mahasiswa&halaman=' . $total_halaman . '&limit=' . $limit . '&search=' . urlencode($search) . '">' . $total_halaman . '</a></li>';
                        }
                        ?>

                        <?php if ($page < $total_halaman): ?>
                            <li>
                                <a
                                    href="?page=aktivitas_mahasiswa&halaman=<?= $page + 1; ?>&limit=<?= $limit ?>&search=<?= urlencode($search); ?>">
                                    Next »
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
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

<script>
    function changeLimit(limit) {
        const search = "<?= urlencode($search); ?>";
        window.location.href = "?page=aktivitas_mahasiswa&limit=" + limit + "&search=" + search;
    }
</script>