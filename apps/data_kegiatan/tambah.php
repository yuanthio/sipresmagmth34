<?php
session_start();

if (isset($_POST['simpan_kegiatan'])) {
    include '../../config/database.php';

    // Set timezone ke WIB
    date_default_timezone_set("Asia/Jakarta");

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $id_mahasiswa = $_POST["mahasiswa"];
    $tanggal = $_POST["tanggal"];
    $waktu_awal = $_POST["waktu_awal"];
    $waktu_akhir = $_POST["waktu_akhir"];
    $kegiatan = $_POST["kegiatan"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (empty($tanggal)) {
            header("Location: ../../index.php?page=data_kegiatan&tambah=gagal&pesan=Tanggal harus diisi");
            exit;
        }

        $nama_file = $_FILES['foto']['name'];
        $nama_sementara = $_FILES['foto']['tmp_name'];
        $tujuan = 'foto_kegiatan/' . $nama_file;

        $file_size = $_FILES['foto']['size'];
        $max_size = 1024 * 1024;

        // Ambil informasi pengguna yang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuai dengan session yang sedang login
        $sql_user = "SELECT * FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
        $result_user = mysqli_query($kon, $sql_user);
        $user = mysqli_fetch_assoc($result_user);

        // Ambil informasi admin
        $sql_admin = "SELECT * FROM tbl_admin WHERE kode_admin = '{$user['kode_pengguna']}'";
        $result_admin = mysqli_query($kon, $sql_admin);
        $admin = mysqli_fetch_assoc($result_admin);
        $nama_admin = $admin['nama'];

        // Ambil nama mahasiswa
        $sql_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
        $result_mahasiswa = mysqli_query($kon, $sql_mahasiswa);
        $mahasiswa_data = mysqli_fetch_assoc($result_mahasiswa);
        $nama_mahasiswa = $mahasiswa_data['nama'];

        // Tanggal sekarang
        $tanggal_sekarang = date("Y-m-d H:i:s");

        if ($file_size > $max_size) {
            // Log aktivitas gagal karena ukuran file terlalu besar
            $aktivitas = "Tambah data kegiatan mahasiswa ($nama_mahasiswa) Ukuran file terlalu besar";
            $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sql_log);

            header("Location: ../../index.php?page=data_kegiatan&tambah=gagal&pesan=File tidak boleh melebihi 1MB.");
            exit;
        }

        $allowed_extensions = array('jpg', 'jpeg', 'png');
        $file_extension = pathinfo($nama_file, PATHINFO_EXTENSION);

        if (!in_array($file_extension, $allowed_extensions)) {
            // Log aktivitas gagal karena format file tidak sesuai
            $aktivitas = "Tambah data kegiatan mahasiswa ($nama_mahasiswa) Format file tidak sesuai";
            $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sql_log);

            header("Location: ../../index.php?page=data_kegiatan&tambah=gagal&pesan=Format file harus JPG, JPEG atau PNG");
            exit;
        }

        move_uploaded_file($nama_sementara, $tujuan);

        // Ambil nama hari dari tanggal yang diinput
        $hari = date('l', strtotime($tanggal));

        // Cek apakah sudah ada kegiatan pada tanggal yang sama untuk mahasiswa tersebut
        $sql_check = "SELECT * FROM tbl_kegiatan WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'";
        $result_check = mysqli_query($kon, $sql_check);
        if (mysqli_num_rows($result_check) > 0) {
            // Jika sudah ada, update kegiatan dan waktu
            $row = mysqli_fetch_assoc($result_check);
            $kegiatan_existing = $row['kegiatan'];
            $waktu_awal_existing = $row['waktu_awal'];
            $waktu_akhir_existing = $row['waktu_akhir'];
            $foto_existing = $row['foto'];

            // Gabungkan kegiatan, waktu, dan foto
            $kegiatan = $kegiatan_existing . ', ' . $kegiatan;
            $waktu_awal = $waktu_awal_existing . ', ' . $waktu_awal;
            $waktu_akhir = $waktu_akhir_existing . ', ' . $waktu_akhir;
            $foto = $foto_existing . ', ' . $nama_file;

            $sql_update = "UPDATE tbl_kegiatan SET kegiatan='$kegiatan', waktu_awal='$waktu_awal', waktu_akhir='$waktu_akhir', foto='$foto' WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'";
            $simpan_kegiatan = mysqli_query($kon, $sql_update);
        } else {
            // Jika belum ada, masukkan data baru
            $sql = "INSERT INTO tbl_kegiatan (id_mahasiswa, kegiatan, waktu_awal, waktu_akhir, tanggal, hari, foto) 
                VALUES ('$id_mahasiswa', '$kegiatan', '$waktu_awal', '$waktu_akhir', '$tanggal', '$hari', '$nama_file')";
            $simpan_kegiatan = mysqli_query($kon, $sql);
        }

        if ($simpan_kegiatan) {
            mysqli_query($kon, "COMMIT");

            // Log aktivitas berhasil
            $aktivitas = "Tambah data kegiatan mahasiswa ($nama_mahasiswa)";
            $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sql_log);

            header("Location: ../../index.php?page=data_kegiatan&tambah=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal karena kesalahan saat menyimpan data
            $aktivitas = "Tambah data kegiatan mahasiswa ($nama_mahasiswa) Kesalahan menyimpan data";
            $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal_sekarang', '$nama_admin', 'Admin', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sql_log);

            header("Location: ../../index.php?page=data_kegiatan&tambah=gagal");
        }
    }
}
?>

<form action="apps/data_kegiatan/tambah.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Karyawan Magang :</label>
                <select class="form-control" id="mahasiswa" name="mahasiswa" required autofocus
                    oninvalid="this.setCustomValidity('Harap di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                    <option selected disabled>Pilih Karyawan Magang</option>
                    <?php
                    include '../../config/database.php';

                    // Memeriksa apakah yang login adalah mentor
                    if ($_SESSION["level"] == 'Mentor') {
                        $mentor_name = $_SESSION["nama_mentor"];
                        $query_mentor = "SELECT kode_mentor FROM tbl_mentor WHERE nama = '$mentor_name'";
                        $result_mentor = mysqli_query($kon, $query_mentor);
                        $row_mentor = mysqli_fetch_assoc($result_mentor);
                        $kode_mentor = $row_mentor['kode_mentor'];

                        // Memilih hanya mahasiswa yang terkait dengan mentor tersebut
                        $query_mahasiswa = "SELECT id_mahasiswa, nama FROM tbl_mahasiswa WHERE kode_mentor = '$kode_mentor'";
                    } else {
                        // Jika yang login bukan mentor, tampilkan semua mahasiswa
                        $query_mahasiswa = "SELECT id_mahasiswa, nama FROM tbl_mahasiswa";
                    }

                    $result_mahasiswa = mysqli_query($kon, $query_mahasiswa);

                    while ($data = mysqli_fetch_assoc($result_mahasiswa)) {
                        echo "<option value='" . $data['id_mahasiswa'] . "'>" . $data['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Kegiatan :</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap tanggal kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Awal Kegiatan :</label>
                <input type="time" name="waktu_awal" id="waktu_awal" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap waktu awal kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Akhir Kegiatan:</label>
                <input type="time" name="waktu_akhir" id="waktu_akhir" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap waktu akhir kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Kegiatan :</label>
                <textarea name="kegiatan" id="kegiatan" class="form-control" placeholder="Masukkan Kegiatan Harian"
                    required autofocus oninvalid="this.setCustomValidity('Harap kegiatan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')" rows="5"></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-7">
                        <div id="msg"></div>
                        <label>Upload Foto :</label>
                        <input type="file" name="foto" class="file" required accept="image/jpeg, image/png">
                        <div class="input-group">
                            <input type="text" class="form-control" disabled placeholder="Upload Foto" id="file">
                            <div class="input-group-append">
                                <button type="button" id="pilih_foto" class="browse btn btn-info"
                                    style="margin: 10px 0;"><i class="fa fa-search"></i> Pilih</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <label>Foto Kegiatan</label>
                        <div style="width: 100%; text-align: center;">
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
                <button type="submit" name="simpan_kegiatan" id="simpan_kegiatan" class="btn btn-success"><i
                        class="fa fa-plus"></i> Simpan</button>
                <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
            </div>
        </div>
    </div>
</form>

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