<?php
session_start();
include '../../config/database.php';

function input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Proses simpan update
if (isset($_POST['simpan_rating'])) {
    $id_rating = input($_POST['id_rating']);
    $level = input($_POST['level']);
    $kode_pengguna = input($_POST['nama']);
    $rating = input($_POST['rating']);
    $pesan = input($_POST['pesan']);
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date('Y-m-d H:i:s');

    // Ambil nama pengguna
    if ($level == 'Admin') {
        $resultNama = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
    } elseif ($level == 'Mahasiswa') {
        $resultNama = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_pengguna'");
    } elseif ($level == 'Mentor') {
        $resultNama = mysqli_query($kon, "SELECT nama FROM tbl_mentor WHERE kode_mentor='$kode_pengguna'");
    }

    $dataNama = mysqli_fetch_assoc($resultNama);
    $nama_pengguna = $dataNama['nama'];

    // Update data rating
    $update = mysqli_query($kon, "UPDATE tbl_rating SET 
        kode_pengguna='$kode_pengguna',
        nama='$nama_pengguna',
        level='$level',
        rating='$rating',
        pesan='$pesan',
        tanggal='$tanggal'
        WHERE id_rating='$id_rating'");

    // Simpan log aktivitas
    $kode_admin = $_SESSION['kode_pengguna'];
    $level_user = mysqli_fetch_assoc(mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_admin'"))['level'];
    $nama_admin = mysqli_fetch_assoc(mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_admin'"))['nama'];

    $aktivitas = "Mengubah rating untuk $nama_pengguna dengan nilai $rating ($level)";
    $status = $update ? "berhasil" : "gagal";

    mysqli_query($kon, "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                        VALUES ('$tanggal', '$nama_admin', '$level_user', '$kode_admin', '$aktivitas', '$status')");

    header("Location:../../index.php?page=data_rating&edit=$status");
    exit;
}

// Ambil data rating untuk ditampilkan di form
$id_rating = $_POST['id_rating'] ?? '';
$data = mysqli_fetch_assoc(mysqli_query($kon, "SELECT * FROM tbl_rating WHERE id_rating='$id_rating'"));
?>

<!-- Form HTML -->
<form action="apps/data_rating/edit.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <input type="hidden" name="id_rating" value="<?= $data['id_rating'] ?>">
    <input type="hidden" name="nama" value="<?= $data['kode_pengguna'] ?>">

    <!-- Level: tidak bisa diubah, hanya tampilkan -->
    <div class="form-group">
        <label for="level">Level :</label>
        <input type="text" name="level" class="form-control" id="level" value="<?= $data['level'] ?>" readonly>
    </div>

    <!-- Nama: tidak bisa diubah, hanya tampilkan -->
    <div class="form-group">
        <label for="nama_pengguna">Nama :</label>
        <input type="text" class="form-control" id="nama_pengguna" value="<?= $data['nama'] ?>" readonly>
    </div>

    <!-- Rating: bintang yang bisa dipilih -->
    <div class="form-group">
        <label for="rating">Rating :</label>
        <div id="rating-stars" style="font-size: 24px; color: gold;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="bi <?= ($i <= $data['rating']) ? 'bi-star-fill' : 'bi-star' ?>" data-value="<?= $i ?>"
                    style="cursor: pointer;"></i>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="rating" value="<?= $data['rating'] ?>" required>
    </div>

    <!-- Pesan: input yang bisa diubah -->
    <div class="form-group">
        <label>Pesan :</label>
        <textarea name="pesan" id="pesan" class="form-control" rows="5" required><?= $data['pesan'] ?></textarea>
    </div>

    <!-- Tombol simpan dan reset -->
    <div class="form-group mt-3">
        <button type="submit" name="simpan_rating" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
    </div>
</form>

<!-- Script untuk rating bintang & nama dinamis -->
<script>
    const stars = document.querySelectorAll('#rating-stars i');
    const ratingInput = document.getElementById('rating');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = star.getAttribute('data-value');
            ratingInput.value = rating;
            stars.forEach(s => {
                s.classList.remove('bi-star-fill');
                s.classList.add('bi-star');
            });
            for (let i = 0; i < rating; i++) {
                stars[i].classList.remove('bi-star');
                stars[i].classList.add('bi-star-fill');
            }
        });
    });

    $('#level').on('change', function () {
        var level = $(this).val();
        var namaSelect = $('#nama');
        namaSelect.empty();
        namaSelect.append('<option selected disabled>Pilih Nama</option>');
        $.ajax({
            url: 'apps/data_rating/get_names.php',
            method: 'POST',
            data: { level: level },
            success: function (data) {
                var names = JSON.parse(data);
                names.forEach(function (name) {
                    namaSelect.append('<option value="' + name.kode + '">' + name.nama + '</option>');
                });
            }
        });
    });
</script>