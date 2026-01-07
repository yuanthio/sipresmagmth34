<?php
session_start();
if (isset($_POST['edit_mahasiswa'])) {
    include '../../config/database.php';

    // Fungsi untuk membersihkan dan memvalidasi data
    function input($data)
    {
        if (isset($data)) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
        return null;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Mulai transaksi database
        mysqli_query($kon, "START TRANSACTION");

        // Mengambil data mahasiswa yang akan di-update
        $id_mahasiswa = input($_POST["id_mahasiswa"]);
        $nama = input($_POST["nama"]);
        $universitas = input($_POST["universitas"]);
        $jurusan = input($_POST["jurusan"]);
        $nim = input($_POST["nim"]);
        $mulai_magang = input($_POST["mulai_magang"]);
        $akhir_magang = input($_POST["akhir_magang"]);
        $unit_kerja = input($_POST["unit_kerja"]);
        $mentor = input($_POST["mentor"]);
        $no_telp = input($_POST["no_telp"]);
        $email = input($_POST["email"]);
        $alamat = input($_POST["alamat"]);

        // Mengambil nama file foto saat ini dan data foto baru
        $foto_saat_ini = input($_POST['foto_saat_ini']);
        $foto_baru = $_FILES['foto_baru']['name'];
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'gif');
        $x = explode('.', $foto_baru);
        $ekstensi_foto = strtolower(end($x));
        $ukuran = $_FILES['foto_baru']['size'];
        $file_tmp = $_FILES['foto_baru']['tmp_name'];

        if (!empty($foto_baru)) {
            if (in_array($ekstensi_foto, $ekstensi_diperbolehkan)) {
                // Simpan foto baru
                move_uploaded_file($file_tmp, 'foto/' . $foto_baru);

                if ($foto_saat_ini != 'foto_default.png') {
                    unlink("foto/" . $foto_saat_ini);
                }

                $sql = "UPDATE tbl_mahasiswa SET
                    nama='$nama',
                    universitas='$universitas',
                    jurusan='$jurusan',
                    nim='$nim',
                    mulai_magang='$mulai_magang',
                    akhir_magang='$akhir_magang',
                    unit_kerja='$unit_kerja',
                    mentor='$mentor',
                    alamat='$alamat',
                    no_telp='$no_telp',
                    email='$email',
                    foto='$foto_baru'
                    WHERE id_mahasiswa=$id_mahasiswa";
            }
        } else {
            $sql = "UPDATE tbl_mahasiswa SET
                nama='$nama',
                universitas='$universitas',
                jurusan='$jurusan',
                nim='$nim',
                mulai_magang='$mulai_magang',
                akhir_magang='$akhir_magang',
                unit_kerja='$unit_kerja',
                mentor='$mentor',
                alamat='$alamat',
                no_telp='$no_telp',
                email='$email'
                WHERE id_mahasiswa=$id_mahasiswa";
        }

        // Eksekusi query update mahasiswa
        $edit_mahasiswa = mysqli_query($kon, $sql);

        // Mendapatkan level dan kode_pengguna dari session
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $level = $_SESSION['level'];

        // Mendapatkan nama admin dari tabel admin
        $query_admin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
        $data_admin = mysqli_fetch_array($query_admin);
        $nama_admin = $data_admin['nama'];

        // Mendapatkan tanggal sekarang dengan format "Y-m-d H:i:s" (timezone WIB)
        date_default_timezone_set('Asia/Jakarta');
        $tanggal_sekarang = date("Y-m-d H:i:s");

        // Menentukan status aktivitas
        if ($edit_mahasiswa) {
            mysqli_query($kon, "COMMIT");
            $status = "berhasil";
            header("Location:../../index.php?page=mahasiswa&edit=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            $status = "gagal";
            header("Location:../../index.php?page=mahasiswa&edit=gagal");
        }

        // Menyimpan aktivitas ke tabel log
        $aktivitas = "Edit data mahasiswa ($nama)";
        $log_sql = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                    VALUES ('$tanggal_sekarang', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $log_sql);
    }
}
?>

<?php
include '../../config/database.php';
$id_mahasiswa = $_POST["id_mahasiswa"];
$sql = "select * from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa limit 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
?>

<form action="apps/mahasiswa/edit.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="hidden" name="id_mahasiswa" class="form-control"
                    value="<?php echo $data['id_mahasiswa']; ?>">
                <input type="text" name="nama" class="form-control" value="<?php echo $data['nama']; ?>"
                    placeholder="Masukan Nama Mahasiswa" required>

            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Universitas / Sekolah :</label>
                <input type="text" name="universitas" class="form-control" value="<?php echo $data['universitas']; ?>"
                    placeholder="Masukan Nama Universitas" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Jurusan :</label>
                <input type="text" name="jurusan" class="form-control" value="<?php echo $data['jurusan']; ?>"
                    placeholder="Masukan Nama Jurusan" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>NIM / NIS :</label>
                <input type="text" name="nim" class="form-control" value="<?php echo $data['nim']; ?>"
                    placeholder="Masukan Nomor Induk Mahasiswa" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Mulai Magang :</label>
                <input type="date" name="mulai_magang" class="form-control" value="<?php echo $data['mulai_magang']; ?>"
                    required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Akhir Magang :</label>
                <input type="date" name="akhir_magang" class="form-control" value="<?php echo $data['akhir_magang']; ?>"
                    required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>No Telp :</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukan No Telp"
                    value="<?php echo $data['no_telp']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" placeholder="Masukan Email"
                    value="<?php echo $data['email']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_kerja">Unit Kerja / Instansi :</label>
                <select name="unit_kerja" class="form-control" id="unit_kerja">
                    <option disabled>Pilih Unit Kerja/Instansi</option>
                    <option value="Subbag SDM" <?php if ($data['unit_kerja'] == 'Subbag SDM')
                        echo 'selected'; ?>>Subbag
                        SDM</option>
                    <option value="Subbag Umum dan TI" <?php if ($data['unit_kerja'] == 'Subbag Umum dan TI')
                        echo 'selected'; ?>>Subbag Umum dan TI</option>
                    <option value="Subbag Humas" <?php if ($data['unit_kerja'] == 'Subbag Humas')
                        echo 'selected'; ?>>
                        Subbag Humas</option>
                    <option value="Subbag TU Kalan" <?php if ($data['unit_kerja'] == 'Subbag TU Kalan')
                        echo 'selected'; ?>>Subbag TU Kalan</option>
                    <option value="Subbag Keuangan" <?php if ($data['unit_kerja'] == 'Subbag Keuangan')
                        echo 'selected'; ?>>Subbag Keuangan</option>
                    <option value="Subbag Hukum" <?php if ($data['unit_kerja'] == 'Subbag Hukum')
                        echo 'selected'; ?>>
                        Subbag Hukum</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="mentor">Mentor :</label>
                <select name="mentor" class="form-control" id="mentor" required>
                    <?php
                    include '../../config/database.php';
                    $queryMentor = "SELECT id_mentor, nama FROM tbl_mentor";
                    $resultMentor = mysqli_query($kon, $queryMentor);

                    // Periksa apakah query berhasil
                    if (!$resultMentor) {
                        die("Query gagal: " . mysqli_error($kon));
                    }

                    while ($dataMentor = mysqli_fetch_assoc($resultMentor)) {
                        // Periksa apakah mentor saat ini cocok dengan mentor yang terkait dengan mahasiswa
                        $selected = ($dataMentor['nama'] == $data['mentor']) ? 'selected' : '';

                        echo "<option value='" . $dataMentor['nama'] . "' $selected>" . $dataMentor['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Alamat :</label>
                <textarea class="form-control" name="alamat" rows="4"
                    id="alamat"><?php echo $data['alamat']; ?></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <div class="col-sm-7">
                    <div id="msg"></div>
                    <label>Upload Foto Baru:</label>
                    <input type="file" name="foto_baru" class="file">
                    <div class="input-group my-3">
                        <input type="text" class="form-control" disabled placeholder="Upload File" id="file">
                        <div class="input-group-append">
                            <button type="button" id="pilih_foto" class="browse btn btn-info" style="margin-top: 10px;">
                                <i class="fa fa-search"></i> Pilih Foto
                            </button>
                            <button id="hapus_foto"
                                style="margin-top: 10px; <?php echo ($data['foto'] == 'foto_default.png') ? 'display: none;' : ''; ?>"
                                class="btn btn-danger btn-circle"><i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-sm-5">
                    <label>Foto :</label>
                    <div style="width: 100%; text-align: center;">
                        <img src="apps/mahasiswa/foto/<?php echo $data['foto']; ?>" id="preview" class="img-thumbnail"
                            style="width: 130px; height: 130px; object-fit: cover;">
                    </div>
                    <input type="hidden" name="foto_saat_ini" value="<?php echo $data['foto']; ?>"
                        class="form-control" />
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_mahasiswa" id="Submit" class="btn btn-warning"><i
                        class="fa fa-edit"></i> Update</button>
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
    $(document).on("click", "#pilih_foto", function () {
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

        // Tampilkan tombol hapus jika ada file yang dipilih
        $("#hapus_foto").show();
    });

    // Event listener untuk tombol hapus
    $(document).on("click", "#hapus_foto", function (e) {
        e.preventDefault(); // Mencegah pengiriman form saat tombol hapus diklik
        document.getElementById("preview").src = "apps/mahasiswa/foto/foto_default.png";
        $("input[name='foto_saat_ini']").val("foto_default.png");
        $("#file").val("");
        $("input[type='file']").val(null);
        $(this).hide();
    });
</script>

<script>
    $(document).ready(function () {
        $('#unit_kerja').change(function () {
            var unitKerja = $(this).val();

            // Mengambil data mentor berdasarkan unit kerja yang dipilih
            $.ajax({
                url: 'apps/mahasiswa/get_mentor_edit.php',
                type: 'POST',
                data: { unit_kerja: unitKerja },
                dataType: 'json',
                success: function (response) {
                    // Kosongkan opsi mentor
                    $('#mentor').empty();

                    // Tambahkan opsi mentor yang sesuai
                    $.each(response, function (key, value) {
                        var selected = (value.nama === '<?php echo $data['mentor']; ?>') ? 'selected' : '';
                        $('#mentor').append('<option value="' + value.nama + '" ' + selected + '>' + value.nama + '</option>');
                    });
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#mentor').change(function () {
            var mentorNama = $(this).val();

            // Mengambil data unit kerja dari mentor yang dipilih
            $.ajax({
                url: 'apps/mahasiswa/get_unit_kerja_edit.php',
                type: 'POST',
                data: { mentor_nama: mentorNama },
                dataType: 'json',
                success: function (response) {
                    if (response.unit_kerja) {
                        // Set nilai unit kerja berdasarkan data yang diterima
                        $('#unit_kerja').val(response.unit_kerja);
                    }
                }
            });
        });
    });
</script>