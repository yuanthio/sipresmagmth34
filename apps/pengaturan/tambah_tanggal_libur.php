<?php
session_start();
include '../../config/database.php';
date_default_timezone_set("Asia/Jakarta");

$tanggal_awal = '';
$tanggal_akhir = '';

if (isset($_POST['simpan_tanggal_libur'])) {
    $tanggal_awal = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $alasan_libur = $_POST['alasan_libur'];
    $tanggal_input = date("Y-m-d");

    // Cek apakah tanggal bentrok dengan tanggal libur yang sudah ada
    $cek_tanggal = mysqli_query($kon, "SELECT * FROM tbl_tanggal_libur 
        WHERE 
        ('$tanggal_awal' BETWEEN tanggal_awal AND tanggal_akhir)
        OR 
        ('$tanggal_akhir' BETWEEN tanggal_awal AND tanggal_akhir)
        OR 
        (tanggal_awal BETWEEN '$tanggal_awal' AND '$tanggal_akhir')
        OR 
        (tanggal_akhir BETWEEN '$tanggal_awal' AND '$tanggal_akhir')");

    if (mysqli_num_rows($cek_tanggal) > 0) {
        header("Location:../../index.php?page=pengaturan&tambah_tanggal_libur=bentrok");
        exit();
    }

    // Tentukan status berdasarkan tanggal sekarang
    $sekarang = date("Y-m-d");
    if ($sekarang < $tanggal_awal) {
        $status = "Belum dimulai";
    } elseif ($sekarang >= $tanggal_awal && $sekarang <= $tanggal_akhir) {
        $status = "Sedang berlangsung";
    } else {
        $status = "Selesai";
    }

    // Mulai transaksi
    mysqli_query($kon, "START TRANSACTION");

    // Simpan tanggal libur
    $query = "INSERT INTO tbl_tanggal_libur (tanggal_awal, tanggal_akhir, alasan_libur, status, tanggal_input)
              VALUES ('$tanggal_awal', '$tanggal_akhir', '$alasan_libur', '$status', '$tanggal_input')";
    $hasil = mysqli_query($kon, $query);

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
    $aktivitas = "Menambahkan tanggal libur $alasan_libur dari $tanggal_awal sampai $tanggal_akhir";

    // Logging + Commit/Rollback
    if ($hasil) {
        mysqli_query($kon, "COMMIT");
        $status_aktivitas = "berhasil";
    } else {
        mysqli_query($kon, "ROLLBACK");
        $status_aktivitas = "gagal";
    }

    // Simpan log aktivitas
    $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
            VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status_aktivitas')";
    mysqli_query($kon, $log);

    // Redirect sesuai hasil
    if ($status_aktivitas == 'berhasil') {
        header("Location:../../index.php?page=pengaturan&tambah_tanggal_libur=berhasil");
    } else {
        header("Location:../../index.php?page=pengaturan&tambah_tanggal_libur=gagal");
    }
}
?>

<!-- FORM -->
<form action="apps/pengaturan/tambah_tanggal_libur.php" method="post" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <input type="hidden" class="form-control" />

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
        <input type="text" name="alasan_libur" class="form-control" placeholder="Contoh: Hari Raya Idul Fitri" required>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success" name="simpan_tanggal_libur">
            <i class="fa fa-save"></i> Simpan
        </button>
    </div>
</form>

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
                tanggalAkhir.value = ""; // Kosongkan tanggal akhir
            }
        }

        // Set tanggal minimum/maximum agar user tidak bisa pilih sembarangan
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

    // Jalankan saat tanggal berubah
    tanggalAwal.addEventListener('change', validateDates);
    tanggalAkhir.addEventListener('change', validateDates);

    // Jalankan saat halaman dimuat
    validateDates();
</script>
