<?php
session_start();
include '../../config/database.php';
date_default_timezone_set("Asia/Jakarta");

// Cek apakah ada request POST untuk menyimpan perubahan
if (isset($_POST['simpan_tanggal_libur'])) {
    $id = $_POST['id'];
    $tanggal_awal = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $alasan_libur = $_POST['alasan_libur'];
    $tanggal_input = date('Y-m-d');

    // Cek bentrok dengan tanggal libur lain (kecuali data yang sedang diedit)
    $cek_bentrok = mysqli_query($kon, "
        SELECT * FROM tbl_tanggal_libur 
        WHERE id != '$id'
        AND (
            (tanggal_awal <= '$tanggal_akhir' AND tanggal_akhir >= '$tanggal_awal')
        )
    ");

    if (mysqli_num_rows($cek_bentrok) > 0) {
        header("Location:../../index.php?page=pengaturan&edit_tanggal_libur=bentrok");
        exit;
    }

    // Hitung status berdasarkan tanggal
    $today = date('Y-m-d');
    if ($today < $tanggal_awal) {
        $status = 'Belum dimulai';
    } elseif ($today >= $tanggal_awal && $today <= $tanggal_akhir) {
        $status = 'Sedang berlangsung';
    } else {
        $status = 'Selesai';
    }

    // Mulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    $update = mysqli_query($kon, "UPDATE tbl_tanggal_libur SET 
        tanggal_awal = '$tanggal_awal',
        tanggal_akhir = '$tanggal_akhir',
        alasan_libur = '$alasan_libur',
        status = '$status',
        tanggal_input = '$tanggal_input'
        WHERE id = '$id'");

    // Ambil info user dari session
    $kode_pengguna = $_SESSION['kode_pengguna'];

    // Ambil data user
    $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user = mysqli_fetch_assoc($resultUser);
    $level = $user['level'];

    // Ambil nama admin
    $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
    $admin = mysqli_fetch_assoc($resultAdmin);
    $nama_admin = $admin['nama'];

    // Tanggal dan aktivitas
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date("Y-m-d H:i:s");
    $aktivitas = "Mengubah tanggal libur menjadi $alasan_libur dari $tanggal_awal sampai $tanggal_akhir";

    // Logging + Commit/Rollback
    if ($update) {
        mysqli_query($kon, "COMMIT");
        $status_aktivitas = "berhasil";
        header("Location:../../index.php?page=pengaturan&edit_tanggal_libur=berhasil");
    } else {
        mysqli_query($kon, "ROLLBACK");
        $status_aktivitas = "gagal";
        header("Location:../../index.php?page=pengaturan&edit_tanggal_libur=gagal");
    }

    // Simpan log aktivitas
    $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
            VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status_aktivitas')";
    mysqli_query($kon, $log);

    exit;
}

// Ambil data berdasarkan ID
$id = $_POST['id'];
$data = mysqli_fetch_array(mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur WHERE id='$id'"));

$tanggal_awal = $data['tanggal_awal'];
$tanggal_akhir = $data['tanggal_akhir'];
$alasan_libur = $data['alasan_libur'];
?>

<!-- Form Edit -->
<form action="apps/pengaturan/edit_tanggal_libur.php" method="post"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Awal :</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                    value="<?= $tanggal_awal ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Akhir :</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                    value="<?= $tanggal_akhir ?>" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Alasan Libur :</label>
        <input type="text" name="alasan_libur" class="form-control" value="<?= $alasan_libur ?>" required>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success" name="simpan_tanggal_libur">
            <i class="fa fa-save"></i> Simpan
        </button>
    </div>
</form>

<!-- Validasi tanggal otomatis -->
<script>
    const tanggalAwal = document.getElementById('tanggal_awal');
    const tanggalAkhir = document.getElementById('tanggal_akhir');

    let isInteracted = false; // status interaksi

    function validateDates() {
        const awal = new Date(tanggalAwal.value);
        const akhir = new Date(tanggalAkhir.value);

        if (!isInteracted) {
            isInteracted = true;
        }

        if (isInteracted) {
            // Validasi jika tanggal akhir lebih kecil dari awal
            if (awal && akhir && akhir < awal) {
                alert("Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal!");
                tanggalAkhir.value = "";
            }

            // Tetapkan batas minimal dan maksimal
            if (tanggalAwal.value) {
                tanggalAkhir.setAttribute('min', tanggalAwal.value);
            }

            if (tanggalAkhir.value) {
                tanggalAwal.setAttribute('max', tanggalAkhir.value);
            }
        }
    }

    // Jalankan validasi saat terjadi perubahan
    tanggalAwal.addEventListener('change', validateDates);
    tanggalAkhir.addEventListener('change', validateDates);
</script>