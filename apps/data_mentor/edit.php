<?php
session_start();
if (isset($_POST['edit_mentor'])) {

    //Include file koneksi, untuk koneksikan ke database
    include '../../config/database.php';

    //Fungsi untuk mencegah inputan karakter yang tidak sesuai
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    //Cek apakah ada kiriman form dari method post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //Memulai transaksi
        mysqli_query($kon, "START TRANSACTION");

        // Menyimpan input dari form edit mentor
        $id_mentor = input($_POST["id_mentor"]);
        $nama = input($_POST["nama"]);
        $nip = input($_POST["nip"]);
        $email = input($_POST["email"]);
        $unit_kerja = input($_POST["unit_kerja"]);
        $jabatan = input($_POST["jabatan"]);

        // Ambil data mentor lama (untuk dapatkan foto lama)
        $query_old = mysqli_query($kon, "SELECT foto FROM tbl_mentor WHERE id_mentor='$id_mentor'");
        $mentor_old = mysqli_fetch_assoc($query_old);
        $foto_lama = $mentor_old['foto'];

        // Cek apakah ada file foto diupload
        $hapus_foto = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] == '1';

        if ($hapus_foto) {
            if (!empty($foto_lama) && file_exists('../../apps/pengguna/foto_mentor/' . $foto_lama)) {
                unlink('../../apps/pengguna/foto_mentor/' . $foto_lama);
            }
            $foto_baru = ''; // Kosongkan foto di database
        } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ekstensi = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $ukuran = $_FILES['foto']['size'];
            $tipe_valid = ['jpg', 'jpeg', 'png'];

            if ($ukuran > 1048576) {
                header("Location: ../../index.php?page=data_mentor&edit=ukuran_terlalu_besar");
                exit;
            } elseif (!in_array($ekstensi, $tipe_valid)) {
                header("Location: ../../index.php?page=data_mentor&edit=ekstensi_tidak_valid");
                exit;
            } else {
                $nama_file_foto = uniqid('mentor_') . '.' . $ekstensi;
                $tujuan = '../../apps/pengguna/foto_mentor/' . $nama_file_foto;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
                    if (!empty($foto_lama) && file_exists('../../apps/pengguna/foto_mentor/' . $foto_lama)) {
                        unlink('../../apps/pengguna/foto_mentor/' . $foto_lama);
                    }
                    $foto_baru = $nama_file_foto;
                }
            }
        } else {
            $foto_baru = $foto_lama;
        }

        //Query untuk update tbl_mentor
        $sql = "UPDATE tbl_mentor SET 
        nama='$nama', 
        nip='$nip', 
        email='$email',
        unit_kerja='$unit_kerja',
        jabatan='$jabatan',
        foto='$foto_baru'
        WHERE id_mentor=$id_mentor";

        //Mengeksekusi query 
        $edit_mentor = mysqli_query($kon, $sql);

        // Jika nama mentor diubah, perbarui juga nama mentor di tbl_mahasiswa
        $update_mahasiswa = "UPDATE tbl_mahasiswa 
        SET mentor = '$nama' 
        WHERE kode_mentor = (
        SELECT kode_mentor FROM tbl_mentor WHERE id_mentor = $id_mentor
        )";
        mysqli_query($kon, $update_mahasiswa);


        // Ambil informasi admin yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Sesuaikan dengan nama variabel session Anda
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        // Ambil nama admin
        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        // Dapatkan tanggal sekarang dalam format yang diinginkan
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        // Log aktivitas (Edit data mentor)
        $aktivitas = "Edit data mentor ($nama)";

        if ($edit_mentor) {
            // Commit jika berhasil
            mysqli_query($kon, "COMMIT");

            // Simpan aktivitas ke tbl_log_aktivitas
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')";
            mysqli_query($kon, $sqlLog);

            header("Location: ../../index.php?page=data_mentor&edit=berhasil");
        } else {
            // Rollback jika terjadi kesalahan
            mysqli_query($kon, "ROLLBACK");

            // Log aktivitas gagal
            $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                        VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')";
            mysqli_query($kon, $sqlLog);

            header("Location: ../../index.php?page=data_mentor&edit=gagal");
        }
    }
}
?>

<?php
include '../../config/database.php';
$id_mentor = $_POST["id_mentor"];
$sql = "select * from tbl_mentor where id_mentor=$id_mentor limit 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
?>

<form action="apps/data_mentor/edit.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <input type="hidden" name="id_mentor" class="form-control" value="<?php echo $data['id_mentor']; ?>">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" value="<?php echo $data['nama']; ?>"
                    placeholder="Masukan Nama Lengkap" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nomor Induk Pegawai (NIP) :</label>
                <input type="text" name="nip" class="form-control" value="<?php echo $data['nip']; ?>"
                    placeholder="Masukan Nomor Induk Pegawai" required>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" value="<?php echo $data['email']; ?>"
                    placeholder="Masukan Email" required>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="unit_kerja">Unit Kerja / Instansi :</label>
                <?php
                include '../../config/database.php';
                $unit_kerja_query = mysqli_query($kon, "SELECT DISTINCT unit_kerja FROM tbl_jabatan ORDER BY unit_kerja ASC");
                ?>
                <select class="form-control" id="unit_kerja_display" disabled>
                    <option selected disabled>Pilih Unit Kerja</option>
                    <?php while ($row = mysqli_fetch_assoc($unit_kerja_query)): ?>
                        <option value="<?= htmlspecialchars($row['unit_kerja']) ?>"
                            <?= ($data['unit_kerja'] == $row['unit_kerja']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($row['unit_kerja']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <!-- Hidden input to send real value -->
                <input type="hidden" name="unit_kerja" value="<?= htmlspecialchars($data['unit_kerja']) ?>">
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label for="jabatan">Jabatan :</label>
                <select class="form-control" id="jabatan_display" disabled>
                    <option selected disabled><?= htmlspecialchars($data['jabatan']) ?></option>
                </select>
                <!-- Hidden input to send real value -->
                <input type="hidden" name="jabatan" value="<?= htmlspecialchars($data['jabatan']) ?>">
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
                                <button type="button" id="hapus_foto" class="btn btn-danger ml-2"
                                    <?= empty($data['foto']) ? 'disabled' : '' ?>>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="hapus_foto" id="hapus_foto_input" value="0">
                    </div>
                    <div class="col-sm-6">
                        <label>Foto</label>
                        <div style="width: 100%;">
                            <img src="apps/pengguna/foto_mentor/<?php echo htmlspecialchars($data['foto'] ?: 'foto_default.png'); ?>"
                                id="preview" class="img-thumbnail"
                                style="width: 130px; height: 130px; object-fit: cover;">
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
                <button type="submit" name="edit_mentor" id="Submit" class="btn btn-warning"><i class="fa fa-edit"></i>
                    Update</button>
            </div>
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
        function loadJabatan(unitKerja, jabatanLama = '') {
            $.ajax({
                type: 'POST',
                url: 'apps/data_mentor/edit_ambil_jabatan.php',
                data: {
                    unit_kerja: unitKerja,
                    jabatan_lama: jabatanLama
                },
                success: function (response) {
                    $('#jabatan').html(response); // Update jabatan dropdown
                }
            });
        }

        // Panggil saat halaman dimuat jika unit_kerja sudah ada
        const unitKerja = $('#unit_kerja').val();
        const jabatanLama = "<?= $data['jabatan']; ?>";
        if (unitKerja) {
            loadJabatan(unitKerja, jabatanLama);
        }

        // Panggil saat user mengubah unit kerja
        $('#unit_kerja').change(function () {
            var selectedUnit = $(this).val();
            loadJabatan(selectedUnit);
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

<script>
    document.getElementById("pilih_foto").addEventListener("click", function () {
        document.getElementById("file-upload").click();
    });

    document.getElementById("file-upload").addEventListener("change", function () {
        var fileName = this.files[0].name;
        document.getElementById("file-name").value = fileName;

        // Preview
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("preview").src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);

        // Reset hapus foto
        document.getElementById("hapus_foto_input").value = "0";
    });

    document.getElementById("hapus_foto").addEventListener("click", function () {
        document.getElementById("preview").src = "apps/pengguna/foto_mentor/foto_default.png";
        document.getElementById("file-name").value = "";
        document.getElementById("file-upload").value = "";
        document.getElementById("hapus_foto_input").value = "1";
        this.disabled = true;
    });
</script>