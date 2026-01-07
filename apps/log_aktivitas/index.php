<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">

<?php
// Cek level akses pengguna
if ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'admin') {
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
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('hapus_log_aktivitas');
                window.history.replaceState(null, '', url);
            }
        });
    }
</script>

<?php
// Koneksi ke database
include 'config/database.php';

// Mengambil kode pengguna dari sesi
$kode_pengguna = $_SESSION['kode_pengguna'];

// Mengambil level pengguna
$queryUser = "SELECT level FROM tbl_user WHERE kode_pengguna = ?";
$stmtUser = $kon->prepare($queryUser);
$stmtUser->bind_param("s", $kode_pengguna);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userInfo = $resultUser->fetch_assoc();
$level = $userInfo['level'];

// Hanya mencatat log jika level adalah Admin
if ($level === 'Admin') {
    // Mengambil nama admin
    $queryAdmin = "SELECT nama FROM tbl_admin WHERE kode_admin = ?";
    $stmtAdmin = $kon->prepare($queryAdmin);
    $stmtAdmin->bind_param("s", $kode_pengguna);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();
    $adminInfo = $resultAdmin->fetch_assoc();
    $nama_admin = $adminInfo['nama'];

    // Jika nama bukan "Yuanthio Virly", catat log
    if ($nama_admin !== 'Yuanthio Virly') {
        // Mendapatkan tanggal dan waktu sekarang dalam format yang diinginkan
        date_default_timezone_set('Asia/Jakarta'); // Set timezone Indonesia
        $tanggal_sekarang = date("Y-m-d H:i:s");

        // Menyimpan aktivitas ke tabel log
        $aktivitas = "Melihat catatan aktivitas"; // Aktivitas yang ingin dicatat
        $status = "berhasil"; // Status log

        $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtLog = $kon->prepare($queryLog);
        $stmtLog->bind_param("ssssss", $tanggal_sekarang, $nama_admin, $level, $kode_pengguna, $aktivitas, $status);
        $stmtLog->execute();
    }
}
?>

<?php
// Koneksi ke database
include 'config/database.php';

// Ambil kode pengguna dari session
$kode_pengguna = $_SESSION['kode_pengguna'];
$level = '';
$nama = '';

// Ambil level & nama user
$query = "SELECT u.level, a.nama 
          FROM tbl_user u 
          LEFT JOIN tbl_admin a ON u.kode_pengguna = a.kode_admin 
          WHERE u.kode_pengguna = ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("s", $kode_pengguna);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $level = $row['level'];
    $nama = $row['nama'];
}

// Cek apakah SuperAdmin
$isSuperAdmin = ($level === 'Admin' && $nama === 'Yuanthio Virly');

// ==================== PAGINATION SETUP ====================

// Ambil nilai limit dari GET, default 25
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
if (!in_array($limit, [25, 50, 75, 100])) {
    $limit = 25; // fallback kalau user ubah URL manual
}

$page = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Ambil jumlah total data
$search = isset($_GET['search']) ? $_GET['search'] : '';
$likeSearch = "%" . $search . "%";

$queryCount = "SELECT COUNT(*) as total FROM tbl_log_aktivitas 
               WHERE tanggal LIKE ? OR nama LIKE ? OR level LIKE ? OR aktivitas LIKE ?";
$stmtCount = $kon->prepare($queryCount);
$stmtCount->bind_param("ssss", $likeSearch, $likeSearch, $likeSearch, $likeSearch);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$totalData = $resultCount->fetch_assoc()['total'];

$totalPages = ceil($totalData / $limit);

// Ambil data dengan limit + offset
$query = "SELECT * FROM tbl_log_aktivitas 
          WHERE tanggal LIKE ? OR nama LIKE ? OR level LIKE ? OR aktivitas LIKE ? 
          ORDER BY tanggal DESC 
          LIMIT ? OFFSET ?";
$stmt = $kon->prepare($query);
$stmt->bind_param("ssssii", $likeSearch, $likeSearch, $likeSearch, $likeSearch, $limit, $offset);
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

    .filter {
        display: flex;
        column-gap: 5px;
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
                <?php
                if (isset($_GET['hapus_log_aktivitas'])) {
                    if ($_GET['hapus_log_aktivitas'] == 'berhasil') {
                        echo "<script>showAlert('success', 'Berhasil!', 'Log Aktivitas Telah Dihapus');</script>";
                    } else {
                        echo "<script>showAlert('error', 'Gagal!', 'Log Aktivitas Gagal Dihapus');</script>";
                    }
                }
                ?>

                <form action="apps/log_aktivitas/hapus_multiple.php" method="post" id="formHapusBanyak">
                    <div class="form-group filter">
                        <?php if ($isSuperAdmin): ?>
                            <button type="button" class="btn btn-danger btn-hapus-log-multiple">
                                <i class="fa fa-trash"></i> Hapus yang dipilih
                            </button>
                        <?php endif; ?>
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
                                    <th width="150">Tanggal</th>
                                    <th>Nama</th>
                                    <th>Level</th>
                                    <th>Aktivitas</th>
                                    <th>Status</th>
                                    <th width="100">Aksi</th>
                                    <?php if ($isSuperAdmin): ?>
                                        <th><input type="checkbox" id="checkAll"> Pilih Semua</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['level']); ?></td>
                                            <td>
                                                <?php
                                                $aktivitas = $row['aktivitas'];
                                                if ($row['level'] == 'Mahasiswa' && $row['status'] == 'gagal') {
                                                    $aktivitas = preg_replace('/\((.*?)\)/', '(<span style="color: red;">$1</span>)', $aktivitas);
                                                } elseif ($row['level'] == 'Admin' && $row['status'] == 'gagal') {
                                                    $aktivitas = preg_replace('/\)(.*)/', ')</span><span style="color: red;">$1</span>', $aktivitas);
                                                }
                                                echo $aktivitas;
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] == 'berhasil'): ?>
                                                    <span class="label label-success">Berhasil</span>
                                                <?php else: ?>
                                                    <span class="label label-danger">Gagal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" class="tombol_detail btn btn-info btn-circle"
                                                    title="Detail Aktivitas" data-kode="<?php echo $row['kode_pengguna']; ?>">
                                                    <i class="fa fa-history"></i>
                                                </button>
                                                <?php if ($isSuperAdmin): ?>
                                                    <a href="apps/log_aktivitas/hapus.php?id_log_aktivitas=<?php echo $row['id_log_aktivitas']; ?>"
                                                        class="btn-hapus-log btn btn-danger btn-circle" title="Hapus Log Aktivitas">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($isSuperAdmin): ?>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" name="id_log_aktivitas[]"
                                                        value="<?= $row['id_log_aktivitas']; ?>" class="checkItem">
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data ditemukan</td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- PAGINATION -->
                <nav aria-label="Page navigation" style="display: flex; justify-content: center;">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li><a
                                    href="?page=log_aktivitas&search=<?= urlencode($search); ?>&limit=<?= $limit; ?>&halaman=<?= $page - 1; ?>">«
                                    Prev</a></li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1) {
                            echo '<li><a href="?page=log_aktivitas&search=' . urlencode($search) . '&limit=' . $limit . '&halaman=1">1</a></li>';
                            if ($start > 2)
                                echo '<li class="disabled"><span>...</span></li>';
                        }
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="<?= ($i == $page) ? 'active' : ''; ?>">
                                <a
                                    href="?page=log_aktivitas&search=<?= urlencode($search); ?>&limit=<?= $limit; ?>&halaman=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor;
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1)
                                echo '<li class="disabled"><span>...</span></li>';
                            echo '<li><a href="?page=log_aktivitas&search=' . urlencode($search) . '&limit=' . $limit . '&halaman=' . $totalPages . '">' . $totalPages . '</a></li>';
                        }
                        ?>

                        <?php if ($page < $totalPages): ?>
                            <li><a
                                    href="?page=log_aktivitas&search=<?= urlencode($search); ?>&limit=<?= $limit; ?>&halaman=<?= $page + 1; ?>">Next
                                    »</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
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

            <div class="modal-body" style="background-color: rgb(24, 18, 92); padding: 20px;">
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

        $('.tombol_detail').on('click', function () {
            var kode_pengguna = $(this).data('kode'); // Ambil kode pengguna dari atribut data-kode

            $.ajax({
                url: 'apps/log_aktivitas/detail.php', // Pastikan URL ini benar
                method: 'POST',
                data: { kode: kode_pengguna }, // Kirim kode ke server
                success: function (data) {
                    $('#tampil_data').html(data); // Tampilkan data ke dalam modal
                    document.getElementById("judul").innerHTML = 'Detail Catatan Aktivitas';
                },
                error: function (xhr, status, error) {
                    console.error(error); // Tambahkan error handling
                }
            });

            $('#modal').modal('show'); // Tampilkan modal
        });
    });
</script>

<script>
    // Untuk hapus satuan (pakai href)
    document.querySelectorAll('.btn-hapus-log').forEach(button => {
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

    // Untuk hapus multiple (submit form)
    document.querySelector('.btn-hapus-log-multiple').addEventListener('click', function (e) {
        e.preventDefault();

        // Cek apakah ada checkbox yang dicentang
        const checked = document.querySelectorAll('.checkItem:checked');
        if (checked.length === 0) {
            Swal.fire({
                icon: 'info',
                title: '<span style="font-size: 1.5em;">Tidak ada data dipilih!</span>',
                html: '<span style="font-size: 1.5em;">Silakan pilih data terlebih dahulu.</span>',
                confirmButtonText: '<span style="font-size: 1.5em;">OK</span>'
            });
            return;
        }

        Swal.fire({
            title: "<span style='font-size: 1.5em;'>Apa anda yakin??</span>",
            html: "<span style='font-size: 1.5em;'>Data yang dipilih akan dihapus permanen!</span>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "<span style='font-size: 1.5em;'>Ya, hapus!</span>",
            cancelButtonText: "<span style='font-size: 1.5em;'>Batal</span>"
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formHapusBanyak').submit();
            }
        });
    });
</script>

<script>
    // Select/Deselect semua checkbox
    document.getElementById('checkAll').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('.checkItem');
        checkboxes.forEach(cb => cb.checked = this.checked);
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

<script>
    function changeLimit(val) {
        const url = new URL(window.location.href);
        url.searchParams.set("limit", val);
        url.searchParams.set("halaman", 1); // reset ke halaman 1 kalau ganti limit
        window.location.href = url.toString();
    }
</script>