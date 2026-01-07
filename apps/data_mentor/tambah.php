<?php
session_start();
if (isset($_POST['tambah_mentor'])) {
    include '../../config/database.php';

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        // Ambil input form
        $nip = input($_POST["nip"]);
        $nama = input($_POST["nama"]);
        $email = input($_POST["email"]);
        $unit_kerja = input($_POST["unit_kerja"]);
        $jabatan = input($_POST["jabatan"]);

        // Generate kode mentor otomatis
        $query = mysqli_query($kon, "SELECT max(id_mentor) AS id_terbesar FROM tbl_mentor");
        $ambil = mysqli_fetch_array($query);
        $id_mentor = $ambil['id_terbesar'];
        $id_mentor++;
        $huruf = "S";
        $kode_mentor = $huruf . sprintf("%03s", $id_mentor);

        // Upload foto
        $nama_file_foto = ""; // Foto bisa kosong
        if (!empty($_FILES['foto']['name'])) {
            $nama_file = $_FILES['foto']['name'];
            $tmp_file = $_FILES['foto']['tmp_name'];
            $ukuran_file = $_FILES['foto']['size'];
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];

            // Validasi ekstensi dan ukuran
            if (!in_array($ekstensi, $ekstensi_diizinkan)) {
                header("Location: ../../index.php?page=data_mentor&add=format_tidak_valid");
                exit;
            }

            if ($ukuran_file > 1048576) { // 1MB = 1048576 bytes
                header("Location: ../../index.php?page=data_mentor&add=ukuran_terlalu_besar");
                exit;
            }

            $nama_file_foto = uniqid('mentor_') . '.' . $ekstensi;
            $folder_upload = "../../apps/pengguna/foto_mentor/";
            move_uploaded_file($tmp_file, $folder_upload . $nama_file_foto);
        }

        // Simpan ke tbl_user
        $sql_user = "INSERT INTO tbl_user (kode_pengguna) VALUES ('$kode_mentor')";
        $simpan_pengguna = mysqli_query($kon, $sql_user);

        // Simpan ke tbl_mentor, jika foto kosong maka tetap simpan NULL
        $sql_mentor = "INSERT INTO tbl_mentor (kode_mentor, nama, nip, email, unit_kerja, jabatan, foto)
               VALUES ('$kode_mentor', '$nama', '$nip', '$email', '$unit_kerja', '$jabatan', '$nama_file_foto')";
        $simpan_mentor = mysqli_query($kon, $sql_mentor);

        // Info user login
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Commit jika berhasil, rollback jika gagal
        if ($simpan_pengguna && $simpan_mentor) {
            mysqli_query($kon, "COMMIT");
            $aktivitas = "Tambah data mentor ($nama)";
            mysqli_query($kon, "INSERT INTO tbl_log_aktivitas 
                (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')");
            header("Location: ../../index.php?page=data_mentor&add=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            $aktivitas = "Tambah data mentor ($nama)";
            mysqli_query($kon, "INSERT INTO tbl_log_aktivitas 
                (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')");
            header("Location: ../../index.php?page=data_mentor&add=gagal");
        }
    }
}
?>

<form action="apps/data_mentor/tambah.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukan Nama Lengkap" required
                    autofocus oninvalid="this.setCustomValidity('Harap nama lengkap di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nomor Induk Pegawai (NIP) :</label>
                <input type="text" name="nip" class="form-control" value="" placeholder="Masukan Nomor Induk Pegawai"
                    required autofocus oninvalid="this.setCustomValidity('Harap NIP di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" placeholder="Masukan Email" required autofocus
                    oninvalid="this.setCustomValidity('Harap email di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="unit_kerja">Unit Kerja / Instansi :</label>
                <?php
                include '../../config/database.php';
                $unit_kerja_query = mysqli_query($kon, "SELECT DISTINCT unit_kerja FROM tbl_jabatan ORDER BY unit_kerja ASC");

                // Ambil data mentor
                $mentor_query = mysqli_query($kon, "SELECT unit_kerja, jabatan FROM tbl_mentor");
                $mentor_jabatan_terpakai = [];
                while ($row = mysqli_fetch_assoc($mentor_query)) {
                    $uk = $row['unit_kerja'];
                    $jabatans = array_map('trim', explode(',', $row['jabatan']));
                    foreach ($jabatans as $j) {
                        $mentor_jabatan_terpakai[$uk][] = $j;
                    }
                }
                ?>
                <select name="unit_kerja" class="form-control" id="unit_kerja" required>
                    <option selected disabled>Pilih Unit Kerja</option>
                    <?php while ($row = mysqli_fetch_assoc($unit_kerja_query)):
                        $uk = $row['unit_kerja'];

                        // Ambil semua jabatan untuk unit kerja ini
                        $jabatan_query = mysqli_query($kon, "SELECT nama FROM tbl_jabatan WHERE unit_kerja='$uk'");
                        $jabatan_list = [];
                        while ($j = mysqli_fetch_assoc($jabatan_query)) {
                            $jabatan_array = array_map('trim', explode(',', $j['nama']));
                            $jabatan_list = array_merge($jabatan_list, $jabatan_array);
                        }
                        $jabatan_list = array_unique($jabatan_list);

                        // Ambil yang sudah terpakai
                        $terpakai = isset($mentor_jabatan_terpakai[$uk]) ? array_unique($mentor_jabatan_terpakai[$uk]) : [];

                        // Cek apakah semua jabatan sudah terpakai
                        $belum_terpakai = array_diff($jabatan_list, $terpakai);
                        $is_full = empty($belum_terpakai);
                        $disabled = $is_full ? 'disabled title="Semua jabatan sudah digunakan"' : '';

                        // Tambahkan label jika semua sudah digunakan
                        $label = $is_full ? htmlspecialchars($uk) . " (sudah digunakan)" : htmlspecialchars($uk);
                        ?>
                        <option value="<?= htmlspecialchars($uk) ?>" <?= $disabled ?>><?= $label ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="jabatan">Jabatan :</label>
                <select name="jabatan" class="form-control" id="jabatan" required>
                    <option selected disabled>Pilih Jabatan</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6">
                        <div id="msg"></div>
                        <label>Upload Foto :</label>
                        <input type="file" name="foto" class="file" id="file-upload" style="display: none;"
                            accept="image/jpeg, image/png">
                        <div class="input-group">
                            <input type="text" style="margin-bottom: 10px;" class="form-control" disabled
                                placeholder="Upload Foto" id="file-name">
                            <div class="input-group-append">
                                <button type="button" id="pilih_foto" class="browse btn btn-info">
                                    <i class="fa fa-search"></i> Pilih
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label>Foto</label>
                        <div style="width: 100%;">
                            <img src="source/img/size.png" id="preview" class="img-thumbnail"
                                style="width: 130px; height: 130px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="tambah_mentor" id="Submit" class="btn btn-success"><i class="fa fa-plus"></i>
                Daftar</button>
            <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
        </div>
    </div>
</form>

<style>
    .file {
        visibility: hidden;
        position: absolute;
    }
</style>

<script>
    $(document).ready(function () {
        $('#unit_kerja').change(function () {
            var unitKerja = $(this).val();
            $.ajax({
                type: 'POST',
                url: 'apps/data_mentor/tambah_ambil_jabatan.php',
                data: { unit_kerja: unitKerja },
                success: function (response) {
                    $('#jabatan').html(response);
                }
            });
        });
    });
</script>

<script>
    document.getElementById("pilih_foto").addEventListener("click", function () {
        document.getElementById("file-upload").click();
    });

    document.getElementById("file-upload").addEventListener("change", function () {
        var fileInput = this;
        var fileName = fileInput.files[0].name;
        document.getElementById("file-name").value = fileName;

        // Preview image
        if (fileInput.files && fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview').src = e.target.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });
</script>