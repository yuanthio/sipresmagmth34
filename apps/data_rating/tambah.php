<?php
session_start();
include '../../config/database.php';

function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (isset($_POST['simpan_rating'])) {
    $level = input($_POST['level']);
    $nama = input($_POST['nama']); // ini adalah kode_pengguna
    $rating = input($_POST['rating']);
    $pesan = input($_POST['pesan']);
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date('Y-m-d H:i:s');

    // Ambil nama pengguna berdasarkan level
    $queryNama = "";
    if ($level == 'Admin') {
        $queryNama = "SELECT nama FROM tbl_admin WHERE kode_admin='$nama'";
    } elseif ($level == 'Mahasiswa') {
        $queryNama = "SELECT nama FROM tbl_mahasiswa WHERE kode_mahasiswa='$nama'";
    } elseif ($level == 'Mentor') {
        $queryNama = "SELECT nama FROM tbl_mentor WHERE kode_mentor='$nama'";
    }

    $hasil = mysqli_query($kon, $queryNama);
    $dataPengguna = mysqli_fetch_assoc($hasil);
    $nama_pengguna = $dataPengguna ? $dataPengguna['nama'] : '';

    // Cek apakah rating untuk pengguna ini sudah ada
    $cek = mysqli_query($kon, "SELECT * FROM tbl_rating WHERE kode_pengguna='$nama'");
    if (mysqli_num_rows($cek) > 0) {
        // Catat log aktivitas untuk data duplikat
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level_user = $user['level'];

        $resultNama = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultNama);
        $nama_admin = $admin['nama'];

        $aktivitas = "Gagal menambahkan rating untuk $nama_pengguna karena data sudah ada ($level)";
        $status = "duplikat";

        $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                   VALUES ('$tanggal', '$nama_admin', '$level_user', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $sqlLog);

        header("Location:../../index.php?page=data_rating&add=duplikat");
        exit;
    }

    // Simpan rating
    $query = "INSERT INTO tbl_rating (kode_pengguna, nama, level, rating, pesan, tanggal)
              VALUES ('$nama', '$nama_pengguna', '$level', '$rating', '$pesan', '$tanggal')";
    $simpan = mysqli_query($kon, $query);

    // Log aktivitas
    $kode_pengguna = $_SESSION['kode_pengguna'];
    $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'");
    $user = mysqli_fetch_assoc($resultUser);
    $level_user = $user['level'];

    $resultNama = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
    $admin = mysqli_fetch_assoc($resultNama);
    $nama_admin = $admin['nama'];

    $aktivitas = "Menambahkan rating untuk $nama_pengguna dengan nilai $rating ($level)";
    $status = $simpan ? "berhasil" : "gagal";

    $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
               VALUES ('$tanggal', '$nama_admin', '$level_user', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $sqlLog);

    header("Location:../../index.php?page=data_rating&add=$status");
    exit;
}
?>

<!-- FORM HTML -->
<form action="apps/data_rating/tambah.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="form-group">
        <label for="level">Level :</label>
        <select name="level" class="form-control" id="level" required>
            <option selected disabled>Pilih Level</option>
            <option value="Admin">Admin</option>
            <option value="Mahasiswa">Mahasiswa</option>
            <option value="Mentor">Mentor</option>
        </select>
    </div>
    <div class="form-group">
        <label for="nama">Nama :</label>
        <select name="nama" class="form-control" id="nama" required>
            <option selected disabled>Pilih Nama</option>
        </select>
    </div>
    <div class="form-group">
        <label for="rating">Rating :</label>
        <div id="rating-stars" style="font-size: 24px; color: gold;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="bi bi-star" data-value="<?= $i ?>" style="cursor: pointer;"></i>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="rating" required>
    </div>
    <div class="form-group">
        <label>Pesan :</label>
        <textarea name="pesan" id="pesan" class="form-control" placeholder="Tulis disini..." required
            rows="5"></textarea>
    </div>
    <div class="form-group">
        <br>
        <button type="submit" name="simpan_rating" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
        <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
    </div>
</form>

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

    // Load names based on selected level
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
                    var optionText = name.nama;
                    var disabledAttr = "";

                    if (name.disabled) {
                        optionText += " (Rating sudah ada)";
                        disabledAttr = "disabled";
                    }

                    namaSelect.append('<option value="' + name.kode + '" ' + disabledAttr + '>' + optionText + '</option>');
                });
            }
        });
    });
</script>