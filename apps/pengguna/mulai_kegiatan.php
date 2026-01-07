<?php
// Mulai sesi
session_start();
// Mengatur zona waktu ke "Asia/Jakarta"
date_default_timezone_set("Asia/Jakarta");

// Cek apakah tombol "simpan_kegiatan" diklik
if (isset($_POST['simpan_kegiatan'])) {

    // Include file database
    include '../../config/database.php';

    // Fungsi untuk membersihkan dan memvalidasi data
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Mendapatkan ID mahasiswa dari sesi
    $id_mahasiswa = $_SESSION["id_mahasiswa"];

    // Mendapatkan data dari formulir
    $kegiatan = input($_POST["kegiatan"]);
    $waktu_awal = input($_POST["waktu_awal"]);
    $waktu_akhir = input($_POST["waktu_akhir"]);
    $hari = date("l");
    $tanggal = date("Y-m-d");

    // Proses upload foto
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    $path = "../../apps/data_kegiatan/foto_kegiatan/" . $foto;

    // Validasi ukuran file (1MB)
    $max_file_size = 1 * 1024 * 1024; // 1MB dalam bytes
    if ($_FILES['foto']['size'] > $max_file_size) {
        logAktivitas($kon, $id_mahasiswa, "gagal", "Ukuran file foto lebih dari 1MB");
        header("Location:../../index.php?page=kegiatan&tambah=gagal_ukuran");
        exit();
    }

    // Validasi jenis file (harus JPG, JPEG, atau PNG)
    $allowed_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
    $detected_type = exif_imagetype($tmp);
    if (!in_array($detected_type, $allowed_types)) {
        logAktivitas($kon, $id_mahasiswa, "gagal", "Format file foto tidak valid");
        header("Location:../../index.php?page=kegiatan&tambah=gagal_format");
        exit();
    }

    // Proses upload file
    if (move_uploaded_file($tmp, $path)) {
        // Jika foto berhasil diupload, simpan data ke database
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            // Cek apakah ada data kegiatan di hari yang sama untuk mahasiswa tersebut
            $sql_check = "SELECT * FROM tbl_kegiatan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal' LIMIT 1";
            $result_check = mysqli_query($kon, $sql_check);

            if (mysqli_num_rows($result_check) > 0) {
                $row_check = mysqli_fetch_array($result_check);
                $existing_kegiatan = $row_check['kegiatan'];
                $existing_waktu_awal = $row_check['waktu_awal'];
                $existing_waktu_akhir = $row_check['waktu_akhir'];
                $existing_foto = $row_check['foto'];

                // Menggabungkan waktu baru dengan waktu yang ada menggunakan koma
                $updated_waktu_awal = $existing_waktu_awal . ', ' . $waktu_awal;
                $updated_waktu_akhir = $existing_waktu_akhir . ', ' . $waktu_akhir;
                $updated_kegiatan = $existing_kegiatan . ', ' . $kegiatan;
                $updated_foto = $existing_foto . ', ' . $foto;

                // Query untuk memperbarui data yang ada di hari yang sama
                $sql_update = "UPDATE tbl_kegiatan SET kegiatan = '$updated_kegiatan', waktu_awal = '$updated_waktu_awal', waktu_akhir = '$updated_waktu_akhir', foto = '$updated_foto' WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal'";
                $result_update = mysqli_query($kon, $sql_update);

                // Cek apakah update berhasil dan catat aktivitas
                if ($result_update) {
                    logAktivitas($kon, $id_mahasiswa, "berhasil");
                    header("Location:../../index.php?page=kegiatan&tambah=berhasil");
                } else {
                    logAktivitas($kon, $id_mahasiswa, "gagal", "Gagal memperbarui kegiatan");
                    header("Location:../../index.php?page=kegiatan&tambah=gagal");
                }
            } else {
                // Query untuk memasukkan data baru jika belum ada di hari yang sama
                $sql_insert = "INSERT INTO tbl_kegiatan (id_mahasiswa, kegiatan, waktu_awal, waktu_akhir, tanggal, hari, foto) 
                    VALUES ('$id_mahasiswa', '$kegiatan', '$waktu_awal', '$waktu_akhir', '$tanggal', '$hari', '$foto')";
                $result_insert = mysqli_query($kon, $sql_insert);

                // Cek apakah insert berhasil dan catat aktivitas
                if ($result_insert) {
                    logAktivitas($kon, $id_mahasiswa, "berhasil");
                    header("Location:../../index.php?page=kegiatan&tambah=berhasil");
                } else {
                    logAktivitas($kon, $id_mahasiswa, "gagal", "Gagal menambahkan kegiatan baru");
                    header("Location:../../index.php?page=kegiatan&tambah=gagal");
                }
            }
        }
    } else {
        logAktivitas($kon, $id_mahasiswa, "gagal", "Gagal mengunggah foto");
        header("Location:../../index.php?page=kegiatan&tambah=gagal");
    }
}

// Fungsi untuk mencatat aktivitas ke tabel log
function logAktivitas($kon, $id_mahasiswa, $status, $detail_status = "")
{
    // Mendapatkan nama mahasiswa
    $query_nama = "SELECT nama, kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
    $result_nama = mysqli_query($kon, $query_nama);
    $data_nama = mysqli_fetch_assoc($result_nama);
    $nama = $data_nama['nama'];
    $kode_pengguna = $data_nama['kode_mahasiswa'];

    // Menyimpan log aktivitas
    $aktivitas = ($status == "berhasil") ? "Input kegiatan" : "Input kegiatan ($detail_status)";
    $tanggal_log = date("Y-m-d H:i:s"); // Format tanggal dan waktu
    $level = "Mahasiswa"; // Level pengguna

    $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                  VALUES ('$tanggal_log', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $log_query);
}
?>

<form action="apps/pengguna/mulai_kegiatan.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Awal Kegiatan :</label>
                <input type="time" name="waktu_awal" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap waktu awal kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Akhir Kegiatan :</label>
                <input type="time" name="waktu_akhir" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap waktu akhir kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Kegiatan :</label>
                <textarea name="kegiatan" class="form-control" placeholder="Masukkan Kegiatan Anda?" required autofocus
                    oninvalid="this.setCustomValidity('Harap kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')" rows="5"></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-7">
                        <div id="msg"></div>
                        <label>Upload Foto :</label>
                        <input type="file" name="foto" class="file" required>
                        <div class="input-group">
                            <input type="text" class="form-control" disabled placeholder="Upload Foto" id="file">
                            <div class="input-group-append">
                                <button type="button" id="pilih_foto" class="browse btn btn-info"
                                    style="margin: 10px 0;"><i class="fa fa-search"></i> Pilih</button>
                            </div>
                        </div>
                    </div>
                    <label></label>
                    <div class="col-sm-5">
                        <div style="width: 100%; text-align: center;">
                            <label>Foto Kegiatan :</label>
                            <img src="source/img/size.png" id="preview" class="img-thumbnail" style="width: 130px; height: 130px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="simpan_kegiatan" id="simpan_kegiatan"
                    class="simpan_kegiatan btn btn-success"><i class="fa fa-plus"></i> Simpan</button>
                <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Hapus</button>
            </div>
        </div>
    </div>
</form>

<script>
    $('#simpan_kegiatan').on('click', function () {
        konfirmasi = confirm("Yakin ingin menyimpan kegiatan ini?")
        if (konfirmasi) {
            return true;
        } else {
            return false;
        }
    });
</script>

<script>
    $(document).on("click", "#pilih_foto", function () {
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