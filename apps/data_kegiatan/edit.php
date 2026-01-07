<?php
session_start();

if (isset($_POST['edit_kegiatan'])) {
    include '../../config/database.php';
    date_default_timezone_set('Asia/Jakarta'); // Set timezone ke Indonesia WIB

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $id_mahasiswa = $_POST["id_mahasiswa"];
    $id_kegiatan = $_POST["id_kegiatan"];
    $tanggal = $_POST["tanggal"];
    $waktu_awal = $_POST["waktu_awal"];
    $waktu_akhir = $_POST["waktu_akhir"];
    $kegiatan = $_POST["kegiatan"];
    $foto_saat_ini = $_POST["foto_saat_ini"];
    $foto_baru = $_FILES["foto_baru"]["name"];
    $foto_size = $_FILES["foto_baru"]["size"];
    $target_dir = "../../apps/data_kegiatan/foto_kegiatan/";

    // Ambil level dan kode_pengguna dari sesi login
    $kode_pengguna = $_SESSION['kode_pengguna'];
    $level_pengguna = $_SESSION['level'];

    // Ambil nama admin yang sedang login dari tabel tbl_admin
    $query_admin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
    $data_admin = mysqli_fetch_assoc($query_admin);
    $nama_admin = $data_admin['nama'];

    // Ambil nama mahasiswa
    $query_mahasiswa = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa'");
    $data_mahasiswa = mysqli_fetch_assoc($query_mahasiswa);
    $nama_mahasiswa = $data_mahasiswa['nama'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if ($foto_baru) {
            if ($foto_size > 1048576) {
                // Log aktivitas gagal untuk ukuran file terlalu besar
                $aktivitas = "Edit data kegiatan mahasiswa ($nama_mahasiswa) Ukuran file terlalu besar";
                $log_aktivitas = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                                  VALUES (NOW(), '$nama_admin', '$level_pengguna', '$kode_pengguna', '$aktivitas', 'gagal')";
                mysqli_query($kon, $log_aktivitas);
                header("Location:../../index.php?page=data_kegiatan&edit=size_error");
                exit();
            }

            $allowed_formats = array('jpg', 'jpeg', 'png');
            $file_extension = strtolower(pathinfo($_FILES['foto_baru']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_formats)) {
                // Log aktivitas gagal untuk format file tidak valid
                $aktivitas = "Edit data kegiatan mahasiswa ($nama_mahasiswa) Format file tidak sesuai";
                $log_aktivitas = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                                  VALUES (NOW(), '$nama_admin', '$level_pengguna', '$kode_pengguna', '$aktivitas', 'gagal')";
                mysqli_query($kon, $log_aktivitas);
                header("Location:../../index.php?page=data_kegiatan&edit=format_error");
                exit();
            }

            $target_file = $target_dir . basename($foto_baru);
            if (move_uploaded_file($_FILES["foto_baru"]["tmp_name"], $target_file)) {
                if ($foto_saat_ini && file_exists($target_dir . $foto_saat_ini) && $foto_saat_ini !== 'gambar_default/No_gambar.jpg') {
                    unlink($target_dir . $foto_saat_ini);
                }
                $foto = $foto_baru;
            } else {
                $foto = $foto_saat_ini;
            }
        } else {
            $foto = $foto_saat_ini;
        }

        // Buat query untuk update data kegiatan
        $sql = "UPDATE tbl_kegiatan SET
                kegiatan = '$kegiatan',
                waktu_awal = '" . implode(',', $waktu_awal) . "',
                waktu_akhir = '" . implode(',', $waktu_akhir) . "',
                tanggal = '$tanggal',
                foto = '$foto'
                WHERE id_kegiatan = '$id_kegiatan'";

        $update_hari_query = "UPDATE tbl_kegiatan SET hari = DAYNAME('$tanggal') WHERE id_kegiatan = '$id_kegiatan'";
        mysqli_query($kon, $update_hari_query);

        $edit_kegiatan = mysqli_query($kon, $sql);

        if ($edit_kegiatan) {
            mysqli_query($kon, "COMMIT");
            // Log aktivitas berhasil
            $aktivitas = "Edit data kegiatan mahasiswa ($nama_mahasiswa)";
            $log_aktivitas = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                              VALUES (NOW(), '$nama_admin', '$level_pengguna', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $log_aktivitas);
            header("Location:../../index.php?page=data_kegiatan&edit=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            // Log aktivitas gagal
            $aktivitas = "Edit data kegiatan mahasiswa ($nama_mahasiswa) Kesalahan sistem saat update data";
            $log_aktivitas = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                              VALUES (NOW(), '$nama_admin', '$level_pengguna', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $log_aktivitas);
            header("Location:../../index.php?page=data_kegiatan&edit=gagal");
        }
    }
}
?>

<?php
$id_kegiatan = $_POST['id_kegiatan'];
include '../../config/database.php';

// Query untuk mendapatkan data kegiatan berdasarkan id_kegiatan
$query = "SELECT id_kegiatan, kegiatan, waktu_awal, waktu_akhir, tanggal, foto 
          FROM tbl_kegiatan WHERE id_kegiatan = '$id_kegiatan' LIMIT 1";
$result = $kon->query($query);
$row = $result->fetch_assoc();
$waktu_awal = $row['waktu_awal'];
$waktu_akhir = $row['waktu_akhir'];
$tanggal = $row['tanggal'];
$kegiatan = $row['kegiatan'];
$foto = $row['foto'];

// Memisahkan waktu_awal dan waktu_akhir menjadi array
$waktu_awal_array = explode(',', $waktu_awal);
$waktu_akhir_array = explode(',', $waktu_akhir);

// Memisahkan nama-nama file yang terdapat dalam kolom foto
$foto_array = explode(",", $foto);
$foto_pertama = !empty($foto_array[0]) ? $foto_array[0] : 'gambar_default/No_gambar.jpg'; // Menampilkan foto default jika tidak ada foto
?>

<!-- Form HTML -->
<form action="apps/data_kegiatan/edit.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <input type="hidden" name="id_mahasiswa" value="<?php echo $_POST['id_mahasiswa']; ?>">
        <input type="hidden" name="id_kegiatan" value="<?php echo $_POST['id_kegiatan']; ?>">

        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Kegiatan :</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?php echo $tanggal; ?>"
                    required autofocus
                    oninvalid="this.setCustomValidity('Harap tanggal kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
            <div class="form-group">
                <label>Kegiatan :</label>
                <textarea name="kegiatan" id="kegiatan" class="form-control" placeholder="Masukkan Kegiatan Harian"
                    autofocus oninvalid="this.setCustomValidity('Harap kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')" rows="5"><?php echo $kegiatan; ?></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <!-- Loop untuk membuat form waktu awal dan waktu akhir berdasarkan jumlah waktu -->
                <?php
                for ($i = 0; $i < count($waktu_awal_array); $i++) {
                    $waktu_awal = trim($waktu_awal_array[$i]);
                    $waktu_akhir = trim($waktu_akhir_array[$i]);

                    // Menentukan apakah label memerlukan angka berdasarkan jumlah waktu
                    $label_suffix = count($waktu_awal_array) > 1 ? ' ' . ($i + 1) : '';
                    ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Waktu Awal Kegiatan<?php echo $label_suffix; ?>:</label>
                            <input type="time" name="waktu_awal[]" id="waktu_awal_<?php echo $i; ?>" class="form-control"
                                value="<?php echo $waktu_awal; ?>" required autofocus
                                oninvalid="this.setCustomValidity('Harap waktu awal kegiatan di isi terlebih dahulu')"
                                oninput="this.setCustomValidity('')">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Waktu Akhir Kegiatan<?php echo $label_suffix; ?>:</label>
                            <input type="time" name="waktu_akhir[]" id="waktu_akhir_<?php echo $i; ?>" class="form-control"
                                value="<?php echo $waktu_akhir; ?>" required autofocus
                                oninvalid="this.setCustomValidity('Harap waktu akhir kegiatan di isi terlebih dahulu')"
                                oninput="this.setCustomValidity('')">
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Bagian input untuk foto -->
        <div class="col-sm-6">
            <div class="row">
                <div class="col-sm-7">
                    <div id="msg"></div>
                    <label>Upload Foto Baru:</label>
                    <input type="file" name="foto_baru" class="file">
                    <div class="input-group my-3">
                        <input type="text" class="form-control" disabled placeholder="Upload File" id="file">
                        <div class="input-group-append">
                            <button type="button" id="pilih_foto" class="browse btn btn-info"
                                style="margin-top: 10px;"><i class="fa fa-search"></i> Pilih Foto</button>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5">
                    <label>Foto Kegiatan:</label><br>
                    <div style="width: 100%; text-align: center;">
                        <img class="img-thumbnail" src="apps/data_kegiatan/foto_kegiatan/<?php echo $foto_pertama; ?>" id="preview"
                            width="90%" class="rounded" alt="Foto Kegiatan" style="width: 130px; height: 130px; object-fit: cover;">
                        <input type="hidden" name="foto_saat_ini" value="<?php echo $foto_pertama; ?>"
                            class="form-control" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tombol submit -->
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_kegiatan" id="edit_kegiatan" class="btn btn-primary"><i
                        class="fa fa-edit"></i> Edit</button>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).on("click", ".browse", function () {
        var file = $(this).parents().find(".file");
        file.trigger("click");
    });
    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);

        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("preview").src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>